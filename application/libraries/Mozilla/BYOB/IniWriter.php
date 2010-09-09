<?php
/**
 * Writer for a BYOB INI file.
 *
 * @package    Mozilla_BYOB
 * @subpackage Libraries
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB_IniWriter extends Zend_Config_Writer_Ini
{
    private $_current_section;
    private $_current_name;
    private $_quoted_value_section_names = array();

    /**
     * Set the names of sections that require quoted values
     */
    public function setQuotedValueSectionNames($sections)
    {
        return $this->_quoted_value_section_names = $sections;
    }

    /**
     * Render a Zend_Config into a INI config string.
     *
     * @since 1.10
     * @return string
     */
    public function render()
    {
        $this->_current_section = '';

        $iniString   = '';
        $extends     = $this->_config->getExtends();
        $sectionName = $this->_config->getSectionName();

        if($this->_renderWithoutSections == true) {
            $iniString .= $this->_addBranch($this->_config);
        } else if (is_string($sectionName)) {
            $this->_current_section = $sectionName;
            $iniString .= '[' . $sectionName . ']' . "\n"
                       .  $this->_addBranch($this->_config)
                       .  "\n";
        } else {
            foreach ($this->_config as $sectionName => $data) {
                if (!($data instanceof Zend_Config)) {
                    $iniString .= $sectionName
                               .  '=' 
                               .  $this->_prepareValue($data)
                               .  "\n";
                } else {
                    if (isset($extends[$sectionName])) {
                        $sectionName .= ' : ' . $extends[$sectionName];
                    }

                    $this->_current_section = $sectionName;
                    $iniString .= '[' . $sectionName . ']' . "\n"
                               .  $this->_addBranch($data)
                               .  "\n";
                }
            }
        }

        return $iniString;
    }

    /**
     * Add a branch to an INI string recursively
     *
     * @param  Zend_Config $config
     * @return void
     */
    protected function _addBranch(Zend_Config $config, $parents = array())
    {
        $iniString = '';

        foreach ($config as $key => $value) {
            $group = array_merge($parents, array($key));

            if ($value instanceof Zend_Config) {
                $iniString .= $this->_addBranch($value, $group);
            } else {
                $this->_current_name = implode($this->_nestSeparator, $group);
                if (strpos($this->_current_name, '.__value__')) {
                    $this->_current_name = str_replace(
                        '.__value__', '', $this->_current_name
                    );
                }
                $iniString .= $this->_current_name 
                           .  '=' 
                           .  $this->_prepareValue($value)
                           .  "\n";
            }
        }

        return $iniString;
    }

    /**
     * Prepare a value for INI
     *
     * @param  mixed $value
     * @return string
     */
    protected function _prepareValue($value)
    {
        if (is_bool($value)) {
            return ($value ? 'true' : 'false');
        } elseif (is_integer($value) || is_float($value)) {
            return $value;
        } elseif ('extensions.personas.initial' == $this->_current_name) {
            // HACK: This particular key is always JSON and specially not-quoted
            return $value;
        } elseif (in_array($this->_current_section, $this->_quoted_value_section_names)) {
            $value = str_replace('"', '\"', $value);
            return '"'.$value.'"';
        } else {
            return $value;
        }
    }

}
