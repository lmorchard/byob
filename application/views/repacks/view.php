<?php slot::set('head_title', 'view :: ' . html::specialchars($repack->title)); ?>
<?php slot::start('crumbs') ?>
    <a href="<?=$repack->url() ?>"><?= html::specialchars($repack->title) ?></a> :: view details
<?php slot::end() ?>

<?=View::factory('repacks/elements/details')
    ->set('repack', $repack)->render()?> 

<?php if (!empty($changes)): ?>
<div id="changes">
    <h3>Changes</h3>
    <?=View::factory('repacks/elements/changes')
        ->set('changes', $changes)->render()?> 
</div>
<?php endif ?>

<?=View::factory('repacks/elements/downloads')
    ->set('repack', $repack)->render()?> 

<?php if (!empty($logevents)): ?>
<div id="history">
    <h3>History</h3>
    <?=View::factory('repacks/elements/history')
        ->set('logevents', $logevents)->render()?>
</div>
<?php endif ?>
