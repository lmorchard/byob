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
$state_name = $repack->getStateName();
if (NULL == $state_name) $state_name = 'new';
$title = $state_titles[$state_name];
$h = html::escape_array(array(
    'title'    => $state_titles[$state_name],
    'modified' => $repack->modified,
    'kind'     => ($repack->isRelease()) ?
        'Current release' : 'In-progress changes',
));
?>
<p><?=$h['kind']?> (<?=$h['title']?> at <?=$h['modified']?>)</p>
