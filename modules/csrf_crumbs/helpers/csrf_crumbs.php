<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * CSRF crumbs helper class.
 *
 * @package    csrf_crumbs
 * @subpackage helpers
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class csrf_crumbs_Core 
{
    /** Session-based token to provide unique per-user data */
    public static $session_token = '';
    public static $SESSION_TOKEN_COOKIE_NAME = 'csrf_token';
    public static $SESSION_TOKEN_COOKIE_EXPIRES = 300; // 5 min to CSRF invalid

    /**
     * Set the session token value when one is available. 
     * (eg. on login or session start)
     */
    public static function set_session_token($token)
    {
        cookie::set(
            self::$SESSION_TOKEN_COOKIE_NAME, 
            $token, 
            self::$SESSION_TOKEN_COOKIE_EXPIRES);
        return self::$session_token = $token;
    }

    /**
     * Get the session token, generating and storing in cookie if necessary
     */
    public static function get_session_token()
    {
        $token = cookie::get(self::$SESSION_TOKEN_COOKIE_NAME, False);
        if (!$token) {
            $token = text::random('alnum', 16);
            self::set_session_token($token);
        }
        return $token;
    }

    /**
     * Clear the current session token
     */
    public static function clear_session_token()
    {
        self::set_session_token('');
    }

    /**
     * Generate a new crumb with random token and current time.
     */
    public static function generate()
    {
        return self::build_crumb(
            text::random('alnum', 16), 
            microtime(true)
        );
    }

    /**
     * Build a crumb from given random token and time, along with configured 
     * server secret and current session token.
     */
    public static function build_crumb($rand_token, $time) {
        $server_secret = Kohana::config('csrf_crumbs.secret');
        $session_token = self::get_session_token();

        $crumb = implode('-', array(
            $rand_token,
            $time,
            hash_hmac('sha256', implode("\n", array(
                $rand_token, $time, $session_token, url::site(url::current())
            )), $server_secret),
        ));
        return $crumb;
    }

    /**
     * Validate a crumb as acceptable.
     *
     * @param string $crumb crumb value to be validated.
     */
    public static function validate($crumb)
    {
        if (!$crumb) return false;

        // First, assert that there are 3 parts.
        $parts = explode('-', $crumb);
        if (3 !== count($parts)) return false;

        // Now, extract the parts.
        list($rand_token, $time, $sig) = $parts;

        // Build an expected crumb value
        $expected_crumb = self::build_crumb($rand_token, $time);
        
        // Return whether the expected crumb matches incoming.
        return ($expected_crumb == $crumb);
    }

}
