<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Core
 *
 * Default language locale name(s).
 * First item must be a valid i18n directory name, subsequent items are alternative locales
 * for OS's that don't support the first (e.g. Windows). The first valid locale in the array will be used.
 * @see http://php.net/setlocale
 */
$config['language'] = array('en_US', 'English_United States');

/**
 * In most cases, the first path segment of the URL will be expected to be the 
 * desired locale.  However, for some cases, this may be undesirable - eg.
 * /api/v1/foo, etc.  This config setting allows you to list path prefixes where
 * locale detection and redirects will be disabled.
 */
$config['path_exceptions'] = array( 'api' );

/**
 * Mapping supported browser languages to valid system locales.
 *
 * Note that the browser languages are all in lowercase and with hyphens.
 *
 * The system locales must match the system-installed locales, a list of 
 * which can be found at the command line with `localedef --list-archive` 
 *
 * If you pick a mapping that doesn't exist, your pages will come out in 
 * your default language (set in the LANGUAGE_CONFIG class).
 */
$config['supported_languages'] = array(
    'ar'    => 'ar_EG.utf8',
    'ca'    => 'ca_ES.utf8',
    'cs'    => 'cs_CZ.utf8',
    'da'    => 'da_DK.utf8',
    'de'    => 'de_DE.utf8',
    'en-us' => 'en_US.utf8',
    'el'    => 'el_GR.utf8',
    'es-es' => 'es_ES.utf8',
    'eu'    => 'eu_ES.utf8',
    'fa'    => 'fa_IR.utf8',
    'fi'    => 'fi_FI.utf8',
    'fr'    => 'fr_FR.utf8',
    'ga-ie' => 'ga_IE.utf8',
    'he'    => 'he_IL.utf8',
    'hu'    => 'hu_HU.utf8',
    'id'    => 'id_ID.utf8',
    'it'    => 'it_IT.utf8',
    'ja'    => 'ja_JP.utf8',
    'ko'    => 'ko_KR.utf8',
    'mn'    => 'mn_MN.utf8',
    'nl'    => 'nl_NL.utf8',
    'pl'    => 'pl_PL.utf8',
    'pt-br' => 'pt_BR.utf8',
    'pt-pt' => 'pt_PT.utf8',
    'ro'    => 'ro_RO.utf8',
    'ru'    => 'ru_RU.utf8',
    'sk'    => 'sk_SK.utf8',
    'sq'    => 'sq_AL.utf8',
    'sv-se' => 'sv_SE.utf8',
    'uk'    => 'uk_UA.utf8',
    'vi'    => 'vi_VN.utf8',
    'zh-cn' => 'zh_CN.utf8',
    'zh-tw' => 'zh_TW.utf8',

    // Silly talks, see bin/silly-po.sh
    'xx-b1ff'   => 'xx_b1ff.utf8',
    'xx-chef'   => 'xx_chef.utf8',
    'xx-pirate' => 'xx_pirate.utf8',
    'xx-warez'  => 'xx_warez.utf8'
);

// Languages that work, but to which we won't send a user ourselves
// (dropdown, lang sniffing)
$config['valid_languages'] = array(
    'cy'      => 'cy_GB.utf8',
    'sr'      => 'sr_CS.utf8',
    'sr-Latn' => 'sr_CS.utf8',
    'tr'      => 'tr_TR.utf8'
);
$config['valid_languages'] = array_merge(
    $config['supported_languages'], $config['valid_languages']
);

// If a supported language is displayed right to left, add it to this array.
$config['rtl_languages'] = array( 'ar', 'fa', 'fa-ir', 'he' );

/**
 * Locale timezone. Defaults to use the server timezone.
 * @see http://php.net/timezones
 */
$config['timezone'] = '';
