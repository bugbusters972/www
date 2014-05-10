<?php
	require_once( 'core.php' );

	require_once( 'bug_api.php' );
	require_once( 'bugnote_api.php' );
	require_once( 'current_user_api.php' );

	form_security_validate( 'reponse_delete' );

	$f_reponse_id = gpc_get_int( 'reponse_id' );
	$f_bugnote_id = gpc_get_int( 'bugnote_id' );

	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );

	$t_bug = bug_get( $t_bug_id, true );
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	# Check if the current user is allowed to delete the bugnote
	$t_user_id = auth_get_current_user_id();
	$t_reporter_id = bugnote_get_field( $f_bugnote_id, 'reporter_id' );

	/*if ( ( $t_user_id != $t_reporter_id ) || ( OFF == config_get( 'bugnote_allow_user_edit_delete' ) ) ) {
		access_ensure_bugnote_level( config_get( 'delete_bugnote_threshold' ), $f_reponse_id );
	}*/

	helper_ensure_confirmed( lang_get( 'delete_bugnote_sure_msg' ),
							 lang_get( 'delete_bugnote_button' ) );

	reponse_delete( $f_reponse_id );

	# Event integration
	event_signal( 'EVENT_BUGNOTE_DELETED', array( $t_bug_id, $f_reponse_id ) );

	form_security_purge( 'reponse_delete' );

	print_successful_redirect( string_get_bug_view_url( $t_bug_id ) . '#bugnotes' );
