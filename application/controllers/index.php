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

            $repack_rows = ORM::factory('repack')
                ->where('created_by', AuthProfiles::get_profile('id'))
                ->find_all();

            $repacks = array();
            foreach ($repack_rows as $repack_row) {
                $repacks[] = $repack_row->get();
            }
            $this->view->repacks = $repacks;
        }

        $latest_repacks = ORM::factory('repack')->find_all(10);
        $repacks = array();
        foreach ($latest_repacks as $repack_row) {
            $repacks[] = $repack_row->get();
        }
        $this->view->latest_repacks = $repacks;

    }

}
