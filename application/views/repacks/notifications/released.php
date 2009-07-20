From: admin@byob.mozilla.com
Subject: [BYOB] Release approved for <?=$repack->title?> 

The release requested for browser <?=$repack->title?> has been approved.

<?=$repack->releaseUrl() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

