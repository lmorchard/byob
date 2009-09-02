From: byob-notify-noreply@mozilla.com
Subject: [BYOB] Release rejected for <?=$repack->display_title . "\n"?>

Greetings,

This is an automatically generated email from Mozilla's Build Your Own Browser 
(BYOB) application. The orignating account is not monitored, so please direct 
enquires about this email to the BYOB contact page at <?=$contact_URL?>. 


The release requested for browser <?=$repack->display_title?> has been 
rejected. Please review the comments below for information on why the release 
was rejected, and modify the customizations accordingly. You will need to 
re-submit your release request once the changes have been made. If you have any 
questions regarding the comments below, please use the contact form (above).

Status information and the release history of this browser can be found at:
<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

The Mozilla Build Your Own Browser (BYOB) Team
http://buildyourownbrowser.com
