<?php
/**
 *
 */
class Admin_Controller extends ORM_Manager_Controller
{
    protected $url_base = 'admin';
    protected $known_model_names = array(
        'repack', 'profile', 
        'login',
        'product', 
        'logevent', 
    );

    /**
     * Initialize the controller.
     */
    public function __construct()
    {
        parent::__construct();
    }

}
