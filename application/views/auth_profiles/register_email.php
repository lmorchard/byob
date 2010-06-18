From: byob-notify-noreply@mozilla.com
Subject: Thanks for registering with Mozilla’s Build Your Own Browser!

Hi <?=$login_name?>. Thanks for registering for Mozilla’s Build Your Own 
Browser (BYOB) application!

You’re just a few clicks away from building your very own, customized version 
of a Firefox browser. With BYOB, create a browser with your choice of 
bookmarks, add-ons, personas, and localization of choice. Plus, you’ll get a 
browser built with Firefox’s world-class speed and security.

Before you get started, please remember that by using and distributing a 
customized version of Firefox, you agree to the program terms and conditions. 
You can review the terms and conditions here:
<?= url::full('terms', false, array()) ?>

BYOB is licensed under the Mozilla Public License (MPL), and their contents are 
subject to the restrictions outlined in the Mozilla Trademark Policy. 

Mozilla Public License
http://www.mozilla.org/MPL/

Mozilla Trademark Policy
http://www.mozilla.org/foundation/trademarks/policy.html

If you have any questions regarding these conditions, or need clarification on 
any of the items mentioned here, please contact us by visiting <?=$contact_URL?> 

Please follow the link below to verify your email address and complete the 
account activation process:
<?= 
url::full('verifyemail', false, array(
    'email_verification_token' => $email_verification_token
)) . "\n"
?>

Welcome aboard!

Sincerely, 
The Mozilla BYOB Team 
http://buildyourownbrowser.com

PS: If for some reason you did not register an account using this email 
address, please contact us at <?=$contact_URL?>. This is an 
automatically generated email--please don’t reply directly.
