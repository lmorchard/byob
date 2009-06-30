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

require_once('BigOrNot/CookieManager.php');
require_once('Zend/Acl/Assert/Interface.php');
require_once('Zend/Acl/Exception.php');
require_once('Zend/Acl/Resource/Interface.php');
require_once('Zend/Acl/Resource.php');
require_once('Zend/Acl/Role/Interface.php');
require_once('Zend/Acl/Role/Registry/Exception.php');
require_once('Zend/Acl/Role/Registry.php');
require_once('Zend/Acl/Role.php');
require_once('Zend/Acl.php');
require_once('Zend/Exception.php');

// Initialize the cookie handler for login.
authprofiles::init();
