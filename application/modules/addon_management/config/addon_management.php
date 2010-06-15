<?php
/**
 * Configuration for addons available for use in repacks
 */
$config['api_url'] = 
    'https://services.addons.mozilla.org/en-US/firefox/api/1.3/addon/%s';
$config['dir'] = 
    dirname(APPPATH) . "/addons";
$config['collections_api_url'] =
    'https://addons.mozilla.org/en-US/firefox/api/1.3/sharing/';
$config['collections_username'] =
    'USERNAME_NEEDS_LOCAL_CONFIG';
$config['collections_password'] =
    'PASSWORD_NEEDS_LOCAL_CONFIG';

$config['popular_extension_ids'] = array(
    '13661',
    '9924',
    '3863',
    '2108',
    '271',
    '590',
    '748',
    '684',
    '60',
    '1843',
    '2464',
);

$config['popular_personas_urls'] = array(
    'https://addons.mozilla.org/en-US/firefox/persona/29974',
    'https://addons.mozilla.org/en-US/firefox/persona/15114',
    'https://addons.mozilla.org/en-US/firefox/persona/15131',
    'https://addons.mozilla.org/en-US/firefox/persona/61916',
    'https://addons.mozilla.org/en-US/firefox/persona/17848',
    'https://addons.mozilla.org/en-US/firefox/persona/94252',
);

$config['popular_theme_ids'] = array(
);
