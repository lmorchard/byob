<?php
/**
 * workspace of a customized browser repack.
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

    // Default attributes
    protected $attrs = array(
        'locales' => array('en-US'),
        'os'      => array('win','mac','linux'),
    ); 

    // Titles for named columns
    public $table_column_titles = array(
        'id'          => 'ID',
        'profile_id'  => 'Profile ID',
        'uuid'        => 'UUID',
        'short_name'  => 'Short name',     
        'title'       => 'Title',
        'description' => 'Description',
        'state'       => 'State',
        'created'     => 'Created',
        'modified'    => 'Modified',
    );

    public $list_column_names = array(
        'id', 'profile_id', 'title', 'short_name', 'state', 'created', 'modified'
    );

    public $edit_column_names = array(
        'short_name', 'title'
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
            ->add_rules('addons', 'is_array')
            ->add_rules('locales', 'is_array')
            ->add_rules('os', 'is_array')
            
            ->add_callbacks('short_name', array($this, 'isShortNameAvailable'))
            ->add_callbacks('addons', array($this, 'addonsAreKnown'))
            ->add_callbacks('locales', array($this, 'extractLocales'))
            ->add_callbacks('bookmarks_toolbar', array($this, 'extractBookmarks'))
            ->add_callbacks('bookmarks_menu', array($this, 'extractBookmarks'))
            ;

        if (empty($data['os'])) {
            // No operating systems selected is useless, so ignore the input.
            $data['os'] = $this->os;
        }

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
     * Ensure all selected addons are known to the application.
     */
    public function addonsAreKnown(&$valid, $field)
    {
        $addon_model = new Addon_Model();
        $chosen_ids = $valid[$field];
        foreach ($chosen_ids as $id) {
            $addon = $addon_model->find($id);
            if (!$addon) {
                $valid->add_error($field, 'unknown_addon');
                break;
            }
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
            $choices = array_map('strtolower', $valid[$field]); 
            foreach (self::$locale_choices as $code=>$name) {
                if (in_array(strtolower($code), $choices)) {
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
            if (!$pending_rp->isLockedForChanges()) {
                $pending_rp->state = $edited;
            }
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
     * Find a released alternative for this repack, returning self
     * if released or null if no release found.
     *
     * @return Repack_Model
     */
    public function findRelease()
    {
        if ($this->state == self::$states['released']) {
            // This repack is itself released, so return self.
            return $this;
        }

        // Since this repack is edited, look for a release.
        $rp = ORM::factory('repack')
            ->where(array(
                'uuid'  => $this->uuid,
                'state' => Repack_Model::$states['released'],
            ))->find();
        if ($rp->loaded) return $rp;

        return null;
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
     * Compare this repack against another and assemble an array of the 
     * differences. eg. name => array(other_val, this_val)
     *
     * @param  Repack_Model
     * @return array
     */
    public function compare($other)
    {
        $this_vals  = $this->as_array();
        $other_vals = $other->as_array();
        $keys = array_unique(array_merge(
            array_keys($this_vals), array_keys($other_vals)
        ));

        $changed = array();

        foreach ($keys as $key) {
            $this_val  = isset($this_vals[$key]) ? $this_vals[$key] : null;
            $other_val = isset($other_vals[$key]) ? $other_vals[$key] : null;
            if ($this_val != $other_val)
                $changed[$key] = array($other_val, $this_val);
        }

        return $changed;
    }


    /**
     * Run through possible privileges and assemble results.
     *
     * @TODO: Allow request for just one or two perms, optimization
     */
    public function checkPrivileges($privileges=null,$profile_id=null)
    {
        if (null === $profile_id) {
            $profile_id = authprofiles::get_profile('id');
        }
        $own = $profile_id == $this->profile_id;
        $perms = array(

            'view' => 
                authprofiles::is_allowed('repacks', 'view') ||
                ($this->isRelease() &&
                    authprofiles::is_allowed('repacks', 'view_released')) ||
                (!$this->isRelease() && 
                    authprofiles::is_allowed('repacks', 'view_unreleased')) ||
                ($own && authprofiles::is_allowed('repacks', 'view_own')),

            'view_history' =>
                authprofiles::is_allowed('repacks', 'view_history') ||
                ($own && authprofiles::is_allowed('repacks', 'view_own_history')),
                
            'edit' => 
                (!$this->isLockedForChanges() && 
                    authprofiles::is_allowed('repacks', 'edit')) ||
                (!$this->isLockedForChanges() && $own && 
                    authprofiles::is_allowed('repacks', 'edit_own')) ||
                ($this->isLockedForChanges() &&
                    authprofiles::is_allowed('repacks', 'edit_locked')),

            'delete' => 
                (!$this->isLockedForChanges() && 
                    authprofiles::is_allowed('repacks', 'delete')) ||
                (!$this->isLockedForChanges() && $own && 
                    authprofiles::is_allowed('repacks', 'delete_own')) ||
                ($this->isLockedForChanges() &&
                authprofiles::is_allowed('repacks', 'delete_locked')),

            'download' => 
                authprofiles::is_allowed('repacks', 'download') ||
                ($this->isRelease() &&
                    authprofiles::is_allowed('repacks', 'download_released')) ||
                (!$this->isRelease() && 
                    authprofiles::is_allowed('repacks', 'download_unreleased')) ||
                ($own && authprofiles::is_allowed('repacks', 'download_own')),

            'release' =>
                authprofiles::is_allowed('repacks', 'release') ||
                ($own && authprofiles::is_allowed('repacks', 'release_own')),

            'revert' =>
                authprofiles::is_allowed('repacks', 'revert') ||
                ($own && authprofiles::is_allowed('repacks', 'revert_own')),

            'approve' => 
                authprofiles::is_allowed('repacks', 'approve') ||
                ($own && authprofiles::is_allowed('repacks', 'approve_own')),

            'auto_approve' => 
                authprofiles::is_allowed('repacks', 'auto_approve') ||
                ($own && authprofiles::is_allowed('repacks', 'auto_approve_own')),

            'reject' => 
                authprofiles::is_allowed('repacks', 'reject'),

            'cancel' => 
                authprofiles::is_allowed('repacks', 'cancel') ||
                ($own && authprofiles::is_allowed('repacks', 'cancel_own')),

            'begin' =>
                authprofiles::is_allowed('repacks', 'begin'),

            'finish' =>
                authprofiles::is_allowed('repacks', 'finish'),

            'fail' =>
                authprofiles::is_allowed('repacks', 'fail'),

            'distributionini' =>
                authprofiles::is_allowed('repacks', 'distributionini'),

            'repackcfg' =>
                authprofiles::is_allowed('repacks', 'repackcfg'),

            'repacklog' =>
                authprofiles::is_allowed('repacks', 'repacklog'),

        );
        return $perms;
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
        if (empty($this->state)) {
            return NULL;
        }
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
            array('requested', 'started', 'pending', 'approved')
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
        
        // Schedule the repack process
        $ev_data = $this->as_array();
        Event::run('BYOB.process_repack', $ev_data);

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

        // Clean up the builds.
        $this->deleteBuilds();

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

        // Schedule the builds move.
        $ev_data = $this->as_array();
        Event::run('BYOB.move_builds', $ev_data);

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

        // Clean up the builds.
        $this->deleteBuilds();
        
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

        // Auto-approve for trusted profiles granted the privilege
        if (authprofiles::is_allowed('repacks', 'auto_approve_own', $this->profile)) {
            return $this->approveRelease('Auto approved.');
        }

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

        // Clean up the builds.
        $this->deleteBuilds();
        
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
     * Build and return a repack tools config INI source based on the 
     * properties of this instance.
     */
    public function buildRepackCfg()
    {
        $data = View::factory('repacks/ini/repack_cfg')
            ->set('repack', $this)
            ->render();
        return $data;
    }


    /**
     * Perform the actual process of browser repacking based on the properties 
     * of this object, calling on the external repack script.
     */
    public function processBuilds($run_script=TRUE)
    {
        $this->beginRelease();

        try {

            Kohana::log('info', 'Processing repack for ' .
                $this->profile->screen_name . ' - ' . $this->short_name);
            Kohana::log_save();

            $workspace = Kohana::config('repacks.workspace');
            $script    = Kohana::config('repacks.repack_script');

            $downloads_private = 
                Kohana::config('repacks.downloads_private');

            // Clean up and make the repack directory.
            $repack_dir =
                "$workspace/partners/{$this->profile->screen_name}_{$this->short_name}";
            if (is_dir($repack_dir)) {
                self::rmdirRecurse($repack_dir);
            }
            mkdir("$repack_dir/distribution", 0775, true);

            Kohana::log('debug', "Repack directory at {$repack_dir}");
            Kohana::log_save();

            // Generate the repack configs.
            file_put_contents("$repack_dir/repack.cfg",
                $this->buildRepackCfg());
            file_put_contents("$repack_dir/distribution/distribution.ini",
                $this->buildDistributionIni());

            // Check for selected addons...
            if (!empty($this->addons)) {

                // Create the directory for this repack's addons
                mkdir("$repack_dir/extensions", 0775, true);

                $addon_model = new Addon_Model();
                foreach ($this->addons as $addon_id) {

                    // Look for selected addons, skip any that are unknown.
                    $addon = $addon_model->find($addon_id);
                    if (!$addon) continue;

                    // Update the addon files and copy them into the repack.
                    $addon_dir = $addon->updateFiles();
                    self::recurseCopy(
                        $addon_dir, 
                        "{$repack_dir}/extensions/{$addon->guid}"
                    );

                }
            }

            if ($run_script) {

                // Remember the original directory and change to the repack dir.
                $origdir = getcwd();
                chdir($workspace);

                // Execute the repack script and capture output / state.
                $output = array();
                $state = 0;
                $repack_name = "{$this->profile->screen_name}_{$this->short_name}";
                $cmd = join(' ', array(
                    "{$script}",
                    "-d partners",
                    "-p $repack_name",
                    "-v {$this->product->version}",
                    "-n {$this->product->build}",
                    ">partners/{$repack_name}/repack.log 2>&1"
                ));
                Kohana::log('debug', "Executing {$cmd}...");
                Kohana::log_save();
                exec($cmd, $output, $state);

                // Restore original directory.
                chdir($origdir);

                if (0 != $state) {
                    Kohana::log('error', "Failure in {$script} with state $state");
                    $this->failRelease("Failure in {$script} with state $state");
                    return;
                }

                Kohana::log('debug', "Success in {$script} with state $state");
                Kohana::log_save();

                // Record all the filenames generated by the repack.
                $src = "{$workspace}/repacked_builds/{$this->product->version}".
                    "/build{$this->product->build}/{$repack_name}";
                $files = array();
                foreach (glob("{$src}/*/*/*") as $fn) {
                    if (is_file($fn)) $files[] = str_replace("$src/", '', $fn);
                }
                $this->files = $files;
                $this->save();

                // Move the repacks to the private downloads area
                $dest = "{$downloads_private}/{$repack_name}";
                if (is_dir($dest)) self::rmdirRecurse($dest);
                $cmd = rename($src, $dest);

                Kohana::log('debug', "Moved {$src} to {$dest}");
                Kohana::log_save();

            }

            Kohana::log('info', 'Finished repack for ' . 
                $this->profile->screen_name . ' - ' . $this->short_name);
            Kohana::log_save();

            $this->finishRelease();

        } catch (Exception $e) {
            $this->failRelease($e->getMessage());
        }
    }

    /**
     * Move builds from private to public paths if the repack is a release, or 
     * vice versa if the repack is unreleased.
     */
    public function moveBuilds() {

        $src_path = $this->isRelease() ?
            Kohana::config('repacks.downloads_private') :
            Kohana::config('repacks.downloads_public');

        $dest_path = $this->isRelease() ?
            Kohana::config('repacks.downloads_public') :
            Kohana::config('repacks.downloads_private');

        $repack_name = "{$this->profile->screen_name}_{$this->short_name}";

        $src  = "{$src_path}/{$repack_name}";
        $dest = "{$dest_path}/{$repack_name}";
        if (is_dir($dest)) self::rmdirRecurse($dest);
        $cmd = rename($src, $dest);

        Kohana::log('debug', "Moved {$src} to {$dest}");
    }

    /**
     * Delete all build files associated with this repack.
     */
    public function deleteBuilds()
    {
        // Schedule the deletion of builds, assuming that this repack may be 
        // gone from the database by that point.
        $data = array_merge(
            $this->as_array(),
            array(
                'is_release'  => $this->isRelease(),
                'screen_name' => $this->profile->screen_name,
            )
        );
        Event::run('BYOB.delete_builds', $data);

        // Forget any files the repack knew about.
        $this->files = array();
        $this->save();
    }


    /**
     * Handle an event for repack processing.
     */
    public static function handleProcessRepackEvent()
    {
        if (null === Event::$data) return;
        $repack = ORM::factory('repack', Event::$data['id']);
        $repack->processBuilds();
    }

    /**
     * Handle an event for moving builds.
     */
    public static function handleMoveBuildsEvent()
    {
        if (null === Event::$data) return;
        $repack = ORM::factory('repack', Event::$data['id']);
        $repack->moveBuilds();
    }

    /**
     * Clean up / delete the files associated with a repack build.
     *
     * The repack record itself may be gone by this point, so hopefully the 
     * event data comes with everything we need to know to find the files.
     *
     * See also deleteBuilds()
     */
    public static function handleDeleteBuildsEvent()
    {
        if (null === Event::$data) return;
        extract(Event::$data);

        $base_path = $is_release ?
            Kohana::config('repacks.downloads_public') :
            Kohana::config('repacks.downloads_private');
        $repack_name = "{$screen_name}_{$short_name}";
        $dest = "{$base_path}/{$repack_name}";

        Kohana::log('info', 'Deleting builds for ' .
            $screen_name . ' - ' . $short_name);
        Kohana::log('debug', "rmdir $dest");
        Kohana::log_save();

        self::rmdirRecurse($dest);
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

    /**
     * Utility function to recursively copy files.
     */
    public static function recurseCopy($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::recurseCopy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    } 


    /**
     * Delete this repack, cleaning up any builds and other associated 
     * resources.
     */
    public function delete($id=NULL)
    {
        if ($id === NULL AND $this->loaded) {

            // Clean up builds for this repack.
            $this->deleteBuilds();

            // And if this is the last repack with this UUID, delete the 
            // associated log events.
            $count = $this->db->where('uuid', $this->uuid)
                ->count_records('repacks');
            if ($count == 1) {
                ORM::factory('logevent')
                    ->where('uuid', $this->uuid)->delete_all();
            }

        }
        return parent::delete($id);
    }

    /**
     * Before saving to database, encode the grab bag of attributes into JSON.
     */
    public function save()
    {
        // Force upgrade the repack to the latest product on save.
        $this->product_id = $this->db->query(
            'SELECT id FROM products ORDER BY created DESC LIMIT 1'
        )->current()->id;

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
        if ('version' == $column)
            return gmdate('odmHis',strtotime($this->modified));

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
     * Checkes if object data is set.
     *
     * @param   string  column name
     * @return  boolean
     */
    public function __isset($column) {
        return isset($this->attrs[$column]) ? true : parent::__isset($column);
    }

    /**
     * After loading from database, decode the grab bag of attributes from JSON.
     */
    public function load_values(array $values)
    {
        parent::load_values($values);
        if (!empty($this->json_data)) {
            $this->attrs = json_decode($this->json_data, true);
        }
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
