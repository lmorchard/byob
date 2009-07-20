From: admin@byob.mozilla.com
Subject: [BYOB] Release cancelled for <?=$repack->title?> 

A requested release for the browser <?=$repack->title?> has been cancelled.

<?=$repack->url() . "\n" ?>
<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>
