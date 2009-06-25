<?php
/**
 * Storage of a customized browser repack.
 *
 * @package    BYOB
 * @subpackage Models
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Repack_Model extends ManagedORM
{
    // {{{ Model properties

    // Display title for the model
    public $model_title = "Repack";
    
    public $belongs_to = array('profile', 'product');

    protected $sorting = array(
        'modified' => 'desc',
        'created'  => 'desc'
    );

    // Titles for named columns
    public $table_column_titles = array(
        'id'          => 'ID',
        'uuid'        => 'UUID',
        'short_name'  => 'Short name',     
        'title'       => 'Title',
        'description' => 'Description',
        'created'     => 'Created',
        'modified'    => 'Modified',
    );

    public $list_column_names = array(
        'id', 'profile_id', 'title', 'short_name', 'uuid', 'created', 'modified'
    );

    public $edit_column_names = array(
        'short_name', 'title'
    );

    protected $attrs = array(
        'min_version' => '3.0',
        'max_version' => '3.5.*',
        'version'     => '1',
        'os'          => array('win','mac','linux'),
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

    // Workflow states for a repack
    public static $states = array(
        'new'        => 0,
        'edited'     => 10,
        'requested'  => 20,
        'cancelled'  => 30,
        'started'    => 40,
        'failed'     => 50,
        'pending'    => 60,
        'approved'   => 70,
        'rejected'   => 80,
        'released'   => 90,
        'deleted'    => 100,
        'reverted'   => 110,
    );

    // state flags for which the repack should be treated as read-only.
    public static $read_only_states = array(
    );

    // }}}
    
    /**
     * Find an editable alternative for this repack, creating a new clone if 
     * necessary or simply returning self if editable.
     *
     * Also sets state of the returned repack to edited.
     *
     * @return Repack_Model
     */
    public function findEditable()
    {
        $edited = self::$states['edited'];

        if ($this->state != self::$states['released']) {
            // This repack is itself editable, so return self.
            $this->state = $edited;
            return $this;
        }

        // Since this repack is released, look for one with pending changes.
        $pending_rp = ORM::factory('repack')
            ->where(array(
                'uuid'      => $this->uuid,
                'state <>' => Repack_Model::$states['released'],
            ))->find();
        if ($pending_rp->loaded) {
            $pending_rp->state = $edited;
            return $pending_rp;
        }

        // No repack with pending changes, so clone a new one.
        $new_rp = ORM::factory('repack')->set(array_merge(
            $this->as_array(), 
            array(
                'id'     => null, 
                'state' => $edited
            )
        ));
        return $new_rp;
    }


    /**
     * Build a URL for a repack
     * @TODO Should this be in the controller?
     */
    public function url($action=null)
    {
        $url = url::base() . 
            "profiles/{$this->profile->screen_name}".
            "/browsers/{$this->short_name}";
        if ($this->state != self::$states['released'])
            $url .= '/unreleased';
        if ($action)
            $url .= ";$action";
        return $url;
    }
    
    /**
     * Convert the state code into a name.
     *
     * @return string
     */
    public function getStateName()
    {
        $r_states = array_flip(self::$states);
        return $r_states[$this->state];
    }

    /**
     * Determine whether this repack has been released.
     *
     * @return boolean
     */
    public function isRelease()
    {
        return $this->state == self::$states['released'];
    }

    /**
     * Determine whether this repack is pending release approval
     *
     * @return boolean
     */
    public function isPendingApproval()
    {
        return $this->state == self::$states['pending'];
    }

    /**
     * Determine whether this repack should be considered locked as read-only.
     *
     * @return boolean
     */
    public function isLockedForChanges()
    {
        return in_array(
            $this->getStateName(), 
            array('requested', 'started', 'pending', 'approved', 'released')
        );
    }


    /**
     * Shortcut to add where clause for released state
     *
     * @param boolean Whether to search for released (TRUE) or non-released (FALSE) repacks.
     * @return Repack_Model
     * @chainable
     */
    public function whereReleased($released=TRUE) {
        return $this->where(
            ($released) ? 'state' : 'state <>', 
            self::$states['released']
        );
    }


    /**
     * Request a new release of this repack.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function requestRelease($comments=null)
    {
        $allowed_state = arr::extract(
            self::$states, 
            'new', 'edited', 'failed', 'cancelled', 'rejected', 'reverted'
        );
        if (!in_array($this->state, $allowed_state)) {
            throw new Exception('requestRelease not allowed');
        }
        $this->state = self::$states['requested'];
        $this->save();
        Logevent_Model::log($this->uuid, 'requestRelease', $comments);
        
        // TODO: Fire up the repack process

        return $this;
    }

    /**
     * Cancel a release request for this repack.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function cancelRelease($comments=null)
    {
        $allowed_state = arr::extract(self::$states, 'requested', 'pending');
        if (!in_array($this->state, $allowed_state)) {
            throw new Exception('cancelRelease not allowed');
        }
        $this->state = self::$states['cancelled'];
        $this->save();
        Logevent_Model::log($this->uuid, 'cancelRelease', $comments);

        return $this;
    }

    /**
     * Approve the current release request for this repack.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function approveRelease($comments=null)
    {
        $allowed_state = arr::extract(self::$states, 'pending');
        if (!in_array($this->state, $allowed_state)) {
            throw new Exception('approveRelease not allowed');
        }

        // There should only be one previous release, but search for multiples 
        // anyway just in case.
        $previous_releases = ORM::factory('repack')->where(array(
            'uuid'   => $this->uuid,
            'state' => self::$states['released']
        ))->find_all();

        // Delete each of the previous releases with the model method, so as to 
        // allow for final clean up if necessary.
        foreach ($previous_releases as $release) {
            $release->delete();
        }

        $this->state = self::$states['released'];
        $this->save();
        Logevent_Model::log($this->uuid, 'approveRelease', $comments);

        return $this;
    }

    /**
     * Reject the current release request for this repack.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function rejectRelease($comments=null)
    {
        $allowed_state = arr::extract(self::$states, 'pending');
        if (!in_array($this->state, $allowed_state)) {
            throw new Exception('rejectRelease not allowed');
        }
        $this->state = self::$states['rejected'];
        $this->save();
        Logevent_Model::log($this->uuid, 'rejectRelease', $comments);

        return $this;
    }

    /**
     * Mark the repack as a release in progress.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function beginRelease($comments=null)
    {
        $allowed_state = arr::extract(self::$states, 'requested');
        if (!in_array($this->state, $allowed_state)) {
            throw new Exception('beginRelease not allowed');
        }
        $this->state = self::$states['started'];
        $this->save();
        
        Logevent_Model::log($this->uuid, 'beginRelease', $comments);

        return $this;
    }

    /**
     * Mark the repack as a failed build.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function failRelease($comments=null)
    {
        $allowed_state = arr::extract(self::$states, 'started');
        if (!in_array($this->state, $allowed_state)) {
            throw new Exception('failRelease not allowed');
        }
        $this->state = self::$states['failed'];
        $this->save();
        
        Logevent_Model::log($this->uuid, 'failRelease', $comments);

        return $this;
    }

    /**
     * Complete the release process for this repack.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function finishRelease($comments=null)
    {
        $allowed_state = arr::extract(self::$states, 'started');
        if (!in_array($this->state, $allowed_state)) {
            throw new Exception('finishRelease not allowed');
        }

        // Finally, mark this repack as released.
        $this->state = self::$states['pending'];
        $this->save();
        Logevent_Model::log($this->uuid, 'finishRelease', $comments);

        return $this;
    }

    /**
     * Revert a previously approved & completed release.
     *
     * If there are existing changes in progress, this repack will be deleted.  
     * Otherwise, the state will be changed to reverted.
     *
     * The repack that survives the process will be returned.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function revertRelease($comments=null)
    {
        $allowed_state = arr::extract(self::$states, 'released');
        if (!in_array($this->state, $allowed_state)) {
            throw new Exception('revertRelease not allowed');
        }

        // TODO: Delete / make private released assets
        
        // Look for existing changes - don't want to clobber them.
        $existing_changes = ORM::factory('repack')->where(array(
            'uuid'      => $this->uuid,
            'state <>' => self::$states['released']
        ))->find_all();

        Logevent_Model::log($this->uuid, 'revertRelease', $comments);

        if ($existing_changes->count() > 0) {
            // Delete this repack in favor of pending changes.
            $this->delete();
            return $existing_changes[0];
        } else {
            // Change state of this repack to reverted.
            $this->state = self::$states['reverted'];
            $this->save();
            return $this;
        }

    }


    /**
     * Extract & validate data from a form and optionally update this instance 
     * with the data.
     *
     * @param  array Form data, replaced by reference with a Validation instance.
     * @param  boolean Whether or not to update this instance's properties.
     * @return boolean Whether or not the data was valid.
     */
    public function validateRepack(&$data, $set=true)
    {
		$data = Validation::factory($data)
			->pre_filter('trim')

            ->add_rules('uuid', 'alpha_dash')
            ->add_rules('short_name', 'required', 'alpha_dash', 'length[3,128]')
            ->add_rules('title', 'required', 'length[3,255]')
            ->add_rules('category', 'length[3,255]')
            ->add_rules('description', 'length[0,1000]')
            ->add_rules('firstrun_content', 'length[0,1000]')
            ->add_rules('addons_collection_url', 'length[0,255]', 'url')
            //->add_rules('persona_id', 'is_numeric')
            //->add_rules('product_id', 'is_numeric')
            ->add_rules('locales', 'is_array')
            ->add_rules('os', 'is_array')
            
            ->add_callbacks('short_name', 
                array($this, 'isShortNameAvailable'))

            ->add_callbacks('product_id', 
                array($this, 'extractProduct'))
            ->add_callbacks('locales', 
                array($this, 'extractLocales'))
            ->add_callbacks('bookmarks_toolbar', 
                array($this, 'extractBookmarks'))
            ->add_callbacks('bookmarks_menu', 
                array($this, 'extractBookmarks'))

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
     * Validate & extract the selected product from the form data by ID.
     */
    public function extractProduct(&$valid, $field) 
    {
        return;
        $all_products = Kohana::config('products.all_products');

        if (!isset($valid['product_id'])) {
            $valid['product_id'] = $this->product_id;
            return;
        }

        if (!isset($all_products[$valid['product_id']])) {
            // Not a valid product, so flag an error.
            $valid->add_error("product_id", 'invalid');
            $is_valid = false;
        }
    }

    /**
     * Extract selected locales from form data, accepting only locales that 
     * match valid product locales.
     */
    public function extractLocales(&$valid, $field)
    {
        if (empty($this->locales) && empty($valid[$field])) {
            // Detect locale from request if neither repack nor form offers locales.
            $m = array();
            preg_match_all(
                '/[-a-z]{2,}/', 
                strtolower(trim(@$_SERVER['HTTP_ACCEPT_LANGUAGE'])), 
                $m
            );
            $valid[$field] = $m[0];
        }

        if (empty($valid[$field])) {

            // Populate form from repack product locales.
            $valid[$field] = $this->locales;

        } else {

            // Ensure that only locales appearing in the product locales are 
            // accepted from form data into the repack.
            $valid_locales = array();

            foreach (self::$locale_choices as $code=>$name) {
                if (in_array($code, $valid[$field])) {
                    $valid_locales[] = $code;
                }
            }

            $valid[$field] = $valid_locales;

        }

        return $valid[$field];
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
    public function isShortNameAvailable($valid, $field)
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
        Kohana::log('info', 'Processing repack for ' . $this->profile->screen_name . ' - ' . $this->uuid);
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

        // Execute the repack script and capture output / state.
        Kohana::log('debug', "Executing {$script}...");
        Kohana::log_save();
        $output = array();
        $state = 0;
        exec("{$script} xpi-config.ini >repack.log 2>&1", $output, $state);

        if (0 == $state) {
            Kohana::log('debug', "Success in {$script} with state $state");
        } else {
            Kohana::log('error', "Failure in {$script} with state $state");
        }
        Kohana::log_save();

        // If the script executed successfully, there should be repacks available.
        if (0 == $state) {

            // Copy the repacks into the download directory.
            foreach (glob("{$repack_dir}/repacks/*") as $fn) {
                if (is_file($fn)) 
                    copy($fn, $downloads_dir.'/'.basename($fn));
            }

        }

        // Restore original directory.
        chdir($origdir);

        Kohana::log('info', 'Finished repack for ' . $this->profile->screen_name . ' - ' . $this->uuid);
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
     * Before saving to database, encode the grab bag of attributes into JSON.
     */
    public function save()
    {
        if (!isset($this->state)) {
            $this->state = self::$states['new'];
        } elseif ($this->state == self::$states['new']) {
            $this->state = self::$states['edited'];
        }

        $this->json_data = json_encode($this->attrs);

        parent::save();

        if ($this->state == self::$states['new']) {
            Logevent_Model::log($this->uuid, 'created', null, $this->as_array());
        } elseif ($this->state == self::$states['edited']) {
            Logevent_Model::log($this->uuid, 'modified', null, $this->as_array());
        }

        return $this;
    }

    /**
     * Get object property, with fallback to grab bag of attributes.
     */
    public function __get($column)
    {
        if ('url' == $column)
            return $this->url();

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
        try {
            parent::__set($column, $value);
        } catch (Kohana_Exception $e) {
        }
        if ('json_data' != $column)
            $this->attrs[$column] = $value;
    }

    /**
     * After loading from database, decode the grab bag of attributes from JSON.
     */
    public function load_values(array $values)
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
