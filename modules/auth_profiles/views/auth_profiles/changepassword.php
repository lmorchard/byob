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
        form::fieldset('change password', null, array(
            (empty($forgot_password_login_name)) ?
                form::field('password', 'old_password', 'old password') : '',
            form::field('password', 'new_password', 'new password'),
            form::field('password', 'new_password_confirm', 'new password (confirm)'),
            form::field('submit', 'change', null, array('value'=>'change password'))
        ))
    )) 
    ?>

<?php endif ?>
