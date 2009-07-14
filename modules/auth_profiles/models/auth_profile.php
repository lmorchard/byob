<?php
/**
 * Profiles model
 *
 * @package    auth_profiles
 * @subpackage models
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class Auth_Profile_Model extends ORM
{
    // {{{ Model attributes

    // Titles for named columns
    public $table_column_titles = array(
        'id'             => 'ID',
        'uuid'           => 'UUID',
        'screen_name'    => 'Screen name',     
        'full_name'      => 'Full name',
        'bio'            => 'Bio',
        'created'        => 'Created',
        'last_login'     => 'Last login',
    );

    public $has_and_belongs_to_many = array('logins','roles');

    // }}}

    /**
     * Check an individual privilege by nickname against variants including 
     * *_own & etc.
     */
    public function checkPrivilege($priv, $profile_id=null)
    {
        if (null === $profile_id) {
            $profile_id = authprofiles::get_profile('id');
        }
        $own = ( $profile_id == $this->id );

        switch($priv) {

            case 'view':
                return
                    authprofiles::is_allowed('profiles', 'view') ||
                    ($own && authprofiles::is_allowed('profiles', 'view_own'));

            case 'edit':
                return
                    authprofiles::is_allowed('profiles', 'edit') ||
                    ($own && authprofiles::is_allowed('profiles', 'edit_own'));

            default:
                return authprofiles::is_allowed('profiles', $priv); 

        }
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
     * Find the default login for this profile, usually the first registered.
     * @TODO: Change point for future multiple logins per profile
     */
    public function find_default_login_for_profile()
    {
        if (!$this->loaded) return null;
        $logins = $this->logins;
        return $logins[0];
    }

    /**
     * Find profiles by role name.
     *
     * @param  string|array Role name or names
     * @return ORM_Iterator
     */
    public function find_all_by_role($role_name)
    {
        if (!is_array($role_name)) $role_name = array($role_name);
        return $this
            ->join('profiles_roles', 'profiles_roles.profile_id', 'profiles.id')
            ->join('roles', 'roles.id', 'profiles_roles.role_id')
            ->in('roles.name', $role_name)
            ->find_all();
    }


    /**
     * Validate form data for profile creation, optionally saving it if valid.
     */
    public function validate_create(&$data, $save = FALSE)
    {
        $data = Validation::factory($data)
            ->pre_filter('trim')
            ->add_rules('screen_name',      
                'required', 'length[3,64]', 'valid::alpha_dash', 
                array($this, 'is_screen_name_available'))
            ->add_rules('full_name', 'required', 'valid::standard_text')
            ;
        return $this->validate($data, $save);
    }

    /**
     * Validate form data for profile modification, optionally saving if valid.
     */
    public function validate_update(&$data, $save = FALSE)
    {
        $data = Validation::factory($data)
            ->pre_filter('trim')
            ->add_rules('screen_name',      
                'required', 'length[3,64]', 'valid::alpha_dash', 
                array($this, 'is_screen_name_available'))
            ->add_rules('full_name', 'required', 'valid::standard_text')
            ;
        return $this->validate($data, $save);
    }

    /**
     * Determine whether a given screen name has been taken.
     *
     * @param  string   screen name
     * @return boolean
     */
    public function is_screen_name_available($name)
    {
        if ($this->loaded && $name == $this->screen_name) {
            return true;
        }
        $count = $this->db
            ->where('screen_name', $name)
            ->count_records($this->table_name);
        return (0==$count);
    }


    /**
     * Returns the unique key for a specific value. This method is expected
     * to be overloaded in models if the model has other unique columns.
     *
     * If the key used in a find is a non-numeric string, search 'screen_name' column.
     *
     * @param   mixed   unique value
     * @return  string
     */
    public function unique_key($id)
    {
        if (!empty($id) && is_string($id) && !ctype_digit($id)) {
            return 'screen_name';
        }
        return parent::unique_key($id);
    }


    /**
     * Add a role by name.
     *
     * Looks up an existing record, or creates a new one if not found.
     *
     * @chainable
     * @return Profile_Model
     */
    public function add_role($role_name)
    {
        if (!$this->loaded) return;

        // Look for existing role by name, create a new one if not found.
        $role = ORM::factory('role', $role_name);
        if (!$role->loaded) {
            $role = ORM::factory('role')->set(array(
                'name' => $role_name
            ))->save();
        }

        // Add the role, save it, done.
        $this->add($role);
        return $this;
    }

    /**
     * Set roles for this profile.
     *
     * @chainable
     * @return Profile_Model
     */
    public function add_roles($role_names)
    {
        if (!$this->loaded) return;
        $this->clear_roles();
        foreach ($role_names as $name) {
            $this->add_role($name);
        }
    }

    /**
     * Remove all roles from this profile.
     *
     * @chainable
     * @return Profile_Model
     */
    public function clear_roles()
    {
        if (!$this->loaded) return;
        foreach ($this->roles as $role) {
            $this->remove($role);
        }
        return $this;
    }


    /**
     * Set a profile attribute
     *
     * @param string Profile ID
     * @param string Profile attribute name
     * @param string Profile attribute value
     */
    public function set_attribute($name, $value)
    {
        if (!$this->loaded) return null;
        $profile_id = $this->id;

        $row = $this->db
            ->select()->from('profile_attributes')
            ->where('profile_id', $profile_id)
            ->where('name', $name)
            ->get()->current();

        if (null == $row) {
            $data = array(
                'profile_id' => $profile_id,
                'name'       => $name,
                'value'      => $value
            );
            $data['id'] = $this->db
                ->insert('profile_attributes', $data)
                ->insert_id();
        } else {
            $this->db->update(
                'profile_attributes', 
                array('value' => $value),
                array('profile_id'=>$profile_id, 'name'=>$name)
            );
        }
    }

    /**
     * Set profile attributes
     *
     * @param string Profile ID
     * @param array list of profile attributes
     */
    public function set_attributes($attributes)
    {
        foreach ($attributes as $name=>$value) {
            $this->set_attribute($name, $value);
        }
    }

    /**
     * Get a profile attribute
     *
     * @param string Profile ID
     * @param string Profile attribute name
     * @return string Attribute value 
     */
    public function get_attribute($name)
    {
        if (!$this->loaded) return null;
        $profile_id = $this->id;

        $select = $this->db
            ->select('value')
            ->from('profile_attributes')
            ->where('profile_id', $profile_id)
            ->where('name', $name);
        $row = $select->get()->current();
        if (null == $row) return false;
        return $row->value;
    }

    /**
     * Get all profile attributes
     *
     * @param string Profile ID
     * @return array Profile attributes
     */
    public function get_attributes($names=null)
    {
        if (!$this->loaded) return null;
        $profile_id = $this->id;

        $select = $this->db->select()
            ->from('profile_attributes')
            ->where('profile_id', $profile_id);
        if (null != $names) {
            $select->in('name', $names);
        }
        $rows = $select->get();
        $attribs = array();
        foreach ($rows as $row) {
            $attribs[$row->name] = $row->value;
        }
        return $attribs;
    }

}
