<?php if (!empty($email_verification_token_set)): ?>
    <p>
        Check your inbox for a link to verify this new email address.
    </p>
<?php else: ?>
    <?php form::$errors = @$form_errors ?>
    <?= 
    form::build(null, array('class'=>'changeemail'), array(
        form::fieldset('Change email', null, array(
            form::field('input', 'new_email', 'New email address', array('class'=>'required')),
            form::field('submit', 'change', null, array('class'=>'required','value'=>'change email'))
        ))
    )) 
    ?>
<?php endif ?>

