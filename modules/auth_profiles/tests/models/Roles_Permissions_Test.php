<?php
/**
 * Test class for roles and permissions
 *
 * @package    auth_profiles
 * @subpackage tests
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 * @group      auth_profiles
 * @group      models
 * @group      models.auth_profiles
 * @group      models.auth_profiles.roles
 * @group      models.auth_profiles.permissions
 */
class Roles_Permissions_Test extends PHPUnit_Framework_TestCase 
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
                'bio'         => 'Random bio ensues'
            ),
            'roles' => array(
                'alpha_reader', 'beta_reader'
            )
        ),
        array(
            'profile' => array(
                'screen_name' => 'Tester2',
                'full_name'   => 'Tester Smith',
                'bio'         => 'Juffo wup'
            ),
            'roles' => array(
                'superuser'
            )
        ),
        array(
            'profile' => array(
                'screen_name' => 'Tester3',
                'full_name'   => 'Joe Smith',
                'bio'         => 'Whee haw'
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
        DecafbadUtils_EnvConfig::apply('testing');

        $this->role_model = 
            ORM::factory('role')->delete_all();
        $this->permission_model = 
            ORM::factory('permission')->delete_all();
        $this->profile_model = 
            ORM::factory('profile')->delete_all();

        // Register the handler to provide test permissions.
        Event::add('auth_profiles.collect_permissions',
            array($this, '_provide_permissions'));

        // Reset the known permissions cache
        $this->permission_model->find_known_permissions(true);

    }

    /**
     * Event handler to supply test permissions to model.
     */
    public function _provide_permissions()
    {
        Event::$data += $this->permissions;
    }

    /**
     * Make sure that all known permissions exist in the DB with unique IDs.
     */
    public function test_known_permissions()
    {
        // Try finding all permissions.
        $permissions = $this->permission_model->find_all();
        $this->assertEquals(
            count(array_keys($this->permissions)),
            count($permissions),
            "Permissions in DB should match test set."
        );

        // Inspect and verify permissions rows.
        $found = array();
        foreach ($permissions as $p) {
            if (isset($found[$p->id])) {
                $this->fail("Duplicate permission ID found");
            }
            $found[$p->id] = array(
                'name'        => $p->name, 
                'description' => $p->description
            );
            $this->assertEquals(
                $this->permissions[$p->name],
                $p->description,
                'Database description should match known description'
            );
        }

        // Introduce a new permission and ensure that it (and only it) 
        // is added to the DB.
        $this->permissions['munge_delta'] = 'Munge delta stuff';
        $this->permission_model
            ->find_known_permissions(true); // Reset the cache
        $permissions = $this->permission_model->find_all();
        $this->assertEquals(
            count(array_keys($this->permissions)),
            count($permissions),
            "Permissions in DB should match test set."
        );

        // Ensure previously known permission IDs haven't changed.
        foreach ($permissions as $p) {
            if (!isset($found[$p->id]) && 'munge_delta'!=$p->name) {
                $this->fail('No unknown permission IDs should appear.');
            }
        }
    }

    /**
     * Build all the roles from test data.
     */
    public function _build_test_roles()
    {
        foreach ($this->roles as $role_name => $role_data) {
            $role = ORM::factory('role')->set(array(
                'name'        => $role_name,
                'description' => $role_data['description']
            ));
            foreach ($role_data['permissions'] as $pn) {
                $role->grant_permission($pn);
            }
            $role->save();
        }
    }

    /**
     * Build roles with test permissions, verify grant/revoke.
     */
    public function test_roles_permissions()
    {
        $this->_build_test_roles();

        // Verify all the roles' expected descriptions and permissions.
        foreach ($this->roles as $role_name => $role_data) {

            $role = ORM::factory('role')->find(array(
                'name'=>$role_name
            ));
            $this->assertNotNull($role,
                "Role {$role_name} should be found.");

            $this->assertEquals(
                $role_data['description'], $role->description,
                "Role descriptions should match"
            );

            foreach ($role_data['permissions'] as $pn) {
                $role->revoke_permission($pn);
            }
            $role->save();

            foreach ($role_data['permissions'] as $pn) {
                $this->assertTrue(
                    !$role->has_permission($pn),
                    "Role {$role_name} should have permission {$pn}"
                );
            }

        }

    }

    /**
     * Ensure that profiles can be assigned roles, and that assigned roles 
     * grant appropriate permissions.
     */
    public function test_profiles_roles_permissions()
    {
        $this->_build_test_roles();

        // Create the test profiles and add the desired roles.
        foreach ($this->profiles as $data) {
            $profile = ORM::factory('profile')->set($data['profile']);
            foreach ($data['roles'] as $role_name) {
                $profile->add_role($role_name);
            }
            $profile->save();
        }

        // Ensure the roles have been added, and that the associated 
        // permissions are available.
        foreach ($this->profiles as $data) {
            $profile = ORM::factory('profile', array(
                'screen_name' => $data['profile']['screen_name']
            ));
            foreach ($data['roles'] as $role_name) {
                $this->assertTrue(
                    $profile->has_role($role_name),
                    "Profile {$profile->screen_name} should have role {$role_name}"
                );
                $perms = $this->roles[$role_name]['permissions'];
                foreach ($perms as $perm_name) {
                    $profile->has_permission($perm_name);
                }
            }
        }

        // Remove all roles from all profiles.
        // TODO: Remove a role at a time and test?
        foreach ($this->profiles as $data) {
            $profile = ORM::factory('profile', array(
                'screen_name' => $data['profile']['screen_name']
            ));
            foreach ($data['roles'] as $role_name) {
                $profile->remove_role($role_name);
            }
            $profile->save();
        }

        // Ensure that profiles no longer have the roles previously added, nor 
        // have the associated permissions.
        foreach ($this->profiles as $data) {
            $profile = ORM::factory('profile', array(
                'screen_name' => $data['profile']['screen_name']
            ));
            foreach ($data['roles'] as $role_name) {
                $this->assertTrue(
                    !$profile->has_role($role_name),
                    "Profile {$profile->screen_name} should have role {$role_name}"
                );
                $perms = $this->roles[$role_name]['permissions'];
                foreach ($perms as $perm_name) {
                    !$profile->has_permission($perm_name);
                }
            }
        }

    }


}
