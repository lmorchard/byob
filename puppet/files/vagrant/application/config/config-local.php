<?php
$config['core.needs_installation'] = false;
$config['core.site_protocol'] = 'http';
$config['core.site_domain'] = 'dev.byob.mozilla.org';
$config['core.log_threshold'] = 4;
$config['core.internal_cache'] = false;
$config['core.display_errors'] = True;

$config['core.contact_URL'] = 'http://'.$config['core.site_domain'].'/contact/';

$config['model.database'] = 'local';

$config['database.local'] = array(
    'read_shadow'   => 'shadow',
	'benchmark'     => FALSE,
	'persistent'    => FALSE,
	'connection'    => array
	(
		'type'     => 'mysql',
		'user'     => 'byob',
		'pass'     => 'byob',
		'host'     => 'localhost',
		'port'     => FALSE,
		'socket'   => FALSE,
		'database' => 'byob'
	),
	'character_set' => 'utf8',
	'table_prefix'  => '',
	'object'        => TRUE,
	'cache'         => FALSE,
	'escape'        => TRUE
);
$config['database.shadow'] = array(
    'benchmark'     => TRUE,
    'persistent'    => FALSE,
    'connection'    => array
    (
        'type'     => 'mysql',
        'user'     => 'byob',
        'pass'     => 'byob',
        'host'     => 'localhost',
        'port'     => FALSE,
        'socket'   => FALSE,
        'database' => 'byob'
    ),
    'character_set' => 'utf8',
    'table_prefix'  => '',
    'object'        => TRUE,
    'cache'         => TRUE,
    'escape'        => TRUE
);

$config['email.driver'] = 'native';

# A comma separated list of job servers in the format host:port. 
# If no port is specified, it defaults to 4730. 
$config['gearman_events.servers'] = '127.0.0.1:4730';
$config['gearman_events.deferred_events'] = TRUE;
$config['gearman_events.max_jobs'] = 1;

$config['recaptcha.domain_name'] = 'localhost';
$config['recaptcha.public_key']  = '6Lcb7AYAAAAAAGlcMKBsAQiLlD3Z52UnoK873sjU ';
$config['recaptcha.private_key'] = '6Lcb7AYAAAAAAMZy14GCGWjHzLU5ZAWrxnCwnDZF';

$config['repacks.enable_notifications'] = TRUE;

$config['addon_management.popular_extension_ids'] = array(
    '10868',
    '13661',
    '5045',
    '139',

/*    
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
 */
);

// See: https://addons.mozilla.org/en-US/firefox/personas/
$config['addon_management.popular_personas_urls'] = array(
    'https://addons.mozilla.org/en-US/firefox/persona/18781',
    'https://addons.mozilla.org/en-US/firefox/persona/29974',
    'https://addons.mozilla.org/en-US/firefox/persona/111435',
    'https://addons.mozilla.org/en-US/firefox/persona/31997',
    'https://addons.mozilla.org/en-US/firefox/persona/36483',
    'https://addons.mozilla.org/en-US/firefox/persona/46852',
    'https://addons.mozilla.org/en-US/firefox/persona/22518',
    'https://addons.mozilla.org/en-US/firefox/persona/172009',
    'https://addons.mozilla.org/en-US/firefox/persona/166354',
    'https://addons.mozilla.org/en-US/firefox/persona/140992',
    'https://addons.mozilla.org/en-US/firefox/persona/171341',
    'https://addons.mozilla.org/en-US/firefox/persona/172810',
);


$config['addon_management.popular_theme_ids'] = array(
);

