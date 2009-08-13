From: byob-notify-noreply@mozilla.com
Subject: Build Your Own Browser: New user registration

Greetings,

This is an automatically generated email from Mozilla's Build Your Own Browser 
(BYOB) application. The orignating account is not monitored, so please direct 
enquires about this email to the BYOB contact page at <?=$contact_URL?>.

Someone (possibly you) has registered an account using this email address with 
the BYOB application. The account's login name is "<?=$login_name?>".  If you 
are that someone, you will need to follow the link below to verify your email 
address and complete the account activation process:

<?= 
url::full('verifyemail', false, array(
    'email_verification_token' => $email_verification_token
)) . "\n"
?>

Please click on the link above to complete the e-mail verification process and 
login to BYOB. If you believe this email was sent in error, please contact us 
through <?=$contact_URL?>.

Welcome aboard, and thanks for registering.

The Mozilla Build Your Own Browser (BYOB) Team
http://buildyourownbrowser.com
