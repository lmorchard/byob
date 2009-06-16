<?php
/**
 * Custom URL helpers
 *
 * @package    LMO_Utils
 * @subpackage helpers
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class url extends url_Core
{
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
        return self::full($_SERVER['REQUEST_URI']);
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
