<?php slot::set('head_title', ' :: view :: ' . html::specialchars($repack->title)); ?>
<?php slot::start('page_title') ?>
    :: view :: <a href="<?= url::base() . url::current() ?>"><?= html::specialchars($repack->title) ?></a>
<?php slot::end() ?>

<?=View::factory('repacks/elements/details')
    ->set('repack', $repack)->render()?>

<div>
    <h3>Status</h3>
    <?=View::factory('repacks/elements/status')
        ->set('repack', $repack)->render()?> 
</div>

<div>
    <h3>Actions</h3>
    <?=View::factory('repacks/elements/actions')
        ->set('repack', $repack)->render()?>
</div>

<?php if (!empty($logevents)): ?>
<div>
    <h3>History</h3>
    <?=View::factory('repacks/elements/history')
        ->set('logevents', $logevents)->render()?>
</div>
<?php endif ?>
