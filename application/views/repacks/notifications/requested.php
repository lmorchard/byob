From: byob-notify-noreply@mozilla.com
Subject: [BYOB] Release requested for <?=$repack->display_title?>

Greetings,

This is an automatically generated email from Mozilla's Build Your Own Browser 
(BYOB) application. The orignating account is not monitored, so please direct 
enquires about this email to the BYOB contact page at <?=$contact_URL?>. 


The request for release of the <?=$repack->display_title?> browser has been 
succesfully submitted. Your release will now be generated and submitted for 
review. The installer files will not be available for download until the review 
process has completed, and any change requests will be sent to this email 
address. You will receive additional notifications for when the release has 
been generated for review, and when the review is complete.

Please note that our turnaround target for review is two (2) business days, and 
be greater or less depending on the number of release requests currently being 
reviewed.

Status information and the release history of this browser can be found at:

<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

The Mozilla Build Your Own Browser (BYOB) Team
http://buildyourownbrowser.com
