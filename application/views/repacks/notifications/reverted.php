From: byob-notify-noreply@mozilla.com
Subject: [BYOB] Your browser has been removed

Greetings,

This is an automatically generated email from Mozilla's Build Your Own Browser 
(BYOB) application.

Your browser <?=$repack->display_title?> has been removed at your request and 
is no longer available for download. If you’d like to make it available again 
for download, you need to re-submit it for release by logging into your 
account.

Status information and the release history of this browser can be found at: 
<?=$repack->url() . "\n" ?>

<?php if (!empty($comments)): ?>

Comments:

<?=$comments?>

<?php endif ?>

Sincerely,
The Mozilla Build Your Own Browser (BYOB) Team
http://buildyourownbrowser.com
