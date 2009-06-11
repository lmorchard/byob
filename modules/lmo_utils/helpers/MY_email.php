<?php
/**
 * Custom email helpers
 *
 * @package    LMO_Utils
 * @subpackage helpers
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class email extends email_Core
{

    /**
	 * Send an email message using a rendered view.
	 *
	 * @param   string|array  recipient email (and name), or an array of To, Cc, Bcc names
	 * @param   string        message view or view name
     * @param   array         view variables
	 * @param   boolean       send email as HTML
	 * @return  integer       number of emails sent
     */
    public static function send_view($to, $view, $vars=null, $html=FALSE)
    {
        if (!is_object($view)) {
            $view = View::factory($view);
        }
        if (null===$vars) {
            $vars = array();
        }
        list($headers, $body) = self::parse_message(
            $view->set($vars)->render()
        );
        email::send(
            $to, $headers['From'], $headers['Subject'], $body, $html
        );
    }

    /**
     * Parse an email message for headers and body.
     *
     * Headers (eg. From: and Subject:) are separated by a blank line 
     * from the body.
     *
     * @param  string email message
     * @return array  headers, body
     */
    public static function parse_message($message) 
    {
        $headers = array();
        $body    = '';
        
        $lines = explode("\n", $message);
        while (!empty($lines)) {
            
            $line = trim(array_shift($lines));
            
            if (empty($line)) {
                $body = join("\n", $lines);
                break;
            }

            if (FALSE !== strpos($line, ': ')) {
                list($name, $value) = explode(': ', $line);
                $headers[$name] = $value;
            }

        }

        return array($headers, $body);
    }

}
