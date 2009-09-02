From: byob-notify-noreply@mozilla.com
Subject: [BYOB] Release cancelled for <?=$repack->display_title . "\n"?>

Greetings,

This is an automatically generated email from Mozilla's Build Your Own Browser 
(BYOB) application. The orignating account is not monitored, so please direct 
enquires about this email to the BYOB contact page at <?=$contact_URL?>.


A requested release for the browser <?=$repack->display_title?> has been 
cancelled, and the release has been removed from the review queue. If this 
release was cancelled in error, you must re-submit the release for approval.

Status information and the release history of this browser can be found at:
<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>


The Mozilla Build Your Own Browser (BYOB) Team
http://buildyourownbrowser.com
