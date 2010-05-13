<?php
/**
 * Test class for searchplugin model
 * 
 * @package    byob
 * @subpackage tests
 * @author     l.m.orchard <l.m.orchard@pobox.com>
 * @group      byob
 * @group      models
 * @group      models.byob
 * @group      models.byob.addon_management
 * @group      models.byob.addon_management.searchplugin
 */
class Searchplugin_Test extends PHPUnit_Framework_TestCase 
{

    public $bad_plugin_xml_1 = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"
                       xmlns:moz="http://www.mozilla.org/2006/browser/search/">
  <Url type="text/html" method="get" template="http://mycroft.mozdev.org/search-engines.html?name={searchTerms}"/> 
</OpenSearchDescription>
XML;

    public $bad_plugin_xml_2 = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"
                       xmlns:moz="http://www.mozilla.org/2006/browser/search/">
  <ShortName>Mycroft Project</ShortName> 
  <Url type="text/html" method="get" template="http://mycroft.mozdev.org/search-engines.html?name={searchTerms}"/> 
</OpenSearchDescription>
XML;

    public $plugin_youtube = <<<XML
<SearchPlugin xmlns="http://www.mozilla.org/2006/browser/search/" xmlns:os="http://a9.com/-/spec/opensearch/1.1/">
<os:ShortName>YouTube Video Search</os:ShortName>
<os:Description>Search for videos on YouTube</os:Description>
<os:InputEncoding>UTF-8</os:InputEncoding>
<os:Image width="16" height="16">data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAAAABMLAAATCwAAAAAAAAAAAAD//////////4OD//9paf//bm7//2Fh//9ZWf//Wlr//1pa//9WVv//ZGT//3Bw//9jY///goL//////////////////11d//8sLP//QUH//ygo//84OP//RET//y4u//8xMf//UVH//y4u//8PD///ZWX//x0d//9aWv////////////88PP//Cgr///////8zM///1NT///////+lpf//ubn///////+urv//fHz////////g4P//Fhb/////////////MzP//woK////////NDT//8vL//9ycv//paX//7Cw//9jY///s7P//8nJ//9XV///eXn//yIi/////////////zMz//8LC///+/v//zMz///Gxv//hYX//6Ki//+srP//W1v//6ys//+3t///2tr//93d//8PD/////////////80NP//AgL///b2//8nJ///5ub//56e//+5uf//oaH//+/v//+5uf//oKD//+Li///f3///AgL/////////////MzP//wUF////////Skr//0pK//9NTf//NTX//97e//+ysv//Nzf//xIS//+mpv//Kyv//z09/////////////xkZ///Y2P////////////8nJ///EBD//wAA///y8v//Ly///wAA//8mJv//Hh7//6mp//92dv////////////+vr///Jib//xMS//8eIP//MzP//zY2//84OP//Hh///y4u//9XV///hoj//8LC///R0f//qqr/////////////////////////////////////////////////////////////////////////////////////////////////////////////AAAA/8zMzP/u7u7/IiIi/wAAAP8iIiL//////zMzM/8AAAD/AAAA/////////////////////////////////wAAAP/MzMz//////yIiIv/u7u7/ERER/7u7u/8AAAD/iIiI/xEREf///////////////////////////+7u7v8AAAD/zMzM//////8iIiL/7u7u/xEREf+7u7v/AAAA/8zMzP8RERH///////////////////////////93d3f/AAAA/1VVVf/u7u7/IiIi/wAAAP8iIiL//////wAAAP/MzMz/ERER///////////////////////d3d3/AAAA/4iIiP8AAAD/3d3d/////////////////////////////////////////////////////////////////wAAAP//////AAAA////////////////////////////////////////////////////////////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==</os:Image>
<os:Url type="text/html" method="GET" template="http://youtube.com/results?search_type=search_videos&amp;search_query={searchTerms}&amp;search_sort=relevance&amp;search_category=0&amp;page={startPage?}">
</os:Url>
</SearchPlugin>
XML;

    public $plugin_xml_1 = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"
                       xmlns:moz="http://www.mozilla.org/2006/browser/search/">
  <ShortName>Mycroft Project</ShortName> 
  <Description>Mycroft Project: Sherlock &amp; OpenSearch Search Engine Plugins</Description>
  <Url type="text/html" method="get" template="http://mycroft.mozdev.org/search-engines.html?name={searchTerms}"/> 
  <Contact>mycroft.mozdev.org@googlemail.com</Contact>
  <Image width="32" height="32">http://mycroft.mozdev.org/favicon.ico</Image>
  <Developer>Mycroft Project</Developer>
  <InputEncoding>UTF-8</InputEncoding>
  <moz:SearchForm>http://mycroft.mozdev.org/search-engines.html</moz:SearchForm>
  <moz:UpdateUrl>http://mycroft.mozdev.org/opensearch.xml</moz:UpdateUrl>
  <moz:IconUpdateUrl>http://mycroft.mozdev.org/favicon.ico</moz:IconUpdateUrl>
  <moz:UpdateInterval>7</moz:UpdateInterval>
</OpenSearchDescription>
XML;

    public $plugin_xml_2 = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.0/">
	<ShortName>Fnord</ShortName>
	<Description>Fnord, a search engine of discord</Description>
	<Url type="text/html" method="get" template="http://fnord.xxx/?s={searchTerms}"/>
	<Url type="application/x-suggestions+json" method="GET" template="http://fnord.xxx/?suggest={searchTerms}"/>
    <Url type="application/atom+xml" method="GET" template="http://fnord.xxx/atom.xml?q={searchTerms}"/>
	<Image width="16" height="16">http://fnord.xxx/favicon.ico</Image>
	<SearchForm>http://fnord.xxx/</SearchForm>
</OpenSearchDescription>
XML;

    public $plugin_xml_3 = <<<XML
<?xml version="1.0"?><OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" xmlns:moz="http://www.mozilla.org/2006/browser/search/"><ShortName>MozillaWiki (en)</ShortName><Description>MozillaWiki (en)</Description><Image height="16" width="16" type="image/x-icon">https://wiki.mozilla.org/favicon.ico</Image><Url type="text/html" method="get" template="https://wiki.mozilla.org/index.php?title=Special:Search&amp;search={searchTerms}" /><Url type="application/x-suggestions+json" method="get" template="https://wiki.mozilla.org/api.php?action=opensearch&amp;search={searchTerms}&amp;namespace=0|9|11|100|101|102|103|104|105|106|107|108|109|110|111|112|113|114|115|116|117|118|119" /><moz:SearchForm>https://wiki.mozilla.org/Special:Search</moz:SearchForm></OpenSearchDescription>
XML;

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
     * Exercise basic loading of search plugins.
     */
    public function testBasicLoading()
    {
        $p1 = Model::factory('searchplugin');
        $this->assertTrue(!$p1->loaded);

        $p1 = Model::factory('searchplugin')->loadFromXML('HI THERE NOT XML');
        $this->assertTrue(!$p1->loaded && !empty($p1->last_error),
            'Non-XML should be not loaded and have an error.');
        $this->assertTrue(FALSE !== strpos($p1->last_error, 'parser error'));

        $p1 = Model::factory('searchplugin')->loadFromXML('<BadDocument/>');
        $this->assertTrue(!$p1->loaded && !empty($p1->last_error), 
            'Bad document should be not loaded and have an error');
        $this->assertEquals('Not an OpenSearchDescription document', 
            $p1->last_error);

        $p1 = Model::factory('searchplugin')->loadFromXML($this->bad_plugin_xml_1);
        $this->assertTrue(!$p1->loaded && !empty($p1->last_error), 
            'bad_plugin_xml_1 should be not loaded and have an error');
        $this->assertEquals('Required element ShortName empty.', 
            $p1->last_error);

        $p1 = Model::factory('searchplugin')->loadFromXML($this->bad_plugin_xml_2);
        $this->assertTrue(!$p1->loaded && !empty($p1->last_error), 
            'bad_plugin_xml_2 should be not loaded and have an error');
        $this->assertEquals('Required element Description empty.', 
            $p1->last_error);

        $p1 = Model::factory('searchplugin')->loadFromXML($this->plugin_xml_1);
        $this->assertTrue($p1->loaded && empty($p1->last_error),
            'plugin_xml_1 should be loaded and without error.');
        $this->assertEquals('Mycroft Project', $p1->ShortName);
        $this->assertEquals(
            'Mycroft Project: Sherlock & OpenSearch Search Engine Plugins', 
            $p1->Description
        );

        $p1 = Model::factory('searchplugin')->loadFromXML($this->plugin_youtube);
        $this->assertTrue($p1->loaded && empty($p1->last_error),
            'plugin_youtube should be loaded and without error.');
        $this->assertEquals('YouTube Video Search', $p1->ShortName);
        $this->assertEquals(
            'Search for videos on YouTube', 
            $p1->Description
        );
    }

    /**
     * Exercise XML serialization by reparsing.
     */
    public function testAsXML()
    {
        $p1 = Model::factory('searchplugin')->loadFromXML($this->plugin_xml_1);

        $p2 = Model::factory('searchplugin')->loadFromXML($p1->asXML());
        $this->assertTrue($p2->loaded && empty($p2->last_error),
            'p1->asXML() should be loaded and without error.');
        $this->assertEquals('Mycroft Project', $p2->ShortName);
        $this->assertEquals(
            'Mycroft Project: Sherlock & OpenSearch Search Engine Plugins', 
            $p2->Description
        );
    }

    /**
     * Exercise URL extraction from a known plugin.
     */
    public function testUrls()
    {
        $p1 = Model::factory('searchplugin')->loadFromXML($this->plugin_xml_2);
        $this->assertTrue($p1->loaded && empty($p1->last_error),
            'plugin_xml_2 should be loaded and without error.');
        $this->assertEquals('Fnord', $p1->ShortName);
        $this->assertEquals('Fnord, a search engine of discord', $p1->Description);

        $this->assertEquals(
            array (
                array (
                    'type' => 'text/html',
                    'method' => 'get',
                    'template' => 'http://fnord.xxx/?s={searchTerms}',
                ),
                array (
                    'type' => 'application/x-suggestions+json',
                    'method' => 'GET',
                    'template' => 'http://fnord.xxx/?suggest={searchTerms}',
                ),
                array (
                    'type' => 'application/atom+xml',
                    'method' => 'GET',
                    'template' => 'http://fnord.xxx/atom.xml?q={searchTerms}',
                ),
            ),
            $p1->urls,
            "List of URLs should match expected for plugin_xml_2"
        );

        $p1 = Model::factory('searchplugin')->loadFromXML($this->plugin_xml_3);
        $this->assertTrue($p1->loaded && empty($p1->last_error),
            'plugin_xml_3 should be loaded and without error.');
        $this->assertEquals('MozillaWiki (en)', $p1->ShortName);
        $this->assertEquals('MozillaWiki (en)', $p1->Description);

        $this->assertEquals(
            array (
                0 => 
                array (
                    'type' => 'text/html',
                    'method' => 'get',
                    'template' => 'https://wiki.mozilla.org/index.php?title=Special:Search&search={searchTerms}',
                ),
                1 => 
                array (
                    'type' => 'application/x-suggestions+json',
                    'method' => 'get',
                    'template' => 'https://wiki.mozilla.org/api.php?action=opensearch&search={searchTerms}&namespace=0|9|11|100|101|102|103|104|105|106|107|108|109|110|111|112|113|114|115|116|117|118|119',
                ),
            ),
            $p1->urls,
            "List of URLs should match expected for plugin_xml_3"
        );

    }

    /**
     * Exercise getting an icon URL from the plugin.
     */
    public function testIconUrl()
    {
        $p1 = Model::factory('searchplugin')->loadFromXML($this->plugin_xml_1);
        $this->assertTrue($p1->loaded && empty($p1->last_error),
            'plugin_xml_2 should be loaded and without error.');

        $url = $p1->getIconURL();
        $this->assertTrue(empty($url),
            'plugin_xml_1 should not yield an icon URL.');

        $p1 = Model::factory('searchplugin')->loadFromXML($this->plugin_xml_2);
        $this->assertTrue($p1->loaded && empty($p1->last_error),
            'plugin_xml_2 should be loaded and without error.');

        $this->assertEquals('http://fnord.xxx/favicon.ico', $p1->getIconURL(),
            'plugin_xml_2 should yield the expected icon URL');
    }

}
