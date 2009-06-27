<?php
/**
 * Repack creation / editing controller
 *
 * @package    BYOB
 * @subpackage Controllers
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Repacks_Controller extends Local_Controller
{
    protected $auto_render = TRUE;

    public function __construct()
    {
        parent::__construct();

        $unauth_methods = array(
            'index', 'view', 'firstrun'
        );

        if (!authprofiles::is_logged_in()) {
            if (!in_array(Router::$method, $unauth_methods)) {
                Session::instance()->set_flash(
                    'message', 'Login required to manipulate repacks.'
                );
                return authprofiles::redirect_login();
            }
        }

        $this->repack_model = new Repack_Model();
    }


    /**
     * List browsers for a profile.
     */
    public function index()
    {
        $params = Router::get_params(array(
            'screen_name' => null,
        ));

        // Look for a profile by screen name, 404 if not found.
        $profile = $this->view->profile = 
            ORM::factory('profile', $params['screen_name']);
        if (false === $profile->loaded) {
            return Event::run('system.404');
        }

        // Find all repacks for the profile.
        $all_profile_repacks = ORM::factory('repack')
            ->where('profile_id', $profile->id)
            ->find_all();

        // Index unique repacks by UUID and release / non-release
        $indexed_repacks = array();
        foreach ($all_profile_repacks as $repack) {
            $uuid = $repack->uuid;
            if (!isset($indexed_repacks[$uuid])) 
                $indexed_repacks[$uuid] = array();
            $key = ($repack->isRelease()) ?
                'released' : 'unreleased';
            $indexed_repacks[$uuid][$key] = $repack;
        }

        $this->view->indexed_repacks = $indexed_repacks;
    }

    /**
     * View details of a customized repack
     */
    public function view()
    {
        $rp = $this->_getRequestedRepack();
        $this->view->repack = $rp;
        $this->view->logevents = ORM::factory('logevent')
            ->findByUUID($rp->uuid);
    }

    /**
     * Edit details of a customized repack
     */
    public function edit()
    {
        $params = Router::get_params(array(
            'create' => false,
            'uuid'   => null
        ));

        if (false !== $params['create']) {

            // On creation, instantiate a new repack.
            $rp = ORM::factory('repack');
            $rp->profile_id = authprofiles::get_profile('id');
            $this->view->create = true;

        } else {

            // On editing, look for an editable version of this repack.
            $this->view->create = false;

            $rp = $this->_getRequestedRepack();
            if (!$rp->loaded) {
                return Event::run('system.404');
            }
            if ($rp->profile->id != authprofiles::get_profile('id')) {
                // Bail out if the logged in user doesn't own it.
                return Event::run('system.403');
            }

            $editable_rp = $rp->findEditable();
            if (!$editable_rp) {
                // No editable alternative, so bail.
                return Event::run('system.403');
            } elseif ($editable_rp->id != $rp->id) {
                // Redirect to editable alternative.
                if (!$editable_rp->saved) {
                    $editable_rp->save();
                }
                return url::redirect($editable_rp->url.';edit');
            }

            if ($this->input->post('cancel', false)) {
                return url::redirect($rp->url);
            }

        }

        // Grab the form data, ensure UUID not changed.
        $form_data = ('post' == request::method()) ?
            $this->input->post() : $this->input->get();
        $form_data['uuid'] = $rp->uuid;

        // Try to validate the form data and update the repack.
        $is_valid = $rp->validateRepack(
            $form_data, ('post' == request::method())
        );

        $this->view->set(array(
            'repack'    => $rp,
            'form_data' => $form_data
        ));

        if ('post' != request::method()) {
            // Nothing to do for non-POST but show the form.
            return;
        }

        if (!$is_valid) {

            // Not valid, so flag the errors.
            $this->view->form_errors = 
                $form_data->errors('form_repacks_edit');
        
        } else {

            // This was a valid POST, so save the modified repack.
            $rp->save();

            // Notify the user that the repack was updated.
            Session::instance()->set_flash(
                'message', 
                ($params['create']) ? 
                    'New browser created' : 'Browser details saved'
            );

            if ($this->input->post('done', false)) {
                return url::redirect($rp->url);
            } else {
                return url::redirect($rp->url.';edit');
            }

        }
    }


    /**
     * Request a new browser release
     */
    public function release()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->profile->id != authprofiles::get_profile('id')) {
            return Event::run('system.403');
        }
        if ('post' == request::method()) {
            if (isset($_POST['confirm'])) {
                $rp = $rp->requestRelease($this->input->post('comments'));
            }
            return url::redirect($rp->url);
        }
        $this->view->repack = $rp;
    }

    /**
     * Cancel browser relase request.
     */
    public function cancel()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->profile->id != authprofiles::get_profile('id')) {
            return Event::run('system.403');
        }
        if ('post' == request::method()) {
            if (isset($_POST['confirm'])) {
                $rp = $rp->cancelRelease($this->input->post('comments'));
            }
            return url::redirect($rp->url);
        }
        $this->view->repack = $rp;
    }

    /**
     * Cancel Release of a new release.
     */
    public function approve()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->profile->id != authprofiles::get_profile('id')) {
            return Event::run('system.403');
        }
        if ('post' == request::method()) {
            if (isset($_POST['confirm'])) {
                $rp = $rp->approveRelease($this->input->post('comments'));
            }
            return url::redirect($rp->url);
        }
        $this->view->repack = $rp;
    }

    /**
     * Reject release of a new release.
     */
    public function reject()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->profile->id != authprofiles::get_profile('id')) {
            return Event::run('system.403');
        }
        if ('post' == request::method()) {
            if (isset($_POST['confirm'])) {
                $rp = $rp->rejectRelease($this->input->post('comments'));
            }
            return url::redirect($rp->url);
        }
        $this->view->repack = $rp;
    }

    /**
     * Revert a public release
     */
    public function revert()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->profile->id != authprofiles::get_profile('id')) {
            return Event::run('system.403');
        }
        if ('post' == request::method()) {
            if (isset($_POST['confirm'])) {
                $rp = $rp->revertRelease($this->input->post('comments'));
            }
            return url::redirect($rp->url);
        }
        $this->view->repack = $rp;
    }

    /**
     * Delete the details of a customized repack
     */
    public function delete()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->profile->id != authprofiles::get_profile('id')) {
            return Event::run('system.403');
        }

        $this->view->repack = $rp;

        if ('post' == request::method()) {
            if (isset($_POST['confirm']) ) {
                $rp->delete();
                Session::instance()->set_flash(
                    'message', 'Browser deleted'
                );
                return url::redirect('');
            } else {
                return url::redirect($rp->url);
            }
        }
    }


    public function begin()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->profile->id != authprofiles::get_profile('id')) {
            return Event::run('system.403');
        }
        $rp = $rp->beginRelease();
        return url::redirect($rp->url);
    }

    public function fail()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->profile->id != authprofiles::get_profile('id')) {
            return Event::run('system.403');
        }
        $rp = $rp->failRelease('Solar flares');
        return url::redirect($rp->url);
    }

    public function finish()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->profile->id != authprofiles::get_profile('id')) {
            return Event::run('system.403');
        }
        $rp = $rp->finishRelease();
        return url::redirect($rp->url);
    }


    /**
     * Present the browser first run page for a repack.
     */
    public function firstrun()
    {
        $rp = $this->_getRequestedRepack();

        $this->view->set(array(
            'repack' => $rp
        ));

        $this->layout->set_filename('layout-non-auth');
    }


    /**
     * Spew out the raw xpi-config.ini used by repack
     */
    public function xpiconfigini()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->profile->id != authprofiles::get_profile('id')) {
            return Event::run('system.403');
        }
        $this->auto_render = false;
        header('Content-Type: text/plain');
        echo $rp->buildConfigIni();
    }

    /**
     * Spew out the raw distribution.ini used by repack
     */
    public function distributionini()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->profile->id != authprofiles::get_profile('id')) {
            return Event::run('system.403');
        }
        $this->auto_render = false;
        header('Content-Type: text/plain');
        echo $rp->buildDistributionIni();
    }

}
