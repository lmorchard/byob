<?php
/**
 * Bootstrap for auth_profiles module
 */

// Since Zend libraries use require_once instead of an autoloader, set up some 
// more include paths to find things
$base = dirname(dirname(__FILE__));
set_include_path(implode(PATH_SEPARATOR, array(
    $base . '/libraries',
    $base . '/vendor',
    get_include_path()
)));

// Initialize the cookie handler for login.
authprofiles::init();
