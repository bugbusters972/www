<?php
	require_once( 'core.php' );

	$t_allow_perm_login = ( ON == config_get( 'allow_permanent_cookie' ) );

	$f_username		= gpc_get_string( 'username', '' );
	$f_password		= gpc_get_string( 'password', '' );
	$f_perm_login	= $t_allow_perm_login && gpc_get_bool( 'perm_login' );
	$t_return		= string_url( string_sanitize_url( gpc_get_string( 'return', config_get( 'default_home_page' ) ) ) );
	$f_from			= gpc_get_string( 'from', '' );
	$f_secure_session = gpc_get_bool( 'secure_session', false );

	$f_username = auth_prepare_username($f_username);
	$f_password = auth_prepare_password($f_password);

	gpc_set_cookie( config_get_global( 'cookie_prefix' ) . '_secure_session', $f_secure_session ? '1' : '0' );

	if ( auth_attempt_login( $f_username, $f_password, $f_perm_login ) ) {
		session_set( 'secure_session', $f_secure_session );

		$t_redirect_url = 'login_cookie_test.php?return=' . $t_return;

	} else {
		$t_redirect_url = 'login_page.php?return=' . $t_return .
			'&error=1&username=' . urlencode( $f_username ) .
			'&secure_session=' . ( $f_secure_session ? 1 : 0 );
		if( $t_allow_perm_login ) {
			$t_redirect_url .= '&perm_login=' . ( $f_perm_login ? 1 : 0 );
		}

		if ( HTTP_AUTH == config_get( 'login_method' ) ) {
			auth_http_prompt();
			exit;
		}
	}

	print_header_redirect( $t_redirect_url );
