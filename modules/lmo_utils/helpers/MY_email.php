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

        self::send(
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


    /**
     * Send an email message.
     * 
     * Tweaked to accomodate lists of recipients, with optional nested 
     * name/address sets.
     *
     * @param   string|array  recipient email (and name), or an array of To, Cc, Bcc names
     * @param   string|array  sender email (and name)
     * @param   string        message subject
     * @param   string        message body
     * @param   boolean       send email as HTML
     * @return  integer       number of emails sent
     */
    public static function send($to, $from, $subject, $message, $html = FALSE)
    {
        // Connect to SwiftMailer
        (email::$mail === NULL) and email::connect();

        // Determine the message type
        $html = ($html === TRUE) ? 'text/html' : 'text/plain';

        // Create the message
        $message = new Swift_Message($subject, $message, $html, '8bit', 'utf-8');

        if (is_string($to))
        {
            // Single recipient
            $recipients = new Swift_Address($to);
        }
        elseif (is_array($to))
        {
            if (isset($to[0]) AND isset($to[1]))
            {
                // Create To: address set
                $to = array('to' => $to);
            }

            // Create a list of recipients
            $recipients = new Swift_RecipientList;

            foreach ($to as $method => $list_of_sets)
            {
                if ( ! in_array($method, array('to', 'cc', 'bcc')))
                {
                    // Use To: by default
                    $method = 'to';
                }

                // Create method name
                $method = 'add'.ucfirst($method);

                if (!is_array($list_of_sets))
                    $list_of_sets = array($list_of_sets);

                foreach ($list_of_sets as $set) {
                    if (is_array($set))
                    {
                        // Add a recipient with name
                        $recipients->$method($set[0], $set[1]);
                    }
                    else
                    {
                        // Add a recipient without name
                        $recipients->$method($set);
                    }
                }
            }
        }

        if (is_string($from))
        {
            // From without a name
            $from = new Swift_Address($from);
        }
        elseif (is_array($from))
        {
            // From with a name
            $from = new Swift_Address($from[0], $from[1]);
        }

        return email::$mail->send($message, $recipients, $from);
    }

}
