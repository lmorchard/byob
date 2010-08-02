<?php
/**
 * Main static package for gettext module.
 *
 * TODO: Include something like http://fligtar.com/faketext/faketext.phps ?
 */
class Gettext_Main {

    static $domain;
    static $domain_path;

    static $original_url     = '';
    static $modified_url     = '';

    static $default_language = 'en-US';
    static $default_locale   = 'en_US';
    static $default_domain   = 'messages';

    static $current_language = 'en-US';
    static $current_locale   = 'en_US';
    static $current_dir      = 'ltr';

    /**
     * Initialize the module.
     */
    public static function init()
    {
        if (extension_loaded('gettext')) {
            Event::add_before(
                'system.routing',
                array('Router', 'setup'),
                array(get_class(), 'intercept_routing')
            );
            Event::add(
                'system.post_routing',
                array(get_class(), 'post_routing')
            );
        }
    }

    /**
     * Before the router has a chance to work on the URL, intercept the URL it 
     * found and try to extract a locale from it.  This allows all routes
     * to be agnostic about the presence of the language path.
     */
    public static function intercept_routing()
    {
        self::set_domain();

        // Split the current URL into path segments
        self::$original_url = Router::$current_uri;
        $segs = explode('/', Router::$current_uri);

        if (!count($segs) || empty($segs[0])) {
            // There's no first segment, or it's empty, so detect the current 
            // language and force a redirect to that path.
            list($lang, $locale) = self::detect_language();
            url::redirect(); // Detected language auto-prepended to path.
            die;
        }

        $langs = array();
        $supported_languages = Kohana::config('locale.supported_languages');
        $path_exceptions = Kohana::config('locale.path_exceptions');
        if (!in_array($segs[0], $path_exceptions)) {
            // Since the first path segment isn't an exception, pluck it off 
            // the list and use it as the first language choice.  Then, 
            // reconstitute the router's idea of current URI without that 
            // segment.
            $langs[] = $lang_seg = array_shift($segs);
            if (empty($supported_languages[strtolower($lang_seg)])) {
                // bug 580421: Ensure that this is a supported language
                Event::run('system.404');
                exit;
            }
            Router::$current_uri = self::$modified_url = implode('/', $segs);
        }

        // Finally, detect and set the locale based on the URL segment, if it 
        // was available.
        list($lang, $locale) = self::detect_language($langs);
        self::set_locale($locale);
    }

    /**
     * After routing has completed, restore the original URL with the 
     * locale that was extracted. This helps URL helpers work better. 
     */
    public static function post_routing()
    {
        Router::$current_uri = self::$original_url;
        Router::$complete_uri = Router::$current_uri.Router::$query_string;
        array_unshift(Router::$segments, self::$current_language);
    }

    /**
     * Set the path for a domain and set the domain.
     *
     * @param string Name of the domain
     * @param string Path of the domain
     */
    public static function set_domain($domain=null, $path=null)
    {
        if (null===$domain)
            $domain = self::$default_domain;
        if (null===$path)
            $path = APPPATH . 'locale';

        self::$domain = $domain;
        self::$domain_path = $path;

        bindtextdomain(self::$domain, self::$domain_path);
        bind_textdomain_codeset(self::$domain, 'UTF-8');
        textdomain(self::$domain);
    }

    /**
     * Attempt to detect language from valid candidates.
     *
     * @param  array High priority languages
     * @return array language, locale
     */
    public static function detect_language($langs=null)
    {
        // Build priority list of parameter, ?lang, Accept-Language, and 
        // default language.
        if (null === $langs)
            $langs = array();
        if (!empty($_GET['lang']))
            $langs[] = $_GET['lang'];
        $langs = array_merge($langs, Kohana::user_agent('languages'));
        $langs[] = self::$default_language;

        // Grab mappings for valid languages to system locales.
        $valid_languages = Kohana::config('locale.valid_languages');
        $rtl_languages   = Kohana::config('locale.rtl_languages');

        // Look for the first valid language from the priority list.
        foreach ($langs as $lang) {
            
            // Skip unsupported languages.
            $u_lang = strtolower($lang);
            if (!array_key_exists($u_lang, $valid_languages)) continue;

            // Found a valid language/local mapping, so use it.
            self::$current_dir = in_array($u_lang, $rtl_languages) ?
                'rtl' : 'ltr';
            self::$current_language = $lang;
            self::$current_locale = Kohana::$locale = $locale =
                $valid_languages[$u_lang];

            return array( $lang, $locale );
        }

        // Failed to find a match, so return the defaults.
        // Shouldn't happen in practice, because the defaults should match up 
        // to something valid.
        return array( self::$default_language, self::$default_locale );
    }

    /**
     * Set the locale for the application.
     *
     * @param array List of high-priority locales
     */
    public static function set_locale($locale=null)
    {
        setlocale(LC_COLLATE, $locale);
        setlocale(LC_MONETARY, $locale);
        setlocale(LC_NUMERIC, $locale);
        setlocale(LC_TIME, $locale);
        if (defined('LC_MESSAGES')) {
            setlocale(LC_MESSAGES, $locale);
        }
        putenv("LANG=" . $locale);
        return true;
    }

}

if (!extension_loaded('gettext')) {
    // TODO: Include something like http://fligtar.com/faketext/faketext.phps ?

    function _($msgid) {
        return $msgid;
    }

} else {

    if (!function_exists('pgettext')) {
        /**
         * See: http://us2.php.net/manual/en/book.gettext.php#89975
         */
        function pgettext($context, $msgid)
        {
            $context_string = "{$context}\004{$msgid}";
            $translation = dcgettext('messages', $context_string, LC_MESSAGES);
            if ($translation == $context_string)  return $msgid;
            else  return $translation;
        }     

        function npgettext($context, $msgid, $msgid_plural, $num) {
            $contextString = "{$context}\004{$msgid}";
            $contextStringp = "{$context}\004{$msgid_plural}";
            $translation = ngettext($contextString, $contextStringp, $num);
            if ($translation == $contextString ||
                $translation == $contextStringp)  return $msgid;
            else  return $translation;
        }
    }

}
