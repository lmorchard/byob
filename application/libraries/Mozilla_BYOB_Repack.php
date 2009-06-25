<?php
/**
 * Representation of a customized browser repack.
 *
 * @package    BYOB
 * @subpackage Libraries
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB_Repack
{
    // {{{ Object attributes
    
    public $id = null;
    public $uuid = null;
    
    public $created = '';
    public $modified = '';
    public $created_by = null;
    public $created_by_user = null;
    public $approved_by = null;
    public $approved_on = null;

    public $repack_win = true;
    public $repack_mac = true;
    public $repack_linux = true; 

    public $min_version = '3.0';
    public $max_version = '3.0.*';

    public $hidden = false; // @@TODO

    public $version = '1';
    public $short_name = null;
    public $title = null;
    public $description = null;
    public $category = null;
    public $startpage_content = null;
    public $startpage_feed_url = null;
    public $bookmarks_menu = array();
    public $bookmarks_toolbar = array();
    public $addons_collection_url = null;
    public $persona_id = null;
    public $product = array();
    public $platforms = array();
    public $locales = array();
    public $prefs = array();
    public $search_plugins = array();

    public static $metadata_fields = array(
        'id', 'uuid', 'created', 'modified', 'created_by', 'approved_by',
        'approved_on', 'short_name', 'title', 'description', 'category',
    );

    public static $os_choices = array(
        'win'   => 'Windows',
        'mac'   => 'Macintosh',
        'linux' => 'Linux'
    );

    public static $locale_choices = array(
        'de' =>    'German',
        'en-GB' => 'English (UK)',
        'en-US' => 'English (US)',
        'es-AR' => 'Spanish (Argentinia/Latin America)',
        'es-ES' => 'Spanish (Spain)',
        'fr' =>    'French',
        'ja' =>    'Japanese',
        'ru' =>    'Russian',
        'zh-CN' => 'Chinese Mandarin',
        'zh-TW' => 'Chinese Simplified',
    );

    public static $bookmark_types = array(
        'normal' => 'Normal',
        'live'   => 'Live'
    );

    public static $type_fields = array(
        'normal' => array(
            'title'       => 'Title',
            'location'    => 'Website URL',
            'description' => 'Description'
        ),
        'live' => array(
            'title'       => 'Title',
            'location'    => 'Website URL',
            'feed'        => 'Feed URL'
        )
    );

    public static $bookmark_limits = array(
        'bookmarks_toolbar' => 3,
        'bookmarks_menu' => 5
    );

    // }}}

    public function url()
    {
        $url = url::base() . 
            "profiles/{$this->created_by_user->screen_name}/browsers/{$this->short_name}";
        return $url;
    }

    /**
     * Extract & validate data from a form and optionally update this instance 
     * with the data.
     *
     * @param  array Form data, replaced by reference with a Validation instance.
     * @param  boolean Whether or not to update this instance's properties.
     * @return boolean Whether or not the data was valid.
     */
    public function validate(&$data, $set=true)
    {
        $submitted_fields = array_merge(array(
            // HACK: Since these are synthesized via callbacks, consider 
            // them as submitted.
            'bookmarks_toolbar', 'bookmarks_menu', 'locales'
        ), array_keys($data));

		$data = Validation::factory($data)
			->pre_filter('trim')

            ->add_rules('uuid', 'alpha_dash')
            ->add_rules('short_name', 'required', 'alpha_dash', 'length[3,128]')
            ->add_rules('title', 'required', 'length[3,255]')
            ->add_rules('category', 'length[3,255]')
            ->add_rules('description', 'length[0,1000]')
            ->add_rules('startpage_content', 'length[0,1000]')
            ->add_rules('startpage_feed_url', 'length[0,255]', 'url')
            ->add_rules('addons_collection_url', 'length[0,255]', 'url')
            ->add_rules('persona_id', 'is_numeric')
            
            ->add_callbacks('short_name', 'Repack_Model::short_name_available')

            ->add_callbacks('locales', array($this, 'extractValidLocales'))
            ->add_callbacks('bookmarks_toolbar', array($this, 'extractBookmarks'))
            ->add_callbacks('bookmarks_menu', array($this, 'extractBookmarks'))
            ;

        $is_valid = $data->validate();

        foreach ($data->field_names() as $name) {
            if (!in_array($name, $submitted_fields)) {
                // Pre-populate fields with object instance properties.
                $data[$name] = $this->{$name};
            } else if ($is_valid && $set) {
                // Update the instance with valid form data if necessary.
                $this->{$name} = $data[$name];
            }
        }

        if ('post' != request::method()) {

            $data['product_id'] = $this->product['id'];

            $osen = array();
            foreach (self::$os_choices as $name=>$label) {
                if ($this->{"repack_{$name}"}) {
                    $osen[] = $name;
                }
            }
            $data['os'] = $osen;
        
        } else if (isset($data['product_id'])) {

            $all_products = Kohana::config('products.all_products');
            if (!isset($all_products[$data['product_id']])) {
                // Not a valid product, so flag an error.
                $data->add_error("product_id", 'invalid');
            } else {
                // Set a new product for the repack
                $this->product = $all_products[$data['product_id']];
            }

            foreach (self::$os_choices as $name=>$label) {
                $this->{"repack_{$name}"} = in_array($name, $data['os']);
            }

        }

        return $is_valid;
    }

    /**
     * Extract selected locales from form data, accepting only locales that 
     * match valid product locales.
     */
    public function extractValidLocales(&$valid, $field)
    {
        if (empty($this->locales) && empty($valid['locales'])) {
            // Detect locale from request if neither repack nor form offers locales.
            $m = array();
            preg_match_all(
                '/[-a-z]{2,}/', 
                strtolower(trim(@$_SERVER['HTTP_ACCEPT_LANGUAGE'])), 
                $m
            );
            $valid['locales'] = $m[0];
        }

        if (empty($valid['locales']) || !$valid['locales']) {

            // Populate form from repack product locales.
            $valid['locales'] = $this->locales;

        } else {

            // Ensure that only locales appearing in the product locales are 
            // accepted from form data into the repack.
            $locales = array();

            $lc_prod_locales = array();
            foreach ($this->product['locales'] as $locale) {
                $lc_prod_locales[strtolower($locale)] = $locale;
            }

            $form_locales = $valid['locales'];
            if (!empty($form_locales)) foreach ($form_locales as $locale) {
                $locale = strtolower($locale);
                if (isset($lc_prod_locales[$locale])) {
                    $locales[] = $lc_prod_locales[$locale];
                }
            }
            $valid['locales'] = $locales;

        }

        return $valid['locales'];
    }

    /**
     * Validate a bookmark extracted from form data.
     */
    public function validateBookmark(&$data)
    {
        $type = isset($data['type']) ? $data['type'] : 'normal';
		$data = Validation::factory($data)
			->pre_filter('trim')
            ->add_rules('type', 'length[0,16]')
            ->add_rules('title', 'required', 'length[3,255]')
            ->add_rules('location', 'required', 'url')
            ;
        if ('normal' == $type) {
            $data->add_rules('description', 'length[0,1024]');
        } else {
            $data->add_rules('feed', 'required', 'url');
        }
        $is_valid = $data->validate();
        return $is_valid;
    }

    /**
     * Extract bookmarks from form data.
     */
    public function extractBookmarks(&$valid, $prefix)
    {
        if ('post' != request::method()) {

            // If not a POST, just pass along the object property.
            return $valid[$prefix] = $this->{$prefix};

        } else {

            $new_bookmarks = array();

            if (!empty($valid[$prefix . '_type'])) {
                $types = $valid[$prefix . '_type'];

                // Copy the bookmark data arrays from validator so 
                // array_shift() works in the next loop
                $bm_data = array();
                foreach (self::$type_fields as $type=>$fields) {
                    foreach ($fields as $name=>$label) {
                        $n = "{$prefix}_{$name}";
                        if (!empty($valid[$n]))
                            $bm_data[$n] = $valid[$n];
                    }
                }

                // Now, iterate through all the types listed in the form, which 
                // also happens to correspond to the whole set of bookmarks in 
                // form
                foreach ($types as $idx => $type) {

                    // Extract properties for the current bookmark from the form 
                    // according to type.
                    $bm = array('type'=>$type);
                    $fields = self::$type_fields[$type];
                    foreach ($fields as $name => $label) {
                        $bm[$name] = array_shift($bm_data["{$prefix}_{$name}"]);
                    }

                    // Validate the bookmark and flag any errors.
                    $is_valid = $this->validateBookmark($bm);
                    if (!$is_valid) {
                        foreach ($bm->errors() as $key => $val) {
                            $valid->add_error("{$prefix}_{$key}[{$idx}]", $val);
                        }
                        $valid->add_error("{$prefix}", 'invalid');
                    }

                    // Add the extracted properties to the list of bookmarks.
                    $new_bookmarks[] = $bm->as_array();

                }

            }

            if (count($new_bookmarks) > self::$bookmark_limits[$prefix]) {
                $valid->add_error("{$prefix}", 'limit');
            }

            return $valid[$prefix] = $new_bookmarks;
        }
    }

    /**
     * Constructor, sets all object properties from an optional array.  Assigns 
     * a UUID if none given.
     *
     * @param array Object property values
     */
    public function __construct($data=null)
    {
        $this->created = gmdate('c');
        $this->modified = gmdate('c');
        $this->version = gmdate('YmdHis');
        return $this->init($data);
    }

    /**
     * Initialize this object with data.
     * 
     * @param   array Data used in initialization
     * @returns Mozilla_BYOB_Repack
     */
    public function init($data)
    {
        if (!empty($data) && is_array($data)) {
            foreach ($data as $name=>$value) {
                $this->{$name} = $value;
            }
        }
        if (empty($this->uuid)) {
            $this->uuid = uuid::uuid();
        }
        if (empty($this->created)) {
            $this->created = gmdate('c');
        }
        if (!empty($this->created_by)) {
            $user = ORM::factory('profile', $this->created_by);
            $this->created_by_user = $user;
        }

        //if (empty($this->url)) {
        //    $this->url = $this->url();
        //}
    
        if (empty($this->product)) {
            $this->product = Kohana::config('products.latest_product');
        }

        return $this;
    }

    /**
     * Static class object factory
     *
     * @param array Name/values with which to initialize the object.
     * @return Mozilla_BYOB_Repack
     */
    public static function factory($data=null) 
    {
        return new self($data);
    }

    /**
     * Static class object factory with JSON deserialization
     *
     * @param string serialized JSON data.
     * @return Mozilla_BYOB_Repack
     */
    public static function factoryJSON($json)
    {
        return self::factory()->fromJSON($json);
    }

    /**
     * Produce a flat array of name/value metadata suitable for the model.
     *
     * @return array
     */
    public function getMetadata() 
    {
        $data = array();
        foreach(self::$metadata_fields as $name) {
            $data[$name] = $this->{$name};
        }
        return $data;
    }

    /**
     * Produce an array representation of this object, suitable for init().
     */
    public function asArray()
    {
        $data = array();
        foreach(get_object_vars($this) as $name=>$value) {
            if (substr($name,0,1) == '_') continue;
            $data[$name] = $value;
        }
        return $data;
    }

    /**
     * Build a JSON serialization of this object.
     *
     * @return string
     */
    public function asJSON() 
    {
        return json_encode($this->asArray());
    }

    /**
     * Set properties from a JSON serialization.
     *
     * @return Mozilla_BYOB_Repack
     */
    public function fromJSON($json) 
    {
        return $this->init(json_decode($json, true));
    }

    /**
     * Build and return a repack tools config INI source based on the 
     * properties of this instance.
     */
    public function buildConfigIni()
    {
        $data = View::factory('repacks/ini/main')
            ->set('repack', $this)
            ->render();
        return $data;
    }

    /**
     * Build and return a repack tools config INI source based on the 
     * properties of this instance.
     */
    public function buildDistributionIni()
    {
        $data = View::factory('repacks/ini/distribution')
            ->set('repack', $this)
            ->render();
        return $data;
    }

    /**
     * Perform the actual process of browser repacking based on the properties 
     * of this object, calling on the external repack script.
     */
    public function processRepack($run_script=TRUE)
    {
        Kohana::log('info', 'Processing repack for ' . $this->created_by_user->username . ' - ' . $this->uuid);
        Kohana::log_save();

        $storage   = Kohana::config('repacks.storage');
        $downloads = Kohana::config('repacks.downloads');
        $script    = Kohana::config('repacks.repack_script');

        // Clean up and make the repack directory.
        $repack_dir = "$storage/{$this->uuid}/{$this->version}";
        if (is_dir($repack_dir))
            $this->rmdir_recurse($repack_dir);
        mkdir($repack_dir, 0775, true);

        // Clean up and make the downloads directory.
        $downloads_dir = "$downloads/{$this->uuid}/{$this->version}";
        if (is_dir($downloads_dir))
            $this->rmdir_recurse($downloads_dir);
        mkdir($downloads_dir, 0775, true);

        // Remember the original directory and change to the repack dir.
        $origdir = getcwd();
        chdir($repack_dir);

        Kohana::log('debug', "Repack directory at {$repack_dir}");
        Kohana::log_save();
            
        // Generate the repack configs.
        file_put_contents("$repack_dir/xpi-config.ini", $this->buildConfigIni());
        file_put_contents("$repack_dir/distribution.ini", $this->buildDistributionIni());

        // Execute the repack script and capture output / status.
        Kohana::log('debug', "Executing {$script}...");
        Kohana::log_save();
        $output = array();
        $status = 0;
        exec("{$script} xpi-config.ini >repack.log 2>&1", $output, $status);

        if (0 == $status) {
            Kohana::log('debug', "Success in {$script} with status $status");
        } else {
            Kohana::log('error', "Failure in {$script} with status $status");
        }
        Kohana::log_save();

        // If the script executed successfully, there should be repacks available.
        if (0 == $status) {

            // Copy the repacks into the download directory.
            foreach (glob("{$repack_dir}/repacks/*") as $fn) {
                if (is_file($fn)) 
                    copy($fn, $downloads_dir.'/'.basename($fn));
            }

        }

        // Restore original directory.
        chdir($origdir);

        Kohana::log('info', 'Finished repack for ' . $this->created_by_user->username . ' - ' . $this->uuid);
        Kohana::log_save();
    }

    /**
     * Recursively delete a directory and its contents.
     * see: http://us.php.net/manual/en/function.rmdir.php#89497
     */
    function rmdir_recurse($path) {
        $path= rtrim($path, '/').'/';
        $handle = opendir($path);
        for (;false !== ($file = readdir($handle));)
            if($file != "." and $file != ".." ) {
                $fullpath= $path.$file;
                if( is_dir($fullpath) ) {
                    $this->rmdir_recurse($fullpath);
                } else {
                    unlink($fullpath);
                }
            }
        closedir($handle);
        rmdir($path);
    } 

}
