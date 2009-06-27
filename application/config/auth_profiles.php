<?php
/**
 * Configuration for auth profiles
 */
$config['secret']       = 'c2Vzc2lvbl9pZHxzOjMyOiJkNmY5NTUw';
$config['home_url']     = 'profiles/%1$s/';
$config['cookie_name']  = 'byob_auth_profiles';
$config['cookie_path']  = '/';

$config['default_role'] = 'guest';

$acls = new Zend_Acl();
$config['acls'] = $acls

    ->addRole(new Zend_Acl_Role('guest'))
    ->addRole(new Zend_Acl_Role('member'), 'guest')
    ->addRole(new Zend_Acl_Role('editor'), 'member')
    ->addRole(new Zend_Acl_Role('admin'), 'editor')

    ->add(new Zend_Acl_Resource('repacks'))
    ->add(new Zend_Acl_Resource('profiles'))
    ->add(new Zend_Acl_Resource('products'))

    ->allow('admin')
    ->allow('member', 'repacks', array(
        'view_own', 'edit_own', 'delete_own', 'revert_own', 'cancel_own',
    ))
    ->allow('editor', 'repacks', array(
        'view_unpublished', 'edit', 'delete', 'revert', 'approve', 
        'reject', 'cancel',
    ))
    ->allow('guest', 'repacks', array(
        'view_published',
    ))
    ;
