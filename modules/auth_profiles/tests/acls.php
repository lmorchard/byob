<?php
/**
 * Test class for ACLs
 * 
 * @package    auth_profiles
 * @subpackage tests
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 * @group      auth_profiles
 * @group      models
 * @group      models.auth_profiles
 * @group      models.auth_profiles.acls
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
    }

    /**
     * This test tries out some of the configured ACLs, but not exhaustively.
     */
    public function testSomeRules()
    {
        $acl = Kohana::config('auth_profiles.acls');
        $this->assertTrue(
            $acl->isAllowed('admin',  'repacks', 'delete') &&
            $acl->isAllowed('guest',  'repacks', 'view_published') &&
            $acl->isAllowed('member', 'repacks', 'view_own') &&
            $acl->isAllowed('editor', 'repacks', 'view_own') &&
            true
        );
    }
}
