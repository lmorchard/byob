<!DOCTYPE HTML>
<html xml:lang="en-US" lang="en-US" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Welcome to Firefox</title>
    <script type="text/javascript" src="http://www.mozilla.com/js/util.js"></script>
    <link rel="stylesheet" type="text/css" href="http://www.mozilla.com/includes/yui/2.5.1/reset-fonts-grids/reset-fonts-grids.css" />
    <link rel="stylesheet" type="text/css" href="http://mozcom-cdn.mozilla.net/style/tignish/template.css" media="screen" />
    <script type="text/javascript" src="http://www.mozilla.com/includes/yui/2.5.1/yahoo-dom-event/yahoo-dom-event.js"></script>

    <script type="text/javascript" src="http://www.mozilla.com/includes/yui/2.5.1/container/container_core-min.js"></script>
        <style type="text/css">
        /* MetaWebPro font family licensed from fontshop.com. WOFF-FTW! */
        /*
        @font-face { font-family: 'MetaWebPro-Book'; src: url('<?=url::base()?>img/fonts/MetaWebPro-Book.woff') format('woff'); }
        @font-face { font-family: 'MetaWebPro-Bold'; src: url('<?=url::base()?>img/fonts/MetaWebPro-Bold.woff') format('woff'); }
        */
    </style>
    <link rel="stylesheet" type="text/css" href="http://mozcom-cdn.mozilla.net/style/firefox/3.6/firstrun-page.css" media="screen" />
    <script type="text/javascript" src="http://mozcom-cdn.mozilla.net/js/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="http://mozcom-cdn.mozilla.net/includes/yui/2.5.1/animation/animation-min.js"></script>
    <script type="text/javascript" src="http://mozcom-cdn.mozilla.net/js/mozilla-expanders.js"></script>

    <script type="text/javascript" src="http://mozcom-cdn.mozilla.net/js/mozilla-input-placeholder.js"></script>

    <link rel="stylesheet" type="text/css" href="<?=url::base()?>css/firstrun.css" media="screen" />

</head>

<body id="firstrun" class=" locale-en-US portal-page">
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
<script type="text/javascript">// <![CDATA[
    // Add a class to the body tag to alternate promo features
        var class_options = new Array( 
            <?php if ($repack->addons_collection_url): ?>
                "default"
            <?php else: ?>
                "default", "stumbleupon", "thunderbird", "reminderfox", "rockyourfirefox" 
            <?php endif ?>
        );

    if (Math.random) {
        var choice = Math.floor(Math.random() * (class_options.length));

        // Just in case javascript gets carried away...
        choice = ( (choice < class_options.length)  && choice >= 0) ? choice : 0;

		if (document.body.className == '') {
			document.body.className = class_options[choice];
		} else {
			document.body.className += ' '+class_options[choice];
		}
    }
// ]]></script>

<div id="main-feature">
    <h2><img src="http://www.mozilla.com/img/firefox/3.6/firstrun/title.png" alt="Firefox 3.6" id="title-logo" /></h2>

    <p>Thanks for supporting <a href="http://www.mozilla.com/en-US/about/whatismozilla.html">Mozilla’s mission</a> of encouraging openness, innovation and opportunity on the Web!</p>
</div>

<div id="main-content">

    <div id="personas" class="expander expander-first expander-default-open expander-group-1">
    	<h3 class="expander-header">Choose Your Persona</h3>
    	<div class="expander-content">

			<iframe
				src="http://www.getpersonas.com/en-US/external/mozilla/firstrun.php?ver=2"
				width="480"
				height="125">
				</iframe>
			<p id="try">Roll over to try, click to apply</p>
			<ul id="personas-link" class="link"><li><a href="http://www.getpersonas.com/gallery?source=moz.com_firstrun">See all 60,000+</a></li></ul>
    	</div>
	</div>

    <div id="addons" class="expander expander-group-1">
    	<h3 class="expander-header">More ways to personalize</h3>

    	<div class="expander-content">
			<p>Add-ons are easy-to-install extras that help personalize your Firefox. Here are a few of our favorites:</p>
			<ul id="addons-list">
				<li>
					<div>
						<h4><a href="https://addons.mozilla.org/en-US/firefox/addon/2677/?src=fxfirstrun" class="icon">
							<img src="https://addons.mozilla.org/en-US/firefox/images/addon_icon/2677/1235159442" alt="Morning Coffee icon" />
							Morning Coffee</a></h4>

						<p>Keeps track of daily routine websites in tabs...</p>
						<span title="Rated 5 out of 5 stars" class="stars stars-5">Rated 5 out of 5 stars</span>
					</div>
					<a class="button" href="https://addons.mozilla.org/en-US/firefox/addon/2677/?src=fxfirstrun">Add to Firefox</a>
				</li>
				<li>
					<div>

						<h4><a href="https://addons.mozilla.org/en-US/firefox/addon/11377/?src=fxfirstrun" class="icon">
							<img src="https://addons.mozilla.org/en-US/firefox/images/addon_icon/11377/1270005648" alt="InvisibleHand icon" />
							InvisibleHand</a></h4>
						<p>Shows a notification when a better price is available...</p>
						<span title="Rated 4 out of 5 stars" class="stars stars-4">Rated 4 out of 5 stars</span>
					</div>
					<a class="button" href="https://addons.mozilla.org/en-US/firefox/addon/11377/?src=fxfirstrun">Add to Firefox</a>

				</li>
				<li>
					<div>
						<h4><a href="https://addons.mozilla.org/en-US/firefox/addon/1146/?src=fxfirstrun" class="icon">
							<img src="https://addons.mozilla.org/en-US/firefox/images/addon_icon/1146/1269548420" alt="Screengrab icon" />
							Screengrab</a></h4>
						<p>Screengrab! saves webpages as images...</p>
						<span title="Rated 4 out of 5 stars" class="stars stars-5">Rated 5 out of 5 stars</span>

					</div>
					<a class="button" href="https://addons.mozilla.org/en-US/firefox/addon/1146/?src=fxfirstrun">Add to Firefox</a>
				</li>
			</ul>
			<ul id="addons-link" class="link"><li><a href="https://addons.mozilla.org/en-US/firefox/?src=fxfirstrun">Browse More Add-ons</a></li></ul>
    	</div>
	</div>

    <div id="support" class="expander expander-last expander-group-1">
    	<h3 class="expander-header">Need help?</h3>
    	<div class="expander-content">
			<p>Search our support pages for answers and advice about using Firefox.</p>
			<form action="http://support.mozilla.com/search.php">
				<input type="hidden" name="where" value="all" />
				<input type="hidden" name="locale" value="en-US" />
				<input id="fsearch-new" name="q" type="text" value="" placeholder="Search" /><input class="btn-large" id="searchsubmit-new" type="submit" name="sa" value="" title="Search" />

			</form>
			<ul id="support-link" class="link"><li><a href="http://support.mozilla.com/">Visit our Support website</a></li></ul>
    	</div>
	</div>

</div>

<div id="sidebar">

    <div class="sub-feature" id="connect">
        <h3>Stay Connected</h3>

        <ul class="link">
            <li id="connect-twitter"><a href="http://twitter.com/firefox">Follow us on Twitter</a></li>
            <li id="connect-facebook"><a href="http://www.facebook.com/Firefox">Become a Fan on Facebook</a></li>
            <li id="connect-blog"><a href="http://blog.mozilla.com/">Read our Blog</a></li>
        </ul>
    </div>

    <div class="sub-feature" id="personalize">

        <?php if ($repack->addons_collection_url): ?>
            <?php
            $h = html::escape_array(array(
                'collection_url' => $repack->addons_collection_url
            ));
            ?>
            <div id="default">
                <h3>More Ways to Personalize</h3>
                <p>Adapt Firefox to the way you browse with this collection of suggested add-ons.</p>
                <ul class="link"><li><a href="<?= $h['collection_url'] ?>">Explore Add-ons</a></li></ul>
            </div>
        <?php else: ?>
            <div id="default">
                <h3>More Ways to Personalize</h3>
                <p>Adapt Firefox to the way you browse with 1,000s of free add-ons.</p>
                <ul class="link"><li><a href="https://addons.mozilla.org/en-US/firefox/?src=fxfirstrun">Explore Add-ons</a></li></ul>
            </div>
            <div id="personalize-stumbleupon">
                <h3>StumbleUpon Firefox Add-on</h3>

                <p>Explore the web like never before.</p>
                <ul class="link"><li><a href="http://www.mozilla.com/en-US/firefox/3.6/stumbleupon/">Install StumbleUpon Add-on</a></li></ul>
            </div>
            <div id="personalize-thunderbird">
                <h3>New Thunderbird 3</h3>
                <p>Fast, flexible, and secure email program</p>
                <ul class="link"><li><a href="http://getthunderbird.com/">Download Thunderbird</a></li></ul>

            </div>
            <div id="personalize-reminderfox">
                <h3>ReminderFox Firefox Add-on</h3>
                <p>ReminderFox remembers things so that you don’t have to!</p>
                <ul class="link"><li><a href="http://www.mozilla.com/en-US/firefox/3.6/reminderfox/">Install ReminderFox Add-on</a></li></ul>
            </div>
            <div id="personalize-rockyourfirefox">

                <h3><a href="http://www.rockyourfirefox.com/"><img src="http://www.mozilla.com/img/firefox/3.6/firstrun/rockyourfirefox/title.png" alt="Rock Your Firefox" /></a></h3>
                <p>Discover new add-ons to brighten your day.</p>
                <ul class="link"><li><a href="http://www.rockyourfirefox.com/">View Featured Add-ons</a></li></ul>
            </div>
        <?php endif ?>
    </div>

</div>


    </div><!-- end #doc -->

    </div><!-- end #wrapper -->

<!--
    <script type="text/javascript" src="http://www.mozilla.com/includes/min/min.js?g=js_stats"></script>
<script type="text/javascript">
//<![CDATA[
var _tag=new WebTrends({"dcsid":"dcst2y3n900000gohmphe66rf_3o6x","rate":5,"fpcdom":"mozilla.com"});
_tag.dcsGetId();
//]]>
</script>
<script type="text/javascript">
//<![CDATA[
_tag.dcsCollect();
//]]>
</script>
<noscript>
<div><img alt="DCSIMG" id="DCSIMG" width="1" height="1" src="http://statse.webtrendslive.com/dcso6de4r0000082npfcmh4rf_4b1e/njs.gif?dcsuri=/nojavascript&amp;WT.js=No&amp;WT.tv=8.6.2"/></div>
</noscript>
    
-->
</body>
</html>
