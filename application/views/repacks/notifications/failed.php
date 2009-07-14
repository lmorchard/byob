From: admin@byob.mozilla.com
Subject: Release failed for <?=$repack->title?> 

A requested release for the browser <?=$repack->title?> has failed.

<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>
