From: admin@byob.mozilla.com
Subject: [BYOB] Release failed for <?=$repack->display_title?> 

A requested release for the browser <?=$repack->display_title?> has failed.

<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>
