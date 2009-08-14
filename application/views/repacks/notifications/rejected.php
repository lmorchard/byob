From: admin@byob.mozilla.com
Subject: [BYOB] Release rejected for <?=$repack->display_title?> 

The release requested for browser <?=$repack->display_title?> has been rejected.

<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

