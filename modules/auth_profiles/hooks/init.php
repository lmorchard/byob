<?php
/**
 * Initialize the module.
 *
 * @package    auth_profiles
 * @subpackage hooks
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
Event::add(LMO_Utils_EnvConfig::EVENT_READY, 
    array('AuthProfiles', 'init'));
