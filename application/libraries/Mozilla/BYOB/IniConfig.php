<?php
/**
 * Encapsulation of a BYOB INI file.
 *
 * @package    Mozilla_BYOB
 * @subpackage Libraries
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB_IniConfig extends Zend_Config_Ini
{
    /**
     * Parse an INI config from a string and return a config instance.
     *
     * @param   string
     * @returns Mozilla_BYOB_IniConfig
     */
    public static function fromString($str)
    {
        try {
            // HACK: Since Zend uses parse_ini_file, we need to write a temp 
            // file.  Might be nice to avoid this, someday.
            $fn = tempnam("tmp","byob-");
            file_put_contents($fn, $str);
            $config = new self($fn, null, array(
                'allowModifications' => true,
                'skipExtends'        => true,
            ));
            unlink($fn);
            return $config;
        } catch (Zend_Config_Exception $e) {
            return null;
        }
    }

}
