<?php slot::set('head_title', 'login'); ?>
<?php slot::set('crumbs', 'account login'); ?>

<?php 
    $is_popup = (isset($_GET['popup']));
    slot::set('is_popup', $is_popup);
?>

<?php if (isset($_GET['gohome'])): ?>
    <script type="text/javascript">
        window.top.location.href="<?=url::base()?>home";
    </script>
<?php else: ?>

<?php if ($is_popup): ?>
    <div class="header">
        <h2>Log into your account</h2>
    </div>
    <div class="content">
<?php endif ?>

<?php if (!empty($login_inactive)): ?>
    <p>Sorry, that login has been disabled.</p>
<?php endif ?>

<?php if (!empty($no_verified_email)): ?>
    <p>
    The account you are attempting to login with has not been activated. In order
    to use BYOB, you must first activate your account by following the instructions
    that were sent to the email address associated with that account. If you did
    not receive this email, please ensure any anti-spam methods will accept mail
    from admin@byob.mozilla.com and use the 
    "Re-send Account Activation Information" button below. If
    you still do not receive the activation information, please contact us.
    </p>
    <form action="<?=url::base().'reverifyemail/'.urlencode($_POST['login_name'])?>" method="POST">
        <input type="submit" value="Re-send Account Activation Information" />
    </form>
<?php endif ?>

<?php 
/* Munge the errors to obscure what part of the login was invalid. */
$invalid =  (!empty(form::$errors));
form::$errors = array();
?>

<?php slot::start('submit') ?>
<li class="required submit">
    <label class="hidden" for="login"/>
    <ul class="other">
        <li><a target="_top" href="<?=url::base()?>forgotpassword">Forgot your password?</a></li>
        <li><a target="_top" href="<?=url::base()?>register">Need an account?</a></li>
    </ul>
    <input id="login" class="submit required" type="image" 
        src="<?=url::base()?>img/login-button.gif" alt="Login" name="login"/>
</li>
<?php slot::end() ?>

<?= 
form::build(url::base() . url::current(true), array('class'=>'login'), array(
    form::field('hidden', 'jump', ''),
    form::fieldset(null, array('class'=>'login'), array(
        form::field('input',    'login_name',       'Username', array('class'=>'required' . ( $invalid ? ' error' : '') )),
        form::field('password', 'password',         'Password', array('class'=>'required' . ( $invalid ? ' error' : '') )),
        slot::get('submit')
    ))
)) 
?>

<?php if ($is_popup): ?>
    </div>
<?php endif ?>
<?php endif ?>
