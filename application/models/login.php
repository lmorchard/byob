<?php
/**
 * Login model
 *
 * @package    BYOB
 * @subpackage models
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class Login_Model extends Auth_Login_Model
{
    // {{{ Model attributes

    // Display title for the model
    public $model_title = "Login";

    // Titles for named columns
    public $table_column_titles = array(
        'id'             => 'ID',
        'login_name'     => 'Login name',     
        'active'         => 'Active',
        'email'          => 'Email',
        'password'       => 'Password',
        'last_login'     => 'Last login',
        'modified'       => 'Modified',
        'created'        => 'Created',
    );

    // }}}

    /**
     * Return a set of columns to be shown in lists.
     */
    public function get_list_columns()
    {
        return arr::extract(
            $this->table_columns, 
            'id', 'login_name', 'active', 'email', 'last_login', 
            'created', 'modified'
        );
    }

    /**
     * Return a set of columns to be shown in editing.
     */
    public function get_edit_columns()
    {
        return arr::extract(
            $this->table_columns, 
            'login_name', 'email', 'active', 'password'
        );
    }

    /**
     * Return a view for rendering of editing fields.
     */
    public function get_edit_column_view($view_base, $column_name, $column_info)
    {
        $to_try = array(
            "{$view_base}/edit/{$column_name}",
            "{$view_base}/edit/default_column",
        );
        foreach ($to_try as $name) {
            $found = Kohana::find_file('views', $name);
            if (!empty($found)) {
                return View::factory($name);
            }
        }
    }

    /**
     * Perform custom validation for model.
     *
     * @param  array           Form data by reference, transformed into Validation
     * @param  string|boolean  Whether to save if valid, or URL string to redirect
     * @return boolean
     */
    public function validate_edit_save(&$form, $save=FALSE)
    {
        $form = Validation::factory($form)
            ->pre_filter('trim')
            ->add_rules('login_name',       
                'required', 'length[3,64]', 'valid::alpha_dash', 
                array($this, 'is_login_name_available'))
            ->add_rules('email', 
                'required', 'length[3,255]', 'valid::email',
                array($this, 'is_email_available'))
            ->add_rules('last_login', 'date')
            ->add_rules('created', 'date')
            ->add_rules('active', 'numeric')
            ;

        if (!empty($form['change_password'])) {
            $form
                ->add_rules('new_password', 'required')
                ->add_rules('new_password_confirm', 
                    'required', 'matches[new_password]')
                ;
        }

        $is_valid = $form->validate();

        if ($is_valid && $save) {

            // Change some known valid fields.
            $this->set(arr::extract(
                $form->as_array(), 
                'login_name', 'active'
            ))->save();

            // Perform password change if requested.
            if (!empty($form['change_password'])) {
                $this->change_password($form['new_password']);
            }

            // Perform email change if value differs.
            if (!empty($form['email']) && $form['email'] != $this->email) {
                $this->change_email($form['email']);
            }

        }

        return $is_valid;
    }

}
