From: byob-notify-noreply@mozilla.com
Subject: Build Your Own Browser: Password reset for <?=$login_name?>

Greetings,

This is an automatically generated email from Mozilla's Build Your Own Browser 
(BYOB) application. The orignating account of this email is not monitored, so 
please direct enquires about this email to the BYOB contact page at 
<?=$contact_URL?>.

Someone (possibly you) has requested a reset of the password for the BYOB login 
named "<?=$login_name?>". This login was registered with this email address. If 
you are that someone, and wish to reset your password, please follow the link 
below to complete the password reset process:
 
<?= 
url::full('changepassword', false, array(
    'password_reset_token' => $password_reset_token
)) . "\n"
?>

If you did not request a password reset for this account, simply ignore this 
email, and your current password will remain in place. If you have any 
questions about this email, or experience difficulties resetting your password, 
please contact us via the BYOB contact page at <?=$contact_URL?>.

Thanks!

The Mozilla Build Your Own Browser (BYOB) Team
http://buildyourownbrowser.com
