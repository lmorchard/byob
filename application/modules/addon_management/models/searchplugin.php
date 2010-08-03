<?php
/**
 * Read-only model abstraction for OpenSearch search plugins
 * 
 * See: http://www.opensearch.org/Specifications/OpenSearch/1.1
 * See: https://developer.mozilla.org/en/Creating_OpenSearch_plugins_for_Firefox
 * See: https://developer.mozilla.org/en/Creating_MozSearch_plugins
 *
 * @package    byob
 * @subpackage models
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class Searchplugin_Model extends Model
{
    // {{{ Static class properties

    public static $req_fields = array(
        'ShortName', 'Description', 
    );

    public static $opensearch_ns = array(
        'http://a9.com/-/spec/opensearch/1.0/',
        'http://a9.com/-/spec/opensearch/1.1/',
        'http://a9.com/-/spec/opensearchdescription/1.1/',
        'http://a9.com/-/spec/opensearchdescription/1.0/'
    );
    
    // }}}

    // {{{ Object Properties
    
    public $loaded = false;
    public $doc = null;
    public $last_error = null;

    // }}}

    /**
     * Basic object constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->loaded = false;
    }

    /**
     * Parse and load a search plugin as XML.
     */
    public function loadFromXML($xml)
    {
        // HACK: Exceptions here are caught by Kohana before we can do anything 
        // useful with them.  Ignoring any parsing errors with @
        // TODO: Temporarily disable that exception trap, so we can see what 
        // the error was and report it.
        $this->doc = @simplexml_load_string($xml);

        // Try validating the XML, catching any validation problems.
        if (!$this->isValid()) {
            $this->loaded = false;
            $this->doc = null;
            return $this;
        } else {
            // Looks like it's well-formed and valid enough for us.
            $this->loaded = true;
        }

        return $this;
    }

    /**
     * Check the search plugin doc for validity.  
     *
     * If anything is found wrong with the document, the last_error property is 
     * set with an English description of the problem as a side-effect.
     *
     * TODO: This isn't a complete validation of the spec.  Make it so?
     *
     * @return boolean
     */
    public function isValid()
    {
        if (FALSE === $this->doc) {
            $this->last_error = 'Could not parse XML document.';
            return false;
        }
        if ('SearchPlugin' != (string)$this->doc->getName() &&
            'OpenSearchDescription' != (string)$this->doc->getName()) {
            $this->last_error = _('Not an OpenSearchDescription document');
            return false;
        }
        foreach (self::$req_fields as $name) {
            $found_it = false;
            foreach (self::$opensearch_ns as $ns) {
                $c = $this->doc->children($ns);
                if (!empty($c->{$name})) {
                    $found_it = true;
                    break;
                }
            }
            if (!$found_it) {
                $this->last_error = sprintf(_('Required element %1$s empty.'), $name);
                return false;
            }
        }
        $this->last_error = false;
        return true;
    }

    /**
     * Produce XML for this search plugin.
     */
    public function asXML()
    {
        // TODO: Retain and return the original XML? In theory, this be okay.
        return (!$this->loaded) ? '' : $this->doc->asXML();
    }

    /**
     * Extract the list of URLs as an easier to use PHP structure.
     *
     * @return array
     */
    public function getURLs()
    {
        // Not loaded? You get nothing.
        if (!$this->loaded) return array();

        $urls = array();
        foreach (self::$opensearch_ns as $ns) {

            // Look for URLs under a namespace
            $c = $this->doc->children($ns);
            if (count($c->Url) == 0) continue;

            // Try collection URLs as a plain name/value array.
            foreach ($c->Url as $idx => $c_url) {
                $url = array();
                $attrs = $c_url->attributes();
                foreach (array('type', 'method', 'template') as $name) {
                    $url[$name] = (string)$attrs->{$name};
                }
                $urls[] = $url;
            }

        }
        return $urls;
    }

    /**
     * Extract a 16x16 icon from the plugin's images, if available.
     *
     * @return string
     */
    public function getIconURL()
    {
        // Not loaded? You get nothing.
        if (!$this->loaded) return array();

        foreach (self::$opensearch_ns as $ns) {
            $c = $this->doc->children($ns);
            if (count($c->Image) == 0) continue;

            foreach ($c->Image as $image) {
                $attrs = $image->attributes();
                if ('16' == $attrs['width'] && '16' == $attrs['height']) {
                    return (string)$image;
                }
            }
        }

    }
    
    /**
     * Proxy attribute fetches into element fetches on the document, if loaded.
     */
    public function __get($name)
    {
        // Not loaded? You get nothing.
        if (!$this->loaded) return null;

        if ('urls' == $name) {
            return $this->getURLs();
        }

        // Search all known namespaces for the requested property name.
        foreach (self::$opensearch_ns as $ns) {
            $c = $this->doc->children($ns);
            $val = (string)($c->{$name});
            if (!empty($val)) return $val;
        }

        return null;
    }

}
