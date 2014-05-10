<?php
	require_once( 'core.php' );

	require_once( 'bug_api.php' );
	require_once( 'bugnote_api.php' );

	form_security_validate( 'bugnote_add' );

	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_private		= gpc_get_bool( 'private' );
	$f_time_tracking	= gpc_get_string( 'time_tracking', '0:00' );
	#$f_bugnote_text=  gpc_get_string( 'note', '' );
	$f_dur_violence	=  gpc_get_int( 'dur_violence','' );
	$f_lieu_violence	=  gpc_get_string( 'lieu_violence', '');
	$f_nat_vio_cpl =  gpc_get_string( 'nat_vio_cpl' , '');
	$f_enf_temoin_cpl =  gpc_get_int( 'enf_temoin_cpl', '' );
	$f_enf_vio_cpl =  gpc_get_int( 'enf_vio_cpl', '' );
	$f_comm_cpl =  gpc_get_string( 'comm_cpl', '' );
	$f_cond_trav =  gpc_get_string( 'cond_trav', '');
	$f_isol_trav =  gpc_get_string( 'isol_trav', '' );
	$f_dign_trav=  gpc_get_string( 'dign_trav', '' );
	$f_vio_trav=  gpc_get_string( 'vio_trav', '' );
	$f_juri_trav=  gpc_get_string( 'juri_trav', '' );
	$f_domaine=  gpc_get_int( 'domaine', '' );
	$f_nom_vio=  gpc_get_string( 'nom_vio', '' );
	$f_prenom_vio=  gpc_get_string( 'prenom_vio', '' );
	$f_age_vio=  gpc_get_int( 'age_vio', '' );
	$f_sexe_vio=  gpc_get_int( 'sexe_vio', '' );
	$f_adresse_vio=  gpc_get_int( 'adresse_vio', '' );
	$f_emploi_vio=  gpc_get_string( 'emploi_vio', '' );
	$f_ressources_vio=  gpc_get_int( 'ressources_vio', '' );
	$f_secteur_vio=  gpc_get_int( 'secteur_vio', '' );
	$f_csp_vio=  gpc_get_int( 'csp_vio', '' );
	$f_lien_vict_vio=  gpc_get_int( 'lien_vict_vio', '' );
	$f_vict_enf_vio=  gpc_get_int( 'vict_enf_vio', '' );
	$f_mere_vict_vio=  gpc_get_int( 'mere_vict_vio', '' );
	$f_pb_vio=  gpc_get_string( 'pb_vio', '' );
	$f_comm_vio=  gpc_get_string( 'comm_vio', '' );
	$f_cons_phys=  gpc_get_string( 'cons_phys', '' );
	$f_cons_psy=  gpc_get_string( 'cons_psy', '' );
	$f_cons_trav=  gpc_get_string( 'cons_trav', '' );
	$f_cons_soc=  gpc_get_string( 'cons_soc', '' );
	$f_cons_comm=  gpc_get_string( 'cons_comm', '' );
	$f_bes_vio=  gpc_get_string( 'bes_vio', '' );
	$f_bes_soc=  gpc_get_string( 'bes_soc', '' );
	$f_bes_com=  gpc_get_string( 'bes_com', '' );

	$t_bug = bug_get( $f_bug_id, true );
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	if ( bug_is_readonly( $f_bug_id ) ) {
		error_parameters( $f_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	access_ensure_bug_level( config_get( 'add_bugnote_threshold' ), $f_bug_id );

	// We always set the note time to BUGNOTE, and the API will overwrite it with TIME_TRACKING
	// if $f_time_tracking is not 0 and the time tracking feature is enabled.
	$t_bugnote_id = bugnote_add($f_bes_com,$f_bes_soc,$f_bes_vio,$f_nat_vio_cpl,$f_enf_temoin_cpl,$f_enf_vio_cpl,$f_comm_cpl,$f_cond_trav,$f_isol_trav,$f_dign_trav,$f_vio_trav,$f_juri_trav,$f_domaine,$f_nom_vio,$f_prenom_vio,$f_age_vio,$f_sexe_vio,$f_adresse_vio,$f_emploi_vio,$f_ressources_vio,$f_secteur_vio,$f_csp_vio,$f_lien_vict_vio,$f_vict_enf_vio,$f_mere_vict_vio,$f_pb_vio,$f_comm_vio,$f_cons_phys,$f_cons_psy,$f_cons_trav,$f_cons_soc,$f_cons_comm,$f_dur_violence, $f_lieu_violence, $f_bug_id, $f_time_tracking, $f_private, BUGNOTE);
	
    if ( !$t_bugnote_id ) {
        error_parameters( lang_get( 'bugnote' ) );
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
    }

	form_security_purge( 'bugnote_add' );
	print_successful_redirect_to_bug( $f_bug_id );
