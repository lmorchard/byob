<?php
/**
 * Storage of a customized browser repack.
 *
 * @package    BYOB
 * @subpackage Models
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Repack_Model extends ORM
{
    // {{{ Class properties
    
    public $belongs_to = array(
        'created_by'=>'profile'
   );
    
    protected $sorting = array(
        'modified' => 'desc',
        'created'  => 'desc'
    );

    protected $attrs = array(
        'min_version' => '3.0',
        'max_version' => '3.5.*',
        'version'     => '1'
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
        'bookmarks_toolbar' => 5,
        'bookmarks_menu' => 5
    );


    // }}}

    /**
     * Extract & validate data from a form and optionally update this instance 
     * with the data.
     *
     * @param  array Form data, replaced by reference with a Validation instance.
     * @param  boolean Whether or not to update this instance's properties.
     * @return boolean Whether or not the data was valid.
     */
    public function validate_repack(&$data, $set=true)
    {
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
            ->add_rules('os', 'is_array')
            
            ->add_callbacks('short_name', array($this, 'short_name_available'))

            ->add_callbacks('locales', array($this, 'extractValidLocales'))
            ->add_callbacks('bookmarks_toolbar', array($this, 'extractBookmarks'))
            ->add_callbacks('bookmarks_menu', array($this, 'extractBookmarks'))
            ->add_callbacks('product', array($this, 'extractProduct'))
            ->add_callbacks('os', array($this, 'extractOS'))
            ;

        $is_valid = $data->validate();

        if (!$set) {
            foreach ($data->field_names() as $name) {
                $data[$name] = $this->{$name};
            }
        } elseif ($is_valid && $set) {
            foreach ($data->field_names() as $name) {
                $this->{$name} = $data[$name];
            }
        }

        return $is_valid;
    }


    /**
     * Validate and extract the OS list
     */
    public function extractOS(&$valid, $field) 
    {
        if (isset($valid['os'])) {
            foreach (self::$os_choices as $name=>$label) {
                $this->{"repack_{$name}"} = in_array($name, $valid['os']);
            }
        } else {
            foreach (self::$os_choices as $name=>$label) {
                if ($this->{"repack_{$name}"}) {
                    $osen[] = $name;
                }
            }
        }
    }

    /**
     * Validate & extract the selected product from the form data by ID.
     */
    public function extractProduct(&$valid, $field) 
    {
        $all_products = Kohana::config('products.all_products');
        if (!isset($valid['product_id'])) {
            $valid['product_id'] = $this->product['id'];
        }
        if (!isset($all_products[$valid['product_id']])) {
            // Not a valid product, so flag an error.
            $valid->add_error("product_id", 'invalid');
            $is_valid = false;
        } else {
            $valid['product'] = $all_products[$valid['product_id']];
        }
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
            if (!empty($this->product['locales'])) {
                foreach ($this->product['locales'] as $locale) {
                    $lc_prod_locales[strtolower($locale)] = $locale;
                }
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
     * Validation callback that checks to see if the given short name is taken 
     * any repack other than the one being validated.
     */
    public function short_name_available($valid, $field)
    {
        $taken = (bool) ORM::factory('repack')
            ->where(array(
                'short_name' => $valid[$field],
                'uuid !='    => $valid['uuid']
            ))
            ->count_all();

        if ($taken) {
            $valid->add_error($field, 'short_name_available');
        }
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
        Kohana::log('info', 'Processing repack for ' . $this->created_by->screen_name . ' - ' . $this->uuid);
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

        Kohana::log('info', 'Finished repack for ' . $this->created_by->screen_name . ' - ' . $this->uuid);
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


    /**
     * Internal access to the parent class' isset()
     */
    protected function orm_isset($column) {
        return parent::__isset($column);
    }

    /**
     * Get object property, with fallback to grab bag of attributes.
     */
    public function __get($column)
    {
        if ('url' == $column) {
            return url::base() . 
                "profiles/{$this->created_by->screen_name}".
                "/browsers/{$this->short_name}";
        }

        try {
            return parent::__get($column);
        } catch (Kohana_Exception $e) {
            if (array_key_exists($column, $this->attrs)) {
                return $this->attrs[$column];
            } else {
                return null;
            }
        }
    }

    /**
     * Set object property, with fallback to grab bag of attributes.
     */
    public function __set($column, $value)
    {
        if ('url' == $column) {
            return $this->url;
        }
        try {
            parent::__set($column, $value);
        } catch (Kohana_Exception $e) {
        }
        return $this->attrs[$column] = $value;
    }

    /**
     * Before saving to database, encode the grab bag of attributes into JSON.
     */
    public function save()
    {
        $this->json_data = json_encode($this->attrs);
        return parent::save();
    }

    /**
     * After loading from database, decode the grab bag of attributes from JSON.
     */
    public function load_values($values)
    {
        parent::load_values($values);
        $this->attrs = json_decode($this->json_data, true);
        if (empty($this->attrs)) $this->attrs = array();
        return $this;
    }

    /**
	 * Sets object values from an array.
	 *
	 * @chainable
	 * @return  ORM
     */
    public function set($arr=null)
    {
        if (empty($arr)) return $this;
        foreach ($arr as $name=>$value) {
            $this->{$name} = $value;
        }
        return $this;
    }

    /**
     * Return all properties as an array, including the grab bag of attributes.
     */
    public function as_array()
    {
        return array_merge($this->attrs, parent::as_array());
    }

}
