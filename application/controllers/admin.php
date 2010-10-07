<?php
/**
 * General admin controller
 *
 * @package    BYOB
 * @subpackage Controllers
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Admin_Controller extends ORM_Manager_Controller
{
    protected $url_base = 'admin';
    protected $known_model_names = array(
        'product', 
        // 'profile', 
        // 'login',
    );

    /**
     * Initialize the controller.
     */
    public function __construct()
    {
        parent::__construct();
        
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

        // Check to see if the user can access the requested management method.
        $params = Router::get_params();
        $method = empty($params['model_name']) ?
            Router::$method : $params['model_name'] . '_' . Router::$method;
        if (!authprofiles::is_allowed('admin', Router::$method)) {
            return Event::run('system.403');
        }
    }

    /**
     * Home page list of models
     */
    public function index()
    {
        parent::index();
        $this->view_base = 'admin';
    }

    /**
     * Schedule rebuilds of all repacks with latest product release
     */
    public function rebuild()
    {
        $this->view_base = 'admin';

        $this->view->repack_count = ORM::factory('repack')
            ->from('repacks')
            ->where('state', Repack_Model::$states['released'])
            ->count_records();

        $this->view->results = array(
            'rebuilding' => array(),
            'pending'    => array(),
            'locked'     => array()
        );

        if ('post' == request::method()) {

            // Disable notifications for now, to prevent a flood of email
            Kohana::config_set('repacks.enable_notifications', false);

            // Iterate over all release repacks.
            $repacks = ORM::factory('repack')
                ->where('state', Repack_Model::$states['released'])
                ->find_all();
            foreach ($repacks as $repack) {

                // If there are pending edits, skip the rebuild because it will 
                // clobber those edits.
                $pending_rp = ORM::factory('repack')
                    ->where(array(
                        'uuid'      => $repack->uuid,
                        'state <>' => Repack_Model::$states['released'],
                    ))->find();
                if ($pending_rp->loaded) {
                    Kohana::log('info', 
                        'Skipping rebuild for ' .
                        $repack->profile->screen_name . ' - ' . $repack->short_name .
                        ' because pending edits exist.'
                    );
                    $this->view->results['pending'][] = $repack;
                    continue;
                }

                // If an editable repack couldn't be acquired, there's probably 
                // a build already in progress or some other condition 
                // blocking rebuild
                $editable_rp = $repack->findEditable();
                if (!$editable_rp) {
                    Kohana::log('info', 
                        'Skipping rebuild for ' .
                        $repack->profile->screen_name . ' - ' . $repack->short_name .
                        ' because it is locked for changes.'
                    );
                    $this->view->results['locked'][] = $repack;
                    continue;
                }

                // Finally, flag this as a rebuild and begin the release.
                // The rebuild flag will trigger auto-approval once build has 
                // finished.
                $editable_rp->is_rebuild = true;
                $editable_rp->save();
                $editable_rp->requestRelease('Global rebuild initiated');

                $this->view->results['rebuilding'][] = $repack;

            }

        }

    }

    /**
     * Perform mass approvals, presumably after a manual signing step completed 
     * on pending repacks.
     */
    public function approve()
    {
        $this->view_base = 'admin';

        if ('post' == request::method()) {

            // Attempt to dig up repacks from the pasted text.
            $repacks = array();
            $not_found = array();
            $rejects = array();

            $items = preg_split('/[\s,]+/', 
                $this->input->post('repack_txt', ''));
            foreach ($items as $item) {
                if (empty($item)) continue;

                $repack = NULL;
                $u_pos = strrpos($item, '_');

                if (FALSE !== $u_pos) {
                    // This item has two parts, screen name and short name 
                    list($screen_name, $short_name) = array(
                        substr($item, 0, $u_pos),
                        substr($item, $u_pos+1),
                    );
                    $profile = ORM::factory('profile')->where(array(
                        'screen_name' => $screen_name
                    ))->find();
                    if ($profile->loaded) {
                        $repack = ORM::factory('repack')->where(array(
                            'short_name' => $short_name,
                            'profile_id' => $profile->id,
                        ))->find();
                    }
                } else {
                    // This item has one part, so assume it's just a short name
                    $repack = ORM::factory('repack')->where(array(
                        'short_name' => $item,
                    ))->find();
                }
                
                if ($repack && $repack->loaded) {
                    if ($repack->canChangeState('released')) {
                        // Only repacks eligible for release can be approved.
                        $repacks[] = $repack;
                    } else {
                        // Any other state is rejected.
                        $rejects[] = $repack;
                    }
                } else {
                    // Make a note of any items that didn't result in a repack found.
                    $not_found[] = $item;
                }

            }

            // Queue up release approvals for each repack.
            foreach ($repacks as $repack) {
                $repack->approveRelease($this->input->post('comment', ''));
            }

            $this->view->set(array(
                'repacks' => $repacks,
                'not_found' => $not_found,
                'rejects' => $rejects,
            ));

        }

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
     * Browser approval queue viewe
     */
    public function approvalqueue()
    {
        if (!authprofiles::is_allowed('repacks', 'view_approval_queue')) {
            return Event::run('system.403');
        }

        $model = ORM::factory('repack');
        $rows  = $model
            ->where('state', Repack_Model::$states['pending'])
            ->orderby('modified', 'ASC')
            ->find_all();
        $count = $rows->count();

        $this->view->set_global(array(

            'allow_batch' => FALSE,

            'list_columns' =>    
                arr::extract(
                    $model->table_columns, 
                    'short_name', 'profile_id', 'title', 'state', 
                    'created', 'modified'
                ),

            'actions_view' => 
                View::factory('orm_manager/approvalqueue/actions'),
                    
            'column_views' => array(
                'profile_id' => 
                    View::factory('orm_manager/approvalqueue/column_profile'),
                'short_name' => 
                    View::factory('orm_manager/approvalqueue/column_short_name'),
                'state' => 
                    View::factory('orm_manager/approvalqueue/column_state'),
            ),

        ));

        return $this->_list_model($model, $count, $rows, FALSE);
    }

}
