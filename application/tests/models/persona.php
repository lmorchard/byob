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
 * @group      models.byob.persona
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
        $p = Model::factory('persona')->find('NOTFOUND');
        $this->assertTrue(!$p->loaded);
    }

    /**
     * Exercise persona download and unpack.
     */
    public function testPersonaFind()
    {
        $p = Model::factory('persona')->find('34365');

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
    public function testPersonaFindByURL()
    {
        $p = Model::factory('persona')->find_by_url(
            'http://www.getpersonas.com/persona/34365' 
        );

        $this->assertEquals('34365', $p->id);
        $this->assertEquals('http://www.getpersonas.com/persona/34365', $p->url);
        $this->assertEquals("A Web Browser Renaissance", $p->name);

        // DANGER: This will break if this persona is ever modified.
        //$this->assertEquals($p->json, '{"id":"34365","name":"A Web Browser Renaissance","accentcolor":"#f2d9b1","textcolor":"#000000","header":"6\/5\/34365\/ff35_header7.jpg","footer":"6\/5\/34365\/ff35_footer10.jpg"}');
    }

}
