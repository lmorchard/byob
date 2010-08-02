<?php
/**
 * Localization controller
 *
 * @package    l10n
 * @subpackage controllers
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class L10n_Controller extends Controller {

    /**
     * Return the JSON version of the current locale .mo strings.
     */
    function translations($locale='')
    {
        if (empty($locale)) { 
            // HACK: Strip off the .utf8 locale suffix if present.
            $locale = str_replace('.utf8', '', Gettext_Main::$current_locale);
        }
        
        // Make sure the .mo file exists, throwing a 404 if not.
        $mo_fn = APPPATH . 'locale/' . $locale .'/LC_MESSAGES/messages.mo';
        if (!is_file($mo_fn)) { 
            return Event::run('system.404'); 
        }

        // Support Last-Modified / If-Modified-Since headers for caching.
        $last_modified = date("r", filemtime($mo_fn));
        header('Last-Modified: ' . $last_modified);
        if (array_key_exists("HTTP_IF_MODIFIED_SINCE", $_SERVER)) {
            if ($last_modified === $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
                header('HTTP/1.1 304 Not Modified');
                return;
            }
        }

        // Load and parse the .mo to extract messages.
        $parser = new Gettext_MOParser();
        $parser->load($mo_fn, $locale);

        // Wrap a callback around the JSON, if necessary, and respond with the 
        // result.
        $callback = $this->input->get('callback');
        if ($callback) {
            header('Content-Type: text/javascript');
            // Whitelist the callback to alphanumeric and a few mostly harmless
            // characters, none of which can be used to form HTML or escape a 
            // JSONP call wrapper.
            $callback = preg_replace(
                '/[^0-9a-zA-Z\(\)\[\]\,\.\_\-\+\=\/\|\\\~\?\!\#\$\^\*\: \'\"]/', '', 
                $callback
            );
            echo "$callback(";
        } else {
            header('Content-Type: application/json');
        }
        echo $parser->asJSON($locale);
        if ($callback) echo ')';
    }

}
