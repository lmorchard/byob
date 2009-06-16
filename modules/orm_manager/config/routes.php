<?php defined('SYSPATH') or die('No direct access allowed.');

$config['orm_manager'] = 
    'orm_manager/index';
$config['orm_manager/model/(.*)/edit/(.*)'] = 
    'orm_manager/edit/model_name/$1/primary_key/$2';
$config['orm_manager/model/(.*)'] = 
    'orm_manager/list_model/model_name/$1';

