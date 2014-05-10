<?php
	require_once( 'core.php' );

	require_once( 'bug_api.php' );
	require_once( 'bug_group_action_api.php' );

	auth_ensure_user_authenticated();

	helper_begin_long_process();

	$f_action = gpc_get_string( 'action' );
	$f_bug_arr	= gpc_get_int_array( 'bug_arr', array() );

	$t_form_name = 'bug_actiongroup_' . $f_action;

	form_security_validate( $t_form_name );

	bug_group_action_init( $f_action );

	# group bugs by project
	$t_projects_bugs = array();
	foreach( $f_bug_arr as $t_bug_id ) {
		bug_ensure_exists( $t_bug_id );
		$t_bug = bug_get( $t_bug_id, true );

		if ( isset( $t_projects_bugs[$t_bug->project_id] ) ) {
		  $t_projects_bugs[$t_bug->project_id][] = $t_bug_id;
        } else {
		  $t_projects_bugs[$t_bug->project_id] = array( $t_bug_id );
        }
    }

	$t_failed_ids = array();

	foreach( $t_projects_bugs as $t_project_id => $t_bugs ) {
		$g_project_override = $t_project_id;
		foreach( $t_bugs as $t_bug_id ) {
			$t_result = bug_group_action_validate( $f_action, $t_bug_id );
			if( $t_result !== true ) {
				foreach( $t_result as $t_key => $t_value ) {
					$t_failed_ids[$t_key] = $t_value;
				}
			}
			if( !isset( $t_failed_ids[$t_bug_id] ) ) {
				$t_result = bug_group_action_process( $f_action, $t_bug_id );
				if( $t_result !== true ) {
					$t_failed_ids[] = $t_result;
				}
			}
		}
	}

	$g_project_override = null;

	form_security_purge( $t_form_name );

	if ( count( $t_failed_ids ) > 0 ) {
		html_page_top();

		echo '<div align="center">';
		foreach( $t_failed_ids as $t_id => $t_reason ) {
			printf("<p>%s: %s</p>\n", string_get_bug_view_link( $t_id ), $t_reason );
		}

		print_bracket_link( 'my_view_page.php', lang_get( 'proceed' ) );
		echo '</div>';

		html_page_bottom();
	} else {
		print_header_redirect( 'my_view_page.php' );
	}
