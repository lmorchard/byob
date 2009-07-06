<?php
    echo form::build('details', array('class'=>'details'), array(
        form::fieldset('profile details', array(), array(
            form::field('input',    'screen_name',  'Screen name'),
            form::field('input',    'full_name',    'Full name'),
            form::field('textarea', 'bio',          'Bio / About you'),
        )),
        form::fieldset('finish', array(), array(
            form::captcha('captcha', 'Captcha'),
            form::field('submit', 'update', null, array('value'=>'Update'))
        ))
    ));
?>
