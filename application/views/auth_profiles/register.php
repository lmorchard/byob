<?php slot::set('head_title', 'register'); ?>
<?php slot::set('crumbs', 'register a new account'); ?>
<?php
slot::start('login_details_intro');
?>
<div class="intro">
    <p>
    This is your basic account information, and will be used to login to the Build 
    Your Own Browser application. Your e-mail address will be used for account 
    verification, status notifications, and password resets, and must be a valid 
    address. If you are creating a customized version of Firefox for distribution 
    by an organization, you must use an email account using that organization's 
    domain name to ensure your submissions are approved.
    </p>
    <p class="required_note"><span>*</span> = Required field</p>
</div>
<?php
slot::end();

slot::start('account_details_intro');
?>
<div class="intro">
    <p>
    We require information about you and, if applicable, the organization you 
    represent. It helps us understand who is using BYOB, and to give us additional 
    ways to contact you should the need arise. This information is used solely by 
    Mozilla, and will never be shared with or sold to anyone else.
    </p>
    <p class="required_note"><span>*</span> = Required field</p>
</div>
<?php
slot::end();

echo form::build('register', array('class'=>'register'), array(
    form::fieldset('Login Details', array('class'=>'login'), array(
        '<li>' . slot::get('login_details_intro') . '</li>',
        form::field('input',    'login_name',       'Login name', array('class'=>'required'), array(
            "Enter the account name you will use to login to BYOB (4-12 ",
            "characters in length; alphanumeric, underscore, and hyphens only)"
        )),
        form::field('input',    'email',            'Email', array('class'=>'required'), array(
        )),
        form::field('input',    'email_confirm',    'Email (confirm)', array('class'=>'required'), array(
            "Enter a vaild email address. Verification will be ",
            "required before your account is activated. If you are ",
            "representing an organization, you must use an account with ",
            "that organization's domain name or your submissions may be ",
            "rejected."
        )),
        form::field('password', 'password',         'Password', array('class'=>'required'), array(
        )),
        form::field('password', 'password_confirm', 'Password (confirm)', array('class'=>'required'), array(
            "Enter your password here. Passwords must be a minimum ", 
            "of six characters in length. If you forget your password, reset ",
            "information will be sent to the email address above."
        )),
    )),
    form::fieldset('Account Details', array('class'=>'account'), array(
        '<li>' . slot::get('account_details_intro') . '</li>',
        form::field('input',    'first_name',  'First Name', array('class'=>'required'), array(
            'Your given name.'
        )),
        form::field('input',    'last_name',   'Last Name', array('class'=>'required'), array(
            'Your surname.'
        )),

        form::field('checkbox', 'is_personal', 'Personal account?', array('value'=>'1'), array(
            "Please check this box if you are using the versions of ",
            "Firefox you create for personal use (i.e. sharing with ",
            "friends and family, etc.)"
        )),

        form::field('dropdown', 'org_type',    'Organization Type', array(
            'options' => array(
                'corp'      => 'Corporation', 
                'nonprofit' => 'Non-Profit', 
                'other'     => 'Other',
            ),
            'class'=>'required'
        )),
        form::field('input',    'org_type_other', '(other)'),
        form::field('input',    'org_name',    'Organization Name', array('class'=>'required'), array(
            "Please enter the full, legal name of the organization you represent here."
        )),

        form::field('input',    'phone',       'Phone', array('class'=>'required'), array(
            'Your daytime contact number, with country code (US/Canada is "1").'
        )),
        form::field('input',    'fax',         'Fax', array(), array(
            'Your fax number, with country code (US/Canada is "1")'
        )),
        form::field('input',    'website',     'Website', array(), array(
            'Please provide the URL for your organizational or personal website.'
        )),

        form::field('input',    'address_1', 'Street Address 1', array('class'=>'required')),
        form::field('input',    'address_2', 'Street Address 2'),
        form::field('input',    'city',      'City', array('class'=>'required')),
        View::factory('auth_profiles/elements/states')->render(),
        form::field('input',    'zip',       'Zip / Postal Code', array('class'=>'required', 'class'=>'required'), array(
        )),
        View::factory('auth_profiles/elements/countries')->render(),
    )),
    form::fieldset('finish', array(), array(
        '<li class="required"><label for="recaptcha">Captcha</label><span>' . recaptcha::html() . '</span></li>',
        form::field('submit', 'register', null, array('value'=>'Register')),
    ))
));
?>
