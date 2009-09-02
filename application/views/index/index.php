<?php slot::set('head_title', 'home') ?>
<?php slot::set('crumbs', 'home') ?>

<div class="page_intro">
    <div class="page_intro_content">
        <h3><span>Customize Firefox</span></h3>

        <img src="<?=url::base() . 'img/home-page-intro-screenshot.jpg'?>" 
            alt="Example Firefox screenshot"
            class="screenshot" width="444" height="307" />

        <p>
            Welcome to Build Your Own Browser (BYOB), the Mozilla web 
            application that lets you create and distribute customized versions 
            of Firefox.
        </p>

        <p>
            Customizations you can make with BYOB include:
        </p>

        <ul>
            <li>a browser skin/theme using a bundled <a target="_new" href="http://getpersonas.com">Persona</a></li>
            <li>pre-populated bookmarks and RSS feeds</li>
            <li>links to a <a target="_new" href="https://addons.mozilla.org/en-US/firefox/collections/">Collection</a> of your recommended add-ons </li>
            <li>multiple locales for Windows, OSX, and Linux</li>
        </ul>

        <h4 class="get_started">
            <?php if (authprofiles::is_logged_in()): ?>
                <?php
                    $profile_url = url::base() .
                        "profiles/".authprofiles::get_profile('screen_name');
                ?>
                <a href="<?=$profile_url?>">Get Started!</a>
            <?php else: ?>
                <a href="<?= url::base().'register' ?>">Get Started!</a>
            <?php endif ?>
        </h4>

    </div>
</div>

<div class="sidebar">
    <a href="http://labs.mozilla.com/contests/extendfirefox3.5/"><img src="<?=url::base() . 'img/EXTEND_160x280.png'?>" 
        alt="Extend Firefox 3.5: Make the next great user experience!"
        width="160" height="280" /></a>
</div>

<div class="recent_browsers white_box">
    <div class="header"><h3>Recently created browsers</h3></div>

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
                        date('M d Y', strtotime($repack->modified)),
                    'profile_url' =>
                        url::base() . 'profiles/' . $repack->profile->screen_name,
                ));
            ?>
                <li class="browser <?= ($idx%2)==0 ? 'even' : 'odd' ?>">
                <h4><a href="<?=$h['url']?>"><?=$h['title']?></a></h4>
                <p class="meta">
                    <span class="byline">
                        created by 
                        <a href="<?=$h['profile_url']?>"><?=$h['first_name'] . ' ' . $h['last_name']?></a>
                    </span>
                    <span class="modified"><?=$h['modified']?></span>
                </p>
                <div class="description <?= empty($h['description']) ? 'empty' : '' ?>">
                    <h5 class="download"><a href="<?=$h['url']?>#download">Download</a></h5>
                    <p><?=$h['description']?></p>
                </div>
            </li>
        <?php endforeach ?>
    </ul>

</div>
