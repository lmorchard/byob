From: l.m.orchard@gmail.com
Subject: Email address change for <?=$login_name?>.

Someone (possibly you) has triggered an attempt to change the email address for 
a login named "<?=$login_name?>".  If you are that someone, follow this link to 
complete the process:

<?= 
url::full('verifyemail', false, array(
    'email_verification_token' => $email_verification_token
))
?>
