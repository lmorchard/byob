<!DOCTYPE HTML>
<html xml:lang="en-US" lang="en-US" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Welcome to Firefox</title>
    <script type="text/javascript" src="http://www.mozilla.com/js/util.js"></script>
    <link rel="stylesheet" type="text/css" href="http://www.mozilla.com/includes/yui/2.5.1/reset-fonts-grids/reset-fonts-grids.css" />
    <link rel="stylesheet" type="text/css" href="http://www.mozilla.com/style/tignish/template.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="http://www.mozilla.com/style/tignish/content.css" media="screen" />

    <script type="text/javascript" src="http://www.mozilla.com/includes/yui/2.5.1/yahoo-dom-event/yahoo-dom-event.js"></script>
    <script type="text/javascript" src="http://www.mozilla.com/includes/yui/2.5.1/container/container_core-min.js"></script>
    <link rel="stylesheet" type="text/css" href="http://www.mozilla.com/style/tignish/portal-page.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="http://www.mozilla.com/style/tignish/firstrun-page-3-5.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="http://www.mozilla.com/style/tignish/video-player.css" media="screen" />
    <script type="text/javascript" src="http://www.mozilla.com/includes/yui/2.5.1/utilities/utilities.js"></script>
    <script type="text/javascript" src="http://www.mozilla.com/js/mozilla-video-tools.js"></script>

    <script type="text/javascript">
    // <![CDATA[
        Mozilla.VideoPlayer.close_text = 'Close';
        Mozilla.VideoScaler.close_text = 'Close';
    // ]]>
    </script>

    <link rel="stylesheet" type="text/css" href="<?=url::base()?>css/firstrun.css" media="screen" />

</head>

<body id="firstrun" class=" locale-en-US portal-page">
<!-- SiteCatalyst Reporting -->
<script type="text/javascript">s_account="mozillacom";</script>
 <script src="http://www.mozilla.com/js/s_code.js" type="text/javascript"></script>
<script type="text/javascript">// <![CDATA[
if (document.body.className == '') {
    document.body.className = 'js';
} else {
    document.body.className += ' js';
}

if (gPlatform == 1) {
    document.body.className += ' platform-windows';
} else if (gPlatform == 3 || gPlatform == 4) {
    document.body.className += ' platform-mac';
} else if (gPlatform == 2) {
    document.body.className += ' platform-linux';
}

// ]]></script>

<div id="wrapper">

<div id="doc">

    <div id="nav-access">
        <a href="#nav-main">skip to navigation</a>
        <a href="#switch">switch language</a>
    </div>

    <!-- start #header -->
    <div id="header">

        <div>
        <h1><a href="http://www.mozilla.com/en-US/" title="Back to home page"><img src="http://www.mozilla.com/img/tignish/template/mozilla-logo.png" height="56" width="145" alt="Mozilla" /></a></h1>
        <a href="http://www.mozilla.com/en-US/" id="return">Visit Mozilla.com</a>
        </div>
        <hr class="hide" />
    </div>
    <!-- end #header -->


    <div id="main-feature"<?= ($repack->addons_collection_url) ? ' class="with-collection"' : '' ?>>
    <h2><span>Welcome to</span> <img src="http://www.mozilla.com/en-US/img/tignish/firstrun/welcome-3-5.png" alt="Welcome to Firefox 3.5" id="main-title" /></h2>
    <p>Thanks for supporting <a href="http://www.mozilla.com/en-US/about/whatismozilla.html">Mozilla's mission</a> of encouraging openness, innovation and opportunity on the Web!</p>
</div>

<?php if ($repack->addons_collection_url): ?>
<?php
if ($repack->profile->is_personal) {
    $org_name = $repack->profile->first_name . ' ' . $repack->profile->last_name;
} else {
    $org_name = $repack->profile->org_name;
}
$h = html::escape_array(array(
    'org_name' => $org_name,
    'url'      => $repack->addons_collection_url
));
?>
<div id="collection">
    <img class="logo" src="https://addons.mozilla.org/img/amo2009/illustrations/logo-collections-100x125.png" />
    <h4>Your customized collection of addons</h4>
    <p>
        <strong><?=$h['org_name']?></strong> has recommended some
        add-ons for you to install. 
        <a href="<?=$h['url']?>">Check them out &raquo;</a>
    </p>
    <p>
        What are add-ons? Add-ons extend and add features to Firefox.
        <a href="https://addons.mozilla.org/">Find out more &raquo;</a>
    </p>
    <div class="checkoutcollection"><a href="<?=$h['url']?>">Check out the collection &raquo;</a></div>
</div>
<?php endif ?>

<div id="main-content">
<div id="sub-features">
    <div class="sub-feature" id="open-video">

        <div class="mozilla-video-scaler">
            <div class="mozilla-video-control">
                 <video id="video" src="http://www.dailymotion.com/cdn/OGG-320x240/video/x9euyb?key=a99e7056808342ad0868b4decfe811c814044ec"></video>
            </div>
        </div>
        <h3>Watch This!</h3>
        <p>Firefox 3.5 is the first browser to support open video formats, allowing movies to become part of today’s dynamic web pages without requiring a plug-in. Go ahead – give it a try.</p>

<div id="thanks">This video brought to you by <a href="http://openvideo.dailymotion.com/">Dailymotion</a>, proud supporters of open video.</div>
    </div>
    <div class="sub-feature" id="sumo">
        <h3>Need Help?</h3>
        <p class="first">Our Support site has plenty of answers, plus a live chat feature to guide you through any tricky spots.</p>
        <p><a href="http://support.mozilla.com/" class="blocklevel">Visit Firefox Support</a></p>

    </div>
    <div class="sub-feature" id="addons">
        <h3>Time to Get Personal</h3>
        <p class="first">There are thousands of totally free ways to customize your Firefox to fit exactly what you like to do online.</p>
        <p><a href="https://addons.mozilla.org" class="blocklevel">Explore Add-ons</a></p>
    </div>
    <div class="clear"></div>

</div>

<p id="follow">Stay connected with Firefox on <a href="http://twitter.com/firefox">Twitter</a> and <a href="http://www.facebook.com/Firefox">Facebook</a></p>

</div>



        

    </div><!-- end #doc -->
    </div><!-- end #wrapper -->

    <!-- start #footer -->
    <div id="footer">
    <div id="footer-contents">
    <div id="copyright">
        <p><strong>Copyright &#169; 2005&#8211;2009 Mozilla.</strong> All rights reserved.</p>

        <p id="footer-links"><a href="http://www.mozilla.com/en-US/privacy-policy.html">Privacy Policy</a> &nbsp;|&nbsp;
        <a href="http://www.mozilla.com/en-US/about/legal.html">Legal Notices</a></p>
    </div>
    </div>
    </div>

    <!-- end #footer -->

    <script type="text/javascript">
    /* <![CDATA[ */
        var re_whatsnew = new RegExp('whatsnew');
        if ( !re_whatsnew.test(location.pathname)
                || (new Date()).getSeconds() < 15 ) {
            var s_code=s.t();if(s_code)document.write(s_code);
        }
    /* ]]> */
    </script>
    <!-- end SiteCatalyst code version: H.14 -->
    <script src="http://www.mozilla.com/js/__utm.js" type="text/javascript"></script>
    <script src="http://www.mozilla.com/js/track.js" type="text/javascript"></script>
    

</body>
</html>
