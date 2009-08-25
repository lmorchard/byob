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

<div class="details <?= ($repack->isRelease()) ? 'release' : 'inprogress' ?>">
    <?=View::factory('repacks/elements/status')
        ->set('repack', $repack)->render()?> 
    <h2><?=$h['title']?></h2>
    <div class="byline">
        Created by 
        <a href="<?=url::base()?>profiles/<?=$h['screen_name']?>"><?=$h['screen_name']?></a>
        on <?=$h['modified']?>
    </div>
    <p class="description"><?=$h['description']?></p>
    <?php if (!isset($hide_actions) || (!$hide_actions)): ?>
        <?=View::factory('repacks/elements/actions')
            ->set('repack', $repack)->render()?>
    <?php endif ?>
</div>
