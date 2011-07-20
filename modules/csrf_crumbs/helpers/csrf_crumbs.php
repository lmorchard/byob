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

    /**
     * Set the session token value when one is available. 
     * (eg. on login or session start)
     */
    public static function set_session_token($token)
    {
        return self::$session_token = $token;
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
        $session_token = self::$session_token;

        return implode('-', array(
            $rand_token,
            $time,
            hash_hmac('sha256', implode("\n", array(
                $rand_token, $time, $session_token
            )), $server_secret),
        ));
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

        // Build an expected crumb value and return whether it matches.
        $expected_crumb = self::build_crumb($rand_token, $time);
        return ($expected_crumb == $crumb);
    }

}
