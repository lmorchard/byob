<?php
/**
 * Configured known products available for customized repack
 */

// Product data extracted from a MySQL table
$column_names = array('id', 'name', 'version', 'url', 'locales', 'disable_migration', 'created', 'modified');
$rows = array(
    array(1,'Firefox','2.0.0.2','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.2-candidates/rc5','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ru sk sl sv-SE tr zh-CN zh-TW',1,'2007-04-11 18:17:04','2007-04-11 18:17:04'),
    array(2,'Firefox','2.0.0.3','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.3-candidates/rc1/','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ru sk sl sv-SE tr zh-CN zh-TW',1,'2007-04-11 18:17:06','2007-04-11 18:17:06'),
    array(3,'Firefox','2.0.0.4','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.4-candidates/rc3/','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr zh-CN zh-TW',0,'2007-06-20 20:44:20','2007-06-20 20:44:20'),
    array(4,'Firefox','2.0.0.5','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.5-candidates/rc2/','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr zh-CN zh-TW',0,'2007-07-18 15:42:46','2007-07-18 15:42:46'),
    array(5,'Firefox','2.0.0.6','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.6-candidates/rc2/','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr zh-CN zh-TW',0,'2007-08-01 16:06:05','2007-08-01 16:06:05'),
    array(6,'Firefox','2.0.0.7','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.7-candidates/rc2/','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr zh-CN zh-TW',0,'2007-09-19 14:32:42','2007-09-19 14:32:42'),
    array(7,'Firefox','2.0.0.9','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.9-candidates/rc1/','af ar bg be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr zh-CN zh-TW',0,'2007-11-02 17:26:34','2007-11-02 17:26:34'),
    array(8,'Firefox','2.0.0.11','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.11-candidates/rc1/','af ar be bg ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr uk zh-CN zh-TW',0,'2007-12-03 09:23:54','2007-12-03 09:23:54'),
    array(9,'Firefox','2.0.0.12','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.12-candidates/rc4/','af ar be bg ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr uk zh-CN zh-TW',0,'2008-02-08 05:44:11','2008-02-08 05:44:11'),
    array(10,'Firefox','2.0.0.13','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.13-candidates/rc1/','af ar be bg ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr uk zh-CN zh-TW',0,'2008-03-25 10:38:46','2008-03-25 10:38:46'),
    array(11,'Firefox','2.0.0.14','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.14-candidates/rc1/','af ar be bg ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr uk zh-CN zh-TW',0,'2008-04-16 07:21:15','2008-04-16 07:21:15'),
    array(12,'Firefox RC3','3.0','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/3.0rc3-candidates/build1/','af ar be ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu id it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru si sk sl sq sr sv-SE tr uk zh-CN zh-TW',0,'2008-06-12 09:06:27','2008-06-12 09:06:27'),
    array(13,'Firefox','2.0.0.16','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.16-candidates/build1/','af ar be bg ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr uk zh-CN zh-TW',0,'2008-08-21 06:39:23','2008-08-21 06:39:23'),
    array(14,'Firefox','2.0.0.19','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/2.0.0.19-candidates/build2/','af ar be bg ca cs da de el en-GB en-US es-AR es-ES eu fi fr fy-NL ga-IE gu-IN he hu it ja-JP-mac ja ka ko ku lt mk mn nb-NO nl nn-NO pa-IN pl pt-BR pt-PT ro ru sk sl sv-SE tr uk zh-CN zh-TW',0,'2008-12-29 10:55:52','2008-12-29 10:55:52'),
    array(15,'Firefox','3.0.5','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/3.0.5-candidates/build1/','af ar be bg bn-IN ca cs cy da de el en-GB en-US eo es-AR es-ES et eu fi fr fy-NL ga-IE gl gu-IN he hi-IN hu id is it ja-JP-mac ja ka kn ko ku lt lv mk mn mr nb-NO nl nn-NO oc pa-IN pl pt-BR pt-PT ro ru si sk sl sq sr sv-SE te th tr uk zh-CN zh-TW',0,'2008-12-29 11:05:50','2008-12-29 11:05:50'),
    array(16,'Firefox','3.0.7','http://stage.mozilla.org/pub/mozilla.org/firefox/nightly/3.0.7-candidates/build2/','af ar be bg bn-IN ca cs cy da de el en-GB en-US eo es-AR es-ES et eu fi fr fy-NL ga-IE gl gu-IN he hi-IN hu id is it ja-JP-mac ja ka kn ko ku lt lv mk mn mr nb-NO nl nn-NO oc pa-IN pl pt-BR pt-PT ro ru si sk sl sq sr sv-SE te th tr uk zh-CN zh-TW',0,'2009-02-20 06:51:00','2009-02-20 06:51:00')
);

// Convert flat arrays of product data into associative arrays using the list 
// of fields.
$products = array();
foreach ($rows as $row) {
    $product = array();
    foreach ($column_names as $idx=>$name) 
        $product[$name] = $row[$idx];
    if (!is_array($product['locales']))
        $product['locales'] = 
            explode(' ', $product['locales']);
    $products[$product['id']] = $product;
    $config['latest_product'] = $product;
}

// Finally, shove the results of all the above into the configuration.
$config['all_products'] = $products;
