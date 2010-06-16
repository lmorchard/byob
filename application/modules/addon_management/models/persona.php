<?php
/**
 * Model abstraction for personas.
 */
class Persona_Model extends Model
{
    // {{{ Object properties
    
    // Fake ORM-esque table columns
    public $table_columns = array(
        'id'          => array('type' => 'string'), 
        'url'         => array('type' => 'string'),
        'json'        => array('type' => 'string'),
        'name'        => array('type' => 'string'), 
        'description' => array('type' => 'string'), 
        'author'      => array('type' => 'string'), 
        'accentcolor' => array('type' => 'string'), 
        'textcolor'   => array('type' => 'string'), 
        'header'      => array('type' => 'string'), 
        'headerURL'   => array('type' => 'string'), 
        'footer'      => array('type' => 'string'),
        'footerURL'   => array('type' => 'string'),
        'iconURL'     => array('type' => 'string'),
        'previewURL'  => array('type' => 'string'),
    );

    // }}}

    /**
     * Basic object constructor
     */
    public function __construct()
    {
        $this->loaded = false;

        parent::__construct();
    }

    /**
     * Return persona details given an ID
     * 
     * @param   $persona_id
     * @returns Persona_Model
     * @chainable
     */
    public function find_by_getpersonas_id($persona_id)
    {
        if (empty($persona_id)) return $this;

        $cache = Cache::instance();
        $key = "persona-{$persona_id}";
        $json = $cache->get($key);

        if (!$json) {
            $json_url = 'http://www.getpersonas.com/static/' .
                substr($persona_id, -2, 1)  . '/' .
                substr($persona_id, -1, 1)  . '/' .
                $persona_id . '/' .
                'index_1.json';

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $json_url,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
            ));
            $json = curl_exec($ch);
            if (200 != curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                // Error, or persona not found.
                $json = null;
            }
            $cache->set($key, $json);
        }

        if (empty($json)) {
            $this->loaded = false;
            return $this;
        } else {
            $this->url = "http://www.getpersonas.com/persona/{$persona_id}";
            return $this->load_json($json);
        }
    }

    /**
     * Populate this model object from a JSON string
     *
     * @param   string $json
     * @returns Persona_Model
     * @chainable
     */
    public function load_json($json)
    {
        $persona_data = json_decode($json, true);
        if (empty($persona_data)) {
            $this->loaded = false;
            return $this;
        }

        foreach ($this->table_columns as $name=>$info) {
            $this->{$name} = isset($persona_data[$name]) ? 
                $persona_data[$name] : null;
        }

        if (!empty($this->id)) {
        }

        $this->json   = $json;
        $this->loaded = true;

        return $this;
    }

    /**
     * Look for a persona by a getpersonas.com URL.
     *
     * @param   string $url
     * @returns Persona_Model
     * @chainable
     */
    public function find_by_getpersonas_url($url)
    {
        $persona_id = basename($url);
        $p = $this->find_by_getpersonas_id($persona_id);
        $p->url = $url;
        return $p;
    }

    /**
     * Look for a persona by addons.mozilla.org URL
     *
     * @param   string $url
     * @returns Persona_Model
     * @chainable
     */
    public function find_by_amo_url($url)
    {
        if (empty($url)) return $this;

        $cache = Cache::instance();
        $key   = "persona-amo-" . md5($url);
        $json  = $cache->get($key);

        if (!$json) {

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
            ));
            $html = curl_exec($ch);
            if (200 != curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                // Error, or persona not found.
                $html = null;
            }

            if (!empty($html)) {
                // HACK: This is some gnarly scraping to pick up a link with the 
                // persona JSON embedded.
                $doc = new DOMDocument();
                $doc->strictErrorChecking = FALSE;
                libxml_use_internal_errors(true);
                $doc->loadHTML($html);
                $xml = simplexml_import_dom($doc);
                $result = $xml->xpath('//div/@data-browsertheme');
                while (list(,$node) = each($result)) {
                    $json = (string)$node;
                }
            }

            $cache->set($key, $json);
        }

        if (empty($json)) {
            $this->loaded = false;
            return $this;
        } else {
            $this->load_json($json);
            $this->url = $url;
            return $this;
        }

    }

    /**
     * Return persona details given a URL
     *
     * @param   string $url
     * @returns Persona_Model
     * @chainable
     */
    public function find_by_url($url)
    {
        if (empty($url)) return $this;

        if (false !== strpos($url, 'getpersonas.com')) {
            return $this->find_by_getpersonas_url($url);
        } else if (false !== strpos($url, 'addons.mozilla.org')) {
            return $this->find_by_amo_url($url);
        } else {
            return $this;
        }
    }

}
