<?php slot::set('head_title', 
    sprintf(_('view :: %1$s'), html::specialchars($repack->title))) ?>
<?php slot::start('crumbs') ?>
    <?=sprintf(_('<a href="%1$s">%2$s</a> :: view details'),
        $repack->url(), html::specialchars($repack->title))?>
<?php slot::end() ?>

<?=View::factory('repacks/elements/details')
    ->set('repack', $repack)->render()?> 

<?php if (!empty($changes)): ?>
<div id="changes">
    <h3><?=_('Changes')?></h3>
    <?=View::factory('repacks/elements/changes')
        ->set('changes', $changes)->render()?> 
</div>
<?php endif ?>

<?=View::factory('repacks/elements/downloads')
    ->set('repack', $repack)->render()?> 

<?php if (!empty($logevents)): ?>
<div id="history">
    <h3><?=_('History')?></h3>
    <?= View::factory('repacks/elements/history')->set(array(
        'logevents' => $logevents,
        'repack'    => $repack
    ))->render() ?>
</div>
<?php endif ?>
