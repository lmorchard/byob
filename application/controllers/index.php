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
                ->where('profile_id', AuthProfiles::get_profile('id'))
                ->find_all();
        }

        $this->view->latest_repacks = ORM::factory('repack')
            ->where('state', Repack_Model::$states['released'])
            ->find_all(10);
    }

}
