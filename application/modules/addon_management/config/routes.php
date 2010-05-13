<?php defined('SYSPATH') OR die('No direct access allowed.');

$config['profiles/([^/]+)/browsers/([^;/]+)/?([^/]*)/addons;(.*)'] =
    'addonmanagement/$4/screen_name/$1/short_name/$2/status/$3';

