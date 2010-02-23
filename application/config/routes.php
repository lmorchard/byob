<?php defined('SYSPATH') OR die('No direct access allowed.');

$config['search/([^/]+)/'] =
    'search/$1';

$config['profiles/([^/]+)/'] =
    'repacks/index/screen_name/$1';
$config['profiles/([^/]+)/browsers/'] =                  
    'repacks/index/screen_name/$1';
$config['profiles/([^/]+)/browsers/create'] =            
    'repacks/create';

$config['profiles/([^/]+)/browsers/([^/]+)/?([^/]*)/firstrun'] =  
    'repacks/firstrun/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^/]+)/?([^/]*)/repack.log'] =
    'repacks/repacklog/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^/]+)/?([^/]*)/repack.cfg'] =
    'repacks/repackcfg/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^/]+)/?([^/]*)/distribution.ini'] =
    'repacks/distributionini/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^/]+)/?([^/]*)/repack.json'] =
    'repacks/repackjson/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^/]+)/?([^/]*)/downloads/(.*)'] =  
    'repacks/download/screen_name/$1/short_name/$2/status/$3/filename/$4';

$config['profiles/([^/]+)/browsers/([^;/]+)/?([^;]*);(.*)'] =
    'repacks/$4/screen_name/$1/short_name/$2/status/$3';
$config['profiles/([^/]+)/browsers/([^/]+)/?(.*)'] =              
    'repacks/view/screen_name/$1/short_name/$2/status/$3';

$config['admin'] = 
    'admin/index';
$config['admin/model/(.*)/edit/(.*)'] = 
    'admin/edit/model_name/$1/primary_key/$2';
$config['admin/model/([^;]+);create'] = 
    'admin/edit/model_name/$1/create/true';
$config['admin/model/(.*)'] = 
    'admin/list_model/model_name/$1';
$config['admin/(.*)'] = 
    'admin/$1';

$config['contact']  = 'index/contact';

$config['_default'] = 'index';
