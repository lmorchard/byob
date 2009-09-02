From: byob-notify-noreply@mozilla.com
Subject: [BYOB] Build started for <?=$repack->display_title . "\n"?> 

Greetings,

This is an automatically generated email from Mozilla's Build Your Own Browser 
(BYOB) application. The orignating account is not monitored, so please direct 
enquires about this email to the BYOB contact page at <?=$contact_URL?>. 


The installer generation process has been started for 
<?=$repack->display_title?>. You will be notified when this process is 
complete, but the installers will not be available for download until the 
review process is complete.

Status information and the release history of this browser can be found at:
<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>


The Mozilla Build Your Own Browser (BYOB) Team
http://buildyourownbrowser.com
