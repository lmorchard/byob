<?php
/**
 * Initialize the module.
 *
 * @package    gettext
 * @subpackage hooks
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
require_once Kohana::find_file('libraries','Gettext/Main');
Event::add(LMO_Utils_EnvConfig::EVENT_READY, 
    array('Gettext_Main', 'init'));
