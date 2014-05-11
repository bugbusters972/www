<?php
date_default_timezone_set('America/Martinique');

	if ( !defined( 'BUG_VIEW_INC_ALLOW' ) ) {
		access_denied();
	}
	require_once( 'core.php' );

	require_once( 'bug_api.php' );
	require_once( 'custom_field_api.php' );
	require_once( 'file_api.php' );
	require_once( 'date_api.php' );
	require_once( 'relationship_api.php' );
	require_once( 'last_visited_api.php' );
	require_once( 'tag_api.php' );
	require_once( 'contacts_inc.php' );
	
	$f_bug_id = gpc_get_int( 'id' );

	bug_ensure_exists( $f_bug_id );

	$tpl_bug = bug_get( $f_bug_id, true );

	# In case the current project is not the same project of the bug we are
	# viewing, override the current project. This ensures all config_get and other
	# per-project function calls use the project ID of this bug.
	$g_project_override = $tpl_bug->project_id;

	access_ensure_bug_level( VIEWER, $f_bug_id );

	$f_history = gpc_get_bool( 'history', config_get( 'history_default_visible' ) );

	$t_fields = config_get( $tpl_fields_config_option );
	$t_fields = columns_filter_disabled( $t_fields );

	compress_enable();

	if ( $tpl_show_page_header ) {
		html_page_top( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );
		print_recently_visited();
	}

	$t_action_button_position = config_get( 'action_button_position' );

	$t_bugslist = gpc_get_cookie( config_get( 'bug_list_cookie' ), false );

	require_once( 'nvxchamps_inc.php' );
	
	$tpl_show_versions = version_should_show_product_version( $tpl_bug->project_id );
	$tpl_show_product_version = $tpl_show_versions && in_array( 'product_version', $t_fields );
	$tpl_show_fixed_in_version = $tpl_show_versions && in_array( 'fixed_in_version', $t_fields );
	$tpl_show_product_build = $tpl_show_versions && in_array( 'product_build', $t_fields )
		&& ( config_get( 'enable_product_build' ) == ON );
	$tpl_product_build = $tpl_show_product_build ? string_display_line( $tpl_bug->build ) : '';
	$tpl_show_target_version = $tpl_show_versions && in_array( 'target_version', $t_fields )
		&& access_has_bug_level( config_get( 'roadmap_view_threshold' ), $f_bug_id );

	$tpl_product_version_string  = '';
	$tpl_target_version_string   = '';
	$tpl_fixed_in_version_string = '';

	if ( $tpl_show_product_version || $tpl_show_fixed_in_version || $tpl_show_target_version ) {
		$t_version_rows = version_get_all_rows( $tpl_bug->project_id );

		if ( $tpl_show_product_version ) {
			$tpl_product_version_string  = prepare_version_string( $tpl_bug->project_id, version_get_id( $tpl_bug->version, $tpl_bug->project_id ), $t_version_rows );
		}

		if ( $tpl_show_target_version ) {
			$tpl_target_version_string   = prepare_version_string( $tpl_bug->project_id, version_get_id( $tpl_bug->target_version, $tpl_bug->project_id) , $t_version_rows );
		}

		if ( $tpl_show_fixed_in_version ) {
			$tpl_fixed_in_version_string = prepare_version_string( $tpl_bug->project_id, version_get_id( $tpl_bug->fixed_in_version, $tpl_bug->project_id ), $t_version_rows );
		}
	}

	$tpl_product_version_string = string_display_line( $tpl_product_version_string );
	$tpl_target_version_string = string_display_line( $tpl_target_version_string );
	$tpl_fixed_in_version_string = string_display_line( $tpl_fixed_in_version_string );

	$tpl_bug_id = $f_bug_id;
	$tpl_form_title = lang_get( 'bug_view_title' );
	$tpl_wiki_link = config_get_global( 'wiki_enable' ) == ON ? 'wiki.php?id=' . $f_bug_id : '';

	if ( access_has_bug_level( config_get( 'view_history_threshold' ), $f_bug_id ) ) {
		$tpl_history_link = "view.php?id=$f_bug_id&history=1#history";
	} else {
		$tpl_history_link = '';
	}

	$tpl_show_reminder_link = !current_user_is_anonymous() && !bug_is_readonly( $f_bug_id ) &&
		  access_has_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id );
	$tpl_bug_reminder_link = 'bug_reminder_page.php?bug_id=' . $f_bug_id;

	$tpl_print_link = 'print_bug_page.php?bug_id=' . $f_bug_id;

	$tpl_top_buttons_enabled = !$tpl_force_readonly && ( $t_action_button_position == POSITION_TOP || $t_action_button_position == POSITION_BOTH );
	$tpl_bottom_buttons_enabled = !$tpl_force_readonly && ( $t_action_button_position == POSITION_BOTTOM || $t_action_button_position == POSITION_BOTH );

	$tpl_show_project = in_array( 'project', $t_fields );
	$tpl_project_name = $tpl_show_project ? string_display_line( project_get_name( $tpl_bug->project_id ) ): '';
	$tpl_show_id = in_array( 'id', $t_fields );
	$tpl_formatted_bug_id = $tpl_show_id ? string_display_line( bug_format_id( $f_bug_id ) ) : '';

	$tpl_show_date_submitted = in_array( 'date_submitted', $t_fields );
	$tpl_date_submitted = $tpl_show_date_submitted ? date( config_get( 'normal_date_format' ), (int)$tpl_bug->date_submitted ) : '';

	$tpl_show_last_updated = in_array( 'last_updated', $t_fields );
	$tpl_last_updated = $tpl_show_last_updated ? date( config_get( 'normal_date_format' ), (int)$tpl_bug->last_updated ) : '';

	$tpl_show_tags = in_array( 'tags', $t_fields ) && access_has_global_level( config_get( 'tag_view_threshold' ) );

	$tpl_bug_overdue = bug_is_overdue( $f_bug_id );

	$tpl_show_view_state = in_array( 'view_state', $t_fields );
	$tpl_bug_view_state_enum = $tpl_show_view_state ? string_display_line( get_enum_element( 'view_state', $tpl_bug->view_state ) ) : '';

	$tpl_show_due_date = in_array( 'due_date', $t_fields ) && access_has_bug_level( config_get( 'due_date_view_threshold' ), $f_bug_id );

	if ( $tpl_show_due_date ) {
		if ( !date_is_null( $tpl_bug->due_date ) ) {
			$tpl_bug_due_date = date( config_get( 'normal_date_format' ), $tpl_bug->due_date );
		} else {
			$tpl_bug_due_date = '';
		}
	}

	$tpl_show_reporter = in_array( 'reporter', $t_fields );
	$tpl_show_handler = in_array( 'handler', $t_fields ) && access_has_bug_level( config_get( 'view_handler_threshold' ), $f_bug_id );
	$tpl_show_date_nvvenue = !is_blank( $tpl_bug->date_nvvenue ) && in_array( 'date_nvvenue', $t_fields );
	$tpl_show_motif_nvvenue = !is_blank( $tpl_bug->motif_nvvenue ) && in_array( 'motif_nvvenue', $t_fields );
	$tpl_show_monitor_box = !$tpl_force_readonly;
	$tpl_show_relationships_box = !$tpl_force_readonly;
	$tpl_show_upload_form = !$tpl_force_readonly && !bug_is_readonly( $f_bug_id );
	$tpl_show_history = $f_history;
	$tpl_show_profiles = config_get( 'enable_profiles' );
	$tpl_show_nom = $tpl_show_profiles && in_array( 'nom', $t_fields );
	$tpl_show_nomepouse = $tpl_show_profiles && in_array( 'nomepouse', $t_fields );
	$tpl_nom = $tpl_show_nom ? string_display_line( $tpl_bug->nom ) : '';
	$tpl_nomepouse = $tpl_show_nomepouse ? string_display_line( $tpl_bug->nomepouse ) : '';
	$tpl_show_prenom = $tpl_show_profiles && in_array( 'prenom', $t_fields );
	$tpl_prenom = $tpl_show_prenom ? string_display_line( $tpl_bug->prenom ) : '';
	$tpl_show_telephone = $tpl_show_profiles && in_array( 'telephone', $t_fields );
	$tpl_telephone = $tpl_show_telephone ? string_display_line( $tpl_bug->telephone ) : '';
	$tpl_show_projection = in_array( 'projection', $t_fields );
	$tpl_projection = $tpl_show_projection ? string_display_line( get_enum_element( 'projection', $tpl_bug->projection ) ) : '';
	$tpl_show_eta = in_array( 'eta', $t_fields );
	$tpl_eta = $tpl_show_eta ? string_display_line( get_enum_element( 'eta', $tpl_bug->eta ) ) : '';
	$tpl_show_attachments = in_array( 'attachments', $t_fields );
	$tpl_can_attach_tag = $tpl_show_tags && !$tpl_force_readonly && access_has_bug_level( config_get( 'tag_attach_threshold' ), $f_bug_id );
	$tpl_show_category = in_array( 'category_id', $t_fields );
	$tpl_category = $tpl_show_category ? string_display_line( category_full_name( $tpl_bug->category_id ) ) : '';
	#$tpl_show_motif_contact = in_array( 'motif_contact', $t_fields );
	#$tpl_motif_contact = $tpl_show_motif_contact ? string_display_line( get_enum_element( 'motif_contact', $tpl_bug->motif_contact ) ) : '';
	$tpl_show_sexe = in_array( 'sexe', $t_fields );
	$tpl_sexe = $tpl_show_sexe ? string_display_line( get_enum_element( 'sexe', $tpl_bug->sexe ) ) : '';
	$tpl_show_type_contact = in_array( 'type_contact', $t_fields );

	$tpl_show_status = in_array( 'status', $t_fields );
	$tpl_status = $tpl_show_status ? string_display_line( get_enum_element( 'status', $tpl_bug->status ) ) : '';
	$tpl_show_resolution = in_array( 'resolution', $t_fields );
	$tpl_resolution = $tpl_show_resolution ? string_display_line( get_enum_element( 'resolution', $tpl_bug->resolution ) ) : '';
	$tpl_show_summary = in_array( 'summary', $t_fields );
	$tpl_show_description = in_array( 'description', $t_fields );

	$tpl_summary = $tpl_show_summary ? bug_format_summary( $f_bug_id, SUMMARY_FIELD ) : '';
	$tpl_description = $tpl_show_description ? string_display_line( $tpl_bug->description ) : '';
	$tpl_motif_nvvenue = string_display_line( get_enum_element( 'motif_nvvenue', $tpl_bug->motif_nvvenue ));
	$tpl_date_nvvenue = date( config_get( 'normal_date_format' ), (int)$tpl_bug->date_nvvenue );
	$tpl_rapporteur = string_display_line( $tpl_bug->rapporteur );
	$tpl_type_contact = $tpl_show_type_contact ? string_display_line( get_enum_element( 'type_contact', $tpl_bug->type_contact ) ): '';

		
	$tpl_links = event_signal( 'EVENT_MENU_ISSUE', $f_bug_id );

	#
	# Start of Template
	#
	include('fariane_inc.php');
	include('salleattente_inc.php');
	
	echo '<div class="appnitro"><ul class="part0">';

	# Form Title
	echo '<div class="form_description">';

	echo '<h2>'.$tpl_form_title.'</h2>';

	# Jump to Bugnotes
	#print_bracket_link( "#bugnotes", lang_get( 'jump_to_bugnotes' ) );

	# Send Bug Reminder
	if ( $tpl_show_reminder_link ) {
		print_bracket_link( $tpl_bug_reminder_link, lang_get( 'bug_reminder' ) );
	}

	if ( !is_blank( $tpl_wiki_link ) ) {
		print_bracket_link( $tpl_wiki_link, lang_get( 'wiki' ) );
	}

	foreach ( $tpl_links as $t_plugin => $t_hooks ) {
		foreach( $t_hooks as $t_hook ) {
			if ( is_array( $t_hook ) ) {
				foreach( $t_hook as $t_label => $t_href ) {
					if ( is_numeric( $t_label ) ) {
						print_bracket_link_prepared( $t_href );
					} else {
						print_bracket_link( $t_href, $t_label );
					}
				}
			} else {
				print_bracket_link_prepared( $t_hook );
			}
		}
	}


	# Print Bug
	print_bracket_link( $tpl_print_link, lang_get( 'print' ) );
	echo '</div>';


		
echo '<fieldset ><legend>PRISE EN CHARGE</legend><span class="inline33">';
		# Bug ID
echo '<li><label class="description" width="15%">', $tpl_show_id ? lang_get( 'id' ) : '', '</label><p>', $tpl_formatted_bug_id, '</p></li>';
		# Date Submitted
	Affiche('date_submitted');

		# Date Updated
	Affiche('last_updated');
echo '</span><span class="inline33">';

		# Accueillante
		if ( $tpl_show_reporter ) {
			echo '<li><label class="description">', lang_get( 'reporter' ), '</label>';
			echo '<p>';
			print_user_with_subject( $tpl_bug->reporter_id, $tpl_bug_id );
			echo '</p></li>';
		}
		
		# Assigné à
		if ( $tpl_show_handler ) {
			echo '<li><label class="description">', lang_get( 'assigned_to' ), '</label>';
			echo '<p>';
			print_user_with_subject( $tpl_bug->handler_id, $tpl_bug_id );
			echo '</p></li>';
		}
echo '</span></fieldset><fieldset><legend>IDENTIT&Eacute;</legend><span class="inline33">';
	#
	# Handler, Due Date
	#

	/*if ( $tpl_show_handler || $tpl_show_due_date ) {
		echo '<tr ', helper_alternate_class(), '>';

		$t_spacer = 2;

}*/

		# Due Date
		/*if ( $tpl_show_due_date ) {
			echo '<td class="description">', lang_get( 'due_date' ), '</td>';

			if ( $tpl_bug_overdue ) {
				echo '<td class="overdue">', $tpl_bug_due_date, '</td>';
			} else {
				echo '<td>', $tpl_bug_due_date, '</td>';
			}
		} else {
			$t_spacer += 2;
		}*/

		#echo '<td colspan="', $t_spacer, '">&#160;</td>';
		#echo '</tr>';
	

	#
	# motif_contact, sexe, type_contact
	#



	#
	# Status, Resolution
	#

	/*if ( $tpl_show_status || $tpl_show_resolution ) {
		echo '<tr ', helper_alternate_class(), '>';

		$t_spacer = 2;

		# Status
		if ( $tpl_show_status ) {
			echo '<li><label class="description">', lang_get( 'status' ), '</label>';
			echo '<p bgcolor="', get_status_color( $tpl_bug->status ), '">', $tpl_status, '</p></li>';
		} else {
			$t_spacer += 2;
		}

		# Resolution
		if ( $tpl_show_resolution ) {
			echo '<td class="description">', lang_get( 'resolution' ), '</td>';
			echo '<td>', $tpl_resolution, '</td>';
		} else {
			$t_spacer += 2;
		}

		# spacer
		if ( $t_spacer > 0 ) {
			echo '<td colspan="', $t_spacer, '">&#160;</td>';
		}

		echo '</tr>';
	}*/

	#
	# Projection, ETA
	#

	/*if ( $tpl_show_projection || $tpl_show_eta ) {
		echo '<tr ', helper_alternate_class(), '>';

		$t_spacer = 2;

		if ( $tpl_show_projection ) {
			# Projection
			echo '<td class="description">', lang_get( 'projection' ), '</td>';
			echo '<td>', $tpl_projection, '</td>';
		} else {
			$t_spacer += 2;
		}

		# ETA
		if ( $tpl_show_eta ) {
			echo '<td class="description">', lang_get( 'eta' ), '</td>';
			echo '<td>', $tpl_eta, '</td>';
		} else {
			$t_spacer += 2;
		}

		echo '<td colspan="', $t_spacer, '">&#160;</td>';
		echo '</tr>';
	}*/



	if ( $tpl_show_nom ) {
		$t_spacer =0;

			#nom
			Affiche('nom');
		
			#nomepouse
			Affiche('nomepouse');
		
			#prenom
			Affiche('prenom');
echo '</span><span class="inline33">';
		# sexe
		Affiche ('sexe');
		#telephone
		Affiche('telephone');
echo '</span></fieldset>';

/*echo '<li>
<a class="nouvcontact" href="newcontact.php?id='.$tpl_bug_id.'" target="_blank">NOUVEAU CONTACT</a>
</li>';*/

echo '<li>
<a class="nouvcontact" href="javascript:void(0);">NOUVEAU CONTACT</a>
</li>';

get_all_contacts( $tpl_bug_id );

echo '</ul><a name="partie2"></a><ul class="parta">';
echo '<div class="form_description">
	<h2>Dossier</h2>
	</div>';		
		#attente
			Affiche('attente');
echo'<fieldset ><legend>&Eacute;TAT CIVIL</legend>';
			#date_naissance
echo '<li><label class="description">', lang_get( date_naissance ), '</label>';

echo '<p>'.string_display_line( date( config_get( 'short_date_format' ), $tpl_date_naissance ) ).' ('.
age(date( 'Y/m/d',$tpl_date_naissance)).' ans)'.'</p></li>';
			#lieu_naissance
			Affiche('lieu_naissance');

			#pays_naissance
			Affiche('pays_naissance');

			#nationalite
			Affiche('nationalite');
			
			#rue
			Affiche('rue');
			
			#adrs_suite
			Affiche('adrs_suite');
			
			#ville
			Affiche('ville');

			#region
			Affiche('region');

			#code_postal
			Affiche('code_postal');

			#pays
			Affiche('pays');

			#autre_hebergt
			Affiche('autre_hebergt');
echo'</fieldset>';
			#email
			Affiche('email');
	
			#tel_domicile
			Affiche('tel_domicile');
			
			#tel_domicile
			Affiche('tel_domicile');
			
			#tel_travail
			Affiche('tel_travail');
			
			#orig_orient
			Bnexp('orig_orient');
			
			#connu_assoc
			Affiche('connu_assoc');
echo '<fieldset class="group-logement-venue"><legend>LOGEMENT AU MOMENT DE LA VENUE (VIOLENCES AUTRES QUE TRAVAIL)</legend>';			
			#nom_logem
			Affiche('nom_logem');
			
			#type_logem
			Affiche('type_logem');
			
			#complem_info
			Affiche('complem_info');
echo '</fieldset><fieldset class="group-situation-matrimoniale"><legend>SITUATION MATRIMONIALE</legend>';		
			#matrim_ant
			Affiche('matrim_ant');
			
			#matrim
			Affiche('matrim');
			
			#nb_enfant
			Affiche('nb_enfant');
			
			#nb_enf_charge
			Affiche('nb_enf_charge');
			
			#sxage_enf
echo '<li><label class="description">Sexe et &acirc;ge des enfants</label>';
$chqenf = explode(',',$tpl_bug->sxage_enf);

foreach ($chqenf as $enfant){
if (strlen($enfant)>11){
$data = explode('+',$enfant);
$edad = explode('/',$data[1]);
echo '<span id="enf"><p>ENFANT '.(intval($data[0])+1).'</p><p>'.$data[2].', '.(date('Y')-intval($edad[2])).'&nbsp;ans</p>';
}}

echo'</li>';
			
			#enf_pere
			Affiche('enf_pere');
			
			#enf_recon
			Affiche('enf_recon');

echo '</fieldset><fieldset class="group-situation-entree"><legend>SITUATION &Agrave; L&apos;ENTR&Eacute;E</legend>';
			
			#sit_prof
			Affiche('sit_prof');
			
			#niv_scolr
			Affiche('niv_scolr');
			
			#emploi
			Affiche('emploi');

			#entreprise
			Affiche('entreprise');

			#cddcdi
			Affiche('cddcdi');
			
			#typ_contrat
			Affiche('typ_contrat');
			
			#secteur_activ
			Affiche('secteur_activ');
			
			#type_ent
			Affiche('type_ent');
			
			#csp
			Affiche('csp');
			
			#ressources
			Affiche('ressources');
			
			#prestations
			Bnexp('prestations');

echo '</fieldset><fieldset><legend>HISTOIRE DE LA VICTIME</legend>';
			
			#mere_victime
			Affiche('mere_victime');
			
			#plaintes
			Affiche('plaintes');
			
			#nb_plaintes
			Affiche('nb_plaintes');
			
			#nb_plaintesko
			Affiche('nb_plaintesko');
			
			#plaintes_gend
			Affiche('plaintes_gend');
			
			#nb_plaintesgend
			Affiche('nb_plaintesgend');
			
			#nb_plainteskogend
			Affiche('nb_plainteskogend');
			
			#maincour
			Affiche('maincour');
			
			#nb_maincour
			Affiche('nb_maincour');
			
			#suite_plaintes
			Affiche('suite_plaintes');
			
			#deja_vict
			Affiche('deja_vict');
			
			#vict_enfce
			Affiche('vict_enfce');
			
			#contacte
			Affiche('contacte');
			
			#note_general
			Affiche('note_general');
echo '</fieldset>';

/*foreach ($t_fields as &$value) {
    Affiche($value);
}*/

		echo '</ul>';
	}

	#
	# Product Version, Product Build
	#

	if ( $tpl_show_product_version || $tpl_show_product_build ) {
		$t_spacer = 2;

		echo '<tr ', helper_alternate_class(), '>';

		# Product Version
		if ( $tpl_show_product_version ) {
			echo '<td class="description">', lang_get( 'product_version' ), '</td>';
			echo '<td>', $tpl_product_version_string, '</td>';
		} else {
			$t_spacer += 2;
		}

		# Product Build
		if ( $tpl_show_product_build ) {
			echo '<td class="description">', lang_get( 'product_build' ), '</td>';
			echo '<td>', $tpl_product_build, '</td>';
		} else {
			$t_spacer += 2;
		}

		# spacer
		echo '<td colspan="', $t_spacer, '">&#160;</td>';

		echo '</tr>';
	}

	#
	# Target Version, Fixed In Version
	#

	if ( $tpl_show_target_version || $tpl_show_fixed_in_version ) {
		$t_spacer = 2;

		echo '<tr ', helper_alternate_class(), '>';

		# target version
		if ( $tpl_show_target_version ) {
			# Target Version
			echo '<td class="description">', lang_get( 'target_version' ), '</td>';
			echo '<td>', $tpl_target_version_string, '</td>';
		} else {
			$t_spacer += 2;
		}

		# fixed in version
		if ( $tpl_show_fixed_in_version ) {
			echo '<td class="description">', lang_get( 'fixed_in_version' ), '</td>';
			echo '<td>', $tpl_fixed_in_version_string, '</td>';
		} else {
			$t_spacer += 2;
		}


	}

	#
	# Bug Details Event Signal
	#

	event_signal( 'EVENT_VIEW_BUG_DETAILS', array( $tpl_bug_id ) );

	# spacer
	/*echo '<tr class="spacer"><td colspan="6"></td></tr>';

	#
	# Bug Details (screen wide fields)
	#

	# Summary
	if ( $tpl_show_summary ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="description">', lang_get( 'summary' ), '</td>';
		echo '<td colspan="5">', $tpl_summary, '</td>';
		echo '</tr>';
	}



	# Steps to Reproduce
	if ( $tpl_show_motif_nvvenue ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="description">', lang_get( 'motif_nvvenue' ), '</td>';
		echo '<td colspan="5">', , '</td>';
		echo '</tr>';
	}

	# Additional Information
	if ( $tpl_show_date_nvvenue ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="description">', lang_get( 'date_nvvenue' ), '</td>';
		echo '<td colspan="5">', $tpl_date_nvvenue, '</td>';
		echo '</tr>';
	}

	# Tagging
	if ( $tpl_show_tags ) {
		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="description">', lang_get( 'tags' ), '</td>';
		echo '<td colspan="5">';
		tag_display_attached( $tpl_bug_id );
		echo '</td></tr>';
	}*/

	# Attachments Form
	if ( $tpl_can_attach_tag ) {
		echo '<ul class="misc">';
		echo '<li><label class="description">', lang_get( 'tag_attach_long' ), '</label>';
		echo '<p colspan="5">';
		print_tag_attach_form( $tpl_bug_id );
		echo '</p></li></ul>';
	}

	# spacer
	#echo '<tr class="spacer"><td colspan="6"></td></tr>';

	# Custom Fields
	$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( $tpl_bug->project_id );

	foreach( $t_related_custom_field_ids as $t_id ) {
		if ( !custom_field_has_read_access( $t_id, $f_bug_id ) ) {
			continue;
		} # has read access

		$t_custom_fields_found = true;
		$t_def = custom_field_get_definition( $t_id );

		echo '<tr ', helper_alternate_class(), '>';
		echo '<td class="description">', string_display( lang_get_defaulted( $t_def['name'] ) ), '</td>';
		echo '<td colspan="5">';
		print_custom_field_value( $t_def, $t_id, $f_bug_id );
		echo '</td></tr>';
	}

	if ( $t_custom_fields_found ) {
		# spacer
		echo '<tr class="spacer"><td colspan="6"></td></tr>';
	} # custom fields found




	# User list sponsoring the bug
	include( $tpl_mantis_dir . 'bug_sponsorship_list_view_inc.php' );

	# Bug Relationships
	/*if ( $tpl_show_relationships_box ) {
		relationship_view_box ( $tpl_bug->id );
	}*/
	# Attachments
	if ( $tpl_show_attachments ) {
		echo '<ul ', helper_alternate_class(), '>';
		echo '<li><label class="description"><a name="attachments" id="attachments" />', lang_get( 'attached_files' ), '</label>';
		echo '<div colspan="5">';
		print_bug_attachments_list( $tpl_bug_id );
		echo '</div></li></ul>';
	}
	# File upload box
	if ( $tpl_show_upload_form ) {
		include( $tpl_mantis_dir . 'bug_file_upload_inc.php' );
	}

	# Demandes de rappel
	if ( $tpl_show_monitor_box ) {
		include( $tpl_mantis_dir . 'bug_monitor_list_view_inc.php' );
	}

	/*if ( $tpl_bottom_buttons_enabled ) {
		html_buttons_view_bug_page( $tpl_bug_id );
	}*/
	
	# Bugnotes and "Add Note" box
	if ( 'ASC' == current_user_get_pref( 'bugnote_order' ) ) {
		include( $tpl_mantis_dir . 'bugnote_view_inc.php' );

		if ( !$tpl_force_readonly ) {
			include( $tpl_mantis_dir . 'bugnote_add_inc.php' );
		}
	} else {
		if ( !$tpl_force_readonly ) {
			include( $tpl_mantis_dir . 'bugnote_add_inc.php' );
		}

		include( $tpl_mantis_dir . 'bugnote_view_inc.php' );
		
	}
include( 'nouv_contact.php' );
echo '</div>';

	# Allow plugins to display stuff after notes
	event_signal( 'EVENT_VIEW_BUG_EXTRA', array( $f_bug_id ) );

	# Time tracking statistics
	if ( config_get( 'time_tracking_enabled' ) &&
		access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_bug_id ) ) {
		include( $tpl_mantis_dir . 'bugnote_stats_inc.php' );
	}

	# History
	/*if ( $tpl_show_history ) {
		include( $tpl_mantis_dir . 'history_inc.php' );
	}*/

	html_page_bottom();

	last_visited_issue( $tpl_bug_id );
