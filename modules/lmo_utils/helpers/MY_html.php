<?php
/**
 * Custom HTML helpers
 *
 * @package    LMO_Utils
 * @subpackage helpers
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class html extends html_Core
{

    /**
     * Wrap an array of items in li tags inside a ul
     */
    public static function ul($arr, $attrs=null, $li=false)
    {
        $out = array();
        foreach ($arr as $item) {
            if (is_array($item))
                $item = join("\n", $item);
            if (!$li) {
                $out[] = $item;
            } else {
                $out[] = "<li>$item</li>";
            }
        }
        return '<ul' . ( !empty($attrs) ? html::attributes($attrs) : '' ) . 
            '>'.join("\n", $out).'</ul>';
    }

    /**
     * Recursively apply specialchars() to all values of the given array.
     *
     * @param  array
     * @return array
     */
    public static function escape_array($arr)
    {
        arr::map_recursive(
            array(get_class(), 'inplace_specialchars'), $arr
        );
        return $arr;
    }

    /**
     * Convert special characters to HTML entities, replacing the string value 
     * in place.
	 *
	 * @param   string   string to convert, passed by reference
	 * @param   boolean  encode existing entities
	 * @return  string
     */
    public static function inplace_specialchars(&$str, $double_encode=TRUE)
    {
        $str = html::specialchars($str, $double_encode);
        return $str;
    }

    /**
     * Recursively apply urlencode() to all values of the given array.
     *
     * @param  array
     * @return array
     */
    public static function urlencode_array($arr)
    {
        arr::map_recursive(
            array(get_class(), 'inplace_urlencode'), $arr
        );
        return $arr;
    }

    /**
     * Perform urlencode() in-place on string passed by reference.
     *
     * @param  string  String, passed by reference
     * @return string  URL encoded string
     */
    public static function inplace_urlencode(&$str)
    {
        $str = urlencode($str);
        return $str;
    }

}
