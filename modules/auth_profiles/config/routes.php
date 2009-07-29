<?php defined('SYSPATH') or die('No direct access allowed.');

$config['home'] =     'auth_profiles/home';
$config['register'] = 'auth_profiles/register';
$config['login'] =    'auth_profiles/login';
$config['logout'] =   'auth_profiles/logout';

$config['verifyemail'] = 
    'auth_profiles/verifyemail';
$config['reverifyemail/(.*)'] = 
    'auth_profiles/reverifyemail/login_name/$1';

$config['changepassword'] = 'auth_profiles/changepassword';
$config['forgotpassword'] = 'auth_profiles/forgotpassword';

$config['settings'] = 'auth_profiles/settings';

$config['profiles/([^/]+)/settings'] = 
    'auth_profiles/settings/screen_name/$1';

$config['profiles/([^/]+)/settings/basics/changepassword'] = 
    'auth_profiles/changepassword/screen_name/$1';

$config['profiles/([^/]+)/settings/basics/changeemail'] = 
    'auth_profiles/changeemail/screen_name/$1';

$config['profiles/([^/]+)/settings/basics/details'] = 
    'auth_profiles/editprofile/screen_name/$1';
