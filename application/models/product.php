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
        'url'               => 'URL',
        'locales'           => 'Locales',
        'disable_migration' => 'Disable migration?',
        'created'           => 'Created',
        'modified'          => 'Modified'
    );

    public $list_column_names = array(
        'id', 'name', 'version', 'created', 'modified'
    );
    public $edit_column_names = array(
        'name', 'version', 'url', 'locales', 'disable_migration'
    );

    public $has_many = array('repack');

    protected $sorting = array(
        'modified' => 'desc',
        'created'  => 'desc'
    );

    // }}}

}
