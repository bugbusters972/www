<?php
require_once( 'core.php' );

define ( 'BUG_VIEW_INC_ALLOW', true );

$tpl_file = __FILE__;
$tpl_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
$tpl_show_page_header = true;
$tpl_force_readonly = false;
$tpl_fields_config_option = 'bug_view_page_fields';

include ( 'bug_view_inc.php' );
