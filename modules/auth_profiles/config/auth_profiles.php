<?php
/**
 * Configuration for auth profiles
 */
$config['secret']      = '8675309jenny';
$config['home_url']    = 'profiles/%1$s';
$config['cookie_name'] = 'auth_profiles';
$config['cookie_path'] = '/';

$config['base_anonymous_role'] = 'guest';
$config['base_login_role']     = 'member';

$acls = new Zend_Acl();
$config['acls'] = $acls

    ->addRole(new Zend_Acl_Role('guest'))
    ->addRole(new Zend_Acl_Role('member'), 'guest')
    ->addRole(new Zend_Acl_Role('admin'), 'member')

    // Admins can do anything.
    ->allow('admin')

    ->add(new Zend_Acl_Resource('profiles'))
    ->allow('member', 'profiles', array('view_own', 'edit_own',))

    ->add(new Zend_Acl_Resource('logins'))
    ->allow('member', 'logins', array('view_own', 'edit_own',))

    ;
