<?php
/**
 *
 */
class Post_Model extends ManagedORM
{
    protected $belongs_to = array('profile');

    public $list_column_names = array(
        'id', 'title', 'user_date', 'modified'
    );

} 
