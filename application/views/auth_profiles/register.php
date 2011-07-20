<?php slot::set('head_title', 'Sign up'); ?>
<?php slot::set('crumbs', 'Sign Up for a New Account'); ?>

<script type="text/javascript">
var RecaptchaOptions = {
    theme: 'white'
};
</script>

<?php slot::start('body_end') ?>
    <?=html::script(array(
        'js/jquery.passwordStrengthMeter.js',
    ))?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.fields li.use_password_meter').passwordStrengthMeter(); 
        });
    </script>
<?php slot::end() ?>

<?php slot::start('login_details_intro') ?>
<div class="intro">
    <p>
        You are just a few moments away from creating your new browser.<br />
        Please fill in <strong>every field</strong> below to sign up.
    </p>
</div>
<?php slot::end() ?>

<?php slot::start('password_strength_meter') ?>
    <span class="password_strength_meter default">
        <span class="label default">Password Strength</span>
        <span class="label short">Too short</span>
        <span class="label bad weak">Weak password</span>
        <span class="label good">Good password</span>
        <span class="label better">Strong password</span>
        <span class="label strong">Strong password</span>
        <span class="meter"><span class="indicator">&nbsp;</span></span>
    </span>
<?php slot::end() ?>

<?php
$name_errors = array();
foreach (array('first_name','last_name') as $name) {
    if (!empty($form_errors[$name])) 
        $name_errors[] = $form_errors[$name];
}

$captcha_error = 
    !empty($form_errors['recaptcha']);

echo form::build('register', array('class'=>'register'), array(
    form::field('hidden', 'crumb', '', array('value'=>$crumb)),
    form::fieldset('Sign Up for a New Account', array('class'=>'login'), array(
        '<li>' . slot::get('login_details_intro') . '</li>',
        array(
            '<li class="input required text two_up ' .
                    (!empty($name_errors) ? 'error' : '').'">',
                '<label for="first_name">Your name</label>',
                form::input(array('name'=>'first_name', 'title'=>'First name')),
                form::input(array('name'=>'last_name', 'title'=>'Last name')),
                (empty($name_errors) ? '' : 
                    '<p class="notes"><strong class="error">' . 
                        join('; ', $name_errors) .
                    '</strong></p>' ),
            '</li>',
        ),
        form::field('input', 'email', 'Your email', array('class'=>'divider required'), array(
            empty($form_errors['email']) ?
                "We'll send an email to this address to complete the sign-up process." :
                "<strong class='error'>".$form_errors['email']."</strong>"
        )),
        form::field('input', 'login_name', 'Your login name', array('class'=>'divider login_name required'), array(
            empty($form_errors['login_name']) ?
                "This is the name you will use to log in to your account. Use 4 to 12 characters. Letters, numbers, hyphens, and underscores only." :
                "<strong class='error'>".$form_errors['login_name']."</strong>"
        )),
        form::field('password', 'password', 'Your password', array('class'=>'password required use_password_meter'), array(
            empty($form_errors['password']) ?
                "Use 6 to 32 characters. Capitalization matters." :
                "<strong class='error'>".$form_errors['password']."</strong>",
            slot::get('password_strength_meter') 
        )),
        form::field('password', 'password_confirm', 'Re-type password', array('class'=>'divider required'), array(
            empty($form_errors['password_confirm']) ? '' :
                "<strong class='error'>".$form_errors['password_confirm']."</strong>",
        )),
        '<li class="required '.($captcha_error ? 'error' : '').'"><label for="recaptcha">Are you human?</label><span>' . recaptcha::html() . '</span></li>',
        '<li class="required submit"><label class="hidden" for="register">&nbsp;</label><button id="register" class="submit required button large yellow">Create a New Account</button>',
    ))
));
?>
