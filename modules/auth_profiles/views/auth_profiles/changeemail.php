<?php if (!empty($email_verification_token_set)): ?>
    <p>
        Check the email address registered for this account for a link 
        to verify your new email address.
    </p>
<?php else: ?>
    <?php form::$errors = @$form_errors ?>
    <?= 
    form::build(null, array('class'=>'changeemail'), array(
        form::fieldset('change email', null, array(
            form::field('input', 'new_email', 'new email'),
            form::field('submit', 'change', null, array('value'=>'change email'))
        ))
    )) 
    ?>
<?php endif ?>

