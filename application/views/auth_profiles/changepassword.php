<?php if (!empty($password_changed)): ?>

    <p>Password changed.</p>

<?php elseif (!empty($invalid_reset_token)): ?>

    <p>Invalid password reset attempt. 
    <?=html::anchor('forgotpassword', 'Try again?')?></p>

<?php else: ?>

    <?php form::$errors = @$form_errors ?>
    <?php if (!empty($forgot_password_login_name)): ?>
        <p>Recovering password for <?=html::specialchars($forgot_password_login_name)?></p>
    <?php endif ?>
    <?= 
    form::build(null, array('class'=>'changepassword'), array(
        form::field('hidden', 'password_reset_token'),
        form::fieldset('Change password', null, array(
            (empty($forgot_password_login_name)) ?
                form::field('password', 'old_password', 'Old password', array('class'=>'required')) : '',
            form::field('password', 'new_password', 'New password', array('class'=>'required')),
            form::field('password', 'new_password_confirm', 'New password (confirm)', array('class'=>'required')),
            form::field('submit_button', 'change', null, array('button_params'=>array('class'=>'button yellow required'),'class'=>'required','value'=>'Change password'))
        ))
    )) 
    ?>

<?php endif ?>
