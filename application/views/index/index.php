<?php slot::set('head_title', 'home') ?>
<?php slot::set('crumbs', 'home') ?>

<?php if (!authprofiles::is_logged_in()): ?>

    <h2>Welcome!</h2>

    <p>To get started building your own browser, 
    <a href="<?= url::base().'register' ?>">register</a> and 
    <a href="<?= url::base().'login' ?>">login</a>!

<?php else: ?>

    <h2>Welcome back!</h2>

    <p>
        <?php
            $create_url = url::base() .
            "profiles/".authprofiles::get_profile('screen_name').
            "/browsers/create";
        ?>
        <form action="<?=$create_url?>" method="POST">
            Want to
            <button name="confirm" id="confirm" value="yes">create a browser</button>?
        </form>
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
