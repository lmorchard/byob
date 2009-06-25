<?php
/**
 * Main auth profiles library
 *
 * @package    auth_profiles
 * @subpackage libraries
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class AuthProfiles
{
    public static $cookie_manager = null;
    public static $cookie_name = 'auth_profiles';
    public static $user_data = null;
    public static $profile = null;
    public static $login = null;

    /**
     * Iniitalize the helper.
     */
    public static function init()
    {
        require_once Kohana::find_file('vendor', 'BigOrNot/CookieManager');
        self::$cookie_manager = new BigOrNot_CookieManager(
            Kohana::config('auth_profiles.secret'),
            array()
        );
        if (Kohana::config('auth_profiles.cookie_name', FALSE, FALSE))
            self::$cookie_name = Kohana::config('auth_profiles.cookie_name');
    }

    /**
     * Bounce to login, with jump back to URL.
     *
     * @param string URL for post-login jump, defaulting to current URL
     */
    public static function redirect_login($url=null)
    {
        if (null===$url) 
            $url = '/'.url::current(TRUE);
        return url::redirect(
            url::base() . '/login?jump=' . rawurlencode($url)
        );
    }

    /**
     * Create a new authentication cookie.
     *
     * @param string user name
     * @param mixed data associated with logged in user
     */
    public static function login($user_name, $login, $profile)
    {
        $duration = Kohana::config('auth_profiles.login_duration');
        if (empty($duration)) $duration = ( 52 * 7 * 24 * 60 * 60 );
        self::$cookie_manager->setCookie(
            self::$cookie_name, 
            serialize(array(
                'login_name' => $login->login_name, 
                'profile_id' => $profile->id
            )),
            $user_name,
            time() + $duration,
            Kohana::config('auth_profiles.cookie_path')
        );
    }

    /**
     * Destroy the current authenticated login.
     */
    public static function logout()
    {
        self::$cookie_manager->deleteCookie(
            self::$cookie_name,
            Kohana::config('auth_profiles.cookie_path')
        );
        return self::$user_data = null;
    }

    /**
     * Return the data for the currently logged in user, if any.
     *
     * @return mixed
     */
    public static function get_user_data()
    {
        if (null===self::$user_data) {

            $data = self::$cookie_manager->getCookieValue(self::$cookie_name);
            self::$user_data = $data ? unserialize($data) : null;

            if (empty(self::$user_data['login_name']) || 
                    empty(self::$user_data['profile_id'])) {
                // Force cookie clear if data is invalid.
                return self::logout();
            }

        }
        return self::$user_data;
    }

    /**
     * Determine whether there's a valid existing authenticated login.
     *
     * @return boolean
     */
    public static function is_logged_in()
    {
        $data = self::get_user_data();
        return !empty( $data );
    }

    /**
     * Return the data for the current login.
     *
     * @param  string name of a login property, or null for the whole login record.
     * @param  mixed  default value
     * @return mixed  value or login record
     */
    public static function get_login($key=null, $default=null)
    {
        $user_data = self::get_user_data();

        if (empty($user_data)) {
            return $default;
        }

        if (null === self::$login) {
            self::$login = ORM::factory('login', self::$user_data['login_name']);
            if (empty(self::$login) || !self::$login->active) {
                // Force cookie clear if no such login, or login disabled.
                self::logout();
                return $default;
            }
        }

        if (null===$key) {
            return self::$login;
        } else {
            return isset(self::$login->{$key}) ?
                self::$login->{$key} : $default;
        }
    }

    /**
     * Return the data for the current logged in profile.
     *
     * @param  string name of a profile property, or null for the whole login record.
     * @param  mixed  default value
     * @return mixed  value or profile record
     */
    public static function get_profile($key=null, $default=null)
    {
        $user_data = self::get_user_data();

        if (empty($user_data)) {
            return $default;
        }

        if (null === self::$login) {
            self::$profile = ORM::factory('profile', self::$user_data['profile_id']);
            if (!self::$profile->loaded) {
                self::logout();
                return $default;
            }
        }

        if (null===$key) {
            return self::$profile;
        } else {
            return isset(self::$profile->{$key}) ?
                self::$profile->{$key} : $default;
        }

    }

}
