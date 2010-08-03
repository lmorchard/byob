<?php
/**
 * Initialization hook for BYOB
 *
 * @package    Mozilla_BYOB
 * @subpackage hooks
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB {

    /**
     * Initialize and wire up event responders.
     */
    public static function init()
    {
        // HACK: Attempt to ensure log file is always group-writable
        @chmod(Kohana::log_directory().date('Y-m-d').'.log'.EXT, 0664);
        
        // Ensure caching varies by cookie (eg. login), browser, and gzip/non-gzip 
        header('Vary: Cookie,User-Agent,Accept-Encoding', true);

    }

    /**
     * Let all registered editors know that locale is ready.
     */
    public static function l10n_ready()
    {
        Mozilla_BYOB_EditorRegistry::l10n_ready();
    }

}
Event::add('system.ready', array('Mozilla_BYOB', 'init'));
Event::add('l10n.ready', array('Mozilla_BYOB', 'l10n_ready'));
