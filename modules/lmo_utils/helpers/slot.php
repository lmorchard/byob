<?php
/**
 * Helpers to capture and include named slots for templates.
 *
 * @package    LMO_Utils
 * @subpackage helpers
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class slot_Core
{

    // Stack of current named open capture slots
    public static $slot_stack = array();

    // Array of named captured slots
    public static $slots = array();

    /**
     * Start capturing output for the given named slot.
     *
     * @param string name of the slot to capture
     */
    public static function start($key)
    {
        array_push(self::$slot_stack, $key);
        ob_start();
    }

    /**
     * Finish capturing output for current opened slot.
     *
     * @return string
     */
    public static function end()
    {
        $key = array_pop(self::$slot_stack);
        if ($key == NULL) {
            return FALSE;
        } else {
            $output = ob_get_contents();
            ob_end_clean();
            self::append($key, $output);
            return $key;
        }
    }

    /**
     * Set content for a named slot, replacing anything already present.
     *
     * @param string name of the slot
     * @param string content for the slot
     */
    public static function set($name, $value)
    {
        self::$slots[$name] = array(trim($value));
    }

    /**
     * Append content to a named slot.
     *
     */
    public static function append($name, $value)
    {
        if (empty(self::$slots[$name])) {
            return self::set($name, $value);
        } else {
            self::$slots[$name][] = trim($value);
        }
    }

    /**
     * Get a named slot or get the end of the latest one.
     *
     * @param  string name of the slot to return, or omit to find all
     * @return string|array
     */
    public static function get($name=FALSE, $default='')
    {
        if (!$name) $name = self::end();

        return isset(self::$slots[$name]) ? 
            join('', self::$slots[$name]) : $default;
    }

    /**
     * Determine whether a particular slot has been created.
     *
     * @return boolean
     */
    public static function exists($name=FALSE)
    {
        return array_key_exists($name, self::$slots);
    }

    /**
     * Output the contents of a named slot
     *
     * @param string name of the desired slot.
     */
    public static function output($name=FALSE, $default='')
    {
        echo self::get($name, $default);
    }

    /**
     * Output the contents of a named slot, after filtering it through a given 
     * callback
     *
     * @param string name of the slot
     * @param callback filter callback
     */
    public static function get_filter($name, $callback)
    {
        return call_user_func($callback, self::get($name));
    }

    /**
     * Output the contents of a named slot, after filtering it through a given 
     * callback
     *
     * @param string name of the slot
     * @param callback filter callback
     */
    public static function filter($name, $callback)
    {
        echo self::get_filter($name, $callback);
    }

    /**
     * Close the current open slot, then output it using the given callback.
     *
     * @param callback filter callback
     */
    public static function end_filter($callback)
    {
        $name = self::end();
        self::filter($name, $callback);
    }

}
