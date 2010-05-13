<?php
/**
 * Test class for persona model
 * 
 * @package    byob
 * @subpackage tests
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 * @group      byob
 * @group      models
 * @group      models.byob
 * @group      models.byob.addon_management
 * @group      models.byob.addon_management.persona
 */
class Persona_Test extends PHPUnit_Framework_TestCase 
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
     * Warm up test to ensure configured personas are found.
     */
    public function testFindAll()
    {
    }

    /**
     * Exercise persona download and unpack.
     */
    public function testPersonaNotFound()
    {
        $p = Model::factory('persona')->find_by_getpersonas_id('NOTFOUND');
        $this->assertTrue(!$p->loaded);
    }

    /**
     * Exercise persona download and unpack.
     */
    public function testPersonaFindById()
    {
        $p = Model::factory('persona')->find_by_getpersonas_id('34365');

        $this->assertTrue($p->loaded);
        $this->assertEquals('34365', $p->id);
        $this->assertEquals('http://www.getpersonas.com/persona/34365', $p->url);
        $this->assertEquals("A Web Browser Renaissance", $p->name);

        // DANGER: This will break if this persona is ever modified.
        //$this->assertEquals($p->json, '{"id":"34365","name":"A Web Browser Renaissance","accentcolor":"#f2d9b1","textcolor":"#000000","header":"6\/5\/34365\/ff35_header7.jpg","footer":"6\/5\/34365\/ff35_footer10.jpg"}');
    }

    /**
     * Exercise persona download and unpack.
     */
    public function testPersonaFindByGetpersonasURL()
    {
        $p = Model::factory('persona')->find_by_url(
            'http://www.getpersonas.com/en-US/persona/2635' 
        );

        $this->assertTrue($p->loaded);
        $this->assertEquals('2635', $p->id);
        $this->assertEquals('http://www.getpersonas.com/en-US/persona/2635', $p->url);
        $this->assertEquals("Mozilla Firefox", $p->name);

        // DANGER: This will break if this persona is ever modified.
        //$this->assertEquals($p->json, '{"id":"34365","name":"A Web Browser Renaissance","accentcolor":"#f2d9b1","textcolor":"#000000","header":"6\/5\/34365\/ff35_header7.jpg","footer":"6\/5\/34365\/ff35_footer10.jpg"}');
    }

    /**
     * Exercise persona download and unpack via an AMO URL.
     */
    public function testPersonaFindByAMOURL()
    {
        $p = Model::factory('persona')->find_by_url(
            'https://addons.mozilla.org/en-US/firefox/persona/15114' 
        );

        $this->assertTrue($p->loaded);
        $this->assertEquals('10', $p->id);
        $this->assertEquals('https://addons.mozilla.org/en-US/firefox/persona/15114', $p->url);
        $this->assertEquals("Firefox B", $p->name);

        $this->assertEquals('{"id":"10","name":"Firefox B","accentcolor":"#f7fcff","textcolor":null,"category":"Firefox","author":"Mozilla","description":null,"header":"http:\/\/getpersonas.com\/static\/\/1\/6\/16\/tbox-firefox_b.jpg","footer":"http:\/\/getpersonas.com\/static\/\/1\/6\/16\/stbar-firefox_b.jpg","headerURL":"http:\/\/getpersonas.com\/static\/\/1\/6\/16\/tbox-firefox_b.jpg","footerURL":"http:\/\/getpersonas.com\/static\/\/1\/6\/16\/stbar-firefox_b.jpg","previewURL":"http:\/\/getpersonas.com\/static\/\/1\/6\/16\/preview.jpg","iconURL":"http:\/\/getpersonas.com\/static\/\/1\/6\/16\/preview_small.jpg"}', $p->json);

    }

}
