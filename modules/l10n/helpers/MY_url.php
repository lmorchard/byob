<?php
/**
 * L10N enabled URL helper
 *
 * Ensures current language is prepended to all site() URLs, unless the 
 * initial path segment is found in the list of path exceptions.
 *
 * @package    l10n
 * @subpackage helpers
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
require_once Kohana::find_file('libraries','Gettext/Main');

class url extends url_Core {

    /**
     * Return the current URL with the language path snipped off the front, if
     * present.
     *
     * @param   boolean  include the query string
     * @return  string
     */
    public static function current($qs = false)
    {
        $current = parent::current($qs);
        $lang_path = Gettext_Main::$current_language . '/';
        if (0 !== strpos($current, $lang_path)) {
            return $current;
        } else {
            return substr($current, strlen($lang_path));
        }
    }

    /**
     * Auto-prepend the language to all site URLs.
     */
    public static function site($uri = '', $protocol = FALSE)
    {
        $segs = explode('/', $uri);
        $path_exceptions = Kohana::config('locale.path_exceptions');
        if (!in_array($segs[0], $path_exceptions)) {
            $uri = Gettext_Main::$current_language . '/' . $uri;
        }
        return parent::site($uri, $protocol);
    }

    /**
     * Produce a true absolute URL for the current request.
     *
     * @param boolean Whether or not to include the current query string.
     * @param array Additional query param data
     */
    public static function full($uri='', $qs=FALSE, $more=null) 
    {
        $data = $qs ? $_GET : array();
        if (!empty($more)) {
            $data = array_merge($data, $more);
        }

        if( ($qpos = strpos($uri,'?')) !== FALSE) {
            $uri = substr($uri, 0, $qpos);
        }

        return url::site($uri) . 
            (!empty( $data ) ? '?'.http_build_query($data) : '');
    }

    /**
     * Produce a true absolute URL for the current request.
     *
     * @param boolean Whether or not to include the current query string.
     * @param array Additional query param data
     */
    public static function full_current($qs=FALSE, $more=null) 
    {
        return self::full(self::current());
    }

    /**
     * Filter that normalizes URLs, attempting to account for insignificant user 
     * input variations.  Used both in form validation and in model munging.
     *
     * TODO: Consider some site-specific normalizations, such as:
     *      * Stripping Amazon affiliate IDs
     *      * Cleaning up YouTube links
     *      * etc...
     */
    public static function normalize($url)
    {
        // Bail if the URL is empty or null.
        if (!$url) return '';
        
        $url_parts = parse_url($url);

        // Default to http scheme if missing.
        if (!isset($url_parts['scheme'])) {
            // Reparse the URL since the scheme has changed.
            $url_parts = parse_url("http://".$url);
        } elseif($url_parts['scheme'] == 'feed') {
            // feed: is a fake scheme, really meant to be http:
            $url_parts['scheme'] = 'http';
        }
        
        // Hosts are normalized as lower-case
        if (isset($url_parts['host'])) {
            $url_parts['host'] = 
                mb_strtolower($url_parts['host'], 'UTF-8');
        }

        // If the path but not host is parsed, assume the path was meant to
        // be a host since we don't accept relative URLs.
        $path_schemes = array('http', 'https', 'ftp');
        if (in_array($url_parts['scheme'], $path_schemes) && 
                isset($url_parts['path']) && !isset($url_parts['host'])) {
            $url_parts['host'] = trim($url_parts['path'], '/');
            unset($url_parts['path']);
        }

        // Remove the standard web server port, if present.
        if (isset($url_parts['port']) && $url_parts['port'] == 80) {
            unset($url_parts['port']);
        }

        // If no path given, add a trailing slash.
        if (!isset($url_parts['path'])) {
            $url_parts['path'] = '/';
        }

        // Time to piece the URL back together from parts.
        $new_url = array( $url_parts['scheme'], ":" );

        // If there's a host, append the '//' along with user, password,
        // and host.  Note that some URL schemes don't have these.
        if (isset($url_parts['host'])) {
            $new_url[] = "//";
            if (isset($url_parts['user'])) {
                $new_url[] = $url_parts['user'];
                if (isset($url_parts['pass'])) {
                    $new_url[] = ":" . $url_parts['pass'];
                }
                $new_url[] = '@';
            }
            $new_url[] = $url_parts['host'];
        }

        // Append the path part now.
        $new_url[] =  $url_parts['path'];

        // Then, include the query parameters if any.
        if (isset($url_parts['query'])) {
            $new_url[] = "?" . $url_parts['query'];
        }

        // Finally, append the document fragment if any.
        if (isset($url_parts['fragment'])) {
            $new_url[] = "#" . $url_parts['fragment'];
        }

        return join('', $new_url);
    }

}
