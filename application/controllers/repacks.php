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
            'index', 'view', 'download', 'firstrun'
        );

        if (!authprofiles::is_logged_in()) {
            if (!in_array(Router::$method, $unauth_methods)) {
                Session::instance()->set_flash(
                    'message', 'Login required to manipulate repacks.'
                );
                return authprofiles::redirect_login();
            }
        }
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
            if (!$repack->checkPrivilege('view')) continue;
            
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
        $repack = $this->_getRequestedRepack(true);
        if (!$repack->checkPrivilege('view')) 
            return Event::run('system.403');

        $this->view->repack  = $repack;

        $release = $repack->findRelease();
        if ($release && $release->id != $repack->id) {
            if ($repack->checkPrivilege('view_changes')) {
                $this->view->changes = $repack->compare($release);
            }
        }

        if ($repack->checkPrivilege('view_history')) {
            $this->view->logevents = ORM::factory('logevent')
                ->findByUUID($repack->uuid);
        }
    }

    /**
     * Create a new repack.
     */
    public function create()
    {

        if (!authprofiles::is_allowed('repacks', 'create')) {
            return Event::run('system.403');
        }
            
        if ('post' == request::method()) {
            // On creation, instantiate a new repack.
            $rp = ORM::factory('repack');
            $rp->profile_id = authprofiles::get_profile('id');
            $rp->save();
            return url::redirect($rp->url . ';edit');
        }

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

        $section = $this->input->get('section', 'general');

        // On editing, look for an editable version of this repack.
        $this->view->create = false;

        $rp = $this->_getRequestedRepack();

        $editable_rp = $rp->findEditable();
        if (!$editable_rp) {
            // No editable alternative, so bail.
            return Event::run('system.403');
        }

        if (!$editable_rp->checkPrivilege('edit'))
            return Event::run('system.403');
       
        if ($editable_rp->id != $rp->id) {
            // Redirect to editable alternative.
            if (!$editable_rp->saved) {
                $editable_rp->save();
            }
            if ($editable_rp->isLockedForChanges()) {
                return url::redirect($editable_rp->url);
            } else {
                return url::redirect($editable_rp->url.';edit?section='.$section);
            }
        }

        if ($this->input->post('cancel', false)) {
            return url::redirect($rp->url);
        }

        // Grab the form data, ensure UUID not changed.
        $form_data = $this->input->post();
        $form_data['uuid'] = $rp->uuid;

        // Try to validate the form data and update the repack.
        $is_valid = $rp->validateRepack(
            $form_data, ('post' == request::method()), $section
        );

        $addons = Model::factory('addon')->find_all();
        $addons_by_id = array();
        foreach ($addons as $addon) {
            $addons_by_id[$addon->id] = $addon;
        }

        $this->view->set_global(array(
            'repack'       => $rp,
            'section'      => $section,
            'addons'       => $addons,
            'addons_by_id' => $addons_by_id,
            'form_data'    => $form_data,
            'show_review'  => $this->input->get('show_review', 'false')
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

            // Mark this section as changed if the page thinks so.
            if (!is_array($rp->changed_sections)) 
                $rp->changed_sections = array();
            if ('true' == $this->input->post('changed', 'false')) {
                if (!in_array($section, $rp->changed_sections)) {
                    $changed = $rp->changed_sections;
                    array_push($changed, $section);
                    $rp->changed_sections = $changed;
                }
            }

            // This was a valid POST, so save the modified repack.
            $rp->save();

            if ($this->input->post('done', false)) {
                return url::redirect($rp->url);
            } else if ($this->input->post('review', false)) {
                return url::redirect($rp->url.';release');
            } else if ('true' === $this->input->post('show_review', false)) {
                return url::redirect($rp->url.';edit?show_review=true&section='.
                    $this->input->post('next_section', $section));
            } else {
                return url::redirect($rp->url.';edit?section='.
                    $this->input->post('next_section', $section));
            }

        }
    }


    /**
     * Download a build of a repack.
     */
    public function download()
    {
        // Find repack and filename parameter.
        $repack = $this->_getRequestedRepack();
        $params = Router::get_params(array(
            'filename' => null
        ), 'filename');

        // Does the file exist for this repack?
        if (!in_array($params['filename'], $repack->files)) {
            return Event::run('system.404');
        }

        // Is the user allowed to download it?
        if (!$repack->checkPrivilege('download')) 
            return Event::run('system.403');

        // Build a full path to the downloadable file.
        $base_path = $repack->isRelease() ?
            Kohana::config('repacks.downloads_public') :
            Kohana::config('repacks.downloads_private');
        $repack_name = 
            "{$repack->profile->screen_name}_{$repack->short_name}";
        $filename = 
            "{$base_path}/{$repack_name}/{$params['filename']}";

        // Try guessing a content-type for the file.
        $ext_map = array(
            '.tar.bz2' => 'application/x-bzip2',
            '.dmg'     => 'application/x-apple-diskimage',
            '.exe'     => 'application/octet-stream',
        );
        $content_type = 'application/octet-stream';
        foreach ($ext_map as $ext=>$type) {
            if (strpos($filename, $ext) !== FALSE) {
                $content_type = $type; break;
            }
        }

        // Finally, dump the file out as a response.
        $this->auto_render = FALSE;
        header('Content-Type: ' . $content_type);
        header('Content-Length: ' . filesize($filename));
        header('Content-Description: File Transfer');
        //header('Content-Disposition: attachment; filename='.basename($filename));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        ob_clean();
        flush();
        readfile($filename);
    }


    /**
     * Request a new browser release
     */
    public function release()
    {
        $repack = $this->_getRequestedRepack();
        if (!$repack->checkPrivilege('release')) 
            return Event::run('system.403');

        if ('post' == request::method()) {
            if (isset($_POST['confirm'])) {
                $repack = $repack->requestRelease($this->input->post('comments'));
            }
            return url::redirect($repack->url);
        }
        $this->view->repack = $repack;
    }

    /**
     * Cancel browser relase request.
     */
    public function cancel()
    {
        $rp = $this->_getRequestedRepack();
        if (!$rp->checkPrivilege('cancel')) 
            return Event::run('system.403');

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
        $repack = $this->_getRequestedRepack();
        if (!$repack->checkPrivilege('approve')) 
            return Event::run('system.403');

        if ('post' == request::method()) {
            if (isset($_POST['confirm'])) {
                $repack = $repack->approveRelease($this->input->post('comments'));
            }
            return url::redirect($repack->url);
        }
        $this->view->repack = $repack;
    }

    /**
     * Reject release of a new release.
     */
    public function reject()
    {
        $rp = $this->_getRequestedRepack();
        if (!$rp->checkPrivilege('reject')) 
            return Event::run('system.403');

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
        if (!$rp->checkPrivilege('revert')) 
            return Event::run('system.403');

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
        if (!$rp->checkPrivilege('delete')) 
            return Event::run('system.403');

        $this->view->repack = $rp;

        if ('post' == request::method()) {
            if (isset($_POST['confirm']) ) {
                $rp->delete();
                return url::redirect(
                    "profiles/".authprofiles::get_profile('screen_name')
                );
            } else {
                return url::redirect($rp->url);
            }
        }
    }


    /** HACK: temporary methods */

    public function begin()
    {
        $rp = $this->_getRequestedRepack();
        if (!authprofiles::is_allowed('repacks', 'begin'))
            return Event::run('system.403');
        $rp = $rp->beginRelease();
        return url::redirect($rp->url);
    }

    public function fail()
    {
        $rp = $this->_getRequestedRepack();
        if (!authprofiles::is_allowed('repacks', 'fail'))
            return Event::run('system.403');
        $rp = $rp->failRelease('Solar flares induced failure');
        return url::redirect($rp->url);
    }

    public function finish()
    {
        $rp = $this->_getRequestedRepack();
        if (!authprofiles::is_allowed('repacks', 'finish'))
            return Event::run('system.403');
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

        $this->layout = null;
    }


    /**
     * Spew out the raw repack.cfg used by repack
     */
    public function repackcfg()
    {
        $rp = $this->_getRequestedRepack();
        if (!$rp->checkPrivilege('repackcfg')) 
            return Event::run('system.403');

        $this->auto_render = false;
        header('Content-Type: text/plain');
        echo $rp->buildRepackCfg();
    }

    /**
     * Output the last repack.log from build
     */
    public function repacklog()
    {
        $rp = $this->_getRequestedRepack();
        if (!$rp->checkPrivilege('repacklog'))
            return Event::run('system.403');

        $workspace = Kohana::config('repacks.workspace');
        $repack_name = "{$rp->profile->screen_name}_{$rp->short_name}";

        $this->auto_render = false;
        header('Content-Type: text/plain');
        readfile("{$workspace}/partners/{$repack_name}/repack.log");
    }

    /**
     * Spew out the raw distribution.ini used by repack
     */
    public function distributionini()
    {
        $rp = $this->_getRequestedRepack();
        if (!$rp->checkPrivilege('distributionini')) 
            return Event::run('system.403');

        $this->auto_render = false;
        header('Content-Type: text/plain');
        echo $rp->buildDistributionIni();
    }

}
