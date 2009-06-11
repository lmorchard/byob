<?php
    echo form::build('register', array('class'=>'register'), array(
        form::fieldset('login details', array('class'=>'login'), array(
            form::field('input',    'login_name',       'Login name'),
            form::field('input',    'email',            'Email'),
            form::field('input',    'email_confirm',    'Email (confirm)'),
            form::field('password', 'password',         'Password'),
            form::field('password', 'password_confirm', 'Password (confirm)'),
        )),
        form::fieldset('profile details', array(), array(
            form::field('input',    'screen_name',  'Screen name'),
            form::field('input',    'full_name',    'Full name'),
            form::field('textarea', 'bio',          'Bio / About you'),
        )),
        form::fieldset('finish', array(), array(
            form::captcha('captcha', 'Captcha'),
            form::field('submit', 'register', null, array('value'=>'Register'))
        ))
    ));
?>
