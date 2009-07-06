<?php
/**
 * Search controller
 *
 * @package    BYOB
 * @subpackage Controllers
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Search_Controller extends Local_Controller
{
    protected $auto_render = TRUE;

    protected $searchable_models = array(
        'profile', 'repack'
    );

    public function index()
    {
        if (!authprofiles::is_allowed('search', 'search')) {
            return Event::run('system.403');
        }

        $terms = explode(' ', $this->input->get('q'));

        $models = $this->input->get('m');
        $models = (empty($models)) ?
            $this->searchable_models : explode(' ', $models);

        $results = array();
        foreach ($models as $model_name) {
            if (!in_array($model_name, $this->searchable_models)) continue;

            $model = ORM::factory($model_name);
            $fields = $model->list_fields();
            foreach ($terms as $term) {
                foreach ($fields as $name=>$meta) {
                    $model->orlike($name, $term);
                }
            }

            $rows = $model->find_all();
            if ($rows->count()) $results[$model_name] = array(
                'model' => $model, 'rows' => $rows
            );
        }

        $this->view->set(array(
            'results' => $results,
            'terms'   => $terms
        ));
    }

}
