<?php
/**
 * Test class for addon model
 * 
 * @package    byob
 * @subpackage tests
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 * @group      byob
 * @group      models
 * @group      models.byob
 * @group      models.byob.addon
 */
class Addon_Test extends PHPUnit_Framework_TestCase 
{
    /**
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        LMO_Utils_EnvConfig::apply('testing');

        Kohana::config_set('addons.dir', dirname(APPPATH) . "/addons");

        Kohana::config_set('addons.api_url',
            'https://services.addons.mozilla.org/en-US/firefox/api/1.3/addon/%s');

        Kohana::config_set('addons.addons', array(
            '11950' => array(
                'guid' => 'sharing@addons.mozilla.org',
                'name' => 'Add-on Collector',
            ),
            '10900' => array(
                'guid' => 'personas@christopher.beard',
                'name' => 'Personas for Firefox',
            ),
            '1843' => array(
                'guid' => 'firebug@software.joehewitt.com',
                'name' => 'Firebug',
            ),
            '3615' => array(
                'guid' => '{2fa4ed95-0317-4c6a-a74c-5f3e3912c1f9}',
                'name' => 'Delicious Bookmarks',
            ),
        ));
    }

    /**
     * Warm up test to ensure configured addons are found.
     */
    public function testFindAll()
    {
        $known = Kohana::config('addons.addons');

        $all_addons = Model::factory('addon')->find_all();

        $this->assertEquals(count($known), count($all_addons));

        foreach ($all_addons as $addon) {
            $this->assertTrue(
                in_array($addon->id, array_keys($known)),
                'Addon ID should appear in known keys'
            );
            $this->assertEquals(
                $addon->guid, $known[$addon->id]['guid'],
                'Addon GUID should match known GUID'
            );
        }
    }

    /**
     * Exercise fetching a collection of addons.
     */
    public function testFindAllByCollection()
    {
        $collection_url = 
            'https://addons.mozilla.org/en-US/firefox/collection/webdeveloper';
        $addons = Model::factory('addon')
            ->find_all_by_collection_url($collection_url);
        $expected_ids = array(
            '966', '5369', '539', '271', '590', '748', '684', '7943', '60', 
            '1843', '3829', '2464',
        );
        $result_ids = array();
        foreach ($addons as $addon) {
            $result_ids[] = $addon->id;
        }
        $this->assertEquals(
            count($expected_ids), count($result_ids)
        );
        foreach ($expected_ids as $id) {
            $this->assertTrue(in_array($id, $result_ids));
        }
    }

    /**
     * Exercise calls out to the AMO api for addon details.
     */
    public function testDetailsFromAMO()
    {
        $known = Kohana::config('addons.addons');
        $ids = array_keys($known);
        $addon_model = new Addon_Model();

        foreach ($ids as $id) {
            $addon = $addon_model->find($id);

            $this->assertTrue(
                strpos((string)$addon->install, 
                    'https://addons.mozilla.org/downloads/file') === 0,
                'Install URL should be https:// from AMO'
            );

        } 
    }

    /**
     * Exercise addon download and unpack.
     */
    public function testAddonFetch()
    {
        $known = Kohana::config('addons.addons');
        $ids = array_keys($known);
        $addon_model = new Addon_Model();

        foreach ($ids as $id) {
            $addon = $addon_model->find($id);

            $dir_name = $addon->updateFiles();

            $this->assertEquals(
                $addon->guid, basename($dir_name),
                'Directory name for addon files should match GUID'
            );
            
            $this->assertTrue(is_dir($dir_name), 'Directory should exist');
            $this->assertTrue(is_file($dir_name.'/install.rdf'), 
                'install.rdf should exist');
        }
    }

}
