<?php
/**
 * Profiles model
 *
 * @package    auth_profiles
 * @subpackage models
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class Profile_Model extends ORM
{
    // {{{ Model attributes
    protected $has_and_belongs_to_many = array('roles', 'logins');
    protected $has_many = array('profile_attributes');
    // }}}

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
     * Determine whether a given screen name has been taken.
     *
     * @param  string   screen name
     * @return boolean
     */
    public function is_screen_name_available($name)
    {
        $count = $this->db
            ->where('screen_name', $name)
            ->count_records($this->table_name);
        return (0==$count);
    }

    /**
     *
     */
    public function validate_update(&$data, $save = FALSE)
    {
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
     * Note that ->save() must still be called after adding roles.
     *
	 * @chainable
     * @param   string  Role name.
	 * @return  ORM
     */
    public function add_role($role_name)
    {
        return $this->add(ORM::factory('role', $role_name));
    }

    /**
     * Remove a role by name.
     *
     * Note that ->save() must still be called after removing roles.
     *
	 * @chainable
     * @param   string  Role name.
	 * @return  ORM
     */
    public function remove_role($role_name)
    {
        return $this->remove(ORM::factory('role', $role_name));
    }

    /**
     * Check if a user has a given role.
     *
     * @param   string  Role name.
	 * @return  boolean
     */
    public function has_role($role_name)
    {
        return $this->has(ORM::factory('role', $role_name));
    }

    /**
     * Check a permission by name, across all roles.
     *
     * @param   string  Permission name.
	 * @return  boolean
     */
    public function has_permission($perm_name)
    {
        $perm = ORM::factory('permission', $perm_name);
        foreach ($this->roles as $role) {
            if ($role->has($perm)) return TRUE;
        }
        return FALSE;
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
