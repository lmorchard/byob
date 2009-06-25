<?php
/**
 * Extension of ORM with special features for management customizations.
 *
 * @package    orm_manager
 * @subpackage libraries
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class ManagedORM extends ORM
{
    // {{{ Model attributes

    // Display title for the model
    public $model_title = null;

    // Titles for named columns
    public $table_column_titles = array();

    public $edit_column_names = null;
    public $list_column_names = null;
    public $list_row_view_name = null;

    protected $batch_methods = array(
        'delete' => array(
            'method' => 'delete',
            'title'  => 'Delete'
        )
    );
    
    // }}}

    /**
     * Perform custom validation for model.
     *
     * @param  array           Form data by reference, transformed into Validation
     * @param  string|boolean  Whether to save if valid, or URL string to redirect
     * @return boolean
     */
    public function validate_edit_save(&$form, $save=FALSE)
    {
        $form = ManagedORM::generic_validate_edit_save($this, $form);
        return $this->validate($form, $save);
    }

    /**
     * Offer a custom set of columns for display in list.
     *
     * @return array.
     */
    public function get_list_columns()
    {
        if (empty($this->list_column_names)) {
            return $this->table_columns;
        }
        return call_user_func_array(
            array('arr','extract'),
            array_merge(
                array($this->table_columns), 
                $this->list_column_names
            )
        );
    }

    /**
     * Offer a custom view instance for list rows.  
     * TODO: Cache the results of the view name lookup.
     *
     * @return string
     */
    public function get_list_row_view($view_base)
    {
        $to_try = array(
            "{$view_base}/list_model/{$this->object_name}_row",
            "{$view_base}/list_model/default_row",
        );
        foreach ($to_try as $name) {
            $found = Kohana::find_file('views', $name);
            if (!empty($found)) {
                return View::factory($name);
            }
        }
    }

    /**
     * Offer a custom view instance for list columns.
     * TODO: Cache the results of the view name lookup.
     *
     * @param  string Column name
     * @param  array  Column info
     * @return string
     */
    public function get_list_column_view($view_base, $column_name, $column_info)
    {
        $to_try = array(
            "{$view_base}/list_model/{$this->object_name}_{$column_info['type']}_column",
            "{$view_base}/list_model/{$this->object_name}_column",
            "{$view_base}/list_model/default_{$column_info['type']}_column",
            "{$view_base}/list_model/default_column",
        );
        foreach ($to_try as $name) {
            $found = Kohana::find_file('views', $name);
            if (!empty($found)) {
                return View::factory($name);
            }
        }
    }


    /**
     * Offer a custom set of columns for display in list.
     *
     * @return array.
     */
    public function get_edit_columns()
    {
        if (empty($this->edit_column_names)) {
            return $this->table_columns;
        }
        $cols = array();
        foreach ($this->edit_column_names as $cn) {
            if (isset($this->table_columns[$cn])) {
                $cols[$cn] = $this->table_columns[$cn];
            }
        }
        return $cols;

    }
    
    /**
     * Offer a custom view instance for list columns.
     * TODO: Cache the results of the view name lookup.
     *
     * @param  string Column name
     * @param  array  Column info - type, length, format, max, unsigned, sequenced, size, null, etc
     * @return string
     */
    public function get_edit_column_view($view_base, $column_name, $column_info)
    {
        $to_try = array(
            "{$view_base}/edit/{$this->object_name}_{$column_info['type']}_column",
            "{$view_base}/edit/{$this->object_name}_column",
            "{$view_base}/edit/default_{$column_info['type']}_column",
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
    public static function generic_validate_edit_save($model, &$form)
    {
        // Protect primary key from edits.
        if (isset($form[$model->primary_key]))
            unset($form[$model->primary_key]);

        $form = Validation::factory($form)->pre_filter('trim');

        $edit_columns = method_exists($model, 'get_edit_columns') ?
            $model->get_edit_columns() : $model->table_columns;

        foreach ($edit_columns as $column_name=>$column_info) {

            if ($column_name == $model->primary_key) {
                continue; // Protect primary key from edits.
            }

            $rules = array($column_name);

            if (!empty($column_info['length'])) {
                $rules[] = "length[0,{$column_info['length']}]";
            } else {
                // HACK: Adding a rule for the sake of adding a rule to get the 
                // field into validation results.
                $rules[] = "length[0,999999999]";
            }

            if ('int' == $column_info['type']) {
                $rules[] = 'numeric';
            }

            if ('string' == $column_info['type'] && !empty($column_info['format'])) {
                $rules[] = 'date';
            }

            if (empty($column_info['null']) || FALSE == $column_info['null']) {
                $rules[] = 'required';
            }

            call_user_func_array(array($form, 'add_rules'), $rules);
        }

        return $form;
    }


}
