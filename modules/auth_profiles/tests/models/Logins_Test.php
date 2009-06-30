<?php
/**
 * Test class for Model_User.
 *
 * @package    auth_profiles
 * @subpackage tests
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 * @group      auth_profiles
 * @group      models
 * @group      models.auth_profiles
 * @group      models.auth_profiles.logins
 */
class Logins_Test extends PHPUnit_Framework_TestCase 
{
    /**
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        LMO_Utils_EnvConfig::apply('testing');

        $this->login_model = new Login_Model();
        $this->login_model->delete_all();

        $this->profile_model = new Profile_Model();
        $this->profile_model->delete_all();
    }

    /**
     * Ensure that required fields for a login are enforced.
     */
    public function pass_testCreateRequiredFields()
    {
        try {
            $test_id = $this->logins_model->create(array());
            $this->fail('Logins with missing fields should not be allowed');
        } catch (Exception $e1) {
            $this->assertContains('required', $e1->getMessage());
        }
        try {
            $test_id = $this->logins_model->create(array(
                'login_name' => 'tester1'
            ));
            $this->fail('Logins with missing fields should not be allowed');
        } catch (Exception $e2) {
            $this->assertContains('required', $e2->getMessage());
        }
        try {
            $test_id = $this->logins_model->create(array(
                'login_name' => 'tester1',
                'password'   => 'tester_password'
            ));
            $this->fail('Logins with missing fields should not be allowed');
        } catch (Exception $e3) {
            $this->assertContains('required', $e3->getMessage());
        }
        try {
            $test_id = $this->logins_model->create(array(
                'login_name' => 'tester1',
                'password'   => 'tester_password',
                'email'      => 'tester1@example.com'
            ));
        } catch (Exception $e) {
            $this->fail('Users with duplicate login names should raise exceptions');
        }
    }

    /**
     * Ensure a login can be created and found by login name.
     */
    public function test_create_and_fetch_login()
    {
        ORM::factory('login')->set(array(
            'login_name' => 'tester1',
            'email'      => 'tester1@example.com',
            //'password'   => 'tester_password',
        ))->save();

        $login = ORM::factory('login', 'tester1');

        $this->assertEquals($login->login_name, 'tester1');
        $this->assertEquals($login->email, 'tester1@example.com');
        //$this->assertEquals($login->password, $login->encrypt_password('tester_password'));
    }

    /**
     * Ensure that logins with the same login names cannot be created.
     */
    public function test_should_not_allow_duplicate_login_name()
    {
        ORM::factory('login')->set(array(
            'login_name' => 'tester1',
            'email'      => 'tester1@example.com',
            'password'   => 'tester_password',
        ))->save();

        try {
            ORM::factory('login')->set(array(
                'login_name' => 'tester1',
                'email'      => 'tester1@example.com',
                'password'   => 'tester_password'
            ))->save();

            $this->fail('Users with duplicate login names should raise exceptions');
        } catch (Exception $e) {
            $this->assertContains('Duplicate', $e->getMessage());
        }
    }
    
    /**
     * Since login and profile creation during registration are two steps,
     * ensure that a failed profile creation doesn't result in a deadend login.
     */
    public function test_registration_should_create_profile()
    {
        $login = ORM::factory('login')->register_with_profile(array(
            'login_name'  => 'tester1',
            'email'       => 'tester1@example.com',
            'password'    => 'tester_password',
            'screen_name' => 'tester1_screenname',
            'full_name'   => 'Tess T. Erone',
            //'bio'         => 'I live!'
        ));
        $this->assertTrue(null !== $login);
        $login = ORM::factory('login', $login->id);

        $profile = ORM::factory('profile', 'tester1_screenname');

        $this->assertTrue(null !== $profile);
        $this->assertEquals($profile->screen_name, 'tester1_screenname');
        $this->assertEquals($profile->full_name, 'Tess T. Erone');
        //$this->assertEquals($profile->bio, 'I live!');

        $default_profile = $login->find_default_profile_for_login();
        $this->assertEquals($profile->id, $default_profile->id);
    }

    /**
     * Since login and profile creation during registration are two steps,
     * ensure that a failed profile creation doesn't result in a deadend login.
     */
    public function pass_testFailedRegistrationShouldNotCreateLogin()
    {
        try {
            $login_id = $this->logins_model->register_with_profile(array(
                'login_name' => 'tester1',
                'email'      => 'tester1@example.com',
                'password'   => 'tester_password',
            ));
            $this->fail('Missing profile details should cause registration to fail');
        } catch (Exception $e) {
            $this->assertContains('required', $e->getMessage());
            $login = $this->logins_model->find_by_login_name('tester1');
            $this->assertNull($login);
        }
    }
    
}
