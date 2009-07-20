From: admin@byob.mozilla.com
Subject: [BYOB] Release rejected for <?=$repack->title?> 

The release requested for browser <?=$repack->title?> has been rejected.

<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

