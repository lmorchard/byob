<?php
/**
 * Recaptcha adaptor as a static helper.
 */
class recaptcha_Core
{
    private static $last_response = null;

    /**
     * Generate HTML for the recaptcha.
     *
     * @return string HTML for recaptcha
     */
    public static function html($error=NULL, $use_ssl=NULL)
    {
        if (null === $use_ssl) {
            // By default, set the use_ssl flag to match the current request.
            $use_ssl = isset($_SERVER['HTTPS']);
        }
        return recaptcha_get_html(
            Kohana::config('recaptcha.public_key'), $error, $use_ssl
        );
    }

    /**
     * Check the recaptcha data in the current request.
     *
     * @return boolean whether or not the recaptcha was valid
     */
    public static function check()
    {
        $input = new Input();
        self::$last_response = recaptcha_check_answer(
            Kohana::config('recaptcha.private_key'),
            $input->server('REMOTE_ADDR'),
            $input->post('recaptcha_challenge_field'),
            $input->post('recaptcha_response_field')
        );
        return self::$last_response->is_valid;
    }

    /**
     * Return error results for last call to check()
     *
     * @return string error results
     */
    public static function error()
    {
        return empty(self::$last_response) ? 
            null : self::$last_response->error;
    }

    /**
     * Callback usable with validation library.
     *
     * @param Validation Validation object
     * @param string name of field being validated
     */
    public static function callback($valid, $field)
    {
        if (!self::check()) {
            $valid->add_error($field, self::error());
        }
    }

}
