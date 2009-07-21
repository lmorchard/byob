<?php
/**
 * Products for repacks.
 *
 * @package    BYOB
 * @subpackage Models
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Product_Model extends ManagedORM
{

    // {{{ Class properties

    // Display title for the model
    public $model_title = "Product";

    public $table_column_titles = array(
        'id'                => 'ID',
        'name'              => 'Name',
        'version'           => 'Version',
        'build'             => 'Build',
        'locales'           => 'Locales',
        'disable_migration' => 'Disable migration?',
        'created'           => 'Created',
        'modified'          => 'Modified'
    );

    public $list_column_names = array(
        'id', 'name', 'version', 'build', 'created'
    );
    public $edit_column_names = array(
        'name', 'version', 'build', 'locales', 'disable_migration'
    );

    public $has_many = array('repack');

    protected $sorting = array(
        'modified' => 'desc',
        'created'  => 'desc'
    );

    // }}}

}
