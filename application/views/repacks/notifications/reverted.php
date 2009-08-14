From: admin@byob.mozilla.com
Subject: [BYOB] Release take down for <?=$repack->display_title?> 

A release has been taken down for browser <?=$repack->display_title?>.

<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

