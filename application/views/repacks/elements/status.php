<?php
/**
 * Transform a repack's state into a text description.
 */
$state_titles = array(
    'new'        => null,
    'edited'     => null,
    'requested'  => 'Review requested',
    'pending'    => 'Under review',
    'approved'   => 'Download',
    'rejected'   => 'Changes requested',
    'reverted'   => null,
    'started'    => 'Build started',
    'failed'     => 'Build failed',
    'released'   => 'Download',
    'deleted'    => 'Deleted',
    'cancelled'  => null,
);
$state_name = $repack->getStateName();
if (NULL == $state_name) $state_name = 'new';
$title = $state_titles[$state_name];
$h = html::escape_array(array(
    'url'      => $repack->url(),
    'title'    => $state_titles[$state_name],
    'state'    => $state_name,
    'modified' => $repack->modified,
    'kind'     => ($repack->isRelease()) ?
        'Current release' : 'In-progress changes',
));
?>
<div class="status status-<?=$h['state']?>">
    <?php if (null !== $title): ?>
        <span><a href="<?=$h['url']?><?=('released'==$state_name)?'#download':''?>"><?=$h['title']?></a></span>
        <h4>Current status</h4>
    <?php endif ?>
</div>
