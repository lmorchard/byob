<?php
/**
 * Test class for XmlBuilder
 *
 * @package    LMO_Utils
 * @subpackage tests
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 * @group      LMO_Utils
 * @group      libraries
 * @group      LMO_Utils.libraries
 */
class XmlBuilder_Test extends PHPUnit_Framework_TestCase 
{
    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite("XmlBuilder_Test");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
    }

    /**
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
    }

    /**
     * Exercise the XmlBuilder object.
     */
    public function testWriter()
    {
        $x = new XmlBuilder(array(
            'parents' => array( 'feed', 'entry', 'author' )
        ));
        $x->feed(array('xmlns'=>'http://www.w3.org/2005/Atom'))
            ->id('1234')
            ->title('This is a title')
            ->subtitle('This is a subtitle')
            ->link(array( 'rel'=>'self', 'type'=>'application/atom+xml', 'href'=>'http://example.com' ))
            ->link(array( 'rel'=>'alternate', 'type'=>'text.html', 'href'=>'http://example.com' ))
            ->updated(gmdate('c', strtotime('2009-01-03T12:00:15-0500')));

        for ($i=0; $i<5; $i++) {
            $x->entry()
                ->title("HI MOM $i '<>!@#$%^&*()")
                ->id("entry<$i>")
                ->updated(gmdate('c', strtotime('2009-01-03T12:00:15-0500')))
                ->published(gmdate('c', strtotime('2009-01-02T12:00:15-0500')))
                ->author()
                    ->name('joe schmoe')
                    ->email('joe@schmoe.com')
                    ->uri('http://schmoe.com/~joe')
                ->pop()
                ->summary(
                    array('type'=>'text/plain'),
                    'This is content #' . $i
                )
                ->content(
                    array('type'=>'text/html'),
                    '<p>This is content #' . $i . '</p>'
                )
                ;

            for ($j=0; $j<3; $j++) {
                $x->category(array( 
                    'scheme'=>'http://example/', 
                    'term'=>'term' . $j,
                    'bogus'=>null
                ));
            }

            $x->pop();
        }

        $x->pop();

        $test_xml = <<<END_FEED
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <id>1234</id>
    <title>This is a title</title>
    <subtitle>This is a subtitle</subtitle>
    <link rel="self" type="application/atom+xml" href="http://example.com"/>
    <link rel="alternate" type="text.html" href="http://example.com"/>
    <updated>2009-01-03T17:00:15+00:00</updated>
    <entry>
        <title>HI MOM 0 '&lt;&gt;!@#$%^&amp;*()</title>
        <id>entry&lt;0&gt;</id>
        <updated>2009-01-03T17:00:15+00:00</updated>
        <published>2009-01-02T17:00:15+00:00</published>
        <author>
            <name>joe schmoe</name>
            <email>joe@schmoe.com</email>
            <uri>http://schmoe.com/~joe</uri>
        </author>
        <summary type="text/plain">This is content #0</summary>
        <content type="text/html">&lt;p&gt;This is content #0&lt;/p&gt;</content>
        <category scheme="http://example/" term="term0"/>
        <category scheme="http://example/" term="term1"/>
        <category scheme="http://example/" term="term2"/>
    </entry>
    <entry>
        <title>HI MOM 1 '&lt;&gt;!@#$%^&amp;*()</title>
        <id>entry&lt;1&gt;</id>
        <updated>2009-01-03T17:00:15+00:00</updated>
        <published>2009-01-02T17:00:15+00:00</published>
        <author>
            <name>joe schmoe</name>
            <email>joe@schmoe.com</email>
            <uri>http://schmoe.com/~joe</uri>
        </author>
        <summary type="text/plain">This is content #1</summary>
        <content type="text/html">&lt;p&gt;This is content #1&lt;/p&gt;</content>
        <category scheme="http://example/" term="term0"/>
        <category scheme="http://example/" term="term1"/>
        <category scheme="http://example/" term="term2"/>
    </entry>
    <entry>
        <title>HI MOM 2 '&lt;&gt;!@#$%^&amp;*()</title>
        <id>entry&lt;2&gt;</id>
        <updated>2009-01-03T17:00:15+00:00</updated>
        <published>2009-01-02T17:00:15+00:00</published>
        <author>
            <name>joe schmoe</name>
            <email>joe@schmoe.com</email>
            <uri>http://schmoe.com/~joe</uri>
        </author>
        <summary type="text/plain">This is content #2</summary>
        <content type="text/html">&lt;p&gt;This is content #2&lt;/p&gt;</content>
        <category scheme="http://example/" term="term0"/>
        <category scheme="http://example/" term="term1"/>
        <category scheme="http://example/" term="term2"/>
    </entry>
    <entry>
        <title>HI MOM 3 '&lt;&gt;!@#$%^&amp;*()</title>
        <id>entry&lt;3&gt;</id>
        <updated>2009-01-03T17:00:15+00:00</updated>
        <published>2009-01-02T17:00:15+00:00</published>
        <author>
            <name>joe schmoe</name>
            <email>joe@schmoe.com</email>
            <uri>http://schmoe.com/~joe</uri>
        </author>
        <summary type="text/plain">This is content #3</summary>
        <content type="text/html">&lt;p&gt;This is content #3&lt;/p&gt;</content>
        <category scheme="http://example/" term="term0"/>
        <category scheme="http://example/" term="term1"/>
        <category scheme="http://example/" term="term2"/>
    </entry>
    <entry>
        <title>HI MOM 4 '&lt;&gt;!@#$%^&amp;*()</title>
        <id>entry&lt;4&gt;</id>
        <updated>2009-01-03T17:00:15+00:00</updated>
        <published>2009-01-02T17:00:15+00:00</published>
        <author>
            <name>joe schmoe</name>
            <email>joe@schmoe.com</email>
            <uri>http://schmoe.com/~joe</uri>
        </author>
        <summary type="text/plain">This is content #4</summary>
        <content type="text/html">&lt;p&gt;This is content #4&lt;/p&gt;</content>
        <category scheme="http://example/" term="term0"/>
        <category scheme="http://example/" term="term1"/>
        <category scheme="http://example/" term="term2"/>
    </entry>
</feed>

END_FEED;

        $this->assertEquals($test_xml, $x->getXML());

        $doc = new DOMDocument();
        $doc->loadXML($x->getXML());
        $parsed_xml = $doc->saveXML();
        $this->assertEquals($parsed_xml, $x->getXML());
    }
    
}
