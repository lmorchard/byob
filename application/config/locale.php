<?php
/**
 * Configuration for locale and l10n module.
 * See modules/l10n/config/locale.php for base defaults.
 */

// Paths starting with /api and /pfs are not used for locale selection.
$config['path_exceptions'] = array( 
    'gearman_events', 'messagequeue', 'util', 'captcha', 'phpunit' 
);
