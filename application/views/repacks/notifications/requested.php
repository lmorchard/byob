From: admin@byob.mozilla.com
Subject: Release requested for <?=$repack->title?> 

A release has been requested for browser <?=$repack->title?>.

<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

