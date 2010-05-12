<?php
/**
 * Locale selection helper
 *
 * @package    BYOB
 * @subpackage helpers
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
require_once 'product-details/localeDetails.class.php';

/**
 * Login selection helper.
 */
class locale_selection_core {

    /** Locale details instance */
    public static $locale_details = null;

    /** 
     * Pre-selected popular locales.
     *
     * TODO: Move this into config?
     */
    public static $popular_locales = array(
        'en-US', 'en-GB', 'es-AR', 'es-ES', 'de', 'fr', 
        'ja', 'ru', 'zh-CN', 'zh-TW'
    );

    /**
     * Initialize by getting a localeDetails instance.
     */
    public static function init() 
    {
        self::$locale_details = new localeDetails();
    }

    /**
     * Assemble a list of popular locales.
     *
     * TODO: Move this into config?
     */
    public static function get_popular_locales()
    {
        $locales = array();
        foreach (self::$popular_locales as $locale) {
            $locales[$locale] = self::$locale_details->languages[$locale];
        }
        return $locales;
    }

    /**
     * Return the list of all known locales
     */
    public static function get_all_locales()
    {
        return self::$locale_details->getLanguageArraySortedByEnglishName();
    }

    /**
     * Get the details for a locale, or return null.
     *
     * @param  string $locale Code for a locale
     * @reutrn array
     */
    public static function get_locale_details($locale)
    {
        return empty(self::$locale_details->languages[$locale]) ?
            null : self::$locale_details->languages[$locale];
    }

    /**
     * Return a count of available locales.
     *
     * @return integer
     */
    public static function count()
    {
        return count(self::$locale_details->languages);
    }

}
