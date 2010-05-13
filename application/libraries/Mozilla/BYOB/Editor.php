<?php
/**
 * BYOB editor module
 *
 * @package    Mozilla_BYOB_Editor
 * @subpackage Libraries
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB_Editor {

    private static $_instances = array();

    /**
     * @param string $classname
     * @return Singleton
     */
    public static function getInstance($cls=null)
    {
        if (null===$cls && function_exists('get_called_class')) {
            $cls = get_called_class();
        }
        if (!isset(self::$_instances[$cls])) {
            self::$_instances[$cls] = new $cls();
        }
        return self::$_instances[$cls];
    }

    /**
     * Event handler to register this editor.
     */
    public static function register($cls=null)
    {
        if (null===$cls && function_exists('get_called_class')) {
            $cls = get_called_class();
        }
        $self = self::getInstance($cls);
        Mozilla_BYOB_EditorRegistry::register($self);
        return $self;
    }

}
