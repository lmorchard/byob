From: admin@byob.mozilla.com
Subject: [BYOB] Release cancelled for <?=$repack->display_title?> 

A requested release for the browser <?=$repack->display_title?> has been cancelled.

<?=$repack->url() . "\n" ?>
<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>
