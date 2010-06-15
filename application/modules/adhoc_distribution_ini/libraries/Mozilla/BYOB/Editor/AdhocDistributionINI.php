<?php
/**
 * BYOB editor module registry
 *
 * @package    Mozilla_BYOB_Editor_AdhocDistributionINI
 * @subpackage Libraries
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Mozilla_BYOB_Editor_AdhocDistributionINI extends Mozilla_BYOB_Editor {

    /** {{{ Object properties */
    public $id        = 'adhoc_distribution_ini';
    public $title     = 'INI';
    public $view_name = 'repacks/edit/adhoc_distribution_ini';
    /** }}} */

    /**
     * Determine whether the current user has permission to access this 
     * editor.
     */
    public function isAllowed($repack)
    {
        return $repack->checkPrivilege('edit_distribution_ini');
    }

    /**
     * Validate data from incoming editor request.
     */
    public function validate(&$data, $repack)
    {
		$data = Validation::factory($data)
            ->pre_filter('trim')
            ->add_rules('adhoc_ini', 'length[0,999999]')
            ;

        if (!$data->validate()) return false;

        $adhoc_conf = Mozilla_BYOB_IniConfig::fromString($data['adhoc_ini']);
        if (!$adhoc_conf) {
            $data->add_error('adhoc_ini', 'invalid');
            return false;
        }
        return true;
    }

    /**
     * Event handler to filter generated distribution.ini through INI overlay
     */
    public static function filterDistributionIni()
    {
        $repack = Event::$data['repack'];
        if (!empty($repack->adhoc_ini)) {
            Event::$data['output'] = Mozilla_BYOB_IniConfig::mergeINIs(
                Event::$data['output'], $repack->adhoc_ini
            );
        }
    }


    /**
     * TODO: Get rid of this when PHP 5.3+ can be a requirement
     */
    public static function getInstance() { return parent::getInstance(get_class()); }
    /**
     * TODO: Get rid of this when PHP 5.3+ can be a requirement
     */
    public static function register() { return parent::register(get_class()); }

}
