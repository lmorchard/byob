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
        'first_name'     => 'First name',
        'last_name'      => 'Last name',
        'phone'          => 'Phone',
        'fax'            => 'Fax',

        'is_personal'    => 'Is personal account',

        'org_name'       => 'Organization name',
        'org_type'       => 'Organization type',
        'org_type_other' => 'Organization type (other)',

        'address_1'      => 'Street Address 1',
        'address_2'      => 'Street Address 2',
        'city'           => 'City',
        'state'          => 'State',
        'zip'            => 'Zip / Postal Code',
        'country'        => 'Country',

        'created'        => 'Created',
        'modified'       => 'Modified',
    );

    public $search_column_names = array(
        'screen_name', 'first_name', 'last_name', 'org_name', 'modified',
        'is_personal', 'org_type', 'org_type_other',
    );

    // }}}

    /**
     * Build a list of values suitable for display in a list.
     *
     * @return Array
     */
    public function as_list_array()
    {
        $vals = array(
            'screen_name' => array(
                url::base() . 'profiles/' . $this->screen_name,
                $this->screen_name
            ),
            'first_name'  => $this->first_name,
            'last_name'   => $this->last_name,
            'is_personal' => $this->is_personal,
            'org_name'    => $this->org_name,
            'org_type'    => $this->org_type,
            'org_type_other' => $this->org_type_other,
            'modified'    => $this->modified
        ); 
        return $vals;
    }
    
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
     * Validate registration data
     */
    public function validate_registration(&$data)
    {
        // Force screen name to match login name.
        if (isset($data['login_name']))
            $data['screen_name'] = $data['login_name'];

        // TODO: Use login model to validate login properties
        $login_model = new Login_Model();

        $data = Validation::factory($data)
            ->pre_filter('trim')
            ->add_rules('login_name',       
                'required', 'length[4,12]', 'valid::alpha_dash', 
                array($login_model, 'is_login_name_available'))
            ->add_rules('email', 
                'required', 'length[3,255]', 'valid::email',
                array($login_model, 'is_email_available'))
            ->add_rules('password', 'length[6,255]', 'required')
            ->add_rules('password_confirm', 'required', 'matches[password]')
            ->add_rules('screen_name', 
                'required', 'length[3,64]', 'valid::alpha_dash', 
                array($this, 'is_screen_name_available'))
            ->add_rules('first_name', 'required')
            ->add_rules('last_name', 'required')
            ;

        if ('post' == request::method() && !recaptcha::check()) {
            $data->add_error('recaptcha', recaptcha::error());
        }

        return $data->validate();
    }

    /**
     * Validate form data for profile modification, optionally saving if valid.
     */
    public function validate_update(&$data, $save = FALSE)
    {
        $data = Validation::factory($data)
            ->pre_filter('trim')
            ->add_rules('first_name', 'required', 'valid::standard_text')
            ->add_rules('last_name', 'required')
            ;
        return $this->validate($data, $save);
    }

}
