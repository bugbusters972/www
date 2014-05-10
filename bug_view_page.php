<?php
/**
 * UFM Core API's
 */
require_once( 'core.php' );

// Copy 'bug_id' parameter into 'id' so it is found by the view page.
$_GET['id'] = gpc_get_int( 'bug_id' );

include 'view.php';
