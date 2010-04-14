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
     * Perform the merge between generated distribution.ini and adhoc INI 
     * overlay.
     *
     * @param  Repack_Model $repack
     * @return string
     */
    public function performOverlay($repack, $repack_ini)
    {
        // Just return unmodified INI it if the adhoc overlay is empty.
        if (empty($repack->adhoc_ini)) return $repack_ini;

        // Start merging the INIs, retaining comments for a header.
        $merged_conf = new Zend_Config(array(), true);
        $comments = array();
        foreach (array($repack_ini, $repack->adhoc_ini) as $ini) {
            
            // Split up the lines of this INI and retain comments.
            $lines = preg_split('/\n\r?/', $ini);
            foreach ($lines as $line) {
                if (substr($line, 0, 1) == ';') { $comments[] = $line; }
            }

            // Parse the INI and merge into the accumlator conf
            $conf = Mozilla_BYOB_IniConfig::fromString($ini);
            $merged_conf->merge($conf);

        }

        // Sections with "Preferences" or "LocalizablePreferences" need to be 
        // quoted for JS in Firefox
        $sections = array_keys($merged_conf->toArray());
        $quoted = array( 'Preferences' );
        foreach ($sections as $section) {
            if (strpos($section, 'LocalizablePreferences') !== false) {
                $quoted[] = $section;
            }
        }

        // Render the merged INI and restore retained comments at the top
        $writer = new Mozilla_BYOB_IniWriter(array(
            'config' => $merged_conf,
            'quotedValueSectionNames' => $quoted
        ));
        $merged_ini = implode("\n", $comments) . "\n\n" . $writer->render();
        return $merged_ini;
    }

    /**
     * Event handler to filter generated distribution.ini through INI overlay
     */
    public function filterDistributionIni()
    {
        Event::$data['output'] = self::getInstance()->performOverlay(
            Event::$data['repack'], Event::$data['output']
        );
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
