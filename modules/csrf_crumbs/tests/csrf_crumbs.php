<?php
/**
 * Test class for csrf_crumbs helper
 *
 * @package    csrf_crumbs
 * @subpackage tests
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 * @group      csrf_crumbs
 * @group      helpers
 * @group      helpers.csrf_crumbs
 */
class CSRF_Crumbs_Test extends PHPUnit_Framework_TestCase 
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
     * Exercise crumb generation and validation.
     */
    public function testCsrfGeneration()
    {
        $this->assertTrue(!csrf_crumbs::validate('not a crumb'),
            'Malformed crumb should be invalid');

        $this->assertTrue(!csrf_crumbs::validate('catsCfdogs81pso8-8675309-2f204fd9c2233e7fbbf472ce44dcc6562fc6d6a1637277d31f78ac625dd4a390'), 
            'Invalid crumb should be invalid');

        Kohana::config_set('csrf_crumbs.secret', 'original secret');
        csrf_crumbs::set_session_token('i like pie');

        $token = csrf_crumbs::generate();
        $this->assertTrue(csrf_crumbs::validate($token),
            "Generated crumb should be valid.");

        csrf_crumbs::set_session_token('the cake is a lie');

        $this->assertTrue(!csrf_crumbs::validate($token),
            "Generated crumb should be invalid on session change.");

        $token = csrf_crumbs::generate();
        $this->assertTrue(csrf_crumbs::validate($token),
            "Generated crumb should be valid.");

        Kohana::config_set('csrf_crumbs.secret', 'this secret has changed');

        $this->assertTrue(!csrf_crumbs::validate($token),
            "Generated crumb should be invalid on session change.");
    }
    
}
