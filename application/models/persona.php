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
        'accentcolor' => array('type' => 'string'), 
        'textcolor'   => array('type' => 'string'), 
        'header'      => array('type' => 'string'), 
        'footer'      => array('type' => 'string')
    );

    // }}}

    /**
     * Basic object constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Return persona details given an ID
     */
    public function find($persona_id)
    {
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
        } 

        $persona_data = json_decode($json, true);
        foreach ($this->table_columns as $name=>$info) {
            if (isset($persona_data[$name])) {
                $this->{$name} = $persona_data[$name];
            }
        }

        $this->url    = "http://www.getpersonas.com/persona/{$persona_id}";
        $this->json   = $json;
        $this->loaded = true;

        return $this;
    }

    /**
     * Return persona details given a URL
     *
     * @return array
     */
    public function find_by_url($url)
    {
        $persona_id = basename($url);
        return $this->find($persona_id);
    }

}
