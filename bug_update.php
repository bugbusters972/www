<?php
	require_once( 'core.php' );
	require_once( 'bug_api.php' );
	require_once( 'bugnote_api.php' );
	require_once( 'custom_field_api.php' );

	form_security_validate( 'bug_update' );

	$f_bug_id 						= gpc_get_int( 'bug_id' );
	$t_bug_data 					= bug_get( $f_bug_id, true );

	$f_update_mode 					= gpc_get_bool( 'update_mode', FALSE ); # set if called from generic update page
	$f_new_status					= gpc_get_int( 'status', $t_bug_data->status );

	if( $t_bug_data->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug_data->project_id;
	}

	$t_user = auth_get_current_user_id();
	if ( !(
			   access_has_bug_level( access_get_status_threshold( $f_new_status, bug_get_field( $f_bug_id, 'project_id' ) ), $f_bug_id )
			|| access_has_bug_level( config_get( 'update_bug_threshold' ) , $f_bug_id )
			|| (   bug_is_user_reporter( $f_bug_id, $t_user )
				&& access_has_bug_level( config_get( 'report_bug_threshold' ), $f_bug_id, $t_user )
				&& (   ON == config_get( 'allow_reporter_reopen' )
					|| ON == config_get( 'allow_reporter_close' )
				   )
			   )
		  )
	) {
		access_denied();
	}

	# extract current extended information
	$t_old_bug_status = $t_bug_data->status;

	$t_bug_data->reporter_id		= gpc_get_int( 'reporter_id', $t_bug_data->reporter_id );
	$t_bug_data->handler_id			= gpc_get_int( 'handler_id', $t_bug_data->handler_id );
	$t_bug_data->duplicate_id		= gpc_get_int( 'duplicate_id', $t_bug_data->duplicate_id );
	#$t_bug_data->motif_contact		= gpc_get_int( 'motif_contact', $t_bug_data->motif_contact );
	$t_bug_data->sexe				= gpc_get_int( 'sexe', $t_bug_data->sexe );
	$t_bug_data->status				= gpc_get_int( 'status', $t_bug_data->status );
	$t_bug_data->resolution			= gpc_get_int( 'resolution', $t_bug_data->resolution );
	$t_bug_data->projection			= gpc_get_int( 'projection', $t_bug_data->projection );
	$t_bug_data->category_id		= gpc_get_int( 'category_id', $t_bug_data->category_id );
	$t_bug_data->eta				= gpc_get_int( 'eta', $t_bug_data->eta );
	$t_bug_data->prenom				= gpc_get_string( 'prenom', $t_bug_data->prenom );
	$t_bug_data->telephone			= preg_replace('/\s+/', '',gpc_get_string( 'telephone', $t_bug_data->telephone ));
	$t_bug_data->nom				= strtoupper (gpc_get_string( 'nom', $t_bug_data->nom ));
	$t_bug_data->nomepouse			= gpc_get_string( 'nomepouse', $t_bug_data->nomepouse );
	$t_bug_data->version			= gpc_get_string( 'version', $t_bug_data->version );
	$t_bug_data->build				= gpc_get_string( 'build', $t_bug_data->build );
	$t_bug_data->fixed_in_version	= gpc_get_string( 'fixed_in_version', $t_bug_data->fixed_in_version );
	$t_bug_data->view_state			= gpc_get_int( 'view_state', $t_bug_data->view_state );
	#$t_bug_data->summary			= gpc_get_string( 'summary', $t_bug_data->summary );
	$t_due_date 					= gpc_get_string( 'due_date', null );
/*nouveaux champs
	$t_bug_data->demande_rappel 	= gpc_get_int( 'demande_rappel', config_get( 'default_bug_demande_rappel'));*/
	$t_bug_data->attente 			= gpc_get_int( 'attente', config_get( 'default_bug_attente'));
	$t_bug_data->date_naissance 	= encDt(gpc_get_string( 'date_naissance', config_get( 'default_bug_date_naissance')));
	$t_bug_data->lieu_naissance 	= gpc_get_string( 'lieu_naissance', config_get( 'default_bug_lieu_naissance'));
	$t_bug_data->nationalite 		= gpc_get_int( 'nationalite', config_get( 'default_bug_nationalite'));
	$t_bug_data->rue 				= gpc_get_string( 'rue', config_get( 'default_bug_rue'));
	$t_bug_data->adrs_suite 		= gpc_get_string( 'adrs_suite', config_get( 'default_bug_adrs_suite'));
	$t_bug_data->ville 				= gpc_get_string( 'ville', config_get( 'default_bug_ville'));
	#$t_bug_data->region 			= gpc_get_string( 'region', config_get( 'default_bug_region'));
	$t_bug_data->code_postal 		= gpc_get_int( 'code_postal', config_get( 'default_bug_code_postal'));
	$t_bug_data->pays 				= gpc_get_int( 'pays', config_get( 'default_bug_pays'));
	$t_bug_data->autre_hebergt 		= gpc_get_string( 'autre_hebergt', config_get( 'default_bug_autre_hebergt'));
	$t_bug_data->email 				= gpc_get_string( 'email', config_get( 'default_bug_email'));
	$t_bug_data->tel_domicile 		= gpc_get_int( 'tel_domicile', config_get( 'default_bug_tel_domicile'));
	$t_bug_data->tel_travail 		= gpc_get_int( 'tel_travail', config_get( 'default_bug_tel_travail'));
	$t_bug_data->orig_orient 		= gpc_get_string( 'orig_orient', config_get( 'default_bug_orig_orient'));
	$t_bug_data->connu_assoc 		= gpc_get_int( 'connu_assoc', config_get( 'default_bug_connu_assoc'));
	$t_bug_data->nom_logem 			= gpc_get_int( 'nom_logem', config_get( 'default_bug_nom_logem'));
	$t_bug_data->type_logem 		= gpc_get_int( 'type_logem', config_get( 'default_bug_type_logem'));
	$t_bug_data->complem_info 		= gpc_get_string( 'complem_info', config_get( 'default_bug_complem_info'));
	$t_bug_data->matrim_ant 		= gpc_get_int( 'matrim_ant', config_get( 'default_bug_matrim_ant'));
	$t_bug_data->matrim 			= gpc_get_int( 'matrim', config_get( 'default_bug_matrim'));
	$t_bug_data->nb_enfant 			= gpc_get_int( 'nb_enfant', config_get( 'default_bug_nb_enfant'));
	$t_bug_data->nb_enf_charge 		= gpc_get_int( 'nb_enf_charge', config_get( 'default_bug_nb_enf_charge'));
	$t_bug_data->sxage_enf 			= gpc_get_string( 'sxage_enf', bug_get_field( $f_bug_id, 'sxage_enf' ));
	$t_bug_data->enf_pere 			= gpc_get_int( 'enf_pere', config_get( 'default_bug_enf_pere'));
	$t_bug_data->enf_recon 			= gpc_get_int( 'enf_recon', config_get( 'default_bug_enf_recon'));
	$t_bug_data->sit_prof 			= gpc_get_int( 'sit_prof', config_get( 'default_bug_sit_prof'));
	$t_bug_data->niv_scolr 			= gpc_get_int( 'niv_scolr', config_get( 'default_bug_niv_scolr'));
	$t_bug_data->emploi 			= gpc_get_string( 'emploi', config_get( 'default_bug_emploi'));
	$t_bug_data->cddcdi 			= gpc_get_int( 'cddcdi', config_get( 'default_bug_cddcdi'));
	$t_bug_data->typ_contrat 		= gpc_get_int( 'typ_contrat', config_get( 'default_bug_typ_contrat'));
	$t_bug_data->secteur_activ 		= gpc_get_int( 'secteur_activ', config_get( 'default_bug_secteur_activ'));
	$t_bug_data->type_ent 			= gpc_get_int( 'type_ent', config_get( 'default_bug_type_ent'));
	$t_bug_data->csp 				= gpc_get_int( 'csp', config_get( 'default_bug_csp'));
	$t_bug_data->ressources 		= gpc_get_int( 'ressources', config_get( 'default_bug_ressources'));
	$t_bug_data->prestations 		= gpc_get_string( 'prestations', config_get( 'default_bug_prestations'));
	$t_bug_data->mere_victime 		= gpc_get_int( 'mere_victime', config_get( 'default_bug_mere_victime'));
	$t_bug_data->plaintes 			= gpc_get_int( 'plaintes', config_get( 'default_bug_plaintes'));
	$t_bug_data->nb_plaintes 		= gpc_get_int( 'nb_plaintes', config_get( 'default_bug_nb_plaintes'));
	$t_bug_data->nb_plaintesko 		= gpc_get_int( 'nb_plaintesko', config_get( 'default_bug_nb_plaintesko'));
	$t_bug_data->plaintes_gend		= gpc_get_int( 'plaintes_gend', config_get( 'default_bug_plaintes_gend'));
	$t_bug_data->nb_plaintesgend 	= gpc_get_int( 'nb_plaintesgend', config_get( 'default_bug_nb_plaintesgend'));
	$t_bug_data->nb_plainteskogend 	= gpc_get_int( 'nb_plainteskogend', config_get( 'default_bug_nb_plainteskogend'));
	$t_bug_data->maincour 			= gpc_get_int( 'maincour', config_get( 'default_bug_maincour'));
	$t_bug_data->nb_maincour 		= gpc_get_int( 'nb_maincour', config_get( 'default_bug_nb_maincour'));
	$t_bug_data->suite_plaintes 	= gpc_get_string( 'suite_plaintes', config_get( 'default_bug_suite_plaintes'));
	$t_bug_data->deja_vict 			= gpc_get_int( 'deja_vict', config_get( 'default_bug_deja_vict'));
	$t_bug_data->vict_enfce 		= gpc_get_int( 'vict_enfce', config_get( 'default_bug_vict_enfce'));
	$t_bug_data->contacte 			= gpc_get_int( 'contacte', config_get( 'default_bug_contacte'));
	$t_bug_data->note_general 		= gpc_get_string( 'note_general', $t_bug_data->note_general );
#fin nouveaux champs
#champs bugnote
	$f_private			='';
	$f_time_tracking	='';
	$f_dur_violence		='';
	$f_lieu_violence	='';
	$f_nat_vio_cpl 		='';
	$f_enf_temoin_cpl 	='';
	$f_enf_vio_cpl 		='';
	$f_comm_cpl 		='';
	$f_cond_trav 		='';
	$f_isol_trav 		='';
	$f_dign_trav		='';
	$f_vio_trav			='';
	$f_juri_trav		='';
	$f_domaine			='';
	$f_nom_vio			='';
	$f_prenom_vio		='';
	$f_age_vio			='';
	$f_sexe_vio			='';
	$f_adresse_vio		='';
	$f_emploi_vio		='';
	$f_ressources_vio	='';
	$f_secteur_vio		='';
	$f_csp_vio			='';
	$f_lien_vict_vio	='';
	$f_vict_enf_vio		='';
	$f_mere_vict_vio	='';
	$f_pb_vio			='';
	$f_comm_vio			='';
	$f_cons_phys		='';
	$f_cons_psy			='';
	$f_cons_trav		='';
	$f_cons_soc			='';
	$f_cons_comm		='';
	$f_bes_vio			='';
	$f_bes_soc			='';
	$f_bes_com			='';
#champs bugnote

	/*if( access_has_project_level( config_get( 'roadmap_update_threshold' ), $t_bug_data->project_id ) ) {
		$t_bug_data->target_version	= gpc_get_string( 'target_version', $t_bug_data->target_version );
	}

	if( $t_due_date !== null) {
		if ( is_blank ( $t_due_date ) ) {
			$t_bug_data->due_date = 1;
		} else {
			$t_bug_data->due_date = strtotime( $t_due_date );
		}
	}*/

	$t_bug_data->description		= gpc_get_string( 'description', $t_bug_data->description );
	$t_bug_data->motif_nvvenue		= gpc_get_int( 'motif_nvvenue', $t_bug_data->motif_nvvenue );
	$t_bug_data->date_nvvenue		= gpc_get_int('date_nvvenue', $t_bug_data->date_nvvenue );
	$t_bug_data->rapporteur			= gpc_get_int( 'rapporteur', $t_bug_data->rapporteur );
	$t_bug_data->type_contact		= gpc_get_int( 'type_contact', $t_bug_data->type_contact );
	
	$t_bug_data->dossier			= gpc_get_int( 'bug_id', $t_bug_data->f_bug_id );
	
	$f_private						= gpc_get_bool( 'private' );
	#$f_bugnote_text					= gpc_get_string( 'bugnote_text', '' );
	$f_time_tracking				= gpc_get_string( 'time_tracking', '0:00' );
	$f_close_now					= gpc_get_string( 'close_now', false );

	# Handle auto-assigning
	if ( ( config_get( 'bug_submit_status' ) == $t_bug_data->status )
	  && ( $t_bug_data->status == $t_old_bug_status )
	  && ( 0 != $t_bug_data->handler_id )
	  && ( ON == config_get( 'auto_set_status_to_assigned' ) ) ) {
		$t_bug_data->status = config_get( 'bug_assigned_status' );
	}

	helper_call_custom_function( 'issue_update_validate', array( $f_bug_id, $t_bug_data, $f_bugnote_text ) );

	$t_resolved = config_get( 'bug_resolved_status_threshold' );
	$t_closed = config_get( 'bug_closed_status_threshold' );

	$t_custom_status_label = "update"; # default info to check
	if ( $t_bug_data->status == $t_resolved ) {
		$t_custom_status_label = "resolved";
	}
	if ( $t_bug_data->status == $t_closed ) {
		$t_custom_status_label = "closed";
	}

	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug_data->project_id );
	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );

		# Only update the field if it would have been displayed for editing
		if( !( ( !$f_update_mode && $t_def['require_' . $t_custom_status_label] ) ||
						( !$f_update_mode && $t_def['display_' . $t_custom_status_label] && in_array( $t_custom_status_label, array( "resolved", "closed" ) ) ) ||
						( $f_update_mode && $t_def['display_update'] ) ||
						( $f_update_mode && $t_def['require_update'] ) ) ) {
			continue;
		}

		# Do not set custom field value if user has no write access.
		if( !custom_field_has_write_access( $t_id, $f_bug_id ) ) {
			continue;
		}

		# Produce an error if the field is required but wasn't posted
		if ( !gpc_isset_custom_field( $t_id, $t_def['type'] ) &&
			( $t_def['require_' . $t_custom_status_label] ) ) {
			error_parameters( lang_get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		$t_new_custom_field_value = gpc_get_custom_field( "custom_field_$t_id", $t_def['type'], '' );
		$t_old_custom_field_value = custom_field_get_value( $t_id, $f_bug_id );

		# Don't update the custom field if the new value both matches the old value and is valid
		# This ensures that changes to custom field validation will force the update of old invalid custom field values
		if( $t_new_custom_field_value === $t_old_custom_field_value &&
			custom_field_validate( $t_id, $t_new_custom_field_value ) ) {
			continue;
		}

		# Attempt to set the new custom field value
		if ( !custom_field_set_value( $t_id, $f_bug_id, $t_new_custom_field_value ) ) {
			error_parameters( lang_get_defaulted( custom_field_get_field( $t_id, 'name' ) ) );
			trigger_error( ERROR_CUSTOM_FIELD_INVALID_VALUE, ERROR );
		}
	}

	$t_notify = true;
	$t_bug_note_set = false;
	if ( ( $t_old_bug_status != $t_bug_data->status ) && ( FALSE == $f_update_mode ) ) {
		# handle status transitions that come from pages other than bug_*update_page.php
		# this does the minimum to act on the bug and sends a specific message
		if ( $t_bug_data->status >= $t_resolved
			&& $t_bug_data->status < $t_closed
			&& $t_old_bug_status < $t_resolved ) {
			# bug_resolve updates the status, fixed_in_version, resolution,
			# handler_id and bugnote and sends message
			bug_resolve( $f_bug_id,
				$t_bug_data->resolution, $t_bug_data->status,
				$t_bug_data->fixed_in_version,
				$t_bug_data->duplicate_id, $t_bug_data->handler_id,
				$f_bugnote_text, $f_private, $f_time_tracking );
			$t_notify = false;
			$t_bug_note_set = true;

			if ( $f_close_now ) {
				bug_set_field( $f_bug_id, 'status', $t_closed );
			}

			# update bug data with fields that may be updated inside bug_resolve(),
			# otherwise changes will be overwritten in bug_update() call below.
			$t_bug_data->handler_id = bug_get_field( $f_bug_id, 'handler_id' );
			$t_bug_data->status = bug_get_field( $f_bug_id, 'status' );
		} else if ( $t_bug_data->status >= $t_closed
			&& $t_old_bug_status < $t_closed ) {
			# bug_close updates the status and bugnote and sends message
			bug_close( $f_bug_id, $f_bugnote_text, $f_private, $f_time_tracking );
			$t_notify = false;
			$t_bug_note_set = true;
		} else if ( $t_bug_data->status == config_get( 'bug_reopen_status' )
			&& $t_old_bug_status >= $t_resolved ) {
			# fix: update handler_id before calling bug_reopen
			bug_set_field( $f_bug_id, 'handler_id', $t_bug_data->handler_id );
			# bug_reopen updates the status and bugnote and sends message
			bug_reopen( $f_bug_id, $f_bugnote_text, $f_time_tracking, $f_private );
			$t_notify = false;
			$t_bug_note_set = true;

			# update bug data with fields that may be updated inside bug_reopen(),
			# otherwise changes will be overwritten in bug_update() call below.
			$t_bug_data->status = bug_get_field( $f_bug_id, 'status' );
			$t_bug_data->resolution = bug_get_field( $f_bug_id, 'resolution' );
		}
	}

	# Plugin support
	$t_new_bug_data = event_signal( 'EVENT_UPDATE_BUG', $t_bug_data, $f_bug_id );
	if ( !is_null( $t_new_bug_data ) ) {
		$t_bug_data = $t_new_bug_data;
	}

	# Add a bugnote if there is one
	if ( false == $t_bug_note_set ) {
		/*bugnote_add($f_bes_vio,$f_bes_soc,$f_bes_com,$f_nat_vio_cpl,$f_enf_temoin_cpl,$f_enf_vio_cpl,$f_comm_cpl,$f_cond_trav,$f_isol_trav,$f_dign_trav,$f_vio_trav,$f_juri_trav,$f_domaine,$f_nom_vio,$f_prenom_vio,$f_age_vio,$f_sexe_vio,$f_adresse_vio,$f_emploi_vio,$f_ressources_vio,$f_secteur_vio,$f_csp_vio,$f_lien_vict_vio,$f_vict_enf_vio,$f_mere_vict_vio,$f_pb_vio,$f_comm_vio,$f_cons_phys,$f_cons_psy,$f_cons_trav,$f_cons_soc,$f_cons_comm,$f_dur_violence, $f_lieu_violence, $f_bug_id, $f_bugnote_text, $f_time_tracking, $f_private, 0, '', NULL, FALSE );*/
		bugnote_add($f_bes_com,$f_bes_soc,$f_bes_vio,$f_nat_vio_cpl,$f_enf_temoin_cpl,$f_enf_vio_cpl,$f_comm_cpl,$f_cond_trav,$f_isol_trav,$f_dign_trav,$f_vio_trav,$f_juri_trav,$f_domaine,$f_nom_vio,$f_prenom_vio,$f_age_vio,$f_sexe_vio,$f_adresse_vio,$f_emploi_vio,$f_ressources_vio,$f_secteur_vio,$f_csp_vio,$f_lien_vict_vio,$f_vict_enf_vio,$f_mere_vict_vio,$f_pb_vio,$f_comm_vio,$f_cons_phys,$f_cons_psy,$f_cons_trav,$f_cons_soc,$f_cons_comm,$f_dur_violence, $f_lieu_violence, $f_bug_id, $f_time_tracking, $f_private, BUGNOTE);
	}

	# Update the bug entry, notify if we haven't done so already

	$t_bug_data->update( false, ( false == $t_notify ));

	form_security_purge( 'bug_update' );

	#helper_call_custom_function( 'issue_update_notify', array( $f_bug_id ) );

	print_successful_redirect_to_bug( $f_bug_id );
