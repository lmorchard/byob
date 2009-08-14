From: admin@byob.mozilla.com
Subject: [BYOB] Release requested for <?=$repack->display_title?> 

A release has been requested for browser <?=$repack->display_title?>.

<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

