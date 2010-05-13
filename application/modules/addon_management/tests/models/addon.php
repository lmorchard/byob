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
 * @group      models.byob.addon_management
 * @group      models.byob.addon_management.addon
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

        Kohana::config_set('addon_management.dir', dirname(APPPATH) . "/addons");

        Kohana::config_set('addon_management.api_url',
            'https://services.addons.mozilla.org/en-US/firefox/api/1.3/addon/%s');

        Kohana::config_set('addon_management.addons', array(
            '11950' => array(
                'guid' => 'sharing@addons.mozilla.org',
                'name' => 'Add-on Collector',
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
        $known = Kohana::config('addon_management.addons');

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
     * Exercise finding a collection using the bandwagon API
     */
    public function testFindAllByCollectionAPI()
    {
        $collection_name = 
            Kohana::config('addon_management.collection_popular_name');
        $addons = Model::factory('addon')
            ->find_all_by_collection_name($collection_name);

        $this->assertTrue(!empty($addons), "Addons result should be non-empty");

        $result_ids = array();
        foreach ($addons as $addon) {
            $result_ids[] = $addon->id;
        }

        // TODO: Change this if the testing collection changes!
        $expected_ids = array ( '1865', '748', '60', '1843', '3615', ) ;
        
        $this->assertEquals(
            count($expected_ids), count($result_ids)
        );
        foreach ($expected_ids as $id) {
            $this->assertTrue(in_array($id, $result_ids),
                "Addon {$id} should be in collection.");

            $addon = Model::factory('addon')->find($id, true);
            $this->assertTrue($addon->loaded, "Addon {$id} should be loaded");

            $dir_name = $addon->updateFiles();
        }

    }

    /**
     * Exercise fetching a collection of addons.
     */
    public function testFindAllByCollectionRSS()
    {
        $collection_url = 
            'https://addons.mozilla.org/en-US/firefox/collection/webdeveloper';
        $addons = Model::factory('addon')
            ->find_all_by_collection_url($collection_url);
        // NOTE: This is bound to change often.
        $expected_ids = 
            array (
              0 => '13661',
              1 => '9924',
              2 => '3863',
              3 => '2108',
              4 => '271',
              5 => '590',
              6 => '748',
              7 => '684',
              8 => '60',
              9 => '1843',
              10 => '2464',
            )
            ;
        $result_ids = array();
        foreach ($addons as $addon) {
            $result_ids[] = $addon->id;
        }
        $this->assertEquals(
            count($expected_ids), count($result_ids)
        );
        foreach ($expected_ids as $id) {
            $this->assertTrue(in_array($id, $result_ids),
                "Addon {$id} should be in collection.");

            $addon_model = new Addon_Model();
            $addon_model->find($id, true);
            $this->assertTrue($addon_model->loaded,
                "Addon {$id} should be loaded");
        }
    }

    /**
     * Exercise calls out to the AMO api for addon details.
     */
    public function testDetailsFromAMO()
    {
        $known = Kohana::config('addon_management.addons');
        $ids = array_keys($known);
        $addon_model = new Addon_Model();

        foreach ($ids as $id) {
            $addon = $addon_model->find($id);

            $this->assertTrue(
                strpos(trim((string)$addon->install), 
                    'https://addons.mozilla.org/downloads/file') === 0,
                'Install URL should be https:// from AMO (was '.$addon->install.')'
            );

        } 
    }

    /**
     * Exercise addon download and unpack.
     */
    public function testAddonFetch()
    {
        $known = Kohana::config('addon_management.addons');
        $ids = array_keys($known);
        $addon_model = new Addon_Model();

        foreach ($ids as $id) {
            $addon = $addon_model->find($id);

            $dir_name = $addon->updateFiles();

            $this->assertTrue(is_file($dir_name.".xml"), 
                "{$addon->guid}.xml should exist");

            $this->assertEquals(
                $addon->guid, basename($dir_name),
                'Directory name for addon files should match GUID'
            );
            
            $dir_name = $addon->updateFiles(true);

            $this->assertTrue(is_dir($dir_name), 'Directory should exist');
            $this->assertTrue(is_file($dir_name.'/install.rdf'), 
                'install.rdf should exist');
        }
    }

    /**
     * Exercise addon objects with properties loaded from install.rdf extracted 
     * from XPIs
     */
    public function testInstallDetails()
    {
        $known = Kohana::config('addon_management.addons');
        $ids = array_keys($known);
        $addon_model = new Addon_Model();

        $xpi_fns = array();
        foreach ($ids as $id) {
            $addon = $addon_model->find($id);
            $xpi_fn = $addon->updateFiles();
            $this->assertTrue(!empty($xpi_fn));
            $xpi_fns[$xpi_fn] = array(
                'guid' => $addon->guid,
                'name' => $addon->name
            );
        }

        foreach ($xpi_fns as $xpi_fn => $expected_details) {
            $addon = $addon_model->find_by_xpi_file($xpi_fn);
            foreach ($expected_details as $expected_name => $expected_value) {
                $this->assertEquals(
                    $expected_value, $addon->{$expected_name},
                    "Details fetched from AMO should match install.rdf"
                );
            }
        }

    }

}
