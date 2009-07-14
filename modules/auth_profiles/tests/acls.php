<?php
/**
 * Test class for ACLs
 * 
 * @package    auth_profiles
 * @subpackage tests
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 * @group      auth_profiles
 * @group      auth_profiles.acls
 */
class Acl_Test extends PHPUnit_Framework_TestCase 
{
    /**
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        LMO_Utils_EnvConfig::apply('testing');

        ORM::factory('profile')->delete_all();
        ORM::factory('login')->delete_all();
        ORM::factory('role')->delete_all();

        $acls = new Zend_Acl();
        $acls
            ->addRole(new Zend_Acl_Role('default'))
            ->addRole(new Zend_Acl_Role('loggedin'))
            ->addRole(new Zend_Acl_Role('admin'))
            ->addRole(new Zend_Acl_Role('alpha'))
            ->addRole(new Zend_Acl_Role('beta'), 'alpha')
            ->addRole(new Zend_Acl_Role('gamma'))
            ->addRole(new Zend_Acl_Role('delta'), 'gamma')

            ->add(new Zend_Acl_Resource('one'))
            ->add(new Zend_Acl_Resource('two'))
            ->add(new Zend_Acl_Resource('three'))

            ->allow('admin')
            ->allow('alpha',    'one',   array('cut', 'spindle', 'fold'))
            ->allow('beta',     'one',   array('munge'))
            ->allow('gamma',    'two',   array('remix', 'sample'))
            ->allow('delta',    'two',   array('share'))
            ->allow('default',  'three', array('explode'))
            ->allow('loggedin', 'three', array('transplode'))
            ;

        Kohana::config_set('auth_profiles.acls', $acls);
        Kohana::config_set('auth_profiles.base_anonymous_role', 'default');
        Kohana::config_set('auth_profiles.base_login_role',     'loggedin');

        $this->logins   = array();
        $this->profiles = array();
        $this->roles    = array();

        $role_combos = array(
            array('admin'),
            array('alpha'),
            array('beta'),
            array('gamma'),
            array('delta'),
            array('alpha','gamma'),
            array('beta','delta'),
        );

        foreach ($role_combos as $idx=>$roles) {

            $this->logins[] = $login = ORM::factory('login')->set(array(
                'login_name' => "tester{$idx}",
                'email'      => "tester{$idx}@example.com",
            ))->save();

            $this->profiles[] = $profile = ORM::factory('profile')->set(array(
                'screen_name' => "tester{$idx}",
                'full_name'   => "Tess T. Err {$idx}",
                'org_name'    => "Test Organization {$idx}",
            ))->save();

            $profile->add($login);
            $profile->save();

            foreach ($roles as $role_name) {
                $profile->add_role($role_name);
            }
            $profile->save();

        }

    }

    /**
     * Ensure that everything is allowed by default if no ACLs defined.
     */
    public function testDefaultPermission()
    {
        Kohana::config_set('auth_profiles.acls', false);
        $this->assertTrue(
            authprofiles::is_allowed('foo', 'bar'),
            'Default permission with no ACLs should be allowed'
        );
    }

    /**
     * Ensure profiles can be queried by role name
     */
    public function testFindProfileByRole()
    {
        $roles = array(
            'admin' => array( 'tester0' ), 
            'alpha' => array( 'tester1', 'tester5' ), 
            'beta'  => array( 'tester2', 'tester6' ), 
            'gamma' => array( 'tester3', 'tester5' ),
            'delta' => array( 'tester4', 'tester6' ),
            
            'alpha beta' => array( 
                'tester1', 'tester5', 'tester2', 'tester6' 
            ), 
            'gamma delta' => array( 
                'tester3', 'tester5', 'tester4', 'tester6' 
            ),
        );
        foreach ($roles as $role_name => $expected_screen_names) {
            
            if (strpos($role_name, ' ') !== FALSE) {
                $role_name = explode(' ', $role_name);
            }
            $profiles = ORM::factory('profile')
                ->find_all_by_role($role_name);
            
            $result_screen_names = array();
            foreach ($profiles as $profile)
                $result_screen_names[] = $profile->screen_name;

            sort($expected_screen_names);
            sort($result_screen_names);

            $this->assertEquals(
                $expected_screen_names,
                $result_screen_names
            );

        }
    }


    /**
     * Exercise the is_allowed helper method against the configured ACLs.
     */
    public function testHelperIsAllowed()
    {
        // Resource / privilege pairs for each column below.
        $resource_privileges = array(
            array('one',   'cut'),
            array('one',   'spindle'),
            array('one',   'fold'),
            array('one',   'munge'),
            array('two',   'remix'),
            array('two',   'sample'),
            array('two',   'share'),
            array('three', 'implode'),
            array('three', 'explode'),
            array('three', 'transplode'),
        );

        // Permission results for each profile defined in setup.
        $expected_results = array(
            array(true,  true,  true,  true,  true,  true,  true,  true,  true,  true),
            array(true,  true,  true,  false, false, false, false, false, false, true),
            array(true,  true,  true,  true,  false, false, false, false, false, true),
            array(false, false, false, false, true,  true,  false, false, false, true),
            array(false, false, false, false, true,  true,  true,  false, false, true),
            array(true,  true,  true,  false, true,  true,  false, false, false, true),
            array(true,  true,  true,  true,  true,  true,  true,  false, false, true),

            // default role used when profiles run out.
            array(false, false, false, false, false, false, false, false, true,  false),
        );

        // Iterate through the expected results and profiles, check the 
        // permissions for all the permutations.
        foreach ($expected_results as $idx => $results) {
            foreach ($results as $result_idx=>$expected) {

                if ($idx < count($this->profiles)) {
                    // Use the indexed login and profile for auth, if available.
                    authprofiles::$login   = $login = $this->logins[$idx];
                    authprofiles::$profile = $profile = $this->profiles[$idx];
                } else {
                    // Use default role once past the end of known profiles.
                    authprofiles::$login = authprofiles::$profile = null;
                }

                list($resource, $privilege) = $resource_privileges[$result_idx];

                $result = authprofiles::is_allowed($resource, $privilege);
                $this->assertEquals($expected, $result,
                    "{$resource}::{$privilege} for {$profile->screen_name}");

            }
        }

    }

}
