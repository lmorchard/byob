From: byob-notify-noreply@mozilla.com
Subject: [BYOB] Approval pending for <?=$repack->display_title . "\n" ?>

Greetings,

This is an automatically generated email from Mozilla's Build Your Own Browser 
(BYOB) application. The orignating account is not monitored, so please direct 
enquires about this email to the BYOB contact page at <?=$contact_URL?>. 


The installer files for the browser <?=$repack->display_title?> have been 
successfully generated. The browser must still be approved for a release, and 
are not available for download at this time. A separate notification will be 
sent when the release has been approved and is available for download.

Status information and the release history of this browser can be found at:
<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

The Mozilla Build Your Own Browser (BYOB) Team
http://buildyourownbrowser.com
