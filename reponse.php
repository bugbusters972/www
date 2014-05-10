<?php
	require_once( 'core.php' );

	require_once( 'bug_api.php' );
	require_once( 'bugnote_api.php' );

	form_security_validate( 'reponse_form' );

	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_bugnote_id		= gpc_get_int( 'bugnote_id' );
	$f_date_rep		= gpc_get_int( 'date_rep', db_now() );
	$f_reporter_id		= gpc_get_int( 'reporter_id');
	$f_handler_id	= gpc_get_int( 'handler_id');
	$f_type_contact_rep	= gpc_get_int( 'type_contact_rep');
	$f_accompagn	= gpc_get_int( 'accompagn');
	$f_aide_soc_fin	= gpc_get_string( 'aide_soc_fin');
	$f_aide_alim	= gpc_get_string( 'aide_alim');
	$f_demarch	= gpc_get_string( 'demarch');
	$f_sante	= gpc_get_string( 'sante');
	$f_assis_jur	= gpc_get_string( 'assis_jur');
	$f_heberg	= gpc_get_string( 'heberg');
	$f_aide_enf	= gpc_get_string( 'aide_enf');
	$f_aide_prof	= gpc_get_string( 'aide_prof');
	$f_autre_demarch	= gpc_get_string( 'autre_demarch');
	$f_temps	= gpc_get_int( 'temps');
	$f_rep_comm	= gpc_get_string( 'rep_comm');

	/*$t_bug = bug_get( $f_bug_id, true );
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	if ( bug_is_readonly( $f_bug_id ) ) {
		error_parameters( $f_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	access_ensure_bug_level( config_get( 'add_bugnote_threshold' ), $f_bug_id );*/

	// We always set the note time to BUGNOTE, and the API will overwrite it with TIME_TRACKING
	// if $f_time_tracking is not 0 and the time tracking feature is enabled.
	$t_reponse_id = reponse_add($f_bug_id,$f_bugnote_id,$f_date_rep,$f_reporter_id,$f_handler_id,$f_type_contact_rep,$f_accompagn,$f_aide_soc_fin,$f_aide_alim,$f_demarch,$f_sante,$f_assis_jur,$f_heberg,$f_aide_enf,$f_aide_prof,$f_autre_demarch,$f_temps,$f_rep_comm);

	
    if ( !$t_reponse_id ) {
        error_parameters( 'reponse') ;
        trigger_error( ERROR_EMPTY_FIELD, ERROR );
    }


	form_security_purge( 'reponse_form' );

	print_successful_redirect_to_bug( $f_bug_id );
