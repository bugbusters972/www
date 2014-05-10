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

	
	echo '<table width="780"><tbody>';

	# Form Title
	echo '<tr>';

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
	echo '</tr>';


		
		# Bug ID
echo '<tr><td width="250" class="print-category">', $tpl_show_id ? lang_get( 'id' ) : '', '</td><td width="250">', $tpl_formatted_bug_id, '</td></tr>';
		# Date Submitted
	Impression('date_submitted');

		# Date Updated
	Impression('last_updated');

		# Accueillante
		if ( $tpl_show_reporter ) {
			echo '<tr><td class="description">', lang_get( 'reporter' ), '</td>';
			echo '<td width="250">';
			print_user_with_subject( $tpl_bug->reporter_id, $tpl_bug_id );
			echo '</td></tr>';
		}
		
		# Assigné à
		if ( $tpl_show_handler ) {
			echo '<tr><td class="print-category">', lang_get( 'assigned_to' ), '</td>';
			echo '<td width="250">';
			print_user_with_subject( $tpl_bug->handler_id, $tpl_bug_id );
			echo '</td></tr>';
		}
#echo '<fieldset><legend>IDENTIT&Eacute;</legend><span class="inline33">';




	if ( $tpl_show_nom ) {
		$t_spacer =0;

			#nom
			Impression('nom');
		
			#nomepouse
			Impression('nomepouse');
		
			#prenom
			Impression('prenom');
#echo '</span><span class="inline33">';
		# sexe
		Impression ('sexe');
		#telephone
		Impression('telephone');
#echo '</span></fieldset>';
echo '</tbody></table>';
imprim_all_contacts( $tpl_bug_id );


echo '<table width="780"><tbody>';
echo '<tr colspan="2">
	<td><h2>Dossier</h2></td><td></td>
	</tr>';		
		#attente
			Impression('attente');
#echo'<fieldset ><legend>&Eacute;TAT CIVIL</legend>';
			#date_naissance
echo '<tr><td width="250">', lang_get( date_naissance ), '</td>';

echo '<td width="250">'.string_display_line( date( config_get( 'short_date_format' ), $tpl_date_naissance ) ).' ('.
age(date( 'Y/m/d',$tpl_date_naissance)).' ans)'.'</td></tr>';

			#lieu_naissance
			Impression('lieu_naissance');

			#nationalite
			Impression('nationalite');
			
			#rue
			Impression('rue');
			
			#adrs_suite
			Impression('adrs_suite');
			
			#ville
			Impression('ville');

			#region
			Impression('region');

			#code_postal
			Impression('code_postal');

			#pays
			Impression('pays');

			#autre_hebergt
			Impression('autre_hebergt');
#echo'</fieldset>';
			#email
			Impression('email');
	
			#tel_domicile
			Impression('tel_domicile');
			
			#tel_domicile
			Impression('tel_domicile');
			
			#tel_travail
			Impression('tel_travail');
			
			#orig_orient
			Bnexp('orig_orient');
			
			#connu_assoc
			Impression('connu_assoc');
#echo '<fieldset class="group-logement-venue"><legend>LOGEMENT AU MOMENT DE LA VENUE (VIOLENCES AUTRES QUE TRAVAIL)</legend>';			
			#nom_logem
			Impression('nom_logem');
			
			#type_logem
			Impression('type_logem');
			
			#complem_info
			Impression('complem_info');
#echo '</fieldset><fieldset class="group-situation-matrimoniale"><legend>SITUATION MATRIMONIALE</legend>';		
			#matrim_ant
			Impression('matrim_ant');
			
			#matrim
			Impression('matrim');
			
			#nb_enfant
			Impression('nb_enfant');
			
			#nb_enf_charge
			Impression('nb_enf_charge');
			
			#sxage_enf
echo '<tr><td class="print-category">Sexe et &acirc;ge des enfants</td><td width="250"></td></tr><tr>';
$chqenf = explode(',',$tpl_bug->sxage_enf);
foreach ($chqenf as $enfant){
if (strlen($enfant)>11){
$data = explode('+',$enfant);
$edad = explode('/',$data[1]);
echo '<span id="enf"><td width="250">ENFANT '.(intval($data[0])+1).'</td><td width="250">'.$data[2].', '.(date('Y')-intval($edad[2])).'&nbsp;ans</td>';
}}
echo'</tr>';
			
			#enf_pere
			Impression('enf_pere');
			
			#enf_recon
			Impression('enf_recon');

#echo '</fieldset><fieldset class="group-situation-entree"><legend>SITUATION &Agrave; L&apos;ENTR&Eacute;E</legend>';
			
			#sit_prof
			Impression('sit_prof');
			
			#niv_scolr
			Impression('niv_scolr');
			
			#emploi
			Impression('emploi');
			
			#cddcdi
			Impression('cddcdi');
			
			#typ_contrat
			Impression('typ_contrat');
			
			#secteur_activ
			Impression('secteur_activ');
			
			#type_ent
			Impression('type_ent');
			
			#csp
			Impression('csp');
			
			#ressources
			Impression('ressources');
			
			#prestations
			Bnexp('prestations');

#echo '</fieldset><fieldset><legend>HISTOIRE DE LA VICTIME</legend>';
			
			#mere_victime
			Impression('mere_victime');
			
			#plaintes
			Impression('plaintes');
			
			#nb_plaintes
			Impression('nb_plaintes');
			
			#nb_plaintesko
			Impression('nb_plaintesko');
			
			#plaintes_gend
			Impression('plaintes_gend');
			
			#nb_plaintesgend
			Impression('nb_plaintesgend');
			
			#nb_plainteskogend
			Impression('nb_plainteskogend');
			
			#maincour
			Impression('maincour');
			
			#nb_maincour
			Impression('nb_maincour');
			
			#suite_plaintes
			Impression('suite_plaintes');
			
			#deja_vict
			Impression('deja_vict');
			
			#vict_enfce
			Impression('vict_enfce');
			
			#contacte
			Impression('contacte');
			
			#note_general
			Impression('note_general');
#echo '</fieldset>';


		echo '</tbody></table>';
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
			echo '<td width="250">', $tpl_product_version_string, '</td>';
		} else {
			$t_spacer += 2;
		}

		# Product Build
		if ( $tpl_show_product_build ) {
			echo '<td class="description">', lang_get( 'product_build' ), '</td>';
			echo '<td width="250">', $tpl_product_build, '</td>';
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
			echo '<td width="250">', $tpl_target_version_string, '</td>';
		} else {
			$t_spacer += 2;
		}

		# fixed in version
		if ( $tpl_show_fixed_in_version ) {
			echo '<td class="description">', lang_get( 'fixed_in_version' ), '</td>';
			echo '<td width="250">', $tpl_fixed_in_version_string, '</td>';
		} else {
			$t_spacer += 2;
		}


	}


	event_signal( 'EVENT_VIEW_BUG_DETAILS', array( $tpl_bug_id ) );




	$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( $tpl_bug->project_id );

	foreach( $t_related_custom_field_ids as $t_id ) {
		if ( !custom_field_has_read_access( $t_id, $f_bug_id ) ) {
			continue;
		} 

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



	include( $tpl_mantis_dir . 'bug_sponsorship_list_view_inc.php' );





	
	
	if ( 'ASC' == current_user_get_pref( 'bugnote_order' ) ) {
		include( $tpl_mantis_dir . 'bugnote_view_inc.php' );

		if ( !$tpl_force_readonly ) {
			
		}
	} else {
		if ( !$tpl_force_readonly ) {
			
		}

		include( $tpl_mantis_dir . 'bugnote_view_inc.php' );
		
	}


	
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
echo "<script>
$('.form_description').nextAll().show();
$('legend').nextAll().show();
$('* #contacts').nextAll().show();
</script>";
	html_page_bottom();

	last_visited_issue( $tpl_bug_id );
