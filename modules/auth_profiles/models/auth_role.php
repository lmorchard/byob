<?php
/**
 * Roles model
 *
 * @package    auth_profiles
 * @subpackage models
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class Auth_Role_Model extends ORM
{
    public $has_and_belongs_to_many = array('profiles');

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
}
