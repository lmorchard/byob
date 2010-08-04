<?php
/**
 * ORM_Manager main controller
 *
 * @package    orm_manager
 * @subpackage controllers
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 */
class ORM_Manager_Controller extends Layout_Controller
{
    protected $auto_render  = TRUE;

    protected $url_base     = null;
    protected $view_base    = null;

    protected $known_model_names = null;

    /**
     * Initialize the controller.
     */
    public function __construct()
    {
        parent::__construct();

        if (empty($this->view_base)) {
            $this->view_base = 'orm_manager';
        }

        if (empty($this->url_base)) {
            $this->url_base = 'orm_manager';
        }

        $this->view->set_global(array(
            'url_base'     => url::site($this->url_base),
            'view_base'    => $this->view_base,
        ));

        slot::append('head_end', html::stylesheet(array(
            'modules/orm_manager/public/css/orm_manager.css'
        )));

        slot::append('body_end', html::script(array(
            'modules/orm_manager/public/js/orm_manager.js'
        )));

        Event::add('LMO_Utils.layout.before_auto_render',
            array($this, '_set_view_base'));
    }

    /**
     * Set the view based on the module's view base.
     */
    public function _set_view_base()
    {
        if (!$this->view->get_filename())
            $this->view->set_filename(
                $this->view_base . '/' . Router::$method
            );
    }


    /**
     * Home page list of models
     */
    public function index()
    {
        $this->view->models = $this->get_models();
    }

    /**
     * List records for a model.
     */
    public function list_model()
    {
        $params = Router::get_params();

        $model = $this->_load_model($params['model_name']);
        if (null===$model) return Event::run('system.404');

        return $this->_list_model(
            $model, $model->count_all(), $model->find_all()
        );
    }

    public function _list_model($model, $count, $rows, $allow_batch=TRUE) {

        if ($allow_batch && 'post' == request::method()) {

            // Load all the selected rows.
            $model_rows = ORM::factory($model->object_name)
                ->in($model->primary_key, $this->input->post('select_row'))
                ->find_all();

            if ($this->input->post('batch_delete', null)) {
                foreach ($model_rows as $row) {
                    $row->delete();
                }
                Session::instance()->set_flash('message', 
                    'Deleted '. $model_rows->count() .' rows.');
                return url::redirect(url::current());
            }

            // TODO: Dispatch to model batch commands.

        }

        $pg = new Pagination(array(
            'uri_segment'    => 'page',
            'items_per_page' => 20,
            'total_items'    => $count,
        ));

        $model->limit($pg->items_per_page, $pg->sql_offset);

        $this->view->set_global(array(
            'pagination' => $pg,
            'rows'       => $rows,
            'model'      => $model,
        ));

    }

    /**
     * View / edit a row in a model.
     */
    public function edit()
    {
        $params = Router::get_params(array(
            'create' => false    
        ));

        $model = $this->_load_model($params['model_name']);
        if (null===$model) {
            return Event::run('system.404');
        }

        if (false === $params['create']) {
            $model->find($params['primary_key']);
            if (!$model->loaded) {
                // TODO: better error msg?
                return Event::run('system.404');
            }
        }

        /* TODO: get relations working sensibly
         
        $relations = array(
        );
        $rel_props = array(
            'has_one'=>true, 'belongs_to'=>true, 
            'has_many'=>false, 'has_and_belongs_to_many'=>false
        );

        foreach ($rel_props as $rel_prop_name=>$singular) {
            foreach ($model->{$rel_prop_name} as $alias=>$name) {
                $attr_name = (!is_numeric($alias)) ? $alias : $name;
                $relations[$attr_name] = ($singular) ?
                    array($model->{$attr_name}) : $model->{$attr_name};
            }
        }

        $this->view->relations = $relations;
        */

        $return_page = $this->input->get(
            'return_page', $this->input->post('return_page')
        );

        $form   = $this->input->post();
        $errors = array();

        if ('post' == request::method()) {

            // Perform validation based on model.
            $is_valid = (method_exists($model, 'validate_edit_save')) ?
                $model->validate_edit_save($form, TRUE) :
                $model->validate(
                    ManagedORM::generic_validate_edit_save($model, $form),
                    TRUE
                );

            // Report errors, or success and redirect to edit form.
            if (!$is_valid) {
                Session::instance()->set_flash('message', 'Errors in form.');
                $errors = $form->errors();
            } else {
                Session::instance()->set_flash('message', 'Changes saved.');
                return url::redirect(url::site( $this->url_base . '/model/' . 
                    $model->object_name . '/edit/' . $model->id));
            }

        }

        $this->view->set_global(array(
            'row'    => $model,
            'model'  => $model,
            'form'   => $form,
            'errors' => $errors,

            'return_page' => $return_page
        ));
    }


    /**
     * Return instances of models known to this controller, indexed by name.
     *
     * @return array Models indexed by name.
     */
    public function get_models()
    {
        if (empty($this->known_model_names)) {
            $this->known_model_names = $this->_find_models();
        }
        $models = array();
        foreach ($this->known_model_names as $name) {
            $model = $this->_load_model($name);
            if ($model) $models[$name] = $model;
        }
        return $models;
    }

    /**
     * Load a named model, return an instance if it's known and is an ORM 
     * subclass.  NULL returned otherwise.
     *
     * @param  string Model name
     * @return ORM
     */
    public function _load_model($model_name) {
        if (empty($this->known_model_names)) {
            $this->known_model_names = $this->_find_models();
        }
        if (!in_array($model_name, $this->known_model_names)) {
            return null;
        }
        $model = ORM::factory($model_name);
        if (!is_subclass_of($model, 'ORM')){
            return null;
        }
        return $model;
    }

    /**
     * Return the names of all known models, drawn either from configuration or 
     * derived from models found by Kohana.
     *
     * @return array
     */
    public function _find_models()
    {
        $models = Kohana::config('orm_manager.models');

        if (empty($models)) {
            $models = array();
		    $files = Kohana::list_files('models');
            foreach ($files as $file) {
                $m = array();
                preg_match('/.*\/(.*)'.EXT.'/', $file, $m);
                $models[] = $m[1];
            }
        }

        return $models;
    }

}
