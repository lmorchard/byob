From: l.m.orchard@gmail.com
Subject: Password recovery for <?=$login_name?>.

Someone (possibly you) has triggered an attempt to recover the password for a 
login named "<?=$login_name?>" registered with this email address.  If you are 
that someone, follow this link to complete the process:

<?= 
url::full('changepassword', false, array(
    'password_reset_token' => $password_reset_token
))
?>
