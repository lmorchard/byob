<?php
/**
 * Initialization hook for addon management
 *
 * @package    Mozilla_BYOB_Editor_AddonManagement
 * @subpackage hooks
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
$path = array(
    dirname(__FILE__) . '/../libraries',
    dirname(__FILE__) . '/../vendor',
    get_include_path()
);
set_include_path(implode(PATH_SEPARATOR, $path));

require_once 'rdf_parser.php';
require_once 'RdfComponent.php';

Event::add('system.ready',
    array('Mozilla_BYOB_Editor_AddonManagement', 'register'));
