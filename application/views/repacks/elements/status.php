<?php
/**
 * Transform a repack's state into a text description.
 */
$state_titles = array(
    'new'        => 'Edit',
    'edited'     => 'Edit',
    'requested'  => 'Under review',
    'pending'    => 'Under review',
    'approved'   => 'Download',
    'rejected'   => 'Changes requested',
    'reverted'   => 'Edit',
    'started'    => 'Under review',
    'failed'     => 'Edit',
    'released'   => 'Download',
    'deleted'    => 'Deleted',
    'cancelled'  => 'Edit',
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
    <?php endif ?>
</div>
