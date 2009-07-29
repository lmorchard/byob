<?php if (!empty($email_sent)): ?>
    <p>Account activation email sent.</p>
<?php endif ?>
<form action="<?=url::base().'reverifyemail/'.urlencode($login->login_name)?>" method="POST">
    <input type="submit" value="Re-send Account Activation Information" />
</form>
