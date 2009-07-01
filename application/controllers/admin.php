<?php
/**
 *
 */
class Admin_Controller extends ORM_Manager_Controller
{
    protected $url_base = 'admin';
    protected $known_model_names = array(
        'repack', 
        'product', 
        'profile', 
        'login',
        // 'logevent', 
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
