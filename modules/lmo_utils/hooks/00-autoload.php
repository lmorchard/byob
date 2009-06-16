<?php
/**
 * Autoloader class layered atop Kohana's default, allows organization of 
 * classes with an underscore-to-slash directory convention.
 *
 * @package    LMO_Utils
 * @subpackage hooks
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class LMO_Utils_Autoloader
{
    public static function auto_load($class)
    {
        // If Kohana comes up empty, try replacing underscores with directory 
        // separators and look for a library.
        $file = str_replace('_', '/', $class);
        if ($filename = Kohana::find_file('libraries', $file)) {
            require $filename; return TRUE;
        } else if ($filename = Kohana::find_file('vendor', $file)) {
            require $filename; return TRUE;
        } else {
            return FALSE;
        }
    }
}

spl_autoload_register(array('LMO_Utils_Autoloader', 'auto_load'));

$path = array(
    APPPATH,
    APPPATH . '/libraries',
    APPPATH . '/vendor',
    get_include_path()
);
set_include_path(implode(PATH_SEPARATOR, $path));
