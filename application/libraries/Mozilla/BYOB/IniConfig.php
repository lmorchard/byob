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
     * @todo Make this no longer reliant on temp files!
     *
     * @param   string
     * @returns Mozilla_BYOB_IniConfig
     */
    public static function fromString($str)
    {
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
    }

    /**
     * Load the INI file from disk using a liberal PHP-based parser.
     * 
     * @param string $filename
     * @throws Zend_Config_Exception
     * @return array
     */
    protected function _parseIniFile($filename)
    {
        // See also: http://us.php.net/manual/en/function.parse-ini-string.php#94192
        $str = file_get_contents($filename);
        
        $lines      = explode("\n", $str);
        $return     = Array();
        $in_section = false;

        foreach ($lines as $line){
            $line = trim($line);

            if (!$line || $line[0] == "#" || $line[0] == ";") {
                continue;
            }

            if ($line[0] == "[" && $end_idx = strpos($line, "]")){
                $in_section = substr($line, 1, $end_idx-1);
                continue;
            }

            if (strpos($line, '=') > 0) {
                $tmp = explode("=", $line, 2);
                if (count($tmp) == 2) {

                    $name = ltrim($tmp[0]);
                    $val  = ltrim($tmp[1]);

                    if ($val[0] == '"' && $end_idx = strrpos($val, '"')){
                        // HACK: Quoting is optional - but if the value is quoted, 
                        // unquote it and unescape all the escaped quotes, if 
                        // any.
                        $val = str_replace('\"', '"',
                            substr($val, 1, $end_idx-1));
                    }

                    if ($in_section) {
                        $return[$in_section][$name] = $val;
                    } else {
                        $return[$name] = $val;
                    }
                }
                continue;
            }

        }
        return $return;
    }

}
