<?php
/**
 * Configuration for auth profiles
 */
$config['secret']       = 'c2Vzc2lvbl9pZHxzOjMyOiJkNmY5NTUw';
$config['home_url']     = 'profiles/%1$s/';
$config['cookie_name']  = 'byob_auth_profiles';
$config['cookie_path']  = '/';

$config['base_anonymous_role'] = 'guest';
$config['base_login_role']     = 'member';

$config['roles'] = array(
    'guest'   => 'Guest', 
    'member'  => 'Regular member', 
    'trusted' => 'Trusted member', 
    'editor'  => 'Editor', 
    'admin'   => 'Administrator'
);

$acls = new Zend_Acl();
$config['acls'] = $acls

    ->addRole(new Zend_Acl_Role('guest'))
    ->addRole(new Zend_Acl_Role('member'), 'guest')
    ->addRole(new Zend_Acl_Role('trusted'), 'member')
    ->addRole(new Zend_Acl_Role('editor'), 'member')
    ->addRole(new Zend_Acl_Role('admin'), 'editor')

    // Admins can do anything.
    ->allow('admin')

    // Search privileges
    ->add(new Zend_Acl_Resource('search'))
    ->allow('editor', 'search', array(
        'search', 'approvalqueue'
    ))
    // ->allow('member', 'search', array('search'))
    // ->allow('guest', 'search', array('search'))

    // Profile privileges
    ->add(new Zend_Acl_Resource('profiles'))
    ->allow('member', 'profiles', array(
        'view_own', 'edit_own',
    ))

    // Repack privileges
    ->add(new Zend_Acl_Resource('repacks'))
    ->allow('guest', 'repacks', array(
        'view_released', 'download_released',
    ))
    ->allow('member', 'repacks', array(
        'create', 'view_own', 'view_own_history', 'edit_own', 'delete_own', 
        'release_own', 'revert_own', 'cancel_own',
    ))
    ->allow('trusted', 'repacks', array(
        'approve_own', 'auto_approve_own'
    ))
    ->allow('editor', 'repacks', array(
        'view_unreleased', 'view_history', 'view_approval_queue',
        'edit', 'delete', 'release', 
        'revert', 'approve', 'reject', 
        'download_unreleased',
    ))

    // ORM Manager admin privileges
    ->add(new Zend_Acl_Resource('admin'))

    ;
