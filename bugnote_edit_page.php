<?php
	require_once( 'core.php' );
	require_once( 'bug_api.php' );
	require_once( 'bugnote_api.php' );
	require_once( 'string_api.php' );
	require_once( 'current_user_api.php' );

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

	if ( ( $t_user_id != $t_reporter_id ) ||
	 	( OFF == config_get( 'bugnote_allow_user_edit_delete' ) ) ) {
		access_ensure_bugnote_level( config_get( 'update_bugnote_threshold' ), $f_bugnote_id );
	}

	# Check if the bug is readonly
	if ( bug_is_readonly( $t_bug_id ) ) {
		error_parameters( $t_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	#$t_bugnote_text = string_textarea( bugnote_recup_field( $f_bugnote_id,'note' ) );

	# No need to gather the extra information if not used
	if ( config_get('time_tracking_enabled') &&
		access_has_bug_level( config_get( 'time_tracking_edit_threshold' ), $t_bug_id ) ) {
		$t_time_tracking = bugnote_get_field( $f_bugnote_id, "time_tracking" );
		$t_time_tracking = db_minutes_to_hhmm( $t_time_tracking );
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = string_get_bug_view_url( $t_bug_id );

	html_page_top( bug_format_summary( $t_bug_id, SUMMARY_CAPTION ) );

	?>
<form class="appnitro" method="post" action="bugnote_update.php">
<?php echo form_security_field( 'bugnote_update' ) ?>
<ul class="partb">
	<div class="form_description">
			<input type="hidden" name="bugnote_id" value="<?php echo $f_bugnote_id ?>" />
			<h2><?php echo lang_get( 'edit_bugnote_title' ) ?></h2>
	</div>
<?php
Cedit ('dur_violence','input','radio',$f_bugnote_id);

Cedit ('lieu_violence','input','checkbox',$f_bugnote_id);
Cedit ('nat_vio_cpl','input','checkbox',$f_bugnote_id);
Cedit ('enf_temoin_cpl','input','radio',$f_bugnote_id);
Cedit ('enf_vio_cpl','input','radio',$f_bugnote_id);
Cedit ('comm_cpl','input','',$f_bugnote_id);
Cedit ('cond_trav','input','checkbox',$f_bugnote_id);
Cedit ('isol_trav','input','checkbox',$f_bugnote_id);
Cedit ('dign_trav','input','checkbox',$f_bugnote_id);
Cedit ('vio_trav','input','checkbox',$f_bugnote_id);
Cedit ('juri_trav','input','checkbox',$f_bugnote_id);
Cedit ('domaine','input','radio',$f_bugnote_id);
Cedit ('nom_vio','input','',$f_bugnote_id);
Cedit ('prenom_vio','input','',$f_bugnote_id);
Cedit ('age_vio','input','',$f_bugnote_id);
Cedit ('sexe_vio','input','radio',$f_bugnote_id);
Cedit ('adresse_vio','input','radio',$f_bugnote_id);
Cedit ('emploi_vio','input','',$f_bugnote_id);
Cedit ('ressources_vio','input','',$f_bugnote_id);
Cedit ('secteur_vio','select','',$f_bugnote_id);
Cedit ('csp_vio','select','',$f_bugnote_id);
Cedit ('lien_vict_vio','select','',$f_bugnote_id);
Cedit ('vict_enf_vio','input','radio',$f_bugnote_id);
Cedit ('mere_vict_vio','input','radio',$f_bugnote_id);
Cedit ('pb_vio','input','checkbox',$f_bugnote_id);
Cedit ('comm_vio','textarea','',$f_bugnote_id);
Cedit ('cons_phys','input','checkbox',$f_bugnote_id);
Cedit ('cons_psy','input','checkbox',$f_bugnote_id);
Cedit ('cons_trav','input','checkbox',$f_bugnote_id);
Cedit ('cons_soc','input','checkbox',$f_bugnote_id);
Cedit ('cons_comm','textarea','',$f_bugnote_id);
Cedit ('bes_vio','input','checkbox',$f_bugnote_id);
Cedit ('bes_soc','input','checkbox',$f_bugnote_id);
Cedit ('bes_com','textarea','',$f_bugnote_id);
?>
<?php if ( config_get('time_tracking_enabled') ) { ?>
<?php if ( access_has_bug_level( config_get( 'time_tracking_edit_threshold' ), $t_bug_id ) ) { ?>
<li>
		<b><?php echo lang_get( 'time_tracking') ?> (HH:MM)</b><br />
		<input type="text" name="time_tracking" size="5" value="<?php echo $t_time_tracking ?>" />
</li>
<?php } ?>
<?php } ?>

<?php event_signal( 'EVENT_BUGNOTE_EDIT_FORM', array( $t_bug_id, $f_bugnote_id ) ); ?>

<li class="buttons">
	<p class="asterisq">* <?php echo lang_get( 'required' ) ?></p>
		<input type="submit" value="<?php echo lang_get( 'update_information_button' ) ?>" />
	<div>
		<?php print_bracket_link( $t_redirect_url, lang_get( 'go_back' ) ) ?>
	</div>
</li>
</ul>
</form>

<?php html_page_bottom();
