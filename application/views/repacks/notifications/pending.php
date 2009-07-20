From: admin@byob.mozilla.com
Subject: [BYOB] Approval pending for <?=$repack->title?> 

A build pending approval for the browser <?=$repack->title?> is ready.

<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>
