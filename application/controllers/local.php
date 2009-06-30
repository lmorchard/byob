<?php
/**
 * Local controller base class for all application controllers.
 */
class Local_Controller extends Layout_Controller
{

    public function __construct()
    {
        parent::__construct();

        // Set the global profile ID for log events during this request.
        Logevent_Model::setCurrentProfileID(
            authprofiles::get_profile('id')
        );

        if (authprofiles::is_allowed('repacks', 'view_approval_queue')) {
            $this->view->set_global(array(
                'approval_queue_allowed' => TRUE,
                'approval_queue_count' => 
                    ORM::factory('repack')
                    ->where('state', Repack_Model::$states['pending'])
                    ->count_all()
            ));
        }

        Event::add('system.403', array($this, 'show_403'));
    }

    /**
     * In reaction to a 403 Forbidden event, throw up a forbidden view.
     */
    public function show_403()
    {
        header('403 Forbidden');
        $this->view = View::factory('forbidden');
        $this->_display();
        exit();
    }

    /**
     * Attempt to grab a repack based on router parameters.
     */
    protected function _getRequestedRepack()
    {
        $params = Router::get_params(array(
            'uuid'        => null,
            'screen_name' => null,
            'short_name'  => null,
            'status'      => null
        ));

        $m = ORM::factory('repack');

        if (!empty($params['screen_name'])) {
            // If a screen name is supplied, try finding the associated 
            // profile.  Dump out with a 404 if profile not found.
            $profile = ORM::factory('profile', $params['screen_name']);
            if (null == $profile) {
                Event::run('system.404');
                exit();
            }
            $m->where('profile_id', $profile->id);
        }

        // Handle status, if provided.
        if ('released' == $params['status'] || null === $params['status']) {
            $m->whereReleased(TRUE);
        } elseif ('unreleased' == $params['status']) {
            $m->whereReleased(FALSE);
        } else {
            $m->where('state', $params['status']);
        }

        if ($params['uuid']) {
            // Add the criteria for UUID.
            $m->where('uuid', $params['uuid']);
        } else if ($params['short_name']) {
            // Add the criteria for short name.
            $m->where('short_name', $params['short_name']);
        } else {
            // UUID or short name is required.
            Event::run('system.404');
            exit();
        }
         
        // Finally, look for the repack.
        $rp = $m->find();

        if (null === $rp) {
            // Bail out if not found.
            Event::run('system.404');
            exit();
        }

        return $rp;
    }


}
