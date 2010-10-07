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

    /**
     * General search method.
     */
    public function index()
    {
        // Grab the pagination parameters from the URL.
        list($per_page, $page_num, $offset) = $this->_getPageParams();

        // Grab the terms from the URL, or punt with no results on no terms.
        $terms = trim($this->input->get('q'));
        if (empty($terms)) {
            $this->view->set(array(
                'results' => array(), 'terms' => array()
            ));
            return;
        }
        $terms = explode(' ', $terms);

        // Check for models for search, or default to all known searchable 
        // models.
        $models = $this->input->get('m');
        $models = (empty($models)) ?
            $this->searchable_models : explode(' ', $models);

        // Verify search privileges.
        if (!authprofiles::is_allowed('search', 'search')) {
            foreach ($models as $model) {
                if (!authprofiles::is_allowed('search', 'search_'.$model)) {
                    return Event::run('system.403');
                }
            }
        }

        // Iterate over known models and try to accumulate results...
        $results = array();
        foreach ($models as $model_name) {
            if (!in_array($model_name, $this->searchable_models)) continue;

            $model = ORM::factory($model_name);

            $fields = $model->list_fields();
            foreach ($fields as $name=>$meta) {
                foreach ($terms as $term) {
                    $model->orlike($name, $term);
                }
            }

            $db_rows = $model->limit($per_page, $offset)->find_all();
            $count = $model->count_last_query();

            // Filter for only viewable items
            $rows = array();
            foreach ($db_rows as $row) {
                if (method_exists($row, 'checkPrivilege') && 
                        !$row->checkPrivilege('view')) continue;
                $rows[] = $row;
            }

            if ($count) {

                // HACK: Force ?m to current iteration's model name so pagination 
                // links are model-specific.
                $_GET['m'] = $model_name;
                $pg = new Pagination(array(
                    'items_per_page' => 20,
                    'total_items'    => $count,
                    'query_string'   => 'page',
                ));

                $results[$model_name] = array(
                    'pagination' => $pg,
                    'model'      => $model, 
                    'rows'       => $rows
                );

            }
        }

        $this->view->set(array(
            'results' => $results,
            'terms'   => $terms,
        ));
    }

    /**
     * Specialized search for approval queue
     */
    public function approvalqueue()
    {
        if (!authprofiles::is_allowed('search', 'approvalqueue')) {
            return Event::run('system.403');
        }

        list($per_page, $page_num, $offset) = $this->_getPageParams();

        $model = ORM::factory('repack');

        $rows = $model
            ->where('state', Repack_Model::$states['pending'])
            ->limit($per_page, $offset)
            ->find_all();
        $count = $model->count_last_query();

        $pg = new Pagination(array(
            'base_url'       => url::current(),
            'query_string'   => 'page',
            'items_per_page' => $per_page,
            'total_items'    => $count,
        ));

        $this->view->set(array(
            'pagination' => $pg,
            'rows'       => $rows,
            'model'      => $model,
        ));
    }


    /**
     * Grab the pagination 
     */
    protected function _getPageParams()
    {
        $per_page = $this->input->get('limit', 20);
        $page_num = $this->input->get('page', 1);
        $offset   = ($page_num - 1) * $per_page;
        return array($per_page, $page_num, $offset);
    }

}
