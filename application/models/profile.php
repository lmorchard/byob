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

    // Display title for the model
    public $model_title = "Profile";

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
        'modified'       => 'Modified',
    );

    // }}}

    /**
     * Return a set of columns to be shown in lists.
     */
    public function get_list_columns()
    {
        return arr::extract(
            $this->table_columns, 
            'id', 'screen_name', 'full_name', 'org_name', 'created', 'modified'
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


    /**
     * Validate form data for profile modification, optionally saving if valid.
     */
    public function validate_update(&$data, $save = FALSE)
    {
        $data = Validation::factory($data)
            ->pre_filter('trim')
            ->add_rules('full_name', 'required', 'valid::standard_text')
            ;
        return $this->validate($data, $save);
    }

}
