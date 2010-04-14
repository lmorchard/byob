<?php
/**
 * Initialization hook for adhoc distribution INI editor
 *
 * @package    Mozilla_BYOB_Editor_AdhocDistributionINI
 * @subpackage hooks
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
$path = array(
    dirname(__FILE__) . '/../libraries',
    dirname(__FILE__) . '/../vendor',
    get_include_path()
);
set_include_path(implode(PATH_SEPARATOR, $path));

Event::add('system.ready',
    array('Mozilla_BYOB_Editor_AdhocDistributionINI', 'register'));
Event::add('BYOB.repack.buildDistributionIni',
    array('Mozilla_BYOB_Editor_AdhocDistributionINI', 'filterDistributionIni'));
