<?php
/**
 * Configuration for addons available for use in repacks
 */
$config['addons'] = array(
    '11950' => array(
        'guid' => 'sharing@addons.mozilla.org',
        'name' => 'Add-on Collector',
    ),
    '10900' => array(
        'guid' => 'personas@christopher.beard',
        'name' => 'Personas for Firefox',
    ),
);
$config['dir'] = dirname(APPPATH) . "/addons";

$config['api_url'] = 
    'https://services.addons.mozilla.org/en-US/firefox/api/1.3/addon/%s';

$config['collections_api_url'] =
    'https://addons.mozilla.org/en-US/firefox/api/1.3/sharing/';
$config['collections_username'] =
    'USERNAME_NEEDS_LOCAL_CONFIG';
$config['collections_password'] =
    'PASSWORD_NEEDS_LOCAL_CONFIG';
