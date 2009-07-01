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
    '1843' => array(
        'guid' => 'firebug@software.joehewitt.com',
        'name' => 'Firebug',
    ),
    '3615' => array(
        'guid' => '{2fa4ed95-0317-4c6a-a74c-5f3e3912c1f9}',
        'name' => 'Delicious Bookmarks',
    ),
);
$config['dir'] = dirname(APPPATH) . "/addons";

$config['api_url'] = 
    'https://services.addons.mozilla.org/en-US/firefox/api/1.3/addon/%s';
