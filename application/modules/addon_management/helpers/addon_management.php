<?php
/**
 * Addon management helper
 *
 * @package    BYOB
 * @subpackage helpers
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class addon_management_core {

    /**
     * Build list of popular extension choices.
     */
    public function get_popular_extensions()
    {
        $addon_ids = Kohana::config('addon_management.popular_extension_ids');
        $addons = array();
        foreach ($addon_ids as $id) {
            $addon = Model::factory('addon')->find($id);
            if ($addon->loaded) $addons[$id] = $addon;
        }
        return $addons;
    }

    /**
     * Build list of popular search plugins for all locales.
     */
    public function get_popular_searchplugins()
    {
        $locales = locale_selection::get_available_locale_codes();
        $search_dir = dirname(dirname(__FILE__)) . '/search_plugins';

        $all_plugins = array();

        $common_files = glob("{$search_dir}/common/*.xml");

        foreach ($locales as $locale) {
            $plugins = array();

            $prefix = "locale/{$locale}";
            if (is_dir("{$search_dir}/{$prefix}")) {
                $files = glob("{$search_dir}/{$prefix}/*.xml");
            } else {
                $prefix = "common"; 
                $files = $common_files;
            }

            foreach ($files as $fn) {
                $xml = file_get_contents($fn);
                $plugin = Model::factory('searchplugin')->loadFromXML($xml);
                $plugins[$prefix.'/'.basename($fn)] = $plugin;
            };
            
            $all_plugins[$locale] = $plugins;
        }

        return $all_plugins;
    }

    /**
     * Build a list of popular personas indexed by URL MD5
     */
    public function get_popular_personas()
    {
        $persona_urls = Kohana::config('addon_management.popular_personas_urls');
        $personas = array();
        foreach ($persona_urls as $url) {
            $persona = Model::factory('persona')->find_by_url($url);
            if ($persona->loaded) $personas[md5($url)] = $persona;
        }
        return $personas;
    }

    /**
     *
     */
    public function get_popular_themes()
    {
        $addon_ids = Kohana::config('addon_management.popular_theme_ids');
        $addons = array();
        foreach ($addon_ids as $id) {
            $addon = Model::factory('addon')->find($id);
            if ($addon->loaded) $addons[$id] = $addon;
        }
        return $addons;
    }

}
