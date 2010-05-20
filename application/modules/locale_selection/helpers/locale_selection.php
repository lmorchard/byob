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

    /** Newest product record, listing available locales */
    public static $latest_product = null;

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
     * Look up the most recent product for repacks.
     */
    public static function get_latest_product()
    {
        if (null == self::$latest_product) {
            self::$latest_product = ORM::factory('product')
                ->orderby('created','DESC')
                ->limit(1)
                ->find();
        }
        return self::$latest_product;
    }

    /**
     * Get a list of the locale codes available for the latest product.
     */
    public static function get_available_locale_codes()
    {
        return explode(' ', self::get_latest_product()->locales); 
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
     * Return the list of all available locales
     */
    public static function get_all_locales()
    {
        $avail_locales = self::get_available_locale_codes();
        $locales = array();
        foreach ($avail_locales as $locale) {
            $locales[$locale] = self::$locale_details->languages[$locale];
        }
        return $locales;
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
        $avail_locales = self::get_available_locale_codes();
        return count($avail_locales);
    }

}
