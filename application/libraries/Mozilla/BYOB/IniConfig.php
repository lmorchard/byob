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
     * Given an original INI, overlay a second on top of it
     *
     * @param   string|array $ini1 Original INI string or config array
     * @param   string|array $ini2 Overlay INI string or config array
     * @returns string
     */
    public static function mergeINIs($ini1, $ini2) {

        $merged_conf = new Zend_Config(array(), true);

        // Start merging the INIs, retaining comments for a header.
        $comments = array();
        foreach (array($ini1, $ini2) as $ini) {
            if (is_array($ini)) {
                $conf = new Zend_Config($ini, true);
            } elseif (is_string($ini)) {
                // Split up the lines of this INI and retain comments.
                $lines = preg_split('/\n\r?/', $ini);
                foreach ($lines as $line) {
                    if (substr($line, 0, 1) == ';') { $comments[] = $line; }
                }
                // Parse the INI and merge into the accumlator conf
                $conf = Mozilla_BYOB_IniConfig::fromString($ini);
            } else {
                $conf = null;
            }
            if (!empty($conf)) {
                $merged_conf->merge($conf);
            }
        }

        // Sections with "Preferences" or "LocalizablePreferences" need to be 
        // quoted for JS in Firefox
        $sections = array_keys($merged_conf->toArray());
        $quoted = array( 'Preferences' );
        foreach ($sections as $section) {
            if (strpos($section, 'LocalizablePreferences') !== false) {
                $quoted[] = $section;
            }
        }

        // Render the merged INI and restore retained comments at the top
        $writer = new Mozilla_BYOB_IniWriter(array(
            'config' => $merged_conf,
            'quotedValueSectionNames' => $quoted
        ));
        $merged_ini = implode("\n", $comments) . "\n\n" . $writer->render();
        return $merged_ini;
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

    /**
     * Assign the key's value to the property list. Handles the
     * nest separator for sub-properties.
     *
     * @param  array  $config
     * @param  string $key
     * @param  string $value
     * @throws Zend_Config_Exception
     * @return array
     */
    protected function _processKey($config, $key, $value)
    {
        if (strpos($key, $this->_nestSeparator) !== false) {
            $pieces = explode($this->_nestSeparator, $key, 2);
            if (strlen($pieces[0]) && strlen($pieces[1])) {
                if (!isset($config[$pieces[0]])) {
                    if ($pieces[0] === '0' && !empty($config)) {
                        // convert the current values in $config into an array
                        $config = array($pieces[0] => $config);
                    } else {
                        $config[$pieces[0]] = array();
                    }
                } elseif (!is_array($config[$pieces[0]])) {
                    // HACK: Support both value and subkeys for a key.
                    $orig_val = $config[$pieces[0]];
                    $config[$pieces[0]] = array( '__value__' => $orig_val );
                }
                $config[$pieces[0]] = $this->_processKey($config[$pieces[0]], $pieces[1], $value);
            } else {
                /**
                 * @see Zend_Config_Exception
                 */
                require_once 'Zend/Config/Exception.php';
                throw new Zend_Config_Exception("Invalid key '$key'");
            }
        } else {
            $config[$key] = $value;
        }
        return $config;
    }

}
