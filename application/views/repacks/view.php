<?php slot::set('head_title', 'view :: ' . html::specialchars($repack->title)); ?>
<?php slot::start('crumbs') ?>
    <a href="<?=$repack->url() ?>"><?= html::specialchars($repack->title) ?></a> :: view details
<?php slot::end() ?>
<?php
    $h = html::escape_array(array_merge(
        $repack->as_array(),
        array(
            'url'         => $repack->url(),
            'modified'    => date('m/d/Y', strtotime($repack->modified)),
            'screen_name' => $repack->profile->screen_name,
        )
    ));
?>

<div class="intro <?= ($repack->isRelease()) ? 'release' : 'inprogress' ?>">
    <?=View::factory('repacks/elements/status')
        ->set('repack', $repack)->render()?> 
    <h2><?=$h['title']?></h2>
    <div class="byline">
        Created by 
        <a href="<?=url::base()?>profiles/<?=$h['screen_name']?>"><?=$h['screen_name']?></a>
        on <?=$h['modified']?>
    </div>
    <p class="description"><?=$h['description']?></p>
    <?=View::factory('repacks/elements/actions')
        ->set('repack', $repack)->render()?>
</div>

<?php if (false && !empty($changes)): ?>
<div>
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
