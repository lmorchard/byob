<?php
    if (authprofiles::is_logged_in()) {
        $create_url = url::base() . "profiles/" . 
            authprofiles::get_profile('screen_name');
    } else {
        $create_url = url::base().'register';
    }
?>
<?php slot::set('head_title', 'home') ?>
<?php slot::set('crumbs', 'home') ?>

<div class="page_intro clearfix">

    <div class="blurb">
        <h3>Make Firefox Yours</h3>
        <p>
            <em>Build Your Own Browser (BYOB)</em> is a simple way
            that your organization can create and distribute a
            customized version of Firefox
        </p>
        <a class="create_browser yellow button" href="<?=$create_url?>">
            <span class="first_line"><strong>Create Your Browser</strong> (Free!)</span>
            <span class="second_line">It only takes a minute.</span>
        </a>
        <p class="faq">
            Got Questions?
            <a href="faq">Check out our FAQ</a>
        </p>

    </div>

    <div class="video">
        <img src="img/page-intro-video.png" />
    </div>

</div>

<div class="benefits clearfix">

    <div class="your_benefits">
        <h3>How can BYOB benefit your organization?</h3>
        <ul>
            <li><h4>By connecting your affinity group to you.</h4>
                <p>Complement your online presence with a customized version of Firefox,
                    connecting the people you serve with the information they need.</p>
            </li>
            <li><h4>By letting you show your stripes.</h4>
                <p>Add your identity to Firefox with a Persona, bookmarks, and add-ons.</p>
            </li>
            <li><h4>It supports multiple languages and operating systems.</h4>
                <p>Your version of Firefox is available in over 70 languages on Linux,
                    Mac, and Windows. No one's left behind.</p>
            </li>
            <li><h4>It's free to use and distribute.</h4>
                <p>We provide the tools for you to create your own browser with no 
                    development, maintenance, or hosting costs.</p>
            </li>
        </ul>
    </div>

    <div class="who_benefits">
        <h3>Who can this help?</h3>
        <ul>
            <li><h4>Organizations.</h4>
                <p>Highlighting a product, service or information about your 
                    offerings? Use BYOB to create a browser that connects your users 
                    to you out of the box.</p>
            </li>
            <li><h4>Social Groups.</h4>
                <p>Want people to know what useful sources and sites are 
                    available for your running group, gaming clan, club, or other 
                    like-minded people? Share them with a version of Firefox that's
                    customized for your common interests.</p>
            </li>
            <li><h4>Friends and Family.</h4>
                <p>Want to easily share the best of the web with the people you care 
                    about? Give them a browser that puts everything a clich away, without
                    them having to hunt for it.</p>
            </li>
        </ul>
    </div>

</div>

<div class="showcase clearfix">

    <div class="recent_browsers">
        <h3>What browsers are others making?</h3>

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
                            url::base() . 'profiles/' . $repack->profile->screen_name,
                    ));
                ?>
                    <li class="browser <?= ($idx%2)==0 ? 'even' : 'odd' ?>">
                    <h4><a href="<?=$h['url']?>"><?=$h['title']?></a></h4>
                    <p class="meta">
                        <span class="byline">
                            Created by 
                            <a href="<?=$h['profile_url']?>"><?=$h['first_name'] . ' ' . $h['last_name']?></a>
                        </span>
                        <span class="modified">on <?=$h['modified']?></span>
                    </p>
                </li>
            <?php endforeach ?>
        </ul>

    </div>

    <div class="whos_using">
    </div>

</div>

<div class="action_call_footer">
    <h3>Ready to start building a browser of your own?</h3>
    <a class="create_browser yellow button" href="<?=$create_url?>">
        <span class="first_line"><strong>Create Your Browser</strong> (Free!)</span>
        <span class="second_line">It only takes a minute.</span>
    </a>
</div>
