<?php
/**
 * Router customizations
 *
 * @package    LMO_Utils
 * @subpackage libraries
 * @author     l.m.orchard@pobox.com
 */
class Router extends Router_Core
{

    /**
     * Convert the arguments in the route to name/value parameters.
     *
     * @return array Parameters based on current route.
     */
    public function get_params($defaults=null, $wildcard='path')
    {
        $args = self::$arguments;
        $params = empty($defaults) ? array() : $defaults;
        while (!empty($args)) {
            $name = array_shift($args);
            if ($wildcard == $name) {
                $params[$name] = join('/', $args);
                break;
            } else {
                $params[$name] = array_shift($args);
            }
        }
        return $params;
    }

}
