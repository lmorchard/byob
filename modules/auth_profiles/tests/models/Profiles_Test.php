<?php
/**
 * Test class for profiles
 *
 * @package    auth_profiles
 * @subpackage tests
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 * @group      auth_profiles
 * @group      models
 * @group      models.auth_profiles
 * @group      models.auth_profiles.profiles
 */
class Profiles_Test extends PHPUnit_Framework_TestCase 
{
    // {{{ Test data
    
    public $permissions = array(
        'read_alpha'   => 'Read alpha stuff', 
        'write_alpha'  => 'Write alpha stuff', 
        'delete_alpha' => 'Delete alpha stuff',
        'read_beta'    => 'Read beta stuff', 
        'write_beta'   => 'Write beta stuff', 
        'delete_beta'  => 'Delete beta stuff'
    );

    public $roles = array(
        'superuser' => array(
            'description' => 'Superuser!',
            'permissions' => array(
                'read_alpha', 'write_alpha', 'delete_alpha',
                'read_beta', 'write_beta', 'delete_beta'
            ),
        ),
        'alpha_admin' => array(
            'description' => 'Admin for alpha',
            'permissions' => array(
                'read_alpha', 'write_alpha', 'delete_alpha'
            )
        ),
        'alpha_reader' => array(
            'description' => 'Reader of alpha stuff',
            'permissions' => array(
                'read_alpha'
            ),
        ),
        'beta_admin' => array(
            'description' => 'Beta admin',
            'permissions' => array(
                'read_beta', 'write_beta', 'delete_beta'
            )
        ),
        'beta_reader' => array(
            'description' => 'Reader of beta stuff',
            'permissions' => array(
                'read_beta'
            ),
        ),
    );

    public $profiles = array(
        array(
            'profile' => array(
                'screen_name' => 'Tester1',
                'full_name'   => 'Iam Tester',
                //'bio'         => 'Random bio ensues'
            ),
            'roles' => array(
                'alpha_reader', 'beta_reader'
            )
        ),
        array(
            'profile' => array(
                'screen_name' => 'Tester2',
                'full_name'   => 'Tester Smith',
                //'bio'         => 'Juffo wup'
            ),
            'roles' => array(
                'superuser'
            )
        ),
        array(
            'profile' => array(
                'screen_name' => 'Tester3',
                'full_name'   => 'Joe Smith',
                //'bio'         => 'Whee haw'
            ),
            'roles' => array(
                'alpha_admin', 'beta_admin'
            )
        ),
    );
    
    // }}}

    /**
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        LMO_Utils_EnvConfig::apply('testing');

        $this->profile_model = 
            ORM::factory('profile')->delete_all();
    }

    /**
     * Exercise profile validation
     */
    public function test_validation_create()
    {
        // Create a profile to fail is_screen_name_available validation.
        $profile = ORM::factory('profile')->set(array(
            'screen_name' => 'tester1',
            'full_name'   => 'Tess T. Err'
        ))->save();

        $data_errors = array(
            array(
                array('screen_name' => '', 'full_name' => ''),
                array(
                    'screen_name' => 'required', 
                    'full_name'   => 'required'
                ),
            ),
            array(
                array('screen_name' => 'tester1', 'full_name' => ''),
                array(
                    'screen_name' => 'is_screen_name_available', 
                    'full_name'   => 'required'
                ),
            ),
            array(
                array('screen_name' => 'aa', 'full_name' => ''),
                array(
                    'screen_name' => 'length', 
                    'full_name'   => 'required'
                ),
            ),
            array(
                array('screen_name' => '!@#$%^', 'full_name' => '!@#$%^&'),
                array(
                    'screen_name' => 'alpha_dash', 
                    'full_name'   => 'standard_text'
                ),
            ),
            array(
                array('screen_name' => 'foo_bar', 'full_name' => ''),
                array(
                    'full_name' => 'required'
                ),
            ),
        );
        foreach ($data_errors as $data_error) {
            list($data, $expected_errors) = $data_error;
            $this->assertTrue(
                !$this->profile_model->validate_create($data),
                'Empty data should be invalid.'
            );
            $errors = $data->errors();
            $this->assertEquals(
                $expected_errors, $errors,
                'Errors should match up'
            );
        }

    }

    /**
     * Ensure a login can be created and found by login name.
     */
    public function test_create_fetch_update()
    {
        $profile = ORM::factory('profile')->set(array(
            'screen_name' => 'tester1_screenname',
            'full_name'   => 'Tess T. Erone',
            //'bio'         => 'I live!'
        ))->save();

        $found_profile = 
            ORM::factory('profile','tester1_screenname');

        $this->assertEquals($found_profile->screen_name, 
            'tester1_screenname');
        $this->assertEquals($found_profile->full_name, 
            'Tess T. Erone');
        //$this->assertEquals($found_profile->bio, 'I live!');

        $updated_profile = ORM::factory('profile',$found_profile->id)
            ->set(array(
                'screen_name' => 'updated_tester1_screenname',
                'full_name'   => 'Updated Tess T. Erone',
                //'bio'         => 'Updated I live!'
            ))->save();

        $updated_profile = 
            ORM::factory('profile','updated_tester1_screenname');

        $this->assertEquals($updated_profile->screen_name, 
            'updated_tester1_screenname');
        $this->assertEquals($updated_profile->full_name, 
            'Updated Tess T. Erone');
        //$this->assertEquals($updated_profile->bio, 'Updated I live!');

        $updated_profile_1 = 
            ORM::factory('profile',$found_profile->id);

        $this->assertEquals($updated_profile_1->screen_name, 
            'updated_tester1_screenname');
        $this->assertEquals($updated_profile_1->full_name, 
            'Updated Tess T. Erone');
        //$this->assertEquals($updated_profile_1->bio, 'Updated I live!');
    }

    /**
     * Exercise profile attributes.
     */
    public function test_profile_attributes()
    {
        $profile = ORM::factory('profile')->set(array(
            'screen_name' => 'tester1_screenname',
            'full_name'   => 'Tess T. Erone',
            'bio'         => 'I live!'
        ))->save();

        $profile->set_attribute('test1', 'value1');
        $profile->set_attribute('test2', 'value2');
        $profile->set_attribute('test3', 'value3');

        $profile->set_attributes(array(
            'test4' => 'value4',
            'test5' => 'value5',
            'test6' => 'value6'
        ));

        $test_attribs = array(
            'test1' => 'value1',
            'test2' => 'value2',
            'test3' => 'value3',
            'test4' => 'value4',
            'test5' => 'value5',
            'test6' => 'value6'
        );

        foreach ($test_attribs as $name=>$test_value) {
            $result_value = $profile->get_attribute($name);
            $this->assertEquals($test_value, $result_value);
        }

        $result_attribs = $profile->get_attributes();
        $this->assertEquals($result_attribs, $test_attribs);

        $result_attribs2 = $profile->get_attributes(array(
            'test2', 'test4', 'test6'
        ));
        $test_attribs2 = array(
            'test2' => 'value2',
            'test4' => 'value4',
            'test6' => 'value6'
        );
        $this->assertEquals($result_attribs2, $test_attribs2);

        $test_attribs3 = array(
            'test1' => 'updated_value1',
            'test2' => 'updated_value2',
            'test3' => 'updated_value3',
            'test4' => 'updated_value4',
            'test5' => 'updated_value5',
            'test6' => 'updated_value6'
        );

        $profile->set_attributes($test_attribs3);

        $result_attribs3 = $profile->get_attributes();

        $this->assertEquals($result_attribs3, $test_attribs3);

    }

}
