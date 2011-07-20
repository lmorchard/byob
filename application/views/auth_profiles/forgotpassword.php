<?php if (!empty($password_reset_token_set)): ?>
    <p>
        Check your inbox for a link to change your password.
    </p>
<?php else: ?>
    <?= 
    form::build(url::current(), array('class'=>'forgotpassword'), array(
        form::field('hidden', 'crumb', '', array('value'=>$crumb)),
        form::fieldset('Forgot password', null, array(
            "<p>Supply either of these pieces of information to recover your password:</p>",
            form::field('input', 'login_name', 'login name', array('class'=>'required')),
            form::field('input', 'email', 'email address', array('class'=>'required')),
            form::field('submit_button', 'forgot', null, array('button_params'=>array('class'=>'button yellow required'),'class'=>'required','value'=>'Forgot password'))
        ))
    )) 
    ?>
<?php endif ?>
