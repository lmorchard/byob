<?php slot::set('head_title', 'home') ?>
<?php slot::set('crumbs', 'home') ?>

<?php if (!authprofiles::is_logged_in()): ?>
    <p>
        Welcome to Mozilla's Build Your Own Browser (BYOB) web application, a 
        web-based tool that allows you to lightly customize versions of Firefox 
        that you'd like to distribute to other people. To get started, we require 
        that you <a href="<?= url::base().'register' ?>">create an account</a> and 
        <a href="<?= url::base().'login' ?>">login to the application</a>. 
        All of the customized versions of Firefox you create with BYOB will be 
        associated with, and accessible through, this account. Registration takes just 
        a couple of minutes, requires information about you and the organization 
        you represent, if applicable.
    </p>
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

    <?php if (!empty($repacks)): ?>
        Or, you can manage one of your existing custom browsers:
        <ul>
        <?php foreach ($repacks as $repack): ?>
            <li><a href="<?= $repack->url ?>"><?= html::specialchars($repack->title) ?></a></li>
        <?php endforeach ?>
        </ul>
    <?php endif ?>

<?php endif ?>

<h3>Latest browsers by everyone</h3>

<ul>
<?php foreach ($latest_repacks as $repack): ?>
    <li>
    <a href="<?= $repack->url ?>"><?= html::specialchars($repack->title) ?></a>
    by <a href="<?=url::base() . 'profiles/' . $repack->profile->screen_name?>"><?= html::specialchars($repack->profile->screen_name)?></a>
<?php endforeach ?>
</ul>
