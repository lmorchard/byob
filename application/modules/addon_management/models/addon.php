<?php
require_once('Archive/Zip.php');

/**
 * Model abstraction for addons.
 *
 * TODO: Currently does not support extensions with an OS other than ALL in <install>
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

    public $loaded  = false;
    public $api_doc = null;
    public $api_xml = null;
    // }}}

    /**
     * Basic object constructor
     */
    public function __construct()
    {
        parent::__construct();

        // TODO: Divorce this stuff from direct kohana config access?
        $this->_known = 
            Kohana::config('addon_management.addons');
        $this->_base_dir = 
            Kohana::config('addon_management.dir');
        $this->_api_url = 
            Kohana::config('addon_management.api_url');
        $this->_c_api_url = 
            Kohana::config('addon_management.collections_api_url');
        $this->_c_api_user = 
            Kohana::config('addon_management.collections_username');
        $this->_c_api_pass = 
            Kohana::config('addon_management.collections_password');
    }

    /** 
     * Fetch a set of addons by collection name (using API)
     *
     * @param  string $name Name of a collection
     * @return array  List of addons or null
     */
    public function find_all_by_collection_name($name)
    {
        $cache = Cache::instance();
        $key = "collection-named-" . md5($name);
        $addon_ids = $cache->get($key);
        
        if (!empty($addon_ids)) {
            // Find and return cached addons, if any
            $addons = array();
            foreach ($addon_ids as $id) {
                $addon = new self();
                $addon->find($id, true);
                if ($addon->loaded) $addons[] = $addon;
            }
            return $addons;
        }

        // Fetch the collections service doc
        $doc = $this->collection_api();
        if (empty($doc)) return null;

        // Search the service doc for the named collection.
        $collection = null;
        foreach ($doc->collections->collection as $c) {
            if ($name == $c['name']) {
                $collection = $c; 
                break;
            }
        }
        if (empty($collection)) return null;

        // Try fetching the named collection.
        $c_doc = $this->collection_api($collection['href'], $doc);
        if (empty($c_doc->addons->addon)) return null;

        // Gather the addons and addon IDs from the collection.
        $addons = array();
        $addon_ids = array();
        foreach ($c_doc->addons->addon as $addon_el) {

            $addon = new self();
            $addon->updateFromDoc($addon_el);

            // HACK: This is dirty, dirty, dirty, because the collection API 
            // doesn't provide an addon ID.  Oopsies.
            $id = basename(str_replace('?src=sharingapi', '', $addon_el->learnmore));
            $addon->id = $id;

            $addon_ids[] = $id;
            $addons[] = $addon;
        }

        $cache->set($key, $addon_ids);
        return $addons;
    }

    /**
     * Resolve a URL using the xml:base from the given element and the given 
     * href value
     *
     * TODO: Make this do what it actually should do, rather than what just 
     * works in the limited case in which we use is.
     *
     * @param   $base_ele Element bearing xml:base attribute
     * @param   $href     Full or partial URL href
     * @returns string
     */
    private function collection_href($href, $base_ele) 
    {
        $r = $base_ele->xpath('@xml:base');
        $base = (string)($r[0]['base']);
        // TODO: Account for full URLs, do something smarter than just concat
        $href = $base . $href;
        return $href;
    }

    /**
     * Perform an HTTP request to the collection API.
     *
     * @param   string $url  API URL, defaults to service document.
     * @returns SimeXMLElement
     */
    private function collection_api($url=null, $base_el=null)
    {
        if (!empty($base_el))
            $url = $this->collection_href($url, $base_el);
        $api_url  = !empty($url) ? $url : $this->_c_api_url;

        $curl_opts = array(
            CURLOPT_USERPWD => "{$this->_c_api_user}:{$this->_c_api_pass}",
            CURLOPT_URL => $api_url,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
        );

        $ch = curl_init();
        curl_setopt_array($ch, $curl_opts);
        $api_xml = curl_exec($ch);
        curl_close($ch);
        $api_doc = simplexml_load_string($api_xml);

        return $api_doc;
    }

    /**
     * Fetch a set of addons by collection URL from web front-end (not API)
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
            $addon = new self();
            $addon->find($id, true);
            if ($addon->loaded) {
                $addons[] = $addon;
            }
        }
        return $addons;
    }

    /**
     * Find an addon by id
     *
     * @param  string      Addon ID
     * @return Addon_Model
     */
    public function find($id, $load_unknown=true)
    {
        if (!$load_unknown && !isset($this->_known[$id])) {
            $this->loaded = false;
            return $this;
        } 
        $this->id = $id;

        if ($load_unknown) {
            $this->api_doc = null;
            $doc = $this->loadDetailsFromAMO();
            if (null === $doc) {
                $this->loaded = true; 
            }
        } else {
            $details = isset($this->_known[$id]) ?
                $this->_known[$id] : array();
            foreach ($details as $name=>$val) {
                $this->{$name} = $val;
            }
            $this->loaded = true;
        }

        return $this;
    }

    /**
     * Attempt to wrap an addon object around a given XPI file based on the 
     * install.rdf it should contain.
     *
     * @param  string $xpi_fn XPI filename
     * @return Addon_Model
     */
    public function find_by_xpi_file($xpi_fn, $force_refresh=false)
    {
        $addon = new self();
        if ($addon->updateFromXPI($xpi_fn)) {
            $addon->loaded = true;
        }
        return $addon;
    }

    /**
     * Bring the addon files up-to-date, if necessary downloading the newest 
     * XPI file and unpacking the contents.
     */
    public function updateFiles($unpack_xpis=false)
    {
        $url = trim((string)$this->install);
        $filename = basename($url);
        $dest_path = "{$this->_base_dir}/{$this->guid}";
        $xml_path  = "{$this->_base_dir}/{$this->guid}.xml";
        $xpi_fn = "{$this->_base_dir}/{$this->guid}.xpi";

        $needs_download = false;

        if (!is_file($xml_path)) {
            // XML from last API request doesn't exist, so need a download.
            $needs_download = true;
        } else if ($unpack_xpis && !is_dir($dest_path)) {
            // Doesn't yet exist, so create the path and flag download need.
            mkdir($dest_path, 0775, true);
            $needs_download = true;
        } else {
            try {
                if ($unpack_xpis) {
                    // Try extracting version from the XPI files.
                    $install_rdf = simplexml_load_file("$dest_path/install.rdf");
                    $curr_version = $install_rdf
                        ->Description
                        ->children('http://www.mozilla.org/2004/em-rdf#')
                        ->version;
                } else {
                    // Try extracting version from the last API result
                    $prev_api_doc = simplexml_load_file($xml_path);
                    $curr_version = $prev_api_doc ?
                        $prev_api_doc->version : null;
                }
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

            // Stash the last recent download of API XML metadata
            file_put_contents($xml_path, $this->api_xml);

            // Normalize filename to GUID so as to ignore versions in XPI 
            // filenames.
            $xpi_fout = fopen($xpi_fn, 'w');

            // Fetch the addon XPI and store it.
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FILE => $xpi_fout
            ));
            $success = curl_exec($ch);
            fclose($xpi_fout);

            if (!$success) {
                throw new Exception(sprintf(_("Failed to download %1$s"), $url));
            }

            // Now, unpack the XPI for use in repacks.
            if ($unpack_xpis) {
                self::rmdirRecurse($dest_path);
                $zip = new Archive_Zip($xpi_fn);
                $rv = $zip->extract(array(
                    'add_path' => $dest_path
                ));
            }

        }
        $this->xpi_fn = $xpi_fn; 
        return $xpi_fn;
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
        $this->loaded = false;

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
            $this->api_xml = $api_xml;
            $this->updateFromDoc(simplexml_load_string($api_xml));
            $this->loaded = true;

        }
        return $this->api_doc;
    }

    /**
     * Update this addon object from the given simplexml doc.
     */
    public function updateFromDoc($doc) {
        $this->api_doc = $doc;
        foreach ($this->api_attr_names as $name) {
            $this->{$name} = (string)$this->api_doc->{$name};
        }
        return $this;
    }

    /**
     * Update this addon object from the install.rdf contained in an XPI.
     */
    public function updateFromXPI($xpi_fn=null, $force_refresh=true)
    {
        $cache = Cache::instance();
        $key = "xpi-installrdf-" . md5($xpi_fn);

        if (!$force_refresh) {
            $this->install_rdf = $ir = $cache->get($key);
        }

        if (empty($ir)) {
            // HACK: RDF parser uses some deprecated functions, but I just want the 
            // !@#$%^ thing to work for now.
            $old_err = error_reporting(
                E_ALL & ~E_STRICT & ~E_DEPRECATED & ~E_USER_DEPRECATED
            );

            if (null === $xpi_fn) {
                if (!$this->loaded) return null;
                $xpi_fn = $this->updateFiles();
            }
            if (empty($xpi_fn)) {
                return null;
            }

            $zip = new Archive_Zip($xpi_fn);

            $ext = $zip->extract(array(
                'extract_as_string' => true, 
                'by_name' => array('install.rdf')
            ));
            if (empty($ext[0]['content'])) {
                return null;
            }
            $content = $ext[0]['content'];

            $rdf = new RdfComponent();
            $this->install_rdf = $ir = $rdf->parseInstallManifest($content);
            if (empty($ir) || !is_array($ir)) {
                return null;
            }

            $cache->set($key, $ir);

            error_reporting($old_err);
        }

        $this->xpi_fn = $xpi_fn;

        $data = array(
            'guid' => @$ir['id'],
            'name' => @$ir['name']['en-US'],
            'version' => @$ir['version'],
            'description' => @$ir['description']['en-US'],
            'creator' => @$ir['creator']
        );

        foreach ($data as $name=>$val) {
            $this->{$name} = $val;
        }

        return true;
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
