<?php if (!empty($password_reset_token_set)): ?>
    <p>
        Check your inbox for a link to change your password.
    </p>
<?php else: ?>
    <?= 
    form::build(null, array('class'=>'forgotpassword'), array(
        form::fieldset('Forgot password', null, array(
            "<p>Supply either of these pieces of information to recover your password:</p>",
            form::field('input', 'login_name', 'login name', array('class'=>'required')),
            form::field('input', 'email', 'email address', array('class'=>'required')),
            form::field('submit', 'forgot', null, array('class'=>'required','value'=>'forgot password'))
        ))
    )) 
    ?>
<?php endif ?>
