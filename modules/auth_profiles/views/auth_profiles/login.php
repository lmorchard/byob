<?php if (!empty($login_inactive)): ?>
    <p>Sorry, that login has been disabled.
<?php endif ?>

<?php if (!empty($no_verified_email)): ?>
    <p>Sorry, the email address for that login has not yet been verified.</p>
    <form action="<?=url::base().'reverifyemail/'.urlencode($_POST['login_name'])?>" method="POST">
        <input type="submit" value="Re-send Account Activation Information" />
    </form>
<?php endif ?>

<?= 
form::build('login', array('class'=>'login'), array(
    form::field('hidden', 'crumb', '', array('value'=>$crumb)),
    form::field('hidden', 'jump', ''),
    form::fieldset('login details', array('class'=>'login'), array(
        form::field('input',    'login_name',       'Login name'),
        form::field('password', 'password',         'Password'),
        form::field('submit',   'login',  null, array('value'=>'login')),
        html::anchor('/forgotpassword', 'Forgot password?'),
    ))
)) 
?>
