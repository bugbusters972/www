<?php
	require_once( 'core.php' );
	require_once( 'bug_api.php' );
	require_once( 'bugnote_api.php' );
	require_once( 'current_user_api.php' );

	form_security_validate( 'bugnote_update' );

	$f_bugnote_id	 = gpc_get_int( 'bugnote_id' );
	$f_dur_violence	=  gpc_get_int( 'dur_violence', '' );
	$f_lieu_violence	=  gpc_get_string( 'lieu_violence', '' );
	$f_nat_vio_cpl =  gpc_get_string( 'nat_vio_cpl', '' );
	$f_enf_temoin_cpl =  gpc_get_int( 'enf_temoin_cpl', '' );
	$f_enf_vio_cpl =  gpc_get_int( 'enf_vio_cpl', '' );
	$f_comm_cpl =  gpc_get_string( 'comm_cpl', '' );
	$f_cond_trav =  gpc_get_string( 'cond_trav', '' );
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
	$f_time_tracking = gpc_get_string( 'time_tracking', '0:00' );

	# Check if the current user is allowed to edit the bugnote
	$t_user_id = auth_get_current_user_id();
	$t_reporter_id = bugnote_get_field( $f_bugnote_id, 'reporter_id' );

	if ( ( $t_user_id != $t_reporter_id ) || ( OFF == config_get( 'bugnote_allow_user_edit_delete' ) )) {
		access_ensure_bugnote_level( config_get( 'update_bugnote_threshold' ), $f_bugnote_id );
	}

	# Check if the bug is readonly
	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );
	if ( bug_is_readonly( $t_bug_id ) ) {
		error_parameters( $t_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	$f_comm_cpl = trim( $f_comm_cpl ) . "\n\n";
	$f_comm_vio = trim( $f_comm_vio ) . "\n\n";
	$f_cons_comm = trim( $f_cons_comm ) . "\n\n";

	$euxtous = Array('dur_violence','lieu_violence','nat_vio_cpl','enf_temoin_cpl','enf_vio_cpl','comm_cpl','cond_trav','isol_trav','dign_trav','vio_trav','juri_trav','domaine','nom_vio','prenom_vio','age_vio','sexe_vio','adresse_vio','emploi_vio','ressources_vio','secteur_vio','csp_vio','lien_vict_vio','vict_enf_vio','mere_vict_vio','pb_vio','comm_vio','cons_phys','cons_psy','cons_trav','cons_soc','cons_comm','bes_vio','bes_soc','bes_com');
	
	foreach ($euxtous as $value){
	bugnote_set_field( $f_bugnote_id, $value);
	}
	
	bugnote_set_time_tracking( $f_bugnote_id, $f_time_tracking );

	# Plugin integration
	event_signal( 'EVENT_BUGNOTE_EDIT', array( $t_bug_id, $f_bugnote_id ) );

	form_security_purge( 'bugnote_update' );

	print_successful_redirect( string_get_bug_view_url( $t_bug_id ) . '#bugnotes' );
