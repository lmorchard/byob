<?php
    if (authprofiles::is_logged_in()) {
        $create_url = url::site("profiles/" . 
            authprofiles::get_profile('screen_name'));
    } else {
        $create_url = url::site('register');
    }
?>
<?php slot::set('head_title', 'home') ?>
<?php slot::set('crumbs', 'home') ?>

<div class="page_intro clearfix">

    <div class="blurb">
        <h3><?=_('Make Firefox Yours')?></h3>
        <p>
            <?=_('<em>Build Your Own Browser (BYOB)</em> is a simple way that your organization can create and distribute a customized version of Firefox')?>
        </p>
        <a class="create_browser yellow button" href="<?=$create_url?>">
            <?=_('<span class="first_line"><strong>Create Your Browser</strong> (Free!)</span>
                    <span class="second_line">It only takes a minute.</span>')?>
        </a>
        <p class="faq">
            <?=_('Got Questions?')?>
            <a href="faq"><?=_('Check out our FAQ')?></a>
        </p>

    </div>

    <div class="video">
        <video id="video" width="430" height="242" controls="controls">

            <source src="http://videos-cdn.mozilla.net/firefox/3.6/whatsnewin36.ogv" type="video/ogg; codecs=&quot;theora, vorbis&quot;" />

            <source src="http://videos-cdn.mozilla.net/firefox/3.6/whatsnewin36.mp4" type="video/mp4" />

            <object type="application/x-shockwave-flash"
                style="width: 430px; height: 242;"
                data="http://www.mozilla.com/includes/flash/playerWithControls.swf?flv=firefox/3.6/whatsnewin36.mp4&amp;autoplay=false&amp;msg=Play%20Video">

                <param name="movie" value="http://www.mozilla.com/includes/flash/playerWithControls.swf?flv=firefox/3.6/whatsnewin36.mp4&amp;autoplay=false&amp;msg=Play%20Video" />
                <param name="wmode" value="transparent" />

                <div class="video-player-no-flash">
                <?=_('This video requires a browser with support for open video:')?>
                <ul>
                <li><?=_('<a href="http://www.mozilla.com/firefox/">Firefox</a> 3.5 or greater')?></li>
                <li><?=_('<a href="http://www.apple.com/safari/">Safari</a> 3.1 or greater')?></li>
                </ul>
                <?=_('or the <a href="http://www.adobe.com/go/getflashplayer">Adobe Flash Player</a>.')?>
                <?=_('Alternatively, you may use the video download links to the right.')?> 
                </div>
            </object>

        </video>
    </div>

</div>

<div class="benefits clearfix">

    <div class="your_benefits">
        <h3><?=_('How can BYOB benefit your organization?')?></h3>
        <ul>
            <li><h4><?=_('By connecting your affinity group to you.')?></h4>
                <p><?=_('Complement your online presence with a customized version of Firefox, connecting the people you serve with the information they need.')?></p>
            </li>
            <li><h4><?=_('By letting you show your stripes.')?></h4>
                <p><?=_('Add your identity to Firefox with a Persona, bookmarks, and add-ons.')?></p>
            </li>
            <li><h4><?=_('It supports multiple languages and operating systems.')?></h4>
                <p><?=_('Your version of Firefox is available in over 60 languages on Linux, Mac, and Windows. No one\'s left behind.')?></p>
            </li>
            <li><h4><?=_('It\'s free to use and distribute.')?></h4>
                <p><?=_('We provide the tools for you to create your own browser with no development, maintenance, or hosting costs.')?></p>
            </li>
        </ul>
    </div>

    <div class="who_benefits">
        <h3><?=_('Who can this help?')?></h3>
        <ul>
            <li>
                <img src="<?=url::base()?>img/who-globe.png" />
                <h4><?=_('Organizations.')?></h4>
                <p><?=_('Highlighting a product, service or information about your offerings? Use BYOB to create a browser that connects your users to you out of the box.')?></p>
            </li>
            <li>
                <img src="<?=url::base()?>img/who-building.png" />
                <h4><?=_('Social Groups.')?></h4>
                <p><?=_('Want people to know what useful sources and sites are available for your running group, gaming clan, club, or other like-minded people? Share them with a version of Firefox that\'s customized for your common interests.')?></p>
            </li>
            <li>
                <img src="<?=url::base()?>img/who-people.png" />
                <h4><?=_('Friends and Family.')?></h4>
                <p><?=_('Want to easily share the best of the web with the people you care about? Give them a browser that puts everything a click away, without them having to hunt for it.')?></p> </li>
        </ul>
    </div>

</div>

<div class="showcase clearfix">

    <div class="recent_browsers">
        <h3><?=_('What browsers are others making?')?></h3>

        <ul class="browsers">
            <?php foreach ($latest_repacks as $idx=>$repack): ?>
                <?php
                    $h = html::escape_array(array(
                        'url'         => $repack->url,
                        'title'       => $repack->title,
                        'description' => $repack->description,
                        'screen_name' => $repack->profile->screen_name,
                        'first_name'  => $repack->profile->first_name,
                        'last_name'   => $repack->profile->last_name,
                        'modified'    => 
                            date('D, M d', strtotime($repack->modified)),
                        'profile_url' =>
                            url::site() . 'profiles/' . $repack->profile->screen_name,
                    ));
                ?>
                    <li class="browser <?= ($idx%2)==0 ? 'even' : 'odd' ?>">
                    <h4><a href="<?=$h['url']?>"><?=$h['title']?></a></h4>
                    <p class="meta">
                        <span class="byline">
                            <?php /*i18n: %1$s = Profile URL; %2$s = Profile display name */ ?>
                            <?=sprintf(_('Created by <a href="%1$s">%2$s</a>'),
                                $h['profile_url'], $h['first_name'] . ' ' . $h['last_name'])?>
                        </span>
                        <span class="modified">on <?=$h['modified']?></span>
                    </p>
                </li>
            <?php endforeach ?>
        </ul>

    </div>

    <?php /*
    <div class="whos_using strip">
        <h3>Who's using BYOB?</h3>
        <ul class="whos_who viewport">
            <li id="who_1" class="selected">
                <img src="<?=url::site()?>img/who-using-icon.png" />
                <p>The <a href="#">Featured Browser #1</a>
                lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Proin et massa nisi. Aliquam nec ornare nisl. Morbi sit amet
                enim nulla, quis ultricies nisi. Nullam ut lacus
                in erat nec ornare nisl ipsum morbi elit.</p>
            </li>
            <li id="who_2">
                <img src="<?=url::site()?>img/who-using-icon.png" />
                <p>The <a href="#">Featured Browser #2</a>
                lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Proin et massa nisi. Aliquam nec ornare nisl. Morbi sit amet
                enim nulla, quis ultricies nisi. Nullam ut lacus
                in erat nec ornare nisl ipsum morbi elit.</p>
            </li>
            <li id="who_3">
                <img src="<?=url::site()?>img/who-using-icon.png" />
                <p>The <a href="#">Featured Browser #3</a>
                lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Proin et massa nisi. Aliquam nec ornare nisl. Morbi sit amet
                enim nulla, quis ultricies nisi. Nullam ut lacus
                in erat nec ornare nisl ipsum morbi elit.</p>
            </li>
            <li id="who_4">
                <img src="<?=url::site()?>img/who-using-icon.png" />
                <p>The <a href="#">Featured Browser #4</a>
                lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Proin et massa nisi. Aliquam nec ornare nisl. Morbi sit amet
                enim nulla, quis ultricies nisi. Nullam ut lacus
                in erat nec ornare nisl ipsum morbi elit.</p>
            </li>
            <li id="who_5">
                <img src="<?=url::site()?>img/who-using-icon.png" />
                <p>The <a href="#">Featured Browser #5</a>
                lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Proin et massa nisi. Aliquam nec ornare nisl. Morbi sit amet
                enim nulla, quis ultricies nisi. Nullam ut lacus
                in erat nec ornare nisl ipsum morbi elit.</p>
            </li>
        </ul>
        <div class="pagination">
            <ul>
                <li><a href="#" class="prev">&#9664;</a></li> 
                <li><a href="#who_1" class="page selected">&#9679;</a></li> 
                <li><a href="#who_2" class="page">&#9679;</a></li> 
                <li><a href="#who_3" class="page">&#9679;</a></li> 
                <li><a href="#who_4" class="page">&#9679;</a></li> 
                <li><a href="#who_5" class="page">&#9679;</a></li> 
                <li><a href="#" class="next">&#9654;</a></li> 
            </ul>
        </div>
    </div>
    */ ?>


</div>

<div class="action_call_footer">
    <h3><?=_('Ready to start building a browser of your own?')?></h3>
    <a class="create_browser yellow button" href="<?=$create_url?>">
        <?=_('<span class="first_line"><strong>Create Your Browser</strong> (Free!)</span>
                <span class="second_line">It only takes a minute.</span>')?>
    </a>
</div>
