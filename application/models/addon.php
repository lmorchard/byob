<?php
require_once('Archive/Zip.php');

/**
 * Model abstraction for addons.
 */
class Addon_Model extends Model
{
    // {{{ Object properties
    
    // Addon ID at AMO
    public $id = null;

    // Extended properties expected from AMO API, some of which could be 
    // pre-loaded via configuration and find_all()
    public $api_attr_names = array(
        'guid', 'name', 'install', 'version', 'summary', 'description', 'icon', 
        'thumbnail', 'learnmore'
    );

    // Details on known addons
    public $_known;

    public $api_doc = null;

    // }}}

    /**
     * Basic object constructor
     */
    public function __construct()
    {
        parent::__construct();

        // TODO: Divorce this stuff from direct kohana config access?
        $this->_known = Kohana::config('addons.addons');
        $this->_base_dir = Kohana::config('addons.dir');
        $this->_api_url = Kohana::config('addons.api_url');
    }

    /**
     * Return all known addons.
     *
     * @return array
     */
    public function find_all()
    {
        $addons = array();
        foreach ($this->_known as $id=>$details) {
            $addons[] = $this->find($id);
        }
        return $addons;
    }

    /**
     * Fetch a set of addons by collection URL
     *
     * @param  string Collection web URL
     * @return array of Addon_Model
     */
    public function find_all_by_collection_url($url)
    {
        $cache = Cache::instance();
        $key = "collection-" . md5($url);
        $addon_ids = $cache->get($key);

        if (empty($addon_ids)) {
            // HACK: If the addon IDs need loading, try fetching the RSS feed 
            // for the collection and extract the addon IDs that way.
            $addon_ids = array();
            $feed_url = feed::find($url);
            $items = feed::parse($feed_url);
            foreach ($items as $item) {
                $addon_ids[] = basename($item['link']);
            }
            $cache->set($key, $addon_ids);
        }

        $addons = array();
        foreach ($addon_ids as $id) {
            $addons[] = $this->find($id, true);
        }
        return $addons;
    }

    /**
     * Find an addon by id
     *
     * @param  string      Addon ID
     * @return Addon_Model
     */
    public function find($id, $load_unknown=false)
    {
        if (!$load_unknown && !isset($this->_known[$id])) {
            return null;
        } 
        $addon = new self();
        $addon->id = $id;

        $details = isset($this->_known[$id]) ?
            $this->_known[$id] : array();
        foreach ($details as $name=>$val) {
            $addon->{$name} = $val;
        }

        return $addon;
    }

    /**
     * Bring the addon files up-to-date, if necessary downloading the newest 
     * XPI file and unpacking the contents.
     */
    public function updateFiles()
    {
        $url = $this->install;
        $filename = basename($url);
        $dest_path = "{$this->_base_dir}/{$this->guid}";

        $needs_download = false;

        if (!is_dir($dest_path)) {

            // Doesn't yet exist, so create the path and flag a need for 
            // download.
            $needs_download = true;
            mkdir($dest_path, 0775, true);

        } else {

            try {
                // Something exists, so try checking versions.
                $install_rdf = simplexml_load_file("$dest_path/install.rdf");
                $curr_version = $install_rdf
                    ->Description
                    ->children('http://www.mozilla.org/2004/em-rdf#')
                    ->version;
                if ((string)$curr_version != $this->version) {
                    // Versions don't match, so flag for download.
                    $needs_download = true;
                }
            } catch (Exception $e) {
                // Failure in parsing version suggests need for download.
                $needs_download = true;
            }

        }

        if ($needs_download) {

            // Normalize filename to GUID so as to ignore versions in XPI 
            // filenames.
            $xpi_fn = "{$this->_base_dir}/{$this->guid}.xpi";
            $xpi_fout = fopen($xpi_fn, 'w');

            // Fetch the addon XPI and store it.
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $this->install,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FILE => $xpi_fout
            ));
            $success = curl_exec($ch);
            fclose($xpi_fout);

            if (!$success) {
                throw new Exception("Failed to download {$this->install}");
            }

            // Now, unpack the XPI for use in repacks.
            self::rmdirRecurse($dest_path);
            $zip = new Archive_Zip($xpi_fn);
            $rv = $zip->extract(array(
                'add_path' => $dest_path
            ));

        }
            
        return $dest_path;
    }

    /**
     * Intercept attempts to get API-based attributes by first lazy-loading 
     * data from the API when needed.
     *
     * @param  string Property name
     * @return mixed
     */
    public function __get($name)
    {
        if ((!isset($this->{$name})) && 
                in_array($name, $this->api_attr_names) && 
                null === $this->api_doc) {
            $this->loadDetailsFromAMO();
        }
        return $this->{$name};
    }

    /**
     * Load addon details from AMO API, with caching.  Set the attributes of 
     * this object based on the data found there.
     */
    public function loadDetailsFromAMO()
    {
        if (null === $this->id) {
            // Do nothing if this object doesn't at least have an ID set.
            return null;
        }

        if (null === $this->api_doc) {

            // If the API doc hasn't yet been loaded, try fetching the XML 
            // source from cache.
            $cache = Cache::instance();
            $key = "addon-{$this->id}";
            $api_xml = $cache->get($key);

            if (!$api_xml) {
                // Cache was a miss, so make a real API call to get it...
                $ch = curl_init();
                curl_setopt_array($ch, array(
                    CURLOPT_URL => sprintf($this->_api_url, $this->id),
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_RETURNTRANSFER => true,
                ));
                $api_xml = curl_exec($ch);
                $cache->set($key, $api_xml);
            }

            // Parse the API response and populate this object's properties 
            // from the resulting document.
            $this->api_doc = simplexml_load_string($api_xml);
            foreach ($this->api_attr_names as $name) {
                $this->{$name} = (string)$this->api_doc->{$name};
            }

        }
        return $this->api_doc;
    }


    /**
     * Utility function to recursively delete a directory.
     */
    public static function rmdirRecurse($path) {
        $path= rtrim($path, '/').'/';
        if (!is_dir($path)) return;
        $handle = opendir($path);
        for (;false !== ($file = readdir($handle));)
            if($file != "." and $file != ".." ) {
                $fullpath= $path.$file;
                if( is_dir($fullpath) ) {
                    self::rmdirRecurse($fullpath);
                } else {
                    unlink($fullpath);
                }
            }
        closedir($handle);
        rmdir($path);
    } 

}
