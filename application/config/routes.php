<?php defined('SYSPATH') OR die('No direct access allowed.');

$config['admin'] = 
    'admin/index';

$config['admin/model/(.*)/edit/(.*)'] = 
    'admin/edit/model_name/$1/primary_key/$2';

$config['admin/model/(.*)'] = 
    'admin/list_model/model_name/$1';

$config['_default'] = 'index';
