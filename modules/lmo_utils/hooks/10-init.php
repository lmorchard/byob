<?php
/**
 * Initialization for the LMO_Utils module
 *
 * @package    LMO_Utils
 * @subpackage hooks
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class LMO_Utils_Init {

    /**
     * Initialize the application.
     */
    public static function init()
    {
        require_once(Kohana::find_file('vendor', 'Markdown'));
    }

}
LMO_Utils_Init::init();
