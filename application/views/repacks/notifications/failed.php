From: byob-notify-noreply@mozilla.com
Subject: [BYOB] Release failed for <?=$repack->display_title . "\n"?>

Greetings,

This is an automatically generated email from Mozilla's Build Your Own Browser 
(BYOB) application. The orignating account is not monitored, so please direct 
enquires about this email to the BYOB contact page at <?=$contact_URL?>.

The generation of a requested release for the browser 
<?=$repack->display_title?> has failed. BYOB administrators have been notified, 
and you must re-submit the release for generation and approval. Our apologies 
for the inconvenience this may cause.

Status information and the release history of this browser can be found at:
<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

The Mozilla Build Your Own Browser (BYOB) Team
http://buildyourownbrowser.com
