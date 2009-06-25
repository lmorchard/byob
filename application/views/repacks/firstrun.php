<?php slot::set('head_title', ' :: ' . html::specialchars($repack->title)); ?>
<?php slot::start('page_title') ?>
    :: <a href="<?= url::base() . url::current() ?>"><?= html::specialchars($repack->title) ?></a>
<?php slot::end() ?>

<h3>Welcome to <?= html::specialchars($repack->title) ?></h3>
