<?php
/**
 * Profile model
 *
 * @package    auth_profiles
 * @subpackage models
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class Profile_Model extends Auth_Profile_Model
{
    // {{{ Model attributes

    public $has_many = array('repacks');

    // Titles for named columns
    public $table_column_titles = array(
        'id'             => 'ID',
        'uuid'           => 'UUID',
        'screen_name'    => 'Screen name',     
        'full_name'      => 'Full name',
        'phone'          => 'Phone',
        'fax'            => 'Fax',
        'org_address'    => 'Organization address',
        'org_name'       => 'Organization name',
        'org_type'       => 'Organization type',
        'org_type_other' => 'Organization type (other)',
        'created'        => 'Created',
        'last_login'     => 'Last login',
    );

    // }}}

    /**
     * Return a set of columns to be shown in lists.
     */
    public function get_list_columns()
    {
        return arr::extract(
            $this->table_columns, 
            'id', 'screen_name', 'full_name', 'org_name', 'last_login', 
            'created'
        );
    }

    /**
     * Return a set of columns to be shown in editing.
     */
    public function get_edit_columns()
    {
        return arr::extract(
            $this->table_columns, 
            'screen_name', 'full_name', 'phone', 'fax', 'org_address', 
            'org_name', 'org_type', 'org_type_other'
        );
    }

}
