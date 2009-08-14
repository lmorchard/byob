From: admin@byob.mozilla.com
Subject: [BYOB] Approval pending for <?=$repack->display_title?> 

A build pending approval for the browser <?=$repack->display_title?> is ready.

<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>
