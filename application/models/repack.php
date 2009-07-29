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
    public $model_title = "Browser";
    
    public $belongs_to = array('profile', 'product');

    protected $sorting = array(
        'modified' => 'desc',
        'created'  => 'desc'
    );

    // Default attributes
    protected $attrs = array(
        'locales'          => array('en-US'),
        'os'               => array('win','mac','linux'),
        'changed_sections' => array(),
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

    public $search_column_names = array(
        'short_name', 'profile_id', 'state', 'title', 'modified'
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
        'rejected'   => 80,
        'released'   => 90,
        'deleted'    => 100,
        'reverted'   => 110,
    );

    // Legal transitions from key to listed states
    public static $transitions = array(
        'new'        => array('edited', 'requested', 'deleted',),
        'edited'     => array('requested', 'deleted',),
        'requested'  => array('cancelled', 'started',),
        'cancelled'  => array('edited', 'requested', 'deleted',),
        'started'    => array('failed', 'pending',),
        'failed'     => array('edited', 'requested', 'deleted',),
        'pending'    => array('cancelled', 'released', 'rejected',),
        'rejected'   => array('edited', 'requested', 'deleted',),
        'released'   => array('reverted',),
        'reverted'   => array('edited', 'requested', 'deleted',),
        'deleted'    => array(),
    );

    // state flags for which the repack should be treated as read-only.
    public static $read_only_states = array(
        'requested', 'started', 'pending', 'approved'
    );

    public static $edit_sections = array(
        'general',
        'locales',
        'platforms',
        'firstrun',
        'bookmarks',
        'addons',
        'persona',
        'advanced',
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
    public function validateRepack(&$data, $set=true, $section='general')
    {
		$data = Validation::factory($data)
			->pre_filter('trim');

        switch ($section) {

            case 'general':
                $data->add_rules('description', 'length[0,1000]');
                break;
            
            case 'locales':
                $data->add_rules('locales', 'required', 'is_array');
                $data->add_callbacks('locales', array($this, 'extractLocales'));
                break;

            case 'platforms':
                $data->add_rules('os', 'required', 'is_array');
                break;

            case 'firstrun':
                $data->add_rules('firstrun_content', 'length[0,1000]');
                $data->add_rules('addons_collection_url', 'length[0,255]', 'url');
                break;

            case 'bookmarks':
                $data->add_callbacks('bookmarks_toolbar', 
                    array($this, 'extractBookmarks'));
                $data->add_callbacks('bookmarks_menu', 
                    array($this, 'extractBookmarks'));
                break;

            case 'addons':
                $data->add_rules('addons', 'is_array');
                $data->add_callbacks('addons', array($this, 'addonsAreKnown'));
                break;

            case 'persona':
                $data->add_rules('persona_url', 'length[0,255]', 'url');
                $data->add_callbacks('persona_url', array($this, 'personaExists'));
                break;

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

            // HACK: If there's a Persona URL supplied, ensure that
            // the Personas add-on is selected for install.
            if (!empty($this->persona_url) == $section && 
                    !in_array('10900', $this->addons)) {
                $this->addons = array_merge(array('10900'), $this->addons);
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
     * Ensure the persona indicated by URL exists.
     */
    public function personaExists(&$valid, $field)
    {
        $persona_model = new Persona_Model();
        $persona = $persona_model->find_by_url($valid[$field]);
        if (!$persona->loaded) {
            $valid->add_error($field, 'unknown_persona');
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
            $valid->add_error($field, 'need_locale');

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
            ->add_rules('location', 'required', 'valid::url')
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
     * Check multiple privileges, returning an array of indexed results.
     */
    public function checkPrivileges($privs, $profile_id=null)
    {
        $results = array();
        foreach ($privs as $priv) {
            $results[$priv] = $this->checkPrivilege($priv, $profile_id);
        }
        return $results;
    }

    /**
     * Run through possible privileges and assemble results.
     *
     * @TODO: Allow request for just one or two perms, optimization
     */
    public function checkPrivilege($priv,$profile_id=null)
    {
        if (null === $profile_id) {
            $profile_id = authprofiles::get_profile('id');
        }
        $own = $profile_id == $this->profile_id;

        switch($priv) {

            case 'view':
                return authprofiles::is_allowed('repacks', 'view') ||
                    ($this->isRelease() &&
                        authprofiles::is_allowed('repacks', 'view_released')) ||
                    (!$this->isRelease() && 
                        authprofiles::is_allowed('repacks', 'view_unreleased')) ||
                    ($own && authprofiles::is_allowed('repacks', 'view_own'));

            case 'view_history':
                return authprofiles::is_allowed('repacks', 'view_history') ||
                    ($own && authprofiles::is_allowed('repacks', 'view_own_history'));
                
            case 'edit':
                return (!$this->isLockedForChanges() && 
                        authprofiles::is_allowed('repacks', 'edit')) ||
                    (!$this->isLockedForChanges() && $own && 
                        authprofiles::is_allowed('repacks', 'edit_own')) ||
                    ($this->isLockedForChanges() &&
                        authprofiles::is_allowed('repacks', 'edit_locked'));

            case 'delete':
                return (!$this->isLockedForChanges() && 
                        authprofiles::is_allowed('repacks', 'delete')) ||
                    (!$this->isLockedForChanges() && $own && 
                        authprofiles::is_allowed('repacks', 'delete_own')) ||
                    ($this->isLockedForChanges() &&
                    authprofiles::is_allowed('repacks', 'delete_locked'));

            case 'download':
                return authprofiles::is_allowed('repacks', 'download') ||
                    ($this->isRelease() &&
                        authprofiles::is_allowed('repacks', 'download_released')) ||
                    (!$this->isRelease() && 
                        authprofiles::is_allowed('repacks', 'download_unreleased')) ||
                    ($own && authprofiles::is_allowed('repacks', 'download_own'));

            case 'release':
                return authprofiles::is_allowed('repacks', 'release') ||
                    ($own && authprofiles::is_allowed('repacks', 'release_own'));

            case 'revert':
                return authprofiles::is_allowed('repacks', 'revert') ||
                    ($own && authprofiles::is_allowed('repacks', 'revert_own'));
            case 'approve':
                return authprofiles::is_allowed('repacks', 'approve') ||
                    ($own && authprofiles::is_allowed('repacks', 'approve_own'));
            case 'auto_approve':
                return authprofiles::is_allowed('repacks', 'auto_approve') ||
                    ($own && authprofiles::is_allowed('repacks', 'auto_approve_own'));
            case 'reject':
                return authprofiles::is_allowed('repacks', 'reject');
            case 'cancel':
                return authprofiles::is_allowed('repacks', 'cancel') ||
                    ($own && authprofiles::is_allowed('repacks', 'cancel_own'));
            case 'begin':
                return authprofiles::is_allowed('repacks', 'begin');
            case 'finish':
                return authprofiles::is_allowed('repacks', 'finish');
            case 'fail':
                return authprofiles::is_allowed('repacks', 'fail');
            case 'distributionini':
                return authprofiles::is_allowed('repacks', 'distributionini');
            case 'repackcfg':
                return authprofiles::is_allowed('repacks', 'repackcfg');
            case 'repacklog':
                return authprofiles::is_allowed('repacks', 'repacklog');

        };
    }


    /**
     * Build a URL for a repack
     * @TODO Should this be in the controller?
     */
    public function url($action=null)
    {
        $profile = ORM::factory('profile', $this->profile_id);
        $url = url::base() . 
            "profiles/{$profile->screen_name}".
            "/browsers/{$this->short_name}";
        if ($this->state != self::$states['released'])
            $url .= '/unreleased';
        if ($action)
            $url .= ";$action";
        return $url;
    }

    /**
     * Build a release URL for the repack, regardless of the release status of 
     * this particular instance
     * @TODO Should this be in the controller?
     */
    public function releaseUrl($action=null)
    {
        $url = url::base() . 
            "profiles/{$this->profile->screen_name}".
            "/browsers/{$this->short_name}";
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
        if (empty($this->state)) return 'new';
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
        return in_array($this->getStateName(), self::$read_only_states);
    }

    /**
     * Check whether this repack can change to the new named state.
     *
     * @param  string  name of the new state
     * @return boolean
     */
    public function canChangeState($new_state)
    {
        $old_state = $this->getStateName();
        return in_array(
            $new_state, 
            self::$transitions[$old_state]
        );
    }


    /**
     * Change the state of this repack.
     *
     * @param  string Name of the new state
     * @param  string Any user-supplied comments about the change
     * @return Repack_Model
     * @chainable
     */
    public function changeState($new_state, $comments=null)
    {
        $old_state = $this->getStateName();

        if (!$this->canChangeState($new_state)) {
            throw new Repack_Model_State_Exception(
                "State change from {$old_state} to {$new_state} not allowed"
            );
        }
        
        $ev_data = array(
            'repack'    => $this->as_array(),
            'old_state' => $old_state,
            'new_state' => $new_state,
            'comments'  => $comments,
        );
        Event::run("BYOB.repack.changeState", $ev_data);

        $this->state = self::$states[$ev_data['new_state']];
        $this->save();

        if ('modified' !== $ev_data['new_state']) {
            Logevent_Model::log(
                $this->uuid, $ev_data['new_state'], $ev_data['comments']
            );
        }
        return $this;
    }


    /**
     * Request a new release of this repack.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function requestRelease($comments=null)
    {
        return $this->changeState('requested', $comments);
    }

    /**
     * Cancel a release request for this repack.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function cancelRelease($comments=null)
    {
        return $this->changeState('cancelled', $comments);
    }

    /**
     * Approve the current release request for this repack.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function approveRelease($comments=null)
    {
        if ($this->canChangeState('released')) {

            // There should only be one previous release, but search for multiples 
            // anyway just in case.
            $previous_releases = ORM::factory('repack')->where(array(
                'uuid'   => $this->uuid,
                'state' => self::$states['released']
            ))->find_all();

            // Revert each of the previous releases with the model method, so as to 
            // allow for final clean up if necessary.
            foreach ($previous_releases as $release) {
                $release->revertRelease('Previous release made obsolete by new release');
            }

            $this->changed_sections = array();
        }

        return $this->changeState('released', $comments);
    }

    /**
     * Reject the current release request for this repack.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function rejectRelease($comments=null)
    {
        return $this->changeState('rejected', $comments);
    }

    /**
     * Mark the repack as a release in progress.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function beginRelease($comments=null)
    {
        return $this->changeState('started', $comments);
    }

    /**
     * Mark the repack as a failed build.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function failRelease($comments=null)
    {
        return $this->changeState('failed', $comments);
    }

    /**
     * Complete the release process for this repack.
     *
     * @param  string optional comments
     * @return Repack_Model
     */
    public function finishRelease($comments=null)
    {
        $this->changeState('pending', $comments);

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
        $repack = $this;
        if ($this->canChangeState('reverted')) {

            // Look for existing changes - don't want to clobber them.
            $existing_changes = ORM::factory('repack')->where(array(
                'uuid'      => $this->uuid,
                'state <>' => self::$states['released']
            ))->find_all();

            if ($existing_changes->count() > 0) {
                // Delete this repack and switch to pending changes.
                $this->changeState('reverted', $comments);
                $this->delete();
                return $existing_changes[0];
            }

        }
        return $repack->changeState('reverted', $comments);
    }

    /**
     * Delete this repack, cleaning up any builds and other associated 
     * resources.
     */
    public function delete($id=NULL)
    {
        if ($id === NULL AND $this->loaded) {
            $this->changeState('deleted');

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
     * Build a list of values suitable for display in a list.
     *
     * @return Array
     */
    public function as_list_array()
    {
        $vals = array(
            'short_name' => array($this->url(), $this->short_name),
            'profile_id' => array(
                url::base() . 'profiles/' . $this->profile->screen_name,
                $this->profile->screen_name
            ),
            'title'      => $this->title,
            'state'      => $this->getStateName(),
            'modified'   => $this->modified
        ); 
        return $vals;
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

        if (!isset($this->short_name)) {
            // Auto-generate a 12-character short name for the browser.
            $this->short_name = substr(base64_encode(time() + rand()), -14, 12);
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
        if ('version' == $column) {
            $time = $this->modified;
            if (empty($time)) $time = $this->created;
            return gmdate('odmHis',strtotime($time));
        }
        if ('collection_addons' == $column) {
            if (empty($this->addons_collection_url)) {
                return array();
            } else {
                return Model::factory('addon')
                    ->find_all_by_collection_url($this->addons_collection_url);
            }
        }
        if ('persona' == $column) {
            return Model::factory('persona')->find_by_url($this->persona_url);
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
        if ('short_name' == $column && isset($this->short_name)) {
            // Refuse to overwrite autogenerated short_name
            return;
        }
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
        if ('collection_addons' == $column) {
            return isset($this->addons_collection_url);
        }
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
        $this->title = "Mozilla Firefox for {$this->profile->org_name}";
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

/**
 * Exception thrown when an illegal state change is attempted
 */
class Repack_Model_State_Exception extends Exception 
{
}
