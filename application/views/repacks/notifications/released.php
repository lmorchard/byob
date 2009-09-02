From: byob-notify-noreply@mozilla.com
Subject: [BYOB] Release approved for <?=$repack->display_title . "\n"?>

Greetings,

This is an automatically generated email from Mozilla's Build Your Own Browser 
(BYOB) application. The orignating account is not monitored, so please direct 
enquires about this email to the BYOB contact page at <?=$contact_URL?>. 


The release requested for browser <?=$repack->display_title?> has been 
approved, and the installer files are now available for download from your BYOB 
profile. You may now distribute this release under the conditions outlined when 
you submitted the release for approval.

Status information and the release history of this browser can be found at:
<?=$repack->releaseUrl() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

The Mozilla Build Your Own Browser (BYOB) Team
http://buildyourownbrowser.com
