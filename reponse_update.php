<?php
	require_once( 'core.php' );
	require_once( 'bug_api.php' );
	require_once( 'bugnote_api.php' );
	require_once( 'current_user_api.php' );

	form_security_validate( 'reponse_update_form' );

	$f_reponse_id	 = gpc_get_int( 'reponse_id' );
	$f_bug_id	=  gpc_get_int( 'bug_id', '' );
	$f_date_rep	=  gpc_get_int( 'date_rep', '' );
	$f_reporter_id =  gpc_get_int( 'reporter_id', '' );
	$f_handler_id =  gpc_get_int( 'handler_id', '' );
	$f_type_contact_rep =  gpc_get_int( 'type_contact_rep', '' );
	$f_accompagn =  gpc_get_int( 'accompagn', '' );
	$f_aide_soc_fin =  gpc_get_string( 'aide_soc_fin', '' );
	$f_aide_alim =  gpc_get_string( 'aide_alim', '' );
	$f_demarch=  gpc_get_string( 'demarch', '' );
	$f_sante=  gpc_get_string( 'sante', '' );
	$f_assis_jur=  gpc_get_string( 'assis_jur', '' );
	$f_heberg=  gpc_get_string( 'heberg', '' );
	$f_aide_enf=  gpc_get_string( 'aide_enf', '' );
	$f_aide_prof=  gpc_get_string( 'aide_prof', '' );
	$f_autre_demarch=  gpc_get_string( 'autre_demarch', '' );
	$f_temps=  gpc_get_int( 'temps', '' );
	$f_rep_comm=  gpc_get_string( 'rep_comm', '' );


	if ( bug_is_readonly( $f_bug_id ) ) {
		error_parameters( $f_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	$euxtous = Array('date_rep','reporter_id','handler_id','type_contact_rep','accompagn','aide_soc_fin','aide_alim','demarch','sante','assis_jur','heberg','aide_enf','aide_prof','autre_demarch','temps','rep_comm');
	
	foreach ($euxtous as $value){
	reponse_update( $f_reponse_id, $value, $f_bug_id );
	}
	

	form_security_purge( 'reponse_update_form' );

	print_successful_redirect( string_get_bug_view_url( $f_bug_id ) . '#bugnotes' );
