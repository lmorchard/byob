<?php slot::set('head_title', 'registration successful'); ?>
<h2>Registration successful</h2>

<p>
    Your account has been created, but is not yet active. An email containing 
    information on how to activate your account has been sent to the address you 
    provided. You must activate your account before you can login to the Build Your 
    Own Browser application. Please check your email for this information, and 
    activate your account.
</p>

<p>
    If you do not receive this email within twenty-four hours, please contact us at 
    byob-help@mozilla.com. If you are using any spam prevention software, please 
    ensure that byob-registration@mozilla.com is whitelisted.
</p>

<p>
    You can also use the button below to re-send the account activation email:
</p>

<form action="<?=url::base().'reverifyemail/'.urlencode($login_name)?>" method="POST">
    <input type="submit" value="Re-send Account Activation Information" />
</form>
