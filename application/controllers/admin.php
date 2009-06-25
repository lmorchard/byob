<?php
/**
 *
 */
class Admin_Controller extends ORM_Manager_Controller
{
    protected $url_base = 'admin';
    protected $known_models = array('post', 'profile', 'login', 'repack');

    /**
     * Initialize the controller.
     */
    public function __construct()
    {
        parent::__construct();
    }

}
