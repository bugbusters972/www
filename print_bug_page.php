<?php
	/**
	 * @package UFM
	*
	*
	 * @link 
	 */
	 /**
	  * UFM Core API's
	  */
	require_once( 'core.php' );
	require_once( 'bug_api.php' );
	require_once( 'custom_field_api.php' );
	require_once( 'date_api.php' );
	require_once( 'string_api.php' );
	require_once( 'last_visited_api.php' );

	$f_bug_id = gpc_get_int( 'bug_id' );



define ( 'BUG_VIEW_INC_ALLOW', true );

$tpl_file = __FILE__;
$tpl_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
$tpl_show_page_header = true;
$tpl_force_readonly = false;
$tpl_fields_config_option = 'bug_view_page_fields';

include ( 'dossier_print.php' );


	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'print_bugnote_inc.php' ) ;

	html_body_end();
	html_end();
