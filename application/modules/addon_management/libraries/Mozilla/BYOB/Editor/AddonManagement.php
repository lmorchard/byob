<?php
/**
 * BYOB editor module registry
 *
 * @package    Mozilla_BYOB_Editor_AddonManagement
 * @subpackage Libraries
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB_Editor_AddonManagement extends Mozilla_BYOB_Editor {

    /** {{{ Object properties */
    public $id        = 'addon_management';
    public $title     = 'Addons';
    public $view_name = 
        'repacks/edit/edit_addon_management';
    public $review_view_name = 
        'repacks/edit/review_addon_management';
    
    public $assets_dir = '';
    /** }}} */

    /**
     * Register and initialize this app module.
     */
    public static function register() 
    { 
        $self = parent::register(get_class());

        Event::add('BYOB.builds.perform.afterConfig',
            array($self, 'prepareBuild'));

        // TODO: Move this into config?
        $self->assets_dir = 
            dirname(__FILE__) . '/../../../../repack_assets';

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
        }

        // Scan through incoming search plugin filenames for valid choices
        if (!empty($data['search_plugin_filenames'])) {
            $allowed_fns = array_keys($popular_searchplugins);
            foreach ($data['search_plugin_filenames'] as $fn) {
                if (in_array($fn, $allowed_fns)) {
                    $managed_addons['search_plugin_filenames'][] = $fn;
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

            $sp_dir = $assets_dir . "/distribution/searchplugins/common";
            if (is_dir($sp_dir)) {
                $sp_files = glob("{$sp_dir}/*.xml");
                foreach ($sp_files as $fn) {
                    $xml = file_get_contents($fn);
                    $plugin = Model::factory('searchplugin')->loadFromXML($xml);
                    $plugin->filename = basename($fn);
                    $search_plugins[] = $plugin;
                }
            }

            if (!empty($managed_addons['search_plugin_filenames'])) {
                $popular_searchplugins = 
                    addon_management::get_popular_searchplugins();
                foreach ($managed_addons['search_plugin_filenames'] as $fn) {
                    if (empty($popular_searchplugins[$fn])) continue;
                    $search_plugins[] = $popular_searchplugins[$fn];
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

            // Copy the repack assets over from our local module directory.
            // (eg. the extension installer extension & friends)
            if (is_dir($this->assets_dir)) {
                self::recurseCopy($this->assets_dir, "{$repack_dir}");
            }

            // Create the directory for this repack's addons
            mkdir("$repack_dir/distribution/extensions", 0775, true);

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
                    'ExtensionId'   => $extension->guid,
                    'ExtensionFile' => "{$extension->guid}.xpi",
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
                $sp_base_dir = "{$repack_dir}/distribution/searchplugins/common";
                if (!is_dir($sp_base_dir)) {
                    mkdir($sp_base_dir, 0775, true);
                }

                // Run through the selected search plugins and copy them 
                // into the repack assets from shared popular storage if found.
                $popular_searchplugins = 
                    addon_management::get_popular_searchplugins();
                foreach ($managed_addons['search_plugin_filenames'] as $fn) {
                    if (empty($popular_searchplugins[$fn])) continue;
                    file_put_contents(
                        "{$sp_base_dir}/{$fn}", 
                        $popular_searchplugins[$fn]->asXML()
                    );
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
