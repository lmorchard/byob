<?php
/**
 * Roles model
 *
 * @package    auth_profiles
 * @subpackage models
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class Role_Model extends ORM
{
    protected $has_and_belongs_to_many = array('permissions');

    /**
	 * Returns the unique key for a specific value. This method is expected
	 * to be overloaded in models if the model has other unique columns.
	 *
     * If the key used in a find is a non-numeric string, search 'name' column.
     *
	 * @param   mixed   unique value
	 * @return  string
     */
    public function unique_key($id)
    {
        if (!empty($id) && is_string($id) && !ctype_digit($id)) {
            return 'name';
        }
        return parent::unique_key($id);
    }

    /**
     * Add a permission by name.
     *
     * Note that ->save() must still be called after granting permissions.
     *
	 * @chainable
     * @param   string  Permission name.
	 * @return  ORM
     */
    public function grant_permission($perm_name)
    {
        return $this->add(ORM::factory('permission', $perm_name));
    }

    /**
     * Revoke a permission by name.
     *
     * Note that ->save() must still be called after revoking permissions.
     *
	 * @chainable
     * @param   string  Permission name.
	 * @return  ORM
     */
    public function revoke_permission($perm_name)
    {
        return $this->remove(ORM::factory('permission', $perm_name));
    }

    /**
     * Check a permission by name.
     *
     * @param   string  Permission name.
	 * @return  boolean
     */
    public function has_permission($perm_name)
    {
        return $this->has(ORM::factory('permission', $perm_name));
    }

}
