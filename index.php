<?php

require_once( 'core.php' );
if ( auth_is_user_authenticated() ) {
	print_header_redirect( config_get( 'default_home_page' ) );
} else {
	print_header_redirect( 'login_page.php' );
}
