<?php
/**
 * Quick and dirty XML writer.
 *
 * See: http://simonwillison.net/2003/Apr/29/XmlBuilder/
 * See: http://www.xml.com/pub/a/2003/04/09/py-xml.html
 *
 * @package    LMO_Utils
 * @subpackage libraries
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class XmlBuilder {

    private $xml     = array();
    private $indent  = '    ';
    private $stack   = array();
    private $parents = array();

    /**
     * Constructor.
     *
     * @param array Options for XML writer, includes indent and list of known parent container elements
     */
    public function __construct($options=null)
    {
        if (null == $options)
            $options = array();

        $this->indent = isset($options['indent']) ? 
            $options['indent'] : '    ';

        $this->parents = isset($options['parents']) ?
            $options['parents'] : array();

        $this->xml = array( '<?xml version="1.0" encoding="utf-8"?>'."\n" );
    }

    /**
     * Set list of known parent container elements
     *
     * @param array List of known parent container elements
     * @return XmlBuilder
     */
    public function setParents($parents=array())
    {
        $this->parents = $parents;
        return $this;
    }

    /**
     * Magic function call interface, turns names of elements into calls to push() 
     * is element is a known parent, element() if not a parent but supplied with content,
     * and emptyelement() otherwise.
     *
     * @param string Element name
     * @param array Arguments
     * @return XmlBuilder
     */
    public function __call($name, $args) 
    {
        $args = array_pad($args, 4, null);
        $attributes = (is_array($args[0])) ?
            array_shift($args) : null;
        $content = array_shift($args);

        if (in_array($name, $this->parents)) {
            return $this->push($name, $attributes);
        } else {
            if ($content) {
                return $this->element($name, $attributes, $content);
            } else {
                return $this->emptyelement($name, $attributes);
            }
        }

    }

    /**
     * Open a parent container element
     *
     * @param string Name of the element
     * @param array Optional array of attributes
     */
    public function push($element, $attributes=null) 
    {
        $this->_indent();
        $this->xml[] = '<'.$element;
        $this->_attrs($attributes);
        $this->xml[] = ">\n";
        $this->stack[] = $element;
        return $this;
    }

    /**
     * Close the last recently opened parent container element.
     */
    public function pop() 
    {
        $element = array_pop($this->stack);
        $this->_indent();
        $this->xml[] = "</$element>\n";
        return $this;
    }

    /**
     * Open and close an element, with content
     *
     * @param string Name of the element
     * @param array Optional array of attributes
     * @param string content.
     */
    public function element($element, $attributes=null, $content='') 
    {
        $this->_indent();
        $this->xml[] = '<'.$element;
        $this->_attrs($attributes);
        $this->xml[] = '>'.htmlspecialchars($content).'</'.$element.'>'."\n";
        return $this;
    }

    /**
     * Insert a self-closing element without content.
     *
     * @param string Name of the element
     * @param array Optional array of attributes
     */
    public function emptyelement($element, $attributes=null) 
    {
        $this->_indent();
        $this->xml[] = '<'.$element;
        $this->_attrs($attributes);
        $this->xml[] = "/>\n";
        return $this;
    }

    /**
     * Get the result of operations so far.
     *
     * @return string XML
     */
    public function getXML() 
    {
        while ($this->stack) $this->pop();
        return join('', $this->xml);
    }

    /**
     * Append an indent onto the document, depending on the depth of the tag 
     * stack.
     */
    private function _indent() 
    {
        for ($i = 0, $j = count($this->stack); $i < $j; $i++) {
            $this->xml[] = $this->indent;
        }
    }

    /**
     * Append encoded attributed onto the document.
     *
     * @param array Named attributes.
     */
    private function _attrs($attributes)
    {
        if (null!=$attributes) {
            foreach ($attributes as $key => $value) {
                if (null === $value) continue;
                $this->xml[] = ' '.$key.'="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'"';
            }
        }
    }

}
