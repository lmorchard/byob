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
    protected $known_models = null;

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

        if (empty($this->known_models)) {
            $this->known_models = $this->_find_models();
        }

        $this->view->set_global(array(
            'url_base'     => url::base() . $this->url_base,
            'view_base'    => $this->view_base,
            'known_models' => $this->known_models
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
        $this->view->set_filename(
            $this->view_base . '/' . Router::$method
        );
    }


    /**
     * Home page list of models
     */
    public function index()
    {
        $this->view->models = $this->known_models;
    }

    /**
     * List records for a model.
     */
    public function list_model()
    {
        $params = Router::get_params();

        $model = $this->_load_model($params['model_name']);
        if (null===$model) {
            return Event::run('system.404');
        }

        if ('post' == request::method()) {

            // Load all the selected rows.
            $model_rows = $model
                ->in($model->primary_key, $this->input->post('select_row'))
                ->find_all();


            foreach ($model_rows as $row) {
                var_dump($row->as_array());
            }
            var_dump($this->input->post()); die;
        }

        $pg = new Pagination(array(
            'uri_segment'    => 'page',
            'items_per_page' => 20,
            'total_items'    => $model->count_all(),
        ));

        $model->limit($pg->items_per_page, $pg->sql_offset);

        $model_rows = $model->find_all();

        $this->view->set_global(array(
            'pagination' => $pg,
            'rows'       => $model_rows,
            'model'      => $model,
        ));
    }

    /**
     * View / edit a row in a model.
     */
    public function edit()
    {
        $params = Router::get_params();

        $model = $this->_load_model($params['model_name']);
        if (null===$model) {
            return Event::run('system.404');
        }

        $model->find($params['primary_key']);
        if (!$model->loaded) {
            // TODO: better error msg?
            return Event::run('system.404');
        }

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
                url::redirect(url::current()."?return_page={$return_page}");
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
     * Load a named model, return an instance if it's known and is an ORM 
     * subclass.  NULL returned otherwise.
     *
     * @param  string Model name
     * @return ORM
     */
    public function _load_model($model_name) {
        if (!in_array($model_name, $this->known_models)) {
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
