<?php defined('SYSPATH') OR die('No direct access allowed.');

$config['profiles/([^/]+)/'] =
    'repacks/index/screen_name/$1';
$config['profiles/([^/]+)/browsers/'] =                  
    'repacks/index/screen_name/$1';
$config['profiles/([^/]+)/browsers;create'] =            
    'repacks/edit/create/true';
$config['profiles/([^/]+)/browsers/([^;]+);delete'] =    
    'repacks/delete/screen_name/$1/short_name/$2';
$config['profiles/([^/]+)/browsers/([^;]+);edit'] =      
    'repacks/edit/screen_name/$1/short_name/$2';
$config['profiles/([^/]+)/browsers/([^;]+);release'] =   
    'repacks/release/screen_name/$1/short_name/$2';
$config['profiles/([^/]+)/browsers/([^/]+)/startpage'] = 
    'repacks/startpage/screen_name/$1/short_name/$2';
$config['profiles/([^/]+)/browsers/([^/]+)/firstrun'] =  
    'repacks/firstrun/screen_name/$1/short_name/$2';
$config['profiles/([^/]+)/browsers/([^/]+)/xpi-config.ini'] =  
    'repacks/xpiconfigini/screen_name/$1/short_name/$2';
$config['profiles/([^/]+)/browsers/([^/]+)/distribution.ini'] =  
    'repacks/distributionini/screen_name/$1/short_name/$2';
$config['profiles/([^/]+)/browsers/(.*)'] =              
    'repacks/view/screen_name/$1/short_name/$2';

$config['admin'] = 
    'admin/index';

$config['admin/model/(.*)/edit/(.*)'] = 
    'admin/edit/model_name/$1/primary_key/$2';

$config['admin/model/(.*)'] = 
    'admin/list_model/model_name/$1';

$config['_default'] = 'index';
