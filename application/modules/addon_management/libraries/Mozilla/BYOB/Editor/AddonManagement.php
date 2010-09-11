<?php
/**
 * BYOB editor module registry
 *
 * @package    Mozilla_BYOB_Editor_AddonManagement
 * @subpackage Libraries
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB_Editor_AddonManagement extends Mozilla_BYOB_Editor {

    public static $max_extensions = 2;
    public static $max_search_plugins = 3;

    /** {{{ Object properties */
    public $id        = 'addon_management';
    public $title     = 'Addons';
    public $view_name = 
        'repacks/edit/edit_addon_management';
    public $review_view_name = 
        'repacks/edit/review_addon_management';
    
    public $assets_dir = '';
    public $personas_extension_id = '10900';
    /** }}} */

    /**
     * Locale should be worked out by this time, so localize the tab title.
     */
    public function l10n_ready()
    {
        $this->title = _('Addons');
    }

    /**
     * Register and initialize this app module.
     */
    public static function register() 
    { 
        $self = parent::register(get_class());

        Event::add('BYOB.builds.perform.afterConfig',
            array($self, 'prepareBuild'));
        Event::add('BYOB.repack.buildDistributionIni',
            array($self, 'filterDistributionIni'));

        // TODO: Move this into config?
        $self->assets_dir = 
            dirname(__FILE__) . '/../../../../repack_assets';

        $self->personas_extension_id =
            Kohana::config('addon_management.personas_extension_id');

        return $self;
    }

    /**
     * Determine whether the current user has permission to access this 
     * editor.
     */
    public function isAllowed($repack)
    {
        return $repack->checkPrivilege('addon_management');
    }

    /**
     * Validate data from incoming editor request.
     */
    public function validate(&$data, $repack, $set=true)
    {
        $default_locale = (!empty($repack->default_locale)) ?
            $repack->default_locale : 'en-US';
        $unlimited_addons = 
            $repack->checkPrivilege('addon_management_unlimited');
        $max_extensions = 
            Mozilla_BYOB_Editor_AddonManagement::$max_extensions;
        $max_search_plugins = 
            Mozilla_BYOB_Editor_AddonManagement::$max_search_plugins;

        $popular_extensions = 
            addon_management::get_popular_extensions();
        $popular_personas = 
            addon_management::get_popular_personas();
        $popular_themes = 
            addon_management::get_popular_themes();
        $popular_searchplugins = 
            addon_management::get_popular_searchplugins();

        $managed_addons = array(
            'extension_ids' => array(),
            'search_plugin_filenames' => array(),
            'persona_url' => null,
            'theme_id' => null,
        );

        $data = Validation::factory($data)
            ->pre_filter('trim');

        $is_valid = $data->validate();

        // Scan through incoming extension IDs for valid choices
        if (!empty($data['extension_ids'])) {
            $allowed_ids = array_keys($popular_extensions);
            foreach ($data['extension_ids'] as $id) {
                if (in_array($id, $allowed_ids)) {
                    $managed_addons['extension_ids'][] = $id;
                }
            }
            if (!$unlimited_addons &&
                    count($managed_addons['extension_ids']) > $max_extensions) {
                $data->add_error('extensions', 'too_many');
                $is_valid = false;
            } 
        }

        // Scan through incoming search plugin filenames for valid choices
        if (!empty($data['search_plugin_filenames'])) {
            foreach ($popular_searchplugins as $locale=>$plugins) {

                $allowed_fns = array();
                foreach ($plugins as $fn=>$plugin) { 
                    $allowed_fns[] = "{$locale}:{$fn}";
                }
                
                $accepted_fns = array();
                foreach ($data['search_plugin_filenames'] as $fn) {
                    if (in_array($fn, $allowed_fns)) {
                        $accepted_fns[] = $fn;
                    }
                }
                
                $count = count($accepted_fns);
                if (!$unlimited_addons && $count > $max_search_plugins) {
                    $data->add_error('search_plugins', 'too_many');
                    $is_valid = false;
                } else {
                    $managed_addons['search_plugin_filenames'] = 
                        array_merge($accepted_fns,
                            $managed_addons['search_plugin_filenames']);
                }

            }
        }

        // If an allowed theme was selected, remember it.
        if (!empty($data['theme_id'])) {
            echo "<br>{$data['theme_id']}<br>";
            if (!empty($popular_themes[$data['theme_id']])) {
                $managed_addons['theme_id'] = $data['theme_id'];
            }
        }

        $persona = null;

        if (!empty($data['persona_url'])) {
            // If a Persona URL is supplied, try loading a Persona from it.
            $persona = Model::factory('persona')
                ->find_by_url($data['persona_url']);
        } else if (!empty($data['persona_url_hash'])) {
            // If a Persona URL hash is chosen, try loading a popular Persona.
            $url_hash = $data['persona_url_hash'];
            if (!empty($popular_personas[$url_hash])) {
                $persona = $popular_personas[$url_hash];
            }
        }

        // If an optional persona is chosen...
        if (!empty($persona)) {
            if ($persona->loaded) {
                // A valid Persona has been chosen, so remember the URL.
                $managed_addons['persona_url'] = $persona->url;
            } else {
                // An invalid Persona URL has been supplied, so squawk.
                $is_valid = false;
                $data->add_error('persona', 'invalid');
            }
        }

        if ($is_valid && $set) {
            $repack->managed_addons = $managed_addons;
        }

        return $is_valid;
    }

    /**
     * Render the review section for addons.
     */
    public function renderReviewSection()
    {
        $repack = Event::$data['repack'];
        $assets_dir = $repack->getAssetsDirectory();
        
        $extensions = array();
        $search_plugins = array();
        $persona = null;
        $theme = null;

        if (!empty($repack->managed_addons)) {
            $managed_addons = $repack->managed_addons;

            $xpi_dir = $assets_dir . "/distribution/extensions";
            if (is_dir($xpi_dir)) {
                $xpi_files = glob("{$xpi_dir}/*.xpi");
                foreach ($xpi_files as $xpi_fn) {
                    $extension = Model::factory('addon')->find_by_xpi_file($xpi_fn);
                    if ($extension->loaded) $extensions[] = $extension;
                }
            }

            foreach ($managed_addons['extension_ids'] as $id) {
                $extension = Model::factory('addon')->find($id);
                if ($extension->loaded) $extensions[] = $extension;
            }

            foreach ($repack->locales as $locale) {
                $locale_plugins = array();
                $suffix = ($locale == $repack->default_locale) ?
                    'common' : "locale/{$locale}";
                $sp_dir = $assets_dir . "/distribution/searchplugins/" . $suffix;
                if (is_dir($sp_dir)) {
                    $sp_files = glob("{$sp_dir}/*.xml");
                    foreach ($sp_files as $fn) {
                        $xml = file_get_contents($fn);
                        $plugin = Model::factory('searchplugin')->loadFromXML($xml);
                        $plugin->filename = basename($fn);
                        $locale_plugins[] = $plugin;
                    }
                }
                if (!empty($locale_plugins)) {
                    $search_plugins[$locale] = $locale_plugins;
                }
            }

            if (!empty($managed_addons['search_plugin_filenames'])) {

                $all_selections = array();
                foreach ($managed_addons['search_plugin_filenames'] as $pair) {
                    if (strpos($pair, ':') === FALSE) $pair = 'en-US:'.$pair;
                    list ($locale, $fn) = explode(':', $pair);
                    if (!isset($all_selections[$locale])) {
                        $all_selections[$locale] = array($fn);
                    } else {
                        $all_selections[$locale][] = $fn;
                    }
                }

                $popular_searchplugins = 
                    addon_management::get_popular_searchplugins();
                foreach ($popular_searchplugins as $locale=>$plugins) {
                    if (empty($all_selections[$locale])) continue;
                    $selections = $all_selections[$locale];
                    $locale_plugins = array();
                    foreach ($plugins as $fn=>$plugin) {
                        if (in_array($fn, $selections)) {
                            $locale_plugins[$fn] = $plugin;
                        }
                    }
                    if (!empty($locale_plugins)) {
                        if (empty($search_plugins[$locale])) {
                            $search_plugins[$locale] = $locale_plugins;
                        } else {
                            $search_plugins[$locale] = array_merge(
                                $locale_plugins, $search_plugins[$locale]
                            );
                        }
                    }
                }

            }

            if (!empty($managed_addons['persona_url'])) {
                $persona = Model::factory('persona')
                    ->find_by_url($managed_addons['persona_url']);
                if (!$persona->loaded) $persona = null;
            }

            if (!empty($managed_addons['theme_id'])) {
                $theme = Model::factory('addon')->find($managed_addons['theme_id']);
                if (!$theme->loaded) $theme = null;
            }

        }

        slot::append(
            'BYOB.repack.edit.review.sections',
            View::factory($this->review_view_name, array_merge(
                Event::$data, array(
                    'extensions' => $extensions,
                    'search_plugins' => $search_plugins,
                    'persona' => $persona,
                    'theme' => $theme,
                )
            ))
        );
    }

    /**
     * Add anything necessary to the distribution INI
     */
    public function filterDistributionIni() {
        $repack = Event::$data['repack'];
        $managed_addons = $repack->managed_addons;

        if (!empty($managed_addons['persona_url'])) {
            $persona = Model::factory('persona')
                ->find_by_url($managed_addons['persona_url']);
            Event::$data['output'] = Mozilla_BYOB_IniConfig::mergeINIs(
                Event::$data['output'], array(
                    'Preferences' => array(
                        'extensions.personas.initial' => 
                            '"'.addslashes($persona->json).'"'
                    ) 
                )
            );
        }
    }

    /**
     * Do what needs doing before performing a repack build.
     */
    public function prepareBuild()
    {
        $repack = Event::$data['repack'];
        $repack_dir = Event::$data['repack_dir'];

        Kohana::log('info', 'Processing managed addons for ' .
            $repack->profile->screen_name . ' - ' . $repack->short_name);
        Kohana::log_save();

        if (!empty($repack->managed_addons)) {

            $managed_addons = $repack->managed_addons;
            
            // If a persona is specified, ensure that the Personas addon is installed.
            // TODO: Is there a way to specify a persona without the addon?
            if (!empty($managed_addons['persona_url'])) {
                $persona = Model::factory('persona')
                    ->find_by_url($managed_addons['persona_url']);
                if ($persona->loaded && !in_array($this->personas_extension_id, $managed_addons['extension_ids'])) {
                    $managed_addons['extension_ids'][] = $this->personas_extension_id;
                }
            }

            // Copy the repack assets over from our local module directory.
            // (eg. the extension installer extension & friends)
            if (!(empty($managed_addons['extension_ids']) && empty($managed_addons['theme_id'])) && is_dir($this->assets_dir)) {
                self::recurseCopy($this->assets_dir, "{$repack_dir}");
            }

            // Create the directory for this repack's addons
            if (!is_dir("$repack_dir/distribution/extensions")) {
                mkdir("$repack_dir/distribution/extensions", 0775, true);
            }

            // Assemble the set of extensions from selected extensions and a 
            // theme (if any)
            $extensions = array();
            foreach ($managed_addons['extension_ids'] as $id) {
                $extension = Model::factory('addon')->find($id);
                if ($extension->loaded)
                    $extensions[] = $extension;
            }
            if (!empty($managed_addons['theme_id'])) {
                $extension = Model::factory('addon')
                    ->find($managed_addons['theme_id']);
                if ($extension->loaded)
                    $extensions[] = $extension;
            }

            $extensions_for_install = array();

            // Copy over each of the XPIs from shared store, add to the 
            // extension installer config.
            foreach ($extensions as $extension) {
                if (!$extension->loaded) continue;
                $extension_path = $extension->updateFiles();
                copy(
                    $extension_path,
                    "{$repack_dir}/distribution/extensions/{$extension->guid}.xpi"
                );
                $extensions_for_install[] = $extension;
                Kohana::log_save();
            }

            // Scan through any uploaded XPIs in the repack assets dir, add to 
            // the extension installer config.
            $xpi_dir = $repack->getAssetsDirectory() . "/distribution/extensions";
            if (is_dir($xpi_dir)) {
                $xpi_files = glob("{$xpi_dir}/*.xpi");
                foreach ($xpi_files as $xpi_fn) {
                    $extension = Model::factory('addon')->find_by_xpi_file($xpi_fn);
                    if ($extension->loaded) $extensions_for_install[] = $extension;
                }
            }

            // Compose and write the config.ini for extension installer.
            $lines = array();
            foreach ($extensions_for_install as $idx=>$extension) {
                $lines[] = "[Extension{$idx}]";
                $info = array(
                    'id'   => $extension->guid,
                    'file' => "{$extension->guid}.xpi",
                    'Version'       => $extension->version,
                    // TODO: Support multi-OS extensions!
                    'OS'            => 'ALL'
                );
                foreach ($info as $key=>$val) {
                    $lines[] = "{$key}={$val}";
                }
            }
            file_put_contents(
                "{$repack_dir}/distribution/extensions/config.ini",
                join("\n", $lines)
            );

            if (!empty($managed_addons['search_plugin_filenames'])) {

                // Create the search plugins directory if necessary
                $sp_base_dir = "{$repack_dir}/distribution/searchplugins";
                if (!is_dir($sp_base_dir)) {
                    mkdir($sp_base_dir, 0775, true);
                }

                // Convert selections into array of locale => filenames.
                $all_selections = array();
                foreach ($managed_addons['search_plugin_filenames'] as $pair) {
                    if (strpos($pair, ':') === FALSE) $pair = 'en-US:'.$pair;
                    list ($locale, $fn) = explode(':', $pair);
                    if (!isset($all_selections[$locale])) {
                        $all_selections[$locale] = array($fn);
                    } else {
                        $all_selections[$locale][] = $fn;
                    }
                }

                // Run through all known plugins and copy the selections.
                // Might seem cumbersome, but it never actually uses 
                // user-supplied paths unless they appear in the known set.
                $popular_searchplugins = 
                    addon_management::get_popular_searchplugins();
                foreach ($popular_searchplugins as $locale=>$plugins) {

                    // Get selections for this locale, or skip ahead if none.
                    if (empty($all_selections[$locale])) continue;
                    $selections = $all_selections[$locale];

                    // Ensure the destination directory exists and is empty.
                    $dest_dir = ( $repack->default_locale == $locale ) ?
                        'common' : "locale/{$locale}";
                    if (!is_dir("{$sp_base_dir}/{$dest_dir}")) {
                        // Create the directory if necessary.
                        mkdir("{$sp_base_dir}/{$dest_dir}", 0775, true);
                    } else {
                        // Delete any existing files, so that unchecked 
                        // selections go away.
                        $existing = glob("{$sp_base_dir}/{$dest_dir}/*.xml");
                        foreach ($existing as $fn) unlink($fn);
                    }

                    // Copy the selected plugins into the destination dir.
                    foreach ($plugins as $fn=>$plugin) {
                        if (!in_array($fn, $selections)) continue;
                        file_put_contents(
                            "{$sp_base_dir}/{$dest_dir}/".basename($fn), 
                            $plugin->asXML()
                        );
                    }

                }

            }

        }
        Kohana::log_save();

    }


    /**
     * Utility function to recursively delete a directory.
     */
    public static function rmdirRecurse($path) {
        $path= rtrim($path, '/').'/';
        if (!is_dir($path)) return;
        $handle = opendir($path);
        for (;false !== ($file = readdir($handle));)
            if($file != "." and $file != ".." ) {
                $fullpath= $path.$file;
                if( is_dir($fullpath) ) {
                    self::rmdirRecurse($fullpath);
                } else {
                    unlink($fullpath);
                }
            }
        closedir($handle);
        rmdir($path);
    } 

    /**
     * Utility function to recursively copy files.
     */
    public static function recurseCopy($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::recurseCopy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    } 

    /**
     * TODO: Get rid of this when PHP 5.3+ can be a requirement
     */
    public static function getInstance() { 
        return parent::getInstance(get_class()); 
    }

}
