<?php
/**
 * The url::redirect() helper uses the HTTP(S) attempted by the client when 
 * composing an absolute URL for a redirect. This hook changes that to the 
 * HTTP protocol configured for the site.
 *
 * @package    Mozilla_BYOB
 * @subpackage hooks
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB_CorrectRedirectHttpProto
{
    function correct_redirect_proto() {
        $uri = Event::$data;
        if (strpos($uri, '://') === FALSE) {
            $uri = url::site($uri, FALSE);
        }
        Event::$data = $uri;
    }
}
Event::add('system.redirect', 
    array('Mozilla_BYOB_CorrectRedirectHttpProto', 'correct_redirect_proto'));
