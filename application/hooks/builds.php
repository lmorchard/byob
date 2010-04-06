<?php
/**
 * Hooks to perform repack builds in response to state changes
 *
 * @package    Mozilla_BYOB_RepackBuilds
 * @subpackage hooks
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB_RepackBuilds {

    /**
     * Initialize and wire up event responders.
     */
    public static function init()
    {
        // Respond to repack state changes
        Event::add(
            "BYOB.repack.changeState",
            array(get_class(), 'handleStateChange')
        );

        // Defer performance of build actions to a queue.
        DeferredEvent::add(
            'BYOB.builds.perform',
            array(get_class(), 'performBuild')
        );
        DeferredEvent::add(
            'BYOB.builds.release',
            array(get_class(), 'releaseBuilds')
        );
        DeferredEvent::add(
            'BYOB.builds.delete',
            array(get_class(), 'deleteBuilds')
        );
    }

    /**
     * Dispatcher for repack state change events.
     */
    public static function handleStateChange()
    {
        if (!Kohana::config('repacks.enable_builds')) return;

        $old_data =& Event::$data; // HACK: Event::run() is not re-entrant.
        switch (Event::$data['new_state']) {
            case 'requested':
                Event::run('BYOB.builds.perform', Event::$data); break;
            case 'released':
                Event::run('BYOB.builds.release', Event::$data); break;
            case 'canceled':
            case 'rejected':
            case 'reverted':
            case 'deleted':
                Event::run('BYOB.builds.delete', Event::$data); break;
        }
        Event::$data =& $old_data;
    }
    
    /**
     * Delete all builds for the repack.
     */
    public static function deleteBuilds()
    {
        $repack = ORM::factory('repack', Event::$data['repack']['id']);

        $base_paths = array(
            Kohana::config('repacks.downloads_public'),
            Kohana::config('repacks.downloads_private')
        );
        foreach ($base_paths as $base_path) {
            $repack_name = "{$repack->profile->screen_name}_{$repack->short_name}";
            $dest = "{$base_path}/{$repack_name}";

            Kohana::log('info', 'Deleting builds for ' .
                $repack->profile->screen_name . ' - ' . $repack->short_name);
            Kohana::log('debug', "rmdir $dest");
            Kohana::log_save();

            self::rmdirRecurse($dest);
        }

        // Forget any files the repack knew about.
        $repack->files = array();
        $repack->save();
    }

    /**
     * Move private builds to public release path.
     */
    public static function releaseBuilds()
    {
        $repack = ORM::factory('repack', Event::$data['repack']['id']);

        $src_path  = Kohana::config('repacks.downloads_private');
        $dest_path = Kohana::config('repacks.downloads_public');

        $repack_name = "{$repack->profile->screen_name}_{$repack->short_name}";

        $src  = "{$src_path}/{$repack_name}";
        $dest = "{$dest_path}/{$repack_name}";
        if (is_dir($dest)) self::rmdirRecurse($dest);
        $cmd = rename($src, $dest);

        Kohana::log('debug', "Moved {$src} to {$dest}");
    }

    /**
     * Perform the process of repack builds
     */
    public static function performBuild($run_script=true)
    {
        $repack = ORM::factory('repack', Event::$data['repack']['id']);
        $repack->beginRelease();

        // HACK: Since rebuilds might not have been edited (eg. is part of a 
        // global rebuild), ensure older-format bookmarks have been converted.
        $repack->convertOlderBookmarks();

        try {

            Kohana::log('info', 'Processing repack for ' .
                $repack->profile->screen_name . ' - ' . $repack->short_name);
            Kohana::log_save();

            $workspace = Kohana::config('repacks.workspace');
            $script    = Kohana::config('repacks.repack_script');

            $downloads_private = 
                Kohana::config('repacks.downloads_private');

            // Clean up and make the repack directory.
            $repack_dir =
                "$workspace/partners/{$repack->profile->screen_name}_{$repack->short_name}";
            if (is_dir($repack_dir)) {
                self::rmdirRecurse($repack_dir);
            }
            mkdir("$repack_dir/distribution", 0775, true);

            Kohana::log('debug', "Repack directory at {$repack_dir}");
            Kohana::log_save();

            // Generate the repack configs.
            file_put_contents("$repack_dir/repack.cfg",
                $repack->buildRepackCfg());
            file_put_contents("$repack_dir/distribution/distribution.ini",
                $repack->buildDistributionIni());

            // Check for selected addons...
            if (!empty($repack->addons)) {

                // Create the directory for this repack's addons
                mkdir("$repack_dir/extensions", 0775, true);

                $addon_model = new Addon_Model();
                foreach ($repack->addons as $addon_id) {

                    // Look for selected addons, skip any that are unknown.
                    $addon = $addon_model->find($addon_id);
                    if (!$addon) continue;

                    // Update the addon files and copy them into the repack.
                    $addon_dir = $addon->updateFiles();
                    self::recurseCopy(
                        $addon_dir, 
                        "{$repack_dir}/extensions/{$addon->guid}"
                    );

                }
            }

            // Dump out the search plugins, if any.
            $search_plugins = $repack->search_plugins;
            if (!empty($search_plugins)) {
                $sp_base_dir = "{$repack_dir}/distribution/searchplugins/common";
                mkdir($sp_base_dir, 0775, true);
                
                foreach ($search_plugins as $fn => $plugin) {
                    file_put_contents("{$sp_base_dir}/{$fn}", $plugin->asXML());
                }
            }

            if ($run_script) {

                // Remember the original directory and change to the repack dir.
                $origdir = getcwd();
                chdir($workspace);

                // Execute the repack script and capture output / state.
                $output = array();
                $state = 0;
                $repack_name = "{$repack->profile->screen_name}_{$repack->short_name}";
                $cmd = join(' ', array(
                    "{$script}",
                    "-d partners",
                    "-p $repack_name",
                    "-v {$repack->product->version}",
                    "-n {$repack->product->build}",
                    ">partners/{$repack_name}/repack.log 2>&1"
                ));
                Kohana::log('debug', "Executing {$cmd}...");
                Kohana::log_save();
                exec($cmd, $output, $state);

                // Restore original directory.
                chdir($origdir);

                if (0 != $state) {
                    Kohana::log('error', "Failure in {$script} with state $state");
                    $repack->failRelease();
                    return;
                }

                Kohana::log('debug', "Success in {$script} with state $state");
                Kohana::log_save();

                // Record all the filenames generated by the repack.
                $src = "{$workspace}/repacked_builds/{$repack->product->version}".
                    "/build{$repack->product->build}/{$repack_name}";
                $files = array();
                foreach (glob("{$src}/*/*/*") as $fn) {
                    if (is_file($fn)) $files[] = str_replace("$src/", '', $fn);
                }
                $repack->files = $files;
                $repack->save();

                // Move the repacks to the private downloads area
                $dest = "{$downloads_private}/{$repack_name}";
                if (is_dir($dest)) self::rmdirRecurse($dest);
                $cmd = rename($src, $dest);

                Kohana::log('debug', "Moved {$src} to {$dest}");
                Kohana::log_save();

            }

            Kohana::log('info', 'Finished repack for ' . 
                $repack->profile->screen_name . ' - ' . $repack->short_name);
            Kohana::log_save();

            $repack->finishRelease();

        } catch (Exception $e) {
            $repack->failRelease($e->getMessage());
        }
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

}

Event::add('system.ready', array('Mozilla_BYOB_RepackBuilds', 'init'));
