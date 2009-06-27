<?php slot::start('prose') ?>

<?php if (!authprofiles::is_logged_in()): ?>

## Welcome!

To get started building your own browser, [register][] and [login][]!

[register]: <?= url::base().'register' ?> "register!"
[login]: <?= url::base().'login' ?> "login"

<?php else: ?>

## Welcome back!

Want to [get started building a browser][create]?

[create]: <?= url::base() . 'profiles/' . html::specialchars(authprofiles::get_profile('screen_name')) . '/browsers;create' ?> "create"

<?php if (!empty($repacks)): ?>
Or, you can manage one of your existing custom browsers:

<?php foreach ($repacks as $repack): ?>
* [<?= html::specialchars($repack->title) ?>](<?= $repack->url ?>)
<?php endforeach ?>

<?php endif ?>

<?php endif ?>

### Latest browsers by everyone

<?php foreach ($latest_repacks as $repack): ?>
* [<?= html::specialchars($repack->title) ?>](<?= $repack->url ?>) by <?= html::specialchars($repack->profile->screen_name) . "\n" ?>
<?php endforeach ?>

<?php slot::end_filter('Markdown') ?>
