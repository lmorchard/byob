<?php
/**
 *
 */
class Index_Controller extends Local_Controller
{
    protected $auto_render = TRUE;

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if (!AuthProfiles::is_logged_in()) {
            $this->view->repacks = ORM::factory('repack')
                ->where('created_by', AuthProfiles::get_profile('id'))
                ->find_all();
        }

        $this->view->latest_repacks = ORM::factory('repack')->find_all(10);
    }

}
