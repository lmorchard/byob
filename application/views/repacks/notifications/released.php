From: admin@byob.mozilla.com
Subject: [BYOB] Release approved for <?=$repack->display_title?> 

The release requested for browser <?=$repack->display_title?> has been approved.

<?=$repack->releaseUrl() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

