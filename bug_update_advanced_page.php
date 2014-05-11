<?php
$g_allow_browser_cache = 1;
require_once( 'core.php' );
require_once( 'ajax_api.php' );
require_once( 'bug_api.php' );
require_once( 'custom_field_api.php' );
require_once( 'date_api.php' );
require_once( 'last_visited_api.php' );
require_once( 'projax_api.php' );

$f_bug_id = gpc_get_int( 'bug_id' );

$tpl_bug = bug_get( $f_bug_id, true );

if ( $tpl_bug->project_id != helper_get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $tpl_bug->project_id;
	$tpl_changed_project = true;
} else {
	$tpl_changed_project = false;
}

if ( bug_is_readonly( $f_bug_id ) ) {
	error_parameters( $f_bug_id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );

html_page_top( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );

print_recently_visited();

$t_fields = config_get( 'bug_update_page_fields' );
$t_fields = columns_filter_disabled( $t_fields );

$tpl_bug_id = $f_bug_id;

$t_action_button_position = config_get( 'action_button_position' );

$tpl_top_buttons_enabled = $t_action_button_position == POSITION_TOP || $t_action_button_position == POSITION_BOTH;
$tpl_bottom_buttons_enabled = $t_action_button_position == POSITION_BOTTOM || $t_action_button_position == POSITION_BOTH;

$tpl_show_id = in_array( 'id', $t_fields );
$tpl_show_project = in_array( 'project', $t_fields );
$tpl_show_category = in_array( 'category_id', $t_fields );
$tpl_show_view_state = in_array( 'view_state', $t_fields );
$tpl_view_state = $tpl_show_view_state ? string_display_line( get_enum_element( 'view_state', $tpl_bug->view_state ) ) : '';
$tpl_show_date_submitted = in_array( 'date_submitted', $t_fields );
$tpl_show_last_updated = in_array( 'last_updated', $t_fields );
$tpl_show_reporter = in_array( 'reporter', $t_fields );
$tpl_show_handler = in_array( 'handler', $t_fields );
#$tpl_show_motif_contact = in_array( 'motif_contact', $t_fields );
$tpl_show_sexe = in_array( 'sexe', $t_fields );
$tpl_show_type_contact = in_array( 'type_contact', $t_fields );
$tpl_show_status = in_array( 'status', $t_fields );
$tpl_show_resolution = in_array( 'resolution', $t_fields );
$tpl_show_projection = in_array( 'projection', $t_fields ) && config_get( 'enable_projection' ) == ON;
$tpl_show_eta = in_array( 'eta', $t_fields ) && config_get( 'enable_eta' ) == ON;
$t_show_profiles = config_get( 'enable_profiles' ) == ON;
$tpl_show_nom = $t_show_profiles && in_array( 'nom', $t_fields );
$tpl_show_nomepouse = $t_show_profiles && in_array( 'nomepouse', $t_fields );
$tpl_show_prenom = $t_show_profiles && in_array( 'prenom', $t_fields );
$tpl_show_telephone = $t_show_profiles && in_array( 'telephone', $t_fields );
$tpl_show_versions = version_should_show_product_version( $tpl_bug->project_id );
$tpl_show_product_version = $tpl_show_versions && in_array( 'product_version', $t_fields );
$tpl_show_product_build = $tpl_show_versions && in_array( 'product_build', $t_fields ) && ( config_get( 'enable_product_build' ) == ON );
$tpl_product_build_attribute = $tpl_show_product_build ? string_attribute( $tpl_bug->build ) : '';
$tpl_show_attachments = in_array( 'attachments', $t_fields ) && file_allow_bug_upload();
$tpl_show_target_version = $tpl_show_versions && in_array( 'target_version', $t_fields ) && access_has_bug_level( config_get( 'roadmap_update_threshold' ), $tpl_bug_id );
$tpl_show_fixed_in_version = $tpl_show_versions && in_array( 'fixed_in_version', $t_fields );
$tpl_show_due_date = in_array( 'due_date', $t_fields ) && access_has_bug_level( config_get( 'due_date_view_threshold' ), $tpl_bug_id );
$tpl_show_summary = in_array( 'summary', $t_fields );
$tpl_summary_attribute = $tpl_show_summary ? string_attribute( $tpl_bug->summary ) : '';
/*$tpl_show_description = in_array( 'description', $t_fields );
$tpl_description_textarea = $tpl_show_description ? string_textarea( $tpl_bug->description ) : '';
$tpl_show_date_nvvenue = in_array( 'date_nvvenue', $t_fields );
$tpl_date_nvvenue_textarea = $tpl_show_date_nvvenue ? string_textarea( $tpl_bug->date_nvvenue ) : '';
$tpl_show_motif_nvvenue = in_array( 'motif_nvvenue', $t_fields );
$tpl_motif_nvvenue_textarea = $tpl_show_motif_nvvenue ? string_textarea( $tpl_bug->motif_nvvenue ) : '';*/
$tpl_handler_name = string_display_line( user_get_name( $tpl_bug->handler_id ) );

$tpl_can_change_view_state = $tpl_show_view_state && access_has_project_level( config_get( 'change_view_status_threshold' ) );
	include( 'nvxchamps_inc.php' );
if ( $tpl_show_product_version ) {
	$tpl_product_version_released_mask = VERSION_RELEASED;

	if ( access_has_project_level( config_get( 'report_issues_for_unreleased_versions_threshold' ) ) ) {
		$tpl_product_version_released_mask = VERSION_ALL;
	}
}

$tpl_formatted_bug_id = $tpl_show_id ? bug_format_id( $f_bug_id ) : '';
$tpl_project_name = $tpl_show_project ? string_display_line( project_get_name( $tpl_bug->project_id ) ) : '';

include('fariane_inc.php');
include('salleattente_inc.php');

?>
<form class="appnitro" name="update_bug_form" method="post" <?php if ( $tpl_show_attachments ) { echo 'enctype="multipart/form-data"'; } ?>action="bug_update.php">
<?php echo form_security_field( 'bug_update' );?>
<ul class="part0">
	<div class="form_description">
			<input type="hidden" name="bug_id" value="<?php echo $tpl_bug_id ?>"/>
			<input type="hidden" name="update_mode" value="1" />
			<h2><?php echo lang_get( 'updating_bug_advanced_title' );?></h2>
	
		<?php print_bracket_link( string_get_bug_view_url( $tpl_bug_id ), lang_get( 'back_to_bug_link' ) ); ?>
	</div>

<?php
/*Submit Button
if ( $tpl_top_buttons_enabled ) {
        echo '<li><div class="center" colspan="6">';
        echo '<input ', helper_get_tab_index(), ' type="submit"  value="', lang_get( 'update_information_button' ), '" />';
        echo '</div></li>';
}*/

event_signal( 'EVENT_UPDATE_BUG_FORM_TOP', array( $tpl_bug_id, true ) );

if ( $tpl_show_id || $tpl_show_project || $tpl_show_category || $tpl_show_view_state || $tpl_show_date_submitted | $tpl_show_last_updated ) {
	#
	# Titles for Bug Id, Project Name, Category, View State, Date Submitted, Last Updated
	#

	echo '<fieldset><legend>PRISE EN CHARGE</legend><span class="inline50"><li><label class="description">', $tpl_show_id ? lang_get( 'id' ) : '', '</label><p>'. $tpl_formatted_bug_id.'</p></li>';
	echo '<li><label class="description">', $tpl_show_date_submitted ? lang_get( 'date_submitted' ) : '', '</label><p>';
	echo $tpl_show_date_submitted ? date( config_get( 'normal_date_format' ), $tpl_bug->date_submitted ) : ''.'</p></li></span><span class="inline50">';
	echo '<li><label class="description">', $tpl_show_last_updated ? lang_get( 'last_update' ) : '', '</label><p>';
	echo $tpl_show_last_updated ? date( config_get( 'normal_date_format' ), $tpl_bug->last_updated ) : ''.'</p></li>';
	
?>
<li <?php echo helper_alternate_class() ?>>
	<label class="description"><?php echo lang_get( 'assign_to' ) ?></label>
		<div>
			<select <?php echo helper_get_tab_index() ?> name="handler_id">
				<?php print_assign_to_option_list( $tpl_bug->handler_id ) ?>
			</select>
			<!--<script type="text/javascript">
$(document).ready(function(){

$('select[name$="handler_id"]').children().filter(function(index){
return $(this).attr('value')== <?php #$tpl_bug->handler_id ?>;
}).attr('selected','selected');

});
			</script>-->
		</div>
</li>
</span></fieldset>
	<?php
	/*echo '<li><label class="description">', $tpl_show_project ? lang_get( 'email_project' ) : '', '</label><p>'..'</p>';
	echo '<li><labelclass="description">', $tpl_show_category ? lang_get( 'category' ) : '', '</label><p>'..'</p>';
	echo '<li><label class="description">', $tpl_show_view_state ? lang_get( 'view_status' ) : '', '</label><p>'..'</p>';*/

	/*echo '<td>', $tpl_project_name, '</td>';

	# Category
	echo '<td>';

	if ( $tpl_show_category ) {
		echo '<select ', helper_get_tab_index(), ' name="category_id">';
		print_category_option_list( $tpl_bug->category_id, $tpl_bug->project_id );
		echo '</select>';
	}

	echo '</td>';

	# View State
	echo '<td>';

	if ( $tpl_can_change_view_state ) {
		echo '<select ', helper_get_tab_index(), ' name="view_state">';
		print_enum_string_option_list( 'view_state', $tpl_bug->view_state);
		echo '</select>';
	} else if ( $tpl_show_view_state ) {
		echo $tpl_view_state;
	}

	echo '</td>';
	echo '</tr>';

	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';*/
}

echo '<fieldset ><legend>IDENTIT&Eacute;</legend><span class="inline33">';
#nom
Cedit ('nom','input','','');

#nomepouse
Cedit ('nomepouse','input','','');

#prenom
Cedit ('prenom','input','','');

echo '</span><span class="inline33">';

/*if ( $tpl_show_reporter || $tpl_show_handler ) {
	echo '<li ', helper_alternate_class(), '><label class="description">',lang_get( 'reporter' ), '</label>';

		if ( ON == config_get( 'use_javascript' ) ) {
			$t_username = prepare_user_name( $tpl_bug->reporter_id );
			echo ajax_click_to_edit( $t_username, 'reporter_id', 'entrypoint=issue_reporter_combobox&issue_id=' . $tpl_bug_id );
		} else {
			echo '<select ', helper_get_tab_index(), ' name="reporter_id">';
			print_reporter_option_list( $tpl_bug->reporter_id, $tpl_bug->project_id );
			echo '</select>';
		}
	echo '</li><li ', helper_alternate_class(), '><label class="description">',lang_get( 'handler_id' ), '</label><div>';
	if ( access_has_project_level( config_get( 'update_bug_assign_threshold', config_get( 'update_bug_threshold' ) ) ) ) {
		echo '<select ', helper_get_tab_index(), ' name="handler_id">';
		echo '<option value="0"></option>';
		print_assign_to_option_list( $tpl_bug->handler_id, $tpl_bug->project_id );
		echo '</select></div></li>';
	} else {
		echo $tpl_handler_name;
	}
}

if (  $tpl_show_due_date ) {

	if ( $tpl_show_due_date ) {
		# Due Date
		echo '<td class="description">', lang_get( 'due_date' ), '</td>';

		if ( bug_is_overdue( $tpl_bug_id ) ) {
			echo '<td class="overdue">';
		} else {
			echo '<td>';
		}

		if ( access_has_bug_level( config_get( 'due_date_update_threshold' ), $tpl_bug_id ) ) {
			$t_date_to_display = '';

			if ( !date_is_null( $tpl_bug->due_date ) ) {
				$t_date_to_display = date( config_get( 'calendar_date_format' ), $tpl_bug->due_date );
			}

			echo '<input ', helper_get_tab_index(), ' type="text" id="due_date" name="due_date" size="20" maxlength="16" value="', $t_date_to_display, '">';
			date_print_calendar();
			date_finish_calendar( 'due_date', 'trigger');
		} else {
			if ( !date_is_null( $tpl_bug->due_date ) ) {
				echo date( config_get( 'short_date_format' ), $tpl_bug->due_date  );
			}
		}

		echo '</td>';
	} else {
		$t_spacer += 2;
	}

}*/



if ( $tpl_show_sexe || $tpl_show_type_contact ) {
#Cedit ('type_contact', 'select','','');
Cedit ('sexe', 'select','','');
Cedit ('telephone', 'input','','');

echo '</fieldset></span>';

#Cedit ('motif_contact', 'select','','');
}

#Cedit ('description', 'textarea','','');


// File Upload (if enabled)
	if ( $tpl_show_attachments ) {
		$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
		$t_file_upload_max_num = max( 1, config_get( 'file_upload_max_num' ) );
?>
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php 
			echo lang_get( $t_file_upload_max_num == 1 ? 'upload_file' : 'upload_files' );
			echo ' - (' . lang_get( 'max_file_size' ) . ': ' . number_format( $t_max_file_size/1000 ) . 'k)'
			?>
		</label>
		<div>
			<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
<?php
		// Display multiple file upload fields
		for( $i =0; $i < $t_file_upload_max_num; $i++ ) {
?>
			<input <?php echo helper_get_tab_index() ?> id="ufile[]" name="ufile[]" type="file" size="50" />
<?php
			if( $t_file_upload_max_num > 1 ) {
				echo '<br />';
			}
		}
	}
	#File Upload ends here?>
</div>
	</li>
	</ul>
<!--DOSSIER-->
<ul class="parta">
<div class="form_description"><h2>Modifier Dossier</h2></div>

<?php

	
Cedit ('attente','select','','');

echo'<fieldset ><legend>&Eacute;TAT CIVIL</legend>';

#date_naissance
echo '<li '.helper_alternate_class().'><label class="description">';
echo print_documentation_link( 'date_naissance' ).'</label>
<div><input name="date_naissance" value="'.date( config_get( 'short_date_format' ),$tpl_bug->date_naissance).'"/></div></li>';

#lieu_naissance
Cedit ('lieu_naissance','input','','');

#pays_naissance
Cedit ('pays_naissance','input','','');

#nationalite
Cedit ('nationalite','select','','');

#rue
Cedit ('rue','input','','');

#adrs_suite
Cedit ('adrs_suite','input','','');

#ville
Cedit ('ville','input','','');

#region
#Cedit ('region','input','','');

#code_postal
Cedit ('code_postal','input','','');
?>
<!-- pays-->
	<li <?php echo helper_alternate_class() ?>>
		<label class="description">
			<?php print_documentation_link( 'pays' ) ?>
		</label>
		<span>
			<select <?php echo helper_get_tab_index() ?> name="pays">
				<?php print_enum_string_option_list( 'nationalite', $tpl_bug->pays ) ?>
			</select>
		</span>
	</li>
<?php
#autre_hebergt
Cedit ('autre_hebergt','textarea','','');

echo'</fieldset>';
#email
Cedit ('email','input','text','');

#tel_domicile
Cedit ('tel_domicile','input','text','');

#tel_travail
Cedit ('tel_travail','input','text','');

#orig_orient
Cedit ('orig_orient','input','checkbox','');

#connu_assoc
Cedit ('connu_assoc','input','radio','');

echo '<fieldset class="group-logement-venue"><legend>LOGEMENT AU MOMENT DE LA VENUE (VIOLENCES AUTRES QUE TRAVAIL)</legend>';
#nom_logem
Cedit ('nom_logem','input','radio','');

#type_logem
Cedit ('type_logem','select','','');

#complem_info
Cedit ('complem_info','textarea','','');
echo '</fieldset>';

echo'<fieldset class="group-situation-matrimoniale"><legend>SITUATION MATRIMONIALE</legend>';
/* matrim_ant*/
Cedit ('matrim_ant','select','','');

/* matrim*/
Cedit ('matrim','select','','');

/* nb_enfant*/
Cedit ('nb_enfant','input','','');

/* nb_enf_charge*/
Cedit ('nb_enf_charge','input','','');

/* sxage_enf*/
echo '<li id="sxage"><label class="description">Sexe et &acirc;ge des enfants</label>';
$sxagenf = bug_get_field( $f_bug_id, 'sxage_enf' );
$chqenf = explode(',',$sxagenf);

	foreach ($chqenf as $enfant){
		
		if (strlen($enfant)>11){
			
			$data = explode('+',$enfant);
			
			$edad = explode('/',$data[1]);
			
			echo '<span id="enf"><p>ENFANT '.(intval($data[0])+1).'</p>
			
			<label>Sexe</label>
			
			<select id="sexe">';
			
			if ($data[2] == 'Femme') {
				echo '<option value="Femme" selected>Femme</option><option value="Homme">Homme</option>';
			} else {
				echo '<option value="Homme" selected>Homme</option><option value="Femme">Femme</option>';
			}
			
			echo'</select>

			
			<label>N&eacute;(e) le</label>
			<input value="'.intval($edad[0]).'/'.intval($edad[1]).'/'.intval($edad[2]).'"/></span>';
			
		}
	}

echo'<input type="hidden" name="sxage_enf">
	</li>
	<span><a onclick="Enf();">Ajouter enfant</a></span>';

/* enf_pere*/
Cedit ('enf_pere','input','radio','');

/* enf_recon*/
Cedit ('enf_recon','input','radio','');
echo '</fieldset><fieldset class="group-situation-entree"><legend>SITUATION &Agrave; L&apos;ENTR&Eacute;E</legend>';
/* sit_prof*/
Cedit ('sit_prof','input','radio','');

/* niv_scolr*/
Cedit ('niv_scolr','select','','');

/* emploi*/
Cedit ('emploi','input','','');

/* entreprise*/
Cedit ('entreprise','input','','');

/* cddcdi*/
Cedit ('cddcdi','input','radio','');

/* typ_contrat*/
Cedit ('typ_contrat','select','','');

/* secteur_activ*/
Cedit ('secteur_activ','select','','');

/* type_ent*/
Cedit ('type_ent','input','radio','');

/* csp*/
Cedit ('csp','select','','');

/* ressources*/
Cedit ('ressources','input','','');

/* prestations*/
Cedit ('prestations','input','checkbox','');

echo '</fieldset><fieldset><legend>HISTOIRE DE LA VICTIME</legend>';

/* mere_victime*/
Cedit ('mere_victime','input','radio','');

/* plaintes*/
Cedit ('plaintes','input','radio','');

/* nb_plaintes*/
Cedit ('nb_plaintes','input','','');

/* nb_plaintesko*/
Cedit ('nb_plaintesko','input','','');

/* plaintes_gend*/
Cedit ('plaintes_gend','input','radio','');

/* nb_plaintesgend*/
Cedit ('nb_plaintesgend','input','','');

/* nb_plainteskogend*/
Cedit ('nb_plainteskogend','input','','');

/* maincour*/
Cedit ('maincour','input','radio','');

/* nb_maincour*/
Cedit ('nb_maincour','input','','');

/* suite_plaintes*/
Cedit ('suite_plaintes','textarea','','');

/* deja_vict*/
Cedit ('deja_vict','input','radio','');

/* vict_enfce*/
Cedit ('vict_enfce','input','radio','');

/* contacte*/
Cedit ('contacte','input','radio','');

/* note_general*/
Cedit ('note_general','textarea','','');
echo '</fieldset>';


/*
#
# Status, Resolution
#

if ( $tpl_show_status || $tpl_show_resolution ) {
	echo '<tr ', helper_alternate_class(), '>';

	$t_spacer = 2;

	if ( $tpl_show_status ) {
		# Status
		echo '<td class="description">', lang_get( 'status' ), '</td>';
		echo '<td bgcolor="', get_status_color( $tpl_bug->status ), '">';
		print_status_option_list( 'status', $tpl_bug->status,
			access_can_close_bug( $tpl_bug ),
			$tpl_bug->project_id
		);
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	if ( $tpl_show_resolution ) {
		# Resolution
		echo '<td class="description">', lang_get( 'resolution' ), '</td>';
		echo '<td><select ', helper_get_tab_index(), ' name="resolution">';
		print_enum_string_option_list( "resolution", $tpl_bug->resolution );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	if ( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Projection, ETA
#

if ( $tpl_show_projection || $tpl_show_eta ) {
	echo '<tr ', helper_alternate_class(), '>';

	$t_spacer = 2;

	if ( $tpl_show_projection ) {
		# Projection
		echo '<td class="description">';
		echo lang_get( 'projection' );
		echo '</td>';
		echo '<td><select name="projection">';
		print_enum_string_option_list( 'projection', $tpl_bug->projection );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# ETA
	if ( $tpl_show_eta ) {
		echo '<td class="description">', lang_get( 'eta' ), '</td>';

		echo '<td>', '<select ', helper_get_tab_index(), ' name="eta">';
		print_enum_string_option_list( 'eta', $tpl_bug->eta );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}


#
# Product Version, Product Build
#

if ( $tpl_show_product_version || $tpl_show_product_build ) {
	echo '<tr ', helper_alternate_class(), '>';

	$t_spacer = 2;

	# Product Version  or Product Build, if version is suppressed
	if ( $tpl_show_product_version ) {
		echo '<td class="description">', lang_get( 'product_version' ), '</td>';
		echo '<td>', '<select ', helper_get_tab_index(), ' name="version">';
		print_version_option_list( $tpl_bug->version, $tpl_bug->project_id, $tpl_product_version_released_mask );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	if ( $tpl_show_product_build ) {
		echo '<td class="description">', lang_get( 'product_build' ), '</td>';
		echo '<td>';
		echo '<input type="text" name="build" size="16" maxlength="32" value="', $tpl_product_build_attribute, '" />';
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}

#
# Target Versiom, Fixed in Version
#

if ( $tpl_show_target_version || $tpl_show_fixed_in_version ) {
	echo '<tr ', helper_alternate_class(), '>';

	$t_spacer = 2;

	# Target Version
	if ( $tpl_show_target_version ) {
		echo '<td class="description">', lang_get( 'target_version' ), '</td>';
		echo '<td><select ', helper_get_tab_index(), ' name="target_version">';
		print_version_option_list( $tpl_bug->target_version, $tpl_bug->project_id, VERSION_ALL );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# Fixed in Version
	if ( $tpl_show_fixed_in_version ) {
		echo '<td class="description">';
		echo lang_get( 'fixed_in_version' );
		echo '</td>';

		echo '<td>';
		echo '<select ', helper_get_tab_index(), ' name="fixed_in_version">';
		print_version_option_list( $tpl_bug->fixed_in_version, $tpl_bug->project_id, VERSION_ALL );
		echo '</select>';
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}

# Summary
if ( $tpl_show_summary ) {
	echo '<tr ', helper_alternate_class(), '>';
	echo '<td class="description">', lang_get( 'summary' ), '</td>';
	echo '<td colspan="5">', '<input ', helper_get_tab_index(), ' type="text" name="summary" size="105" maxlength="128" value="', $tpl_summary_attribute, '" />';
	echo '</td></tr>';
}

# Description
if ( $tpl_show_description ) {
	echo '<tr ', helper_alternate_class(), '>';
	echo '<td class="description">', lang_get( 'description' ), '</td>';
	echo '<td colspan="5">';
	echo '<textarea ', helper_get_tab_index(), ' cols="72" rows="10" name="description">', $tpl_description_textarea, '</textarea>';
	echo '</td></tr>';
}

# Steps to Reproduce
if ( $tpl_show_motif_nvvenue ) {
	echo '<tr ', helper_alternate_class(), '>';
	echo '<td class="description">', lang_get( 'motif_nvvenue' ), '</td>';
	echo '<td colspan="5">';
	echo '<textarea ', helper_get_tab_index(), ' cols="72" rows="10" name="motif_nvvenue">', $tpl_motif_nvvenue_textarea, '</textarea>';
	echo '</td></tr>';
}*/

# Additional Information
if ( $tpl_show_date_nvvenue ) {
	echo '<tr ', helper_alternate_class(), '>';
	echo '<td class="description">', lang_get( 'date_nvvenue' ), '</td>';
	echo '<td colspan="5">';
	echo '<textarea ', helper_get_tab_index(), ' cols="72" rows="10" name="date_nvvenue">', $tpl_date_nvvenue_textarea, '</textarea>';
	echo '</td></tr>';
}
/*
echo '<tr class="spacer"><td colspan="6"></td></tr>';

# Custom Fields
$t_custom_fields_found = false;
$t_related_custom_field_ids = custom_field_get_linked_ids( $tpl_bug->project_id );

foreach ( $t_related_custom_field_ids as $t_id ) {
	$t_def = custom_field_get_definition( $t_id );
	if ( ( $t_def['display_update'] || $t_def['require_update'] ) && custom_field_has_write_access( $t_id, $tpl_bug_id ) ) {
		$t_custom_fields_found = true;

		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="description">';
		if ( $t_def['require_update'] ) {
			echo '<span class="required">*</span>';
		}

		echo string_display( lang_get_defaulted( $t_def['name'] ) );
		echo '</td><td colspan="5">';
		print_custom_field_input( $t_def, $tpl_bug_id );
		echo '</td></tr>';
	}
} # foreach( $t_related_custom_field_ids as $t_id )

if ( $t_custom_fields_found ) {
	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
} # custom fields found
*/

# Bugnote Text Box
/*echo '<ul>';
echo '<li class="description">', lang_get( 'add_bugnote_title' ), '</li>';
echo '<li colspan="5"><textarea ', helper_get_tab_index(), ' name="bugnote_text" cols="72" rows="10"></textarea></li></ul>';

# Bugnote Private Checkbox (if permitted)
if ( access_has_bug_level( config_get( 'private_bugnote_threshold' ), $tpl_bug_id ) ) {
	echo '<tr ', helper_alternate_class(), '>';
	echo '<td class="description">', lang_get( 'private' ), '</td>';
	echo '<td colspan="5">';

	$t_default_bugnote_view_status = config_get( 'default_bugnote_view_status' );
	if ( access_has_bug_level( config_get( 'set_view_status_threshold' ), $tpl_bug_id ) ) {
		echo '<input ', helper_get_tab_index(), ' type="checkbox" name="private" ', check_checked( config_get( 'default_bugnote_view_status' ), VS_PRIVATE ), ' />';
		echo lang_get( 'private' );
	} else {
		echo get_enum_element( 'view_state', $t_default_bugnote_view_status );
	}

	echo '</td></tr>';
}*/

# Time Tracking (if permitted)
if ( config_get('time_tracking_enabled') ) {
	if ( access_has_bug_level( config_get( 'time_tracking_edit_threshold' ), $tpl_bug_id ) ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="description">', lang_get( 'time_tracking' ), ' (HH:MM)</td>';
		echo '<td colspan="5"><input type="text" name="time_tracking" size="5" value="0:00" /></td></tr>';
	}
}

event_signal( 'EVENT_BUGNOTE_ADD_FORM', array( $tpl_bug_id ) );

# Submit Button
if ( $tpl_bottom_buttons_enabled ) {
       echo '</ul><ul><li class="buttons">';
       echo '<input ', helper_get_tab_index(), ' type="submit" value="Mettre &agrave; jour" />';
       echo '</li></ul>';
}

echo '</form>';
event_signal( 'EVENT_UPDATE_BUG_FORM', array( $tpl_bug_id, true ) );
include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bugnote_view_inc.php' );
html_page_bottom();

last_visited_issue( $tpl_bug_id );
