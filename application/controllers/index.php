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
        if (!authprofiles::is_logged_in()) {
            $this->view->repacks = ORM::factory('repack')
                ->where('profile_id', authprofiles::get_profile('id'))
                ->find_all();
        }

        $repacks = new Repack_Model();
        if (!authprofiles::is_allowed('repacks', 'view_unreleased')) {
            $repacks->whereReleased();
        }
        $this->view->latest_repacks = $repacks->find_all(10);
    }

}
