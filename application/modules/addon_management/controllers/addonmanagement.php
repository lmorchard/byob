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

        if ('delete' == $this->input->post('method')) {

            $delete_xpi_fn = $this->input->post('xpi_fn');
            $xpi_files = glob("{$xpi_dir}/*.xpi");
            foreach ($xpi_files as $xpi_fn) {
                if (basename($xpi_fn) == $delete_xpi_fn) {
                    unlink($xpi_fn);
                    break;
                }
            }

        } else if ('post' == request::method()) {

            if (empty($_FILES['xpi_upload']['tmp_name'])) {
                $errors[] = _('No XPI upload available');
            }

            $xpi_fn = $_FILES['xpi_upload']['tmp_name'];
            $xpi_name = basename($_FILES['xpi_upload']['name']);

            $extension = Model::factory('addon')->find_by_xpi_file($xpi_fn);
            if (!$extension->loaded) {
                $errors[] = _('Unable to read XPI install.rdf');
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

        $default_locale = (!empty($rp->default_locale)) ?
            $rp->default_locale : 'en-US';

        $this->view->locale = $default_locale;

        $sp_dir = $rp->getAssetsDirectory() . "/distribution/searchplugins";
        if (!is_dir($sp_dir)) mkdir($sp_dir, 0775, true);
        if (!is_dir($sp_dir."/common")) mkdir($sp_dir."/common", 0775, true);
        if (!is_dir($sp_dir."/locale")) mkdir($sp_dir."/locale", 0775, true);

        $errors = array();

        if ('delete' == $this->input->post('method')) {

            $delete_fn = $sp_dir.'/'.$this->input->post('searchplugin_fn');
            $sp_files = array_merge(
                glob("{$sp_dir}/common/*.xml"),
                glob("{$sp_dir}/locale/*/*.xml")
            );
            foreach ($sp_files as $sp_fn) {
                if ($sp_fn == $delete_fn) {
                    unlink($sp_fn);
                    break;
                }
            }

        } else if ('post' == request::method()) {

            $locale = $this->input->post('locale', $rp->locales[0]);
            if (!in_array($locale, $rp->locales)) { 
                $locale = $rp->locales[0]; 
            }
            $fn_prefix = ($locale == $default_locale) ?
                'common' : 'locale/'.$locale;
            $this->view->locale = $locale;

            if (empty($_FILES['sp_upload']['tmp_name'])) {
                $errors[] = _('Search plugin upload must be of type text/xml');
            }

            $sp_fn = $_FILES['sp_upload']['tmp_name'];
            $sp_name = basename($_FILES['sp_upload']['name']);
            $sp_xml = file_get_contents($sp_fn);
            $plugin = Model::factory('searchplugin')->loadFromXML($sp_xml);
            if (!$plugin->loaded) {
                $errors[] = _('Search plugin upload could not be parsed');
            }

            if (empty($errors)) {
                if (!is_dir($sp_dir."/".$fn_prefix)) {
                    mkdir($sp_dir."/".$fn_prefix, 0775, true);
                }

                move_uploaded_file(
                    $_FILES['sp_upload']['tmp_name'],
                    "{$sp_dir}/{$fn_prefix}/{$sp_name}"
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

        $sp_files = array_merge(
            glob("{$sp_dir}/common/*.xml"),
            glob("{$sp_dir}/locale/*/*.xml")
        );
        $search_plugins = array();
        foreach ($sp_files as $fn) {
            $xml = file_get_contents($fn);
            $plugin = Model::factory('searchplugin')->loadFromXML($xml);
            $plugin->filename = basename($fn);
            $plugin->locale = basename(dirname($fn));
            if ($plugin->locale == 'common') {
                $plugin->locale = $default_locale;
                $plugin->filename = "common/" . basename($fn);
            } else {
                $plugin->filename = "locale/{$plugin->locale}/" . basename($fn);
            }
            $search_plugins[] = $plugin;
        }

        $this->view->set(array(
            'repack' => $rp,
            'search_plugins' => $search_plugins,
            'errors' => $errors
        ));

    }

}
