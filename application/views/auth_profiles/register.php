<?php
    echo form::build('register', array('class'=>'register'), array(
        form::fieldset('login details', array('class'=>'login'), array(
            form::field('input',    'login_name',       'Login name'),
            form::field('input',    'email',            'Email'),
            form::field('input',    'email_confirm',    'Email (confirm)'),
            form::field('password', 'password',         'Password'),
            form::field('password', 'password_confirm', 'Password (confirm)'),
        )),
        form::fieldset('personal details', array('class'=>'profile'), array(
            form::field('input',    'full_name', 'Full Name'),
            form::field('input',    'phone',     'Phone'),
            form::field('input',    'fax',       'Fax'),
        )),
        form::fieldset('organization details', array('class'=>'organization'), array(
            form::field('input',    'org_name',    'Name'),
            form::field('textarea', 'org_address', 'Address'),
            form::field('dropdown', 'org_type',    'Type', array(
                'options' => array(
                    'corp'      => 'Corporation', 
                    'nonprofit' => 'Non-Profit', 
                    'other'     => 'Other',
                )
            )),
            form::field('input',    'org_type_other', 'Type (other)'),
        )),
        form::fieldset('finish', array(), array(
            '<li><label for="recaptcha">Captcha</label><span>' . recaptcha::html() . '</span></li>',
            form::field('submit', 'register', null, array('value'=>'Register')),
        ))
    ));
?>
