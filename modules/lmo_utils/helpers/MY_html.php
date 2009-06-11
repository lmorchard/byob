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
        return '<ul' . ( !empty($attrs) ? html::attributes($attrs) : '' ) . '>'.join("\n", $out).'</ul>';
    }

}
