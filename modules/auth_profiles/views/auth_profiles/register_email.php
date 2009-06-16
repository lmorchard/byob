From: l.m.orchard@gmail.com
Subject: New user registration for <?=$login_name?>.

Someone (possibly you) has registered for a login named "<?=$login_name?>".  If you are that someone, follow this link to verify your email address and complete the process:

<?= 
url::full('verifyemail', false, array(
    'email_verification_token' => $email_verification_token
))
?>
