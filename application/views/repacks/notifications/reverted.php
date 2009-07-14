From: admin@byob.mozilla.com
Subject: Release take down for <?=$repack->title?> 

A release has been taken down for browser <?=$repack->title?>.

<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

