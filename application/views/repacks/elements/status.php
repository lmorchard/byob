<?php
/**
 * Transform a repack's state into a text description.
 */
$state_titles = array(
    'new'        => 'new',
    'edited'     => 'modified',
    'requested'  => 'release requested',
    'pending'    => 'pending approval',
    'approved'   => 'approved',
    'rejected'   => 'rejected',
    'reverted'   => 'release reverted',
    'started'    => 'new release in progress',
    'failed'     => 'release failed, try again',
    'released'   => 'released',
    'deleted'    => 'deleted',
    'cancelled'  => 'release cancelled',
);
$title = $state_titles[$repack->getstateName()];
$h = html::escape_array(array(
    'title'    => $state_titles[$repack->getstateName()],
    'modified' => $repack->modified,
    'kind'     => ($repack->isRelease()) ?
        'Current release' : 'Pending changes',
));
?>
<span><?=$h['kind']?> (<?=$h['title']?> at <?=$h['modified']?>)</span>
