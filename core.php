<?php
if ( file_exists( 'mantis_offline.php' ) && !isset( $_GET['mbadmin'] ) ) {
	include( 'mantis_offline.php' );
	exit;
}
date_default_timezone_set("America/Martinique");
$g_request_time = microtime(true);

ob_start();

/**
 * Load supplied constants
 */
require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'constant_inc.php' );

/**
 * Load user-defined constants (if required)
 */
if ( file_exists( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'custom_constants_inc.php' ) ) {
	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'custom_constants_inc.php' );
# Check for the old name of the user-defined constants file (to be deprecated in 1.3)
} else if ( file_exists( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'custom_constant_inc.php' ) ) {
	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'custom_constant_inc.php' );
}

$t_config_inc_found = false;

/**
 * Include default configuration settings
 */
require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'config_defaults_inc.php' );

# config_inc may not be present if this is a new install
if ( file_exists( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'config_inc.php' ) ) {
	require_once( dirname( __FILE__ ).DIRECTORY_SEPARATOR.'config_inc.php' );
	$t_config_inc_found = true;
}

# Allow an environment variable (defined in an Apache vhost for example)
#  to specify a config file to load to override other local settings
$t_local_config = getenv( 'MANTIS_CONFIG' );
if ( $t_local_config && file_exists( $t_local_config ) ){
	require_once( $t_local_config );
	$t_config_inc_found = true;
}
unset( $t_local_config );

# Attempt to find the location of the core files.
$t_core_path = dirname(__FILE__).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR;
if (isset($GLOBALS['g_core_path']) && !isset( $HTTP_GET_VARS['g_core_path'] ) && !isset( $HTTP_POST_VARS['g_core_path'] ) && !isset( $HTTP_COOKIE_VARS['g_core_path'] ) ) {
	$t_core_path = $g_core_path;
}

$g_core_path = $t_core_path;
unset( $t_core_path );

/*
 * Set include paths
 */
define ( 'BASE_PATH' , dirname( __FILE__ ) );
$mantisLibrary = time()>0?BASE_PATH . DIRECTORY_SEPARATOR . 'library':'';
$mantisCore = $g_core_path;

/*
 * Prepend the application/ and tests/ directories to the
 * include_path.
 */
$path = array(
    $mantisCore,
    $mantisLibrary,
    get_include_path()
    );
set_include_path( implode( PATH_SEPARATOR, $path ) );

/*
 * Unset global variables that are no longer needed.
 */
unset($mantisRoot, $mantisLibrary, $mantisCore, $path);

require_once( 'mobile_api.php' );

if ( strlen( $GLOBALS['g_mantistouch_url'] ) > 0 && mobile_is_mobile_browser() ) {
	$t_url = sprintf( $GLOBALS['g_mantistouch_url'], $GLOBALS['g_path'] );

	if ( OFF == $g_use_iis ) {
		header( 'Status: 302' );
	}

	header( 'Content-Type: text/html' );

	if ( ON == $g_use_iis ) {
		header( "Refresh: 0;$t_url" );
	} else {
		header( "Location: $t_url" );
	}

	exit; # additional output can cause problems so let's just stop output here
}

# load UTF8-capable string functions
require_once( 'utf8/utf8.php' );
require_once( UTF8 . '/str_pad.php' );

# Include compatibility file before anything else
require_once( 'php_api.php' );

# Define an autoload function to automatically load classes when referenced.
function __autoload( $className ) {
	global $g_core_path;

	$t_require_path = $g_core_path . 'classes' . DIRECTORY_SEPARATOR . $className . '.class.php';

	if ( file_exists( $t_require_path ) ) {
		require_once( $t_require_path );
		return;
	}

	$t_require_path = BASE_PATH . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'rssbuilder' . DIRECTORY_SEPARATOR . 'class.' . $className . '.inc.php';

	if ( file_exists( $t_require_path ) ) {
		require_once( $t_require_path );
		return;
	}
}

spl_autoload_register( '__autoload' );

if ( ($t_output = ob_get_contents()) != '') {
	echo 'Possible Whitespace/Error in Configuration File - Aborting. Output so far follows:<br />';
	echo var_dump($t_output);
	die;
}
unset( $t_output );

require_once( 'utility_api.php' );
require_once( 'compress_api.php' );

compress_start_handler();

if ( false === $t_config_inc_found ) {
	if( php_sapi_name() == 'cli' ) {
		echo "Error: config_inc.php file not found; ensure MantisBT is properly setup.\n";
		exit(1);
	}

	# if not found, redirect to the admin page to install the system
	# this needs to be long form and not replaced by is_page_name as that function isn't loaded yet
	if ( !( isset( $_SERVER['SCRIPT_NAME'] ) && ( 0 < strpos( $_SERVER['SCRIPT_NAME'], 'admin' ) ) ) ) {
		if ( OFF == $g_use_iis ) {
			header( 'Status: 302' );
		}
		header( 'Content-Type: text/html' );

		if ( ON == $g_use_iis ) {
			header( "Refresh: 0;url=admin/install.php" );
		} else {
			header( "Location: admin/install.php" );
		}

		exit; # additional output can cause problems so let's just stop output here
	}
}

# Load rest of core in separate directory.

require_once( 'config_api.php' );
require_once( 'logging_api.php' );

# Load internationalization functions (needed before database_api, in case database connection fails)
require_once( 'lang_api.php' );

# error functions should be loaded to allow database to print errors
require_once( 'error_api.php' );
require_once( 'helper_api.php' );

# DATABASE WILL BE OPENED HERE!!  THE DATABASE SHOULDN'T BE EXPLICITLY
# OPENED ANYWHERE ELSE.
require_once( 'database_api.php' );

# PHP Sessions
require_once( 'session_api.php' );

# Initialize Event System
require_once( 'event_api.php' );
require_once( 'events_inc.php' );

# Plugin initialization
require_once( 'plugin_api.php' );
if ( !defined( 'PLUGINS_DISABLED' ) ) {
	plugin_init_installed();
}

# Authentication and user setup
require_once( 'authentication_api.php' );
require_once( 'project_api.php' );
require_once( 'project_hierarchy_api.php' );
require_once( 'user_api.php' );
require_once( 'access_api.php' );

# Wiki Integration
if( config_get_global( 'wiki_enable' ) == ON ) {
	require_once( 'wiki_api.php' );
	wiki_init();
}

# Display API's
require_once( 'http_api.php' );
require_once( 'html_api.php' );
require_once( 'gpc_api.php' );
require_once( 'form_api.php' );
require_once( 'print_api.php' );
require_once( 'collapse_api.php' );

#nouvelles fonctions

#fonction Champ créé les champs
function Champ ($name, $tag, $type){
	$x = '$f_'.$name;
	echo '<li '.helper_alternate_class().'><label class="description" for="'.$name.'" >';
	echo print_documentation_link( $name ).'</label>';
	
	if ($tag == 'select') {
	echo '<div><'.$tag.' name="'.$name.'">';
	print_enum_string_option_list( $name, $x );
	echo '</'.$tag.'></div>';
	}
	
	else if ( $type=='checkbox'){
	$x =array();
	echo '<span>';
	print_enum_string_checkbox_list( $name, $x);
	echo '</span>';
	}
	else if ( $type=='radio'){
	echo '<span>';
	print_enum_string_radio_list( $name, $x);
	echo '</span>';
	}
	else if($tag == 'input' || $tag == 'textarea'){
	echo '<div><'.$tag.' name="'.$name.'"></'.$tag.'></div>';
	}
	
	echo '</li>';
}


function Cedit ($name, $tag, $type,$p_bugnote_id){
	
	$actuel = ($p_bugnote_id)?bugnote_recup_field( $p_bugnote_id, $name):$GLOBALS['tpl_bug']->{$name};

	echo '<li '.helper_alternate_class().'><label class="description">';
	echo print_documentation_link( $name ).'</label>';
	
	if ($tag == 'select') {
	echo '<div><'.$tag.' name="'.$name.'">';
	print_enum_string_option_list( $name, $actuel );
	echo '</'.$tag.'></div>';
	}
	
	else if ($type=='checkbox'){
	$val = ($p_bugnote_id)?bugnote_recup_field($p_bugnote_id,$name):$GLOBALS['tpl_bug']->{$name};
	$numz = explode("+", $val);
	echo '<span>';
	print_enum_string_checkbox_list( $name, $numz);
	echo '</span>';
	}
	
	else if ($type=='radio'){
	echo '<span>';
	print_enum_string_radio_list( $name, $actuel);
	echo '</span>';
	}
	
	else if($tag == 'input'){
	echo '<div><'.$tag.' name="'.$name.'" value="'.string_display_line($actuel).'"></'.$tag.'></div>';
	}
	
	else if($tag == 'textarea'){
	echo '<div><'.$tag.' name="'.$name.'">'.string_display_line($actuel).'</'.$tag.'></div>';
	}
	
	echo '</li>';
}

function Resp_get($name, $reponse_id){
$query = "SELECT $name
		          	FROM bugnote_reponse_table
		          	WHERE id=" . db_param();
	$result = db_query_bound( $query, Array( $reponse_id ) );

	return db_result( $result );
}

function Respedit ($name, $tag, $type,$reponse_id){
	
	$actuel = Resp_get($name, $reponse_id);
#si une donnée est présente en base
	if ($actuel!=null) {
	echo '<li '.helper_alternate_class().'><label class="description">';
	echo print_documentation_link( $name ).'</label>';
	
	
		if ($tag == 'select') {
		echo '<div><'.$tag.' name="'.$name.'">';
		print_enum_string_option_list( $name, $actuel );
		echo '</'.$tag.'></div>';
		}
		
		else if ($type=='checkbox'){
		$z = explode("+", $actuel);
		foreach($z as $a) {if (strlen($a)>0){
		$b[] = $a;}}
		echo '<span>';
		print_enum_string_checkbox_list( $name, $b);
		echo '</span>';
		}
		
		else if ($type=='radio'){
		echo '<span>';
		print_enum_string_radio_list( $name, $actuel);
		echo '</span>';
		}
		
		else if($tag == 'input'){
		echo '<div><'.$tag.' name="'.$name.'" value="'.string_display_line($actuel).'"></'.$tag.'></div>';
		}
		
		else if($tag == 'textarea'){
		echo '<div><'.$tag.' name="'.$name.'">'.string_display_line($actuel).'</'.$tag.'></div>';
		}
	
	echo '</li>';
	} else {
#sinon, créer un champ insert

		if ($tag == 'select') {
		Champ ($name, 'select', '');
		}
		
		else if ($type=='checkbox'){
		Champ ($name, 'input', 'checkbox');
		}
		
		else if ($type=='radio'){
		Champ ($name, 'input', 'radio');
		}
		
		else if($tag == 'input'){
			Champ ($name, 'input', '');
		}
		
		else if($tag == 'textarea'){
		Champ ($name, 'textarea', '');
		}
		
	}
}

function Affiche ($jeff) {
echo '<li><label class="description">', lang_get( $jeff ), '</label>';
echo '<p>'.$GLOBALS['tpl_'.$jeff].'</p></li>';
}


function Impression ($jeff) {
echo '<tr class="print"><td width="250">', lang_get( $jeff ), '</td>';
echo '<td width="250">'.$GLOBALS['tpl_'.$jeff].'</td></tr>';
}


function Bnaffiche_str($jeff,$bug, $num=null){
echo '<li><label class="description">', lang_get( $jeff ), '</label>';
if ($num){
echo Bndecode($jeff,$bug);
} else {
echo bugnote_recup_field( $bug, $jeff);
}
echo'</li>';
}


function Bnaffiche_int($jeff,$bug){
echo '<li><label class="description">', lang_get( $jeff ), '</label>';
echo '<p>'.bugnote_recup_field( $bug, $jeff).'</p></li>';
}


function Bndecode($jeff,$bug){
return get_enum_element($jeff, bugnote_recup_field($bug, $jeff));
}


function Bnexp ($jeff, $Bn=null){
	
	$val = ($Bn)?bugnote_recup_field($Bn,$jeff):$GLOBALS['tpl_'.$jeff];
	if($val){
		echo '<li><label class="description">', lang_get( $jeff ), '</label>';
		$numz = explode("+", $val);
			foreach ($numz as $num){
				if(is_numeric($num)){echo '<p>'.get_enum_element($jeff,$num).'</p>';};
			}
		echo'</li>';
	}
};

function Respexp ($jeff,$val){
	if($val){
		echo '<li><label class="description">', lang_get( $jeff ), '</label>';
		$numz = explode("+", $val);
			foreach ($numz as $num){
				if(is_numeric($num)){echo '<p>'.get_enum_element($jeff,$num).'</p>';};
			}
		echo'</li>';
	}
};

function age($naiss)  {
  list($annee, $mois, $jour) = split('[-.]', $naiss);
  $today['mois'] = date('n');
  $today['jour'] = date('j');
  $today['annee'] = date('Y');
  $annees = $today['annee'] - $annee;
  if ($today['mois'] <= $mois) {
    if ($mois == $today['mois']) {
      if ($jour > $today['jour'])
        $annees--;
      }
    else
      $annees--;
    }
  return $annees;
}
  
function encDt($str) {
$datez = explode('/',$str);
return mktime(0, 0, 0, $datez[1], $datez[0], $datez[2]);
}

function Statexp($name){
while ($ligne = db_fetch_array( $GLOBALS[$name] )){
		$f= explode('+',$ligne[$name]);
		foreach($f as $g){
			if (strlen($g)>0){
			$k[]= $g;
			}
		}
	}
if (is_array($k)){
foreach(array_count_values($k) as $key=>$value){
echo '<tr><td>'.get_enum_element($name,$key).'</td><td>'.$value.'</td></tr>';}
}
}

function Impstatexp($name){
while ($ligne = db_fetch_array( $GLOBALS[$name] )){
		$f= explode('+',$ligne[$name]);
		foreach($f as $g){
			if (strlen($g)>0){
			$k[]= $g;
			}
		}
	}
if (is_array($k)){
foreach(array_count_values($k) as $key=>$value){
echo get_enum_element($name,$key).';'.$value."\n";}
}
}

function TRstat($name){
while ($ligne = db_fetch_array( $GLOBALS[$name] )){
echo '<tr><td>'.get_enum_element($name,$ligne[$name]).'</td><td>'.$ligne['nombre'].'</td></tr>';
}
}

function Imprimstat($name){
while ($ligne = db_fetch_array( $GLOBALS[$name] )){
echo get_enum_element($name,$ligne[$name]).';'.$ligne['nombre']."\n";
}
}
#fin nouvelles fonctions

if ( !isset( $g_login_anonymous ) ) {
	$g_login_anonymous = true;
}

# Attempt to set the current timezone to the user's desired value
# Note that PHP 5.1 on RHEL/CentOS doesn't support the timezone functions
# used here so we just skip this action on RHEL/CentOS plateformes.
if ( function_exists( 'timezone_identifiers_list' ) ) {
	if ( !is_blank ( config_get_global( 'default_timezone' ) ) ) {
		// if a default timezone is set in config, set it here, else we use php.ini's value
		// having a timezone set avoids a php warning
		date_default_timezone_set( config_get_global( 'default_timezone' ) );
	} else {
		# To ensure proper detection of timezone settings issues, we must not
		# initialize the default timezone when executing admin checks
		if( basename( $_SERVER['SCRIPT_NAME'] ) != 'check.php' ) {
			config_set_global( 'default_timezone', date_default_timezone_get(), true );
		}
	}
	if ( auth_is_user_authenticated() ) {
		date_default_timezone_set( user_pref_get_pref( auth_get_current_user_id(), 'timezone' ) );
	}
}

if ( !defined( 'MANTIS_INSTALLER' ) ) {
	collapse_cache_token();
}

// custom functions (in main directory)
/** @todo Move all such files to core/ */
require_once( 'custom_function_api.php' );
$t_overrides = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'custom_functions_inc.php';
if ( file_exists( $t_overrides ) ) {
	require_once( $t_overrides );
}
unset( $t_overrides );

// set HTTP response headers
http_all_headers();

// push push default language to speed calls to lang_get
if ( !isset( $g_skip_lang_load ) ) {
	lang_push( lang_get_default() );
}

# signal plugins that the core system is loaded
event_signal( 'EVENT_CORE_READY' );

