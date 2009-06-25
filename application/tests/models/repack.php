<?php
/**
 * Test class for repack model
 * 
 * @package    byob
 * @subpackage tests
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 * @group      byob
 * @group      models
 * @group      models.byob
 * @group      models.byob.repack
 */
class Repack_Test extends PHPUnit_Framework_TestCase 
{
    /**
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        LMO_Utils_EnvConfig::apply('testing');

        ORM::factory('repack')->delete_all();
        ORM::factory('profile')->delete_all();
        ORM::factory('login')->delete_all();

        $this->profile_1 = ORM::factory('profile')->set(array(
            'screen_name' => 'tester1',
            'full_name'   => 'Tess T. Err',
            'org_name'    => 'Test Organization'
        ))->save();

        $this->login_1 = ORM::factory('login')->set(array(
            'login_name' => 'tester1',
            'email'      => 'tester1@example.com',
        ))->save();

        $this->profile_1->add($this->login_1);
        $this->profile_1->save();

        $this->test_data_1 = array(
            'short_name'  => 'test-repack',
            'title'       => 'Test repack',
            'description' => 'This is my testing repack',

            'created' => gmdate('c'),

            'startpage_feed_url' => 'http://decafbad.com/blog/feed',

            'bookmarks_menu' => array(
                array(
                    'type'        => 'normal',
                    'title'       => 'foobar',
                    'description' => 'this is a foobar bookmark',
                    'location'    => 'http://example.com/foobar'
                ),
                array(
                    'type'        => 'normal',
                    'title'       => 'bazquux',
                    'description' => 'this is a bazquux bookmark',
                    'location'    => 'http://example.com/bazquux'
                ),
                array(
                    'type'        => 'live',
                    'title'       => 'xyzzy',
                    'location'    => 'http://example.com/xyzzy',
                    'feed'        => 'http://example.com/xyzzy/feed'
                ),
                array(
                    'type'        => 'live',
                    'title'       => 'hello',
                    'location'    => 'http://example.com/hello',
                    'feed'        => 'http://example.com/hello/feed'
                )
            ),

            'bookmarks_toolbar' => array(
                array(
                    'type'        => 'normal',
                    'title'       => 'foobar toolbar',
                    'description' => 'this is a foobar toolbar bookmark',
                    'location'    => 'http://example.com/foobar/toolbar'
                ),
                array(
                    'type'        => 'normal',
                    'title'       => 'bazquux toolbar',
                    'description' => 'this is a bazquux toolbar bookmark',
                    'location'    => 'http://example.com/bazquux/toolbar'
                ),
                array(
                    'type'        => 'live',
                    'title'       => 'xyzzy toolbar',
                    'location'    => 'http://example.com/xyzzy/toolbar',
                    'feed'        => 'http://example.com/xyzzy/toolbar/feed'
                ),
                array(
                    'type'        => 'live',
                    'title'       => 'hello toolbar',
                    'location'    => 'http://example.com/hello/toolbar',
                    'feed'        => 'http://example.com/hello/toolbar/feed'
                )
            ),
            
        );

    }

    public function testConstructorAssignsUuidIfNotGiven()
    {
        $r1 = ORM::factory('repack')->save();
        $r2 = ORM::factory('repack')->save();

        $this->assertNotNull($r1->uuid);
        $this->assertNotNull($r2->uuid);
        $this->assertTrue($r1->uuid != $r2->uuid);
    }

    public function testConstructorAcceptsArrayOfData()
    {
        $data = array(
            'name'        => 'Test repack',
            'description' => 'This is my testing repack'
        ); 
        $r = ORM::factory('repack')->set($data);

        foreach ($data as $name=>$value) {
            $this->assertEquals($value, $r->{$name});
        }
    }

    public function testRepackCanProduceConfigIniRepresentation()
    {
        $r1 = ORM::factory('repack')
            ->set($this->test_data_1)
            ->save();
        $r1->created_by = $this->profile_1;
        $r1->save();

        $ini_txt = $r1->buildConfigIni();
        $ini_fn = tempnam("tmp","test-");
        file_put_contents($ini_fn, $ini_txt);
        $conf = parse_ini_file($ini_fn, true);
        unlink($ini_fn);

        // TODO: Need to inspect the data and make some assertions.  This only 
        // asserts that it was parseable.
        $this->assertNotNull($conf);
    }

    public function testRepackCanGenerateBrowserRepack()
    {
        $r1 = ORM::factory('repack')->set($this->test_data_1);
        $r1->created_by = $this->profile_1;
        $r1->save();
        
        $r1->processRepack(FALSE);

        $storage = Kohana::config('repacks.storage');
        $repack_dir = "$storage/{$r1->uuid}/{$r1->version}";

        $this->assertTrue(is_dir($storage));
        $this->assertTrue(is_dir($repack_dir));

        $ini_fn = "$repack_dir/xpi-config.ini";
        $this->assertFileExists($ini_fn);
        $conf = parse_ini_file($ini_fn, true);
        $this->assertNotNull($conf);

        $ini_fn = "$repack_dir/distribution.ini";
        $this->assertFileExists($ini_fn);
        $conf = parse_ini_file($ini_fn, true);
        $this->assertNotNull($conf);
    }

    public function testFormDataCanBeValidatedAndUsedToSetProperties()
    {
        $r1 = ORM::factory('repack')->set($this->test_data_1);
        $r1->created_by = $this->profile_1;
        $r1->save();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $bad_data_1 = array();
        $is_valid_1 = $r1->validate_repack($bad_data_1);
        $this->assertTrue(!$is_valid_1);
        
        $bad_data_2 = array(
            'short_name' => 'x',
            'title' => ''
        );
        $is_valid_2 = $r1->validate_repack($bad_data_2);
        $this->assertTrue(!$is_valid_2);

        $good_data_1 = $good_data_2 = array(
            'short_name' => 'longenough',
            'title' => 'Good enough for a title',
            'description' => 'Not too long for a description'
        );

        $is_valid_3 = $r1->validate_repack($good_data_1, false);
        $this->assertTrue($is_valid_3);
        $this->assertTrue($r1->short_name != $good_data_1['short_name']);

        $is_valid_4 = $r1->validate_repack($good_data_2);
        $this->assertTrue($is_valid_4);
        $this->assertEquals($r1->short_name, $good_data_2['short_name']);

        $this->test_data_1['created'] = gmdate('c');

        // Start creating form data by copying some test data fields straight 
        // over.
        $form_data = array(
            'short_name' => 'another-name'
        );
        $copy_fields = array(
            'title','description','startpage_feed_url'
        );
        foreach($copy_fields as $field) {
            $form_data[$field] = $this->test_data_1[$field];
        }

        // Convert test data bookmarks into form data.
        foreach(array('menu','toolbar') as $kind) {
            $bookmarks = $this->test_data_1["bookmarks_{$kind}"];
            foreach(array('type','title','description','location','feed') as $field) {
                $data = array();
                foreach ($bookmarks as $bookmark) {
                    if (isset($bookmark[$field])) {
                        $data[] = $bookmark[$field];
                    }
                }
                $form_data["bookmarks_{$kind}_{$field}"] = $data;
            }
        }

        $r1 = ORM::factory('repack')->set($this->test_data_1)->save();

        $r2 = ORM::factory('repack')->set()->save();
        $is_valid = $r2->validate_repack($form_data);
        $this->assertTrue($is_valid, 'Form should be valid. Errors: ' . 
            var_export($form_data->errors(), true));
        $r2->save();

        $r1_meta = arr::extract($r1->as_array(), 
            'title', 'description', 'startpage_feed_url');
        $r2_meta = arr::extract($r2->as_array(),
            'title', 'description', 'startpage_feed_url');
        $this->assertEquals(
            $r1_meta, $r2_meta,
            "Test data and form data repacks should have the same metadata"
        );

        $this->assertEquals(
            $r1->bookmarks_menu, $r2->bookmarks_menu,
            "Bookmarks menu contents should match"
        );

        $this->assertEquals(
            $r1->bookmarks_toolbar, $r2->bookmarks_toolbar,
            "Bookmarks toolbar contents should match"
        );

    }


}
