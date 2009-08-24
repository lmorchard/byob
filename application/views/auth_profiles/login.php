<?php slot::set('head_title', 'login'); ?>
<?php slot::set('crumbs', 'account login'); ?>
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

<?php /* Munge the errors to obscure what part of the login was invalid. */ ?>
<?php if (!empty(form::$errors)): ?>
    <?php slot::start('errors') ?>
    <ul class="errors highlight">
        <li>Invalid login.</li>
    </ul>
    <?php slot::end('errors') ?>
<?php endif ?>
<?php form::$errors = array(); ?>

<?= 
form::build('login', array('class'=>'login'), array(
    form::field('hidden', 'jump', ''),
    slot::get('errors'),
    form::fieldset('Login', array('class'=>'login'), array(
        form::field('input',    'login_name',       'Login name', array('class'=>'required')),
        form::field('password', 'password',         'Password', array('class'=>'required')),
        form::field('submit',   'login',  null, array('class'=>'required','value'=>'login')),
        html::anchor('/forgotpassword', 'Forgot password?'),
    ))
)) 
?>
