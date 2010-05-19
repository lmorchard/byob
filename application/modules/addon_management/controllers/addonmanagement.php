<?php
/**
 * Repack addon management controller
 *
 * @package    BYOB
 * @subpackage Controllers
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
class Addonmanagement_Controller extends Local_Controller
{
    protected $auto_render = TRUE;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handle extension upload interactions.
     */
    public function upload_extension()
    {
        $rp = $this->_getRequestedRepack();
        if (!$rp->checkPrivilege('edit')) 
            return Event::run('system.403');
        if (!$rp->checkPrivilege('addon_management_xpi_upload')) 
            return Event::run('system.403');

        $xpi_dir = $rp->getAssetsDirectory() . "/distribution/extensions";
        if (!is_dir($xpi_dir)) mkdir($xpi_dir, 0775, true);

        $errors = array();

        if ('post' == request::method()) {

            if (empty($_FILES['xpi_upload']['tmp_name'])) {
                $errors[] = 'No XPI upload available';
            }
            if ('application/x-xpinstall' !== $_FILES['xpi_upload']['type']) {
                $errors[] = 'XPI upload must be of type application/x-xpinstall';
            }

            $xpi_fn = $_FILES['xpi_upload']['tmp_name'];
            $xpi_name = basename($_FILES['xpi_upload']['name']);

            $extension = Model::factory('addon')->find_by_xpi_file($xpi_fn);
            if (!$extension->loaded) {
                $errors[] = 'Unable to read XPI install.rdf';
            }

            if (empty($errors)) {

                // Copy the uploaded file into repack assets, but normalize its 
                // name to reflect its GUID.
                move_uploaded_file(
                    $_FILES['xpi_upload']['tmp_name'],
                    "{$xpi_dir}/{$extension->guid}.xpi"
                );

                // Mark the "addons" section as changed
                if (!is_array($rp->changed_sections)) 
                    $rp->changed_sections = array();
                if (!in_array('addons', $rp->changed_sections)) {
                    $changed = $rp->changed_sections;
                    array_push($changed, 'addons');
                    $rp->changed_sections = $changed;
                }

            }

        }

        // Build a list of all the uploaded XPIs
        $xpi_files = glob("{$xpi_dir}/*.xpi");
        $extensions = array();
        foreach ($xpi_files as $xpi_fn) {
            $extension = Model::factory('addon')->find_by_xpi_file($xpi_fn);
            if ($extension->loaded) {
                $extensions[] = $extension;
            }
        }

        $this->view->set(array(
            'repack'     => $rp,
            'extensions' => $extensions,
            'errors'     => $errors
        ));

    }

    /**
     * Handle extension upload interactions.
     */
    public function upload_searchplugin()
    {
        $rp = $this->_getRequestedRepack();
        if (!$rp->checkPrivilege('edit')) 
            return Event::run('system.403');

        $sp_dir = $rp->getAssetsDirectory() . "/distribution/searchplugins/common";
        if (!is_dir($sp_dir)) mkdir($sp_dir, 0775, true);

        $errors = array();

        if ('post' == request::method()) {

            if (empty($_FILES['sp_upload']['tmp_name'])) {
                $errors[] = 'Search plugin upload must be of type text/xml';
            }
            if ('text/xml' !== $_FILES['sp_upload']['type']) {
                $errors[] = 'Search plugin upload must be of type text/xml';
            }

            $sp_fn = $_FILES['sp_upload']['tmp_name'];
            $sp_name = basename($_FILES['sp_upload']['name']);
            $sp_xml = file_get_contents($sp_fn);
            $plugin = Model::factory('searchplugin')->loadFromXML($sp_xml);
            if (!$plugin->loaded) {
                $errors[] = 'Search plugin upload could not be parsed';
            }

            if (empty($errors)) {

                move_uploaded_file(
                    $_FILES['sp_upload']['tmp_name'],
                    "{$sp_dir}/{$sp_name}"
                );

                // Mark the "addons" section as changed if the repack gets saved 
                if (!is_array($rp->changed_sections)) 
                    $rp->changed_sections = array();
                if (!in_array('addons', $rp->changed_sections)) {
                    $changed = $rp->changed_sections;
                    array_push($changed, 'addons');
                    $rp->changed_sections = $changed;
                }
            }

        }

        $popular_searchplugins = 
            addon_management::get_popular_searchplugins();

        $sp_files = glob("{$sp_dir}/*.xml");
        $search_plugins = array();
        foreach ($sp_files as $fn) {
            $xml = file_get_contents($fn);
            $plugin = Model::factory('searchplugin')->loadFromXML($xml);
            $search_plugins[] = $plugin;
        }

        $this->view->set(array(
            'repack' => $rp,
            'search_plugins' => $search_plugins,
            'errors' => $errors
        ));

    }

}
