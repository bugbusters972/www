<?php
	require_once( 'core.php' );

	require_once( 'bug_api.php' );

	form_security_validate( 'bug_monitor_add' );

	$f_bug_id = gpc_get_int( 'bug_id' );
	$t_bug = bug_get( $f_bug_id, true );
	$f_username = gpc_get_string( 'username', '' );

	$t_logged_in_user_id = auth_get_current_user_id();

	if ( is_blank( $f_username ) ) {
		$t_user_id = $t_logged_in_user_id;
	} else {
		$t_user_id = user_get_id_by_name( $f_username );
		if ( $t_user_id === false ) {
			$t_user_id = user_get_id_by_realname( $f_username );

			if ( $t_user_id === false ) {
				error_parameters( $f_username );
				trigger_error( ERROR_USER_BY_NAME_NOT_FOUND, E_USER_ERROR );
			}
		}
	}

	if ( user_is_anonymous( $t_user_id ) ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, E_USER_ERROR );
	}

	bug_ensure_exists( $f_bug_id );

	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	if ( $t_logged_in_user_id == $t_user_id ) {
		access_ensure_bug_level( config_get( 'monitor_bug_threshold' ), $f_bug_id );
	} else {
		access_ensure_bug_level( config_get( 'monitor_add_others_bug_threshold' ), $f_bug_id );
	}

	bug_monitor( $f_bug_id, $t_user_id );
	
	form_security_purge( 'bug_monitor_add' );


	print_successful_redirect_to_bug( $f_bug_id );
