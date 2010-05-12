<?php
/**
 * Initialization hook for locale selection editor
 *
 * @package    Mozilla_BYOB_Editor_LocaleSelection
 * @subpackage hooks
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
$path = array(
    dirname(__FILE__) . '/../libraries',
    dirname(__FILE__) . '/../vendor',
    get_include_path()
);
set_include_path(implode(PATH_SEPARATOR, $path));

// Initialize the helper.
locale_selection::init();

Event::add('system.ready',
    array('Mozilla_BYOB_Editor_LocaleSelection', 'register'));
