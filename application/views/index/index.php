<?php slot::set('head_title', 'home') ?>
<?php slot::set('crumbs', 'home') ?>

<div class="page_intro">
    <div class="page_intro_content">
        <h3><span>Customize Firefox</span></h3>

        <img src="<?=url::base() . 'img/home-page-intro-screenshot.jpg'?>" 
            alt="Example Firefox screenshot"
            class="screenshot" width="444" height="307" />

        <?php if (!authprofiles::is_logged_in()): ?>
            <p>
                Welcome to Mozilla's Build Your Own Browser (BYOB) web application, a 
                web-based tool that allows you to lightly customize versions of Firefox 
                that you'd like to distribute to other people. To get started, we require 
                that you <a href="<?= url::base().'register' ?>">create an account</a> and 
                <a href="<?= url::base().'login' ?>" class="login_inline">login to the application</a>. 
                All of the customized versions of Firefox you create with BYOB will be 
                associated with, and accessible through, this account. Registration 
                takes just a couple of minutes, requires information about you and 
                the organization you represent, if applicable.
            </p>
            
            <h4 class="get_started">
                <a href="<?= url::base().'register' ?>">Get Started!</a>
            </h4>
        <?php else: ?>
            <p>
                Welcome back to Mozilla's Build Your Own Browser (BYOB) web application, a 
                web-based tool that allows you to lightly customize versions of Firefox 
                that you'd like to distribute to other people.
            </p>

            <p>
                <?php
                    $profile_url = url::base() .
                        "profiles/".authprofiles::get_profile('screen_name');
                ?>
                Want to <a href="<?=$profile_url?>">manage your browsers</a>?
            </p>

            <h4 class="get_started">
                <a href="<?=$profile_url?>">Get Started!</a>
            </h4>
        <?php endif ?>
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
