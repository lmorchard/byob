<?php
/**
 * Initialization hook for BYOB
 *
 * @package    Mozilla_BYOB
 * @subpackage hooks
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB {

    /**
     * Initialize and wire up event responders.
     */
    public static function init()
    {
    }

}
Event::add('system.ready', array('Mozilla_BYOB', 'init'));
