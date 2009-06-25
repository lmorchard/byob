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
            'index', 'view', 'startpage', 'firstrun'
        );

        if (!AuthProfiles::is_logged_in()) {
            if (!in_array(Router::$method, $unauth_methods)) {
                Session::instance()->set_flash(
                    'message', 'Login required to manipulate repacks.'
                );
                return AuthProfiles::redirect_login();
            }
        }

        $this->repack_model = new Repack_Model();
    }

    /**
     * List browsers for a user.
     */
    public function index()
    {
        $params = Router::get_params(array(
            'screen_name' => null,
        ));

        $profile = $this->view->profile = 
            ORM::factory('profile', $params['screen_name']);

        if (false === $profile->loaded) {
            return Event::run('system.404');
        }

        $this->view->repacks = ORM::factory('repack')
            ->where('created_by', $profile->id)
            ->find_all();
    }

    /**
     * View details of a customized repack
     */
    public function view()
    {
        $rp = $this->_getRequestedRepack();
        $this->view->repack = $rp;

        if (AuthProfiles::is_logged_in()) {

            // Come up with in-progress releases for this user & repack UUID.
            $mq = new MessageQueue_Model();
            $msgs = $mq->findByOwner(AuthProfiles::get_profile('id'));
            $queued = array();
            foreach ($msgs as $msg) {
                if ($msg->finished_at || $msg->reserved_at) continue;
                $qrp = Mozilla_BYOB_Repack::factoryJSON($msg->body);
                if ($qrp->uuid != $rp->uuid) continue;
                $queued[] = array('msg' => $msg, 'repack' => $qrp);
            }
            $this->view->queued = $queued;

        }

        // Find the completed releases.
        $releases = array();
        $downloads = Kohana::config('repacks.downloads');
        $repack_base = "{$downloads}/{$rp->uuid}/*";
        foreach (glob($repack_base, GLOB_ONLYDIR) as $rev_dir) {
            $files = array();
            foreach (glob("{$rev_dir}/*") as $release_fn) {
                $files[] = basename($release_fn);
            }
            $releases[] = array(
                'rev'   => basename($rev_dir),
                'files' => $files
            );
        }
        $this->view->releases = array_reverse($releases);
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
            $rp->created_by_id = AuthProfiles::get_profile('id');
            $this->view->create = true;

        } else {

            $rp = $this->_getRequestedRepack();
            if ($rp->created_by->id != AuthProfiles::get_profile('id')) {
                // Bail out if the logged in user doesn't own it.
                return Event::run('system.403');
            }
            $this->view->create = false;

            if ($this->input->post('cancel', false)) {
                return url::redirect($rp->url);
            }

        }

        // Grab the form data, ensure UUID not changed.
        $form_data = ('post' == request::method()) ?
            $this->input->post() : $this->input->get();
        $form_data['uuid'] = $rp->uuid;

        // Try to validate the form data and update the repack.
        $is_valid = $rp->validate_repack(
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
     * Delete the details of a customized repack
     */
    public function delete()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->created_by->id != AuthProfiles::get_profile('id')) {
            // Bail out if the logged in user doesn't own it.
            return Event::run('system.403');
        }

        $this->view->repack = $rp;

        if ('post' == request::method()) {
            if (isset($_POST['confirm']) ) {
                $this->repack_model->where(array('uuid' => $rp->uuid))->delete();
                Session::instance()->set_flash(
                    'message', 'Repack deleted'
                );
                return url::redirect('');
            } else {
                return url::redirect($rp->url);
            }
        }
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
     * Present the browser home page for a repack.
     */
    public function startpage()
    {
        $rp = $this->_getRequestedRepack();

        $this->view->set(array(
            'repack'     => $rp,
            'content'    => $rp->startpage_content,
            'feed_items' => (!empty($rp->startpage_feed_url)) ?
                feed::parse($rp->startpage_feed_url) : array(),
        ));

        $this->layout->set_filename('layout-non-auth');
    }

    /**
     * Schedule the build of a new release.
     */
    public function release()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->created_by->id != AuthProfiles::get_profile('id')) {
            // Bail out if the logged in user doesn't own it.
            return Event::run('system.403');
        }

        if ('post' == request::method()) {
            if (isset($_POST['confirm']) ) {
                // Schedule a repack via event and return to detail page.
                $ev_data = $rp->as_array();
                Event::run('BYOB.process_repack', $ev_data);
                return url::redirect($rp->url);
            } else {
                return url::redirect($rp->url);
            }
        }

        $this->view->repack = $rp;
    }

    /**
     * Spew out the raw xpi-config.ini used by repack
     */
    public function xpiconfigini()
    {
        $rp = $this->_getRequestedRepack();
        if ($rp->created_by->id != AuthProfiles::get_profile('id')) {
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
        if ($rp->created_by->id != AuthProfiles::get_profile('id')) {
            return Event::run('system.403');
        }
        $this->auto_render = false;
        header('Content-Type: text/plain');
        echo $rp->buildDistributionIni();
    }

    private function _getRequestedRepack()
    {
        $params = Router::get_params(array(
            'uuid'        => null,
            'screen_name' => null,
            'short_name'  => null
        ));

        if (!empty($params['uuid'])) {

            $rp = $this->repack_model
                ->orwhere(array( 'uuid' => $params['uuid'] ))
                ->find()->get();

        } else if (!empty($params['screen_name']) && !empty($params['short_name'])) {

            $profile = ORM::factory('profile', $params['screen_name']);
            if (null == $profile) {
                Event::run('system.404');
                exit();
            }

            $rp = $this->repack_model
                ->find(array( 
                    'created_by_id' => $profile,
                    'short_name' => $params['short_name']
                ));

        } else {
            $rp = null;
        }

        if (null === $rp) {
            // Bail out if not found.
            Event::run('system.404');
            exit();
        }

        return $rp;
    }

}
