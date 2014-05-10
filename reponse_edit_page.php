<?php
	require_once( 'core.php' );
	require_once( 'bug_api.php' );
	require_once( 'bugnote_api.php' );
	require_once( 'string_api.php' );
	require_once( 'current_user_api.php' );

	$f_reponse_id = gpc_get_int( 'reponse_id' );
	$f_bugnote_id = gpc_get_int( 'bugnote_id' );
	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );

	$t_bug = bug_get( $t_bug_id, true );
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	# Check if the current user is allowed to edit the bugnote
	$t_user_id = auth_get_current_user_id();
	$t_reporter_id = bugnote_get_field( $f_bugnote_id, 'reporter_id' );

	/*if ( ( $t_user_id != $t_reporter_id ) ||
	 	( OFF == config_get( 'bugnote_allow_user_edit_delete' ) ) ) {
		access_ensure_bugnote_level( config_get( 'update_bugnote_threshold' ), $f_bugnote_id );
	}*/

	# Check if the bug is readonly
	if ( bug_is_readonly( $t_bug_id ) ) {
		error_parameters( $t_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	#$t_bugnote_text = string_textarea( bugnote_recup_field( $f_bugnote_id,'note' ) );

	# No need to gather the extra information if not used
	/*if ( config_get('time_tracking_enabled') &&
		access_has_bug_level( config_get( 'time_tracking_edit_threshold' ), $t_bug_id ) ) {
		$t_time_tracking = bugnote_get_field( $f_bugnote_id, "time_tracking" );
		$t_time_tracking = db_minutes_to_hhmm( $t_time_tracking );
	}*/

	# Determine which view page to redirect back to.
	$t_redirect_url = string_get_bug_view_url( $t_bug_id );

	html_page_top( bug_format_summary( $t_bug_id, SUMMARY_CAPTION ) );

	?>
<ul class="partc">
	<form class="appnitro" name="reponse_update_form" method="post" action="reponse_update.php">
<?php echo form_security_field( 'reponse_update_form' ) ?>
<input type="hidden" name="reponse_id" value="<?php echo $f_reponse_id ?>"/>
<input type="hidden" name="bug_id" value="<?php echo $t_bug_id ?>"/>
<div class="form_description"><h2>Modifier une r&eacute;ponse</h2></div>
<fieldset><legend>PRISE EN CHARGE</legend>
<span class="inline33">

	<li <?php echo helper_alternate_class() ?>>
			<label class="description">
				<?php print_documentation_link( 'date_rep' ) ?>
			</label>
			<input type="hidden" name="date_rep" value="<?php echo Resp_get('date_rep',$f_reponse_id); ?>"/>
			<p><?php echo date(config_get('normal_date_format'), Resp_get('date_rep',$f_reponse_id)); ?></p>
	</li>
	
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php print_documentation_link( 'reporter_id' ) ?>
		</label>
		<div>
			<input type="hidden" name="reporter_id" value="<?php echo Resp_get('reporter_id',$f_reponse_id); ?>"/>
			<p><?php echo user_get_name( Resp_get('reporter_id',$f_reponse_id) );?></p>
		</div>
	</li>

	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php print_documentation_link( 'handler_id' ) ?>
		</label>
		<div>
			<input type="hidden" name="handler_id" value="<?php echo Resp_get('handler_id',$f_reponse_id); ?>"/>
			<p><?php echo user_get_name( Resp_get('handler_id',$f_reponse_id) );?></p>
		</div>
	</li>
	
	</span><span class="inline33">
	
	
<?php


Respedit ('type_contact_rep','select','',$f_reponse_id);

Respedit ('accompagn','select','',$f_reponse_id);

echo '</span></fieldset><fieldset><legend>AIDE SOCIALE ET FINANCI&Egrave;RE</legend><span class="inline33">';

Respedit ('aide_soc_fin', 'input', 'checkbox',$f_reponse_id);
Respedit ('aide_alim', 'input', 'checkbox',$f_reponse_id);
Respedit ('demarch', 'input', 'checkbox',$f_reponse_id);

echo '</span></fieldset><span class="inline33">';
Respedit ('sante', 'input', 'checkbox',$f_reponse_id);
Respedit ('assis_jur', 'input', 'checkbox',$f_reponse_id);
Respedit ('heberg', 'input', 'checkbox',$f_reponse_id);

echo '</span><span class="inline33">';

Respedit ('aide_enf', 'input', 'checkbox',$f_reponse_id);
Respedit ('aide_prof', 'input', 'checkbox',$f_reponse_id);
echo '</span>';
#autres démarches
?>
<li <?php echo helper_alternate_class() ?>>
	<label class="description"><?php print_documentation_link( 'autre_demarch' ) ?></label>
		<div id="autdemarch">
<?php 	$z = explode("@@", Resp_get('autre_demarch', $f_reponse_id));
		foreach( $z as $a ) { if ( strlen($a)>0 ){
		echo '<input id="demarche" value ="'.$a.'"/>'; } };
?>
			<input type="text" id="demarche"/>
		</div>
		<a onclick="newdemarch();">Ajouter un champ</a>
	<input type="hidden" name="autre_demarch"/>
</li>
<?php
Respedit ('temps', 'select', '',$f_reponse_id);
Respedit ('rep_comm', 'textarea', '',$f_reponse_id);

echo '<li class="buttons"><p class="asterisq">*Champ obligatoire</p><input type="submit" value="Enregistrer"/></li></form></ul>';

html_page_bottom();
