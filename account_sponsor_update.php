<?php

	require_once( 'core.php' );

	require_once( 'email_api.php' );

	form_security_validate( 'account_sponsor_update' );

	auth_ensure_user_authenticated();

	$f_bug_list = gpc_get_string( 'buglist', '' );
	$t_bug_list = explode( ',', $f_bug_list );

	foreach ( $t_bug_list as $t_bug ) {
		list( $t_bug_id, $t_sponsor_id ) = explode( ':', $t_bug );
		$c_bug_id = (int) $t_bug_id;

		bug_ensure_exists( $c_bug_id ); # dies if bug doesn't exist

		access_ensure_bug_level( config_get( 'handle_sponsored_bugs_threshold' ), $c_bug_id ); # dies if user can't handle bug

		$t_bug = bug_get( $c_bug_id );
		$t_sponsor = sponsorship_get( (int) $t_sponsor_id );

		$t_new_payment = gpc_get_int( 'sponsor_' . $c_bug_id . '_' . $t_sponsor->id, $t_sponsor->paid );
		if ( $t_new_payment != $t_sponsor->paid ) {
			sponsorship_update_paid( $t_sponsor_id, $t_new_payment );
		}
	}

	form_security_purge( 'account_sponsor_update' );

	$t_redirect = 'account_sponsor_page.php';
	html_page_top( null, $t_redirect );

	echo '<br /><div align="center">';

	echo lang_get( 'payment_updated' ) . '<br />';

	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect, lang_get( 'proceed' ) );
	echo '</div>';
	html_page_bottom();
