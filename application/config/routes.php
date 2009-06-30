<?php defined('SYSPATH') OR die('No direct access allowed.');

$config['profiles/([^/]+)/'] =
    'repacks/index/screen_name/$1';
$config['profiles/([^/]+)/browsers/'] =                  
    'repacks/index/screen_name/$1';
$config['profiles/([^/]+)/browsers;create'] =            
    'repacks/edit/create/true';

$config['profiles/([^/]+)/browsers/([^;/]+)/?([^;]*);edit'] =      
    'repacks/edit/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^;/]+)/?([^;]*);delete'] =    
    'repacks/delete/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^;/]+)/?([^;]*);release'] =   
    'repacks/release/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^;/]+)/?([^;]*);cancel'] =   
    'repacks/cancel/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^;/]+)/?([^;]*);approve'] =   
    'repacks/approve/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^;/]+)/?([^;]*);reject'] =   
    'repacks/reject/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^;/]+)/?([^;]*);revert'] =   
    'repacks/revert/screen_name/$1/short_name/$2/status/$3';

// HACK: REMOVE THESE SIMULATION URLS?
$config['profiles/([^/]+)/browsers/([^;/]+)/?([^;]*);begin'] =   
    'repacks/begin/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^;/]+)/?([^;]*);fail'] =   
    'repacks/fail/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^;/]+)/?([^;]*);finish'] =   
    'repacks/finish/screen_name/$1/short_name/$2/status/$3';

$config['profiles/([^/]+)/browsers/([^/]+)/?([^/]*)/firstrun'] =  
    'repacks/firstrun/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^/]+)/?([^/]*)/repack.cfg'] =
    'repacks/repackcfg/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^/]+)/?([^/]*)/distribution.ini'] =
    'repacks/distributionini/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^/]+)/?([^/]*)/downloads/(.*)'] =  
    'repacks/download/screen_name/$1/short_name/$2/status/$3/filename/$4';
$config['profiles/([^/]+)/browsers/([^/]+)/?(.*)'] =              
    'repacks/view/screen_name/$1/short_name/$2/status/$3';

$config['admin'] = 
    'admin/index';

$config['admin/model/(.*)/edit/(.*)'] = 
    'admin/edit/model_name/$1/primary_key/$2';

$config['admin/model/(.*)'] = 
    'admin/list_model/model_name/$1';

$config['_default'] = 'index';
