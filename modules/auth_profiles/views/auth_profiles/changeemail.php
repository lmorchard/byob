<?php if (!empty($email_verification_token_set)): ?>
    <p>
        Check your inbox for a link to verify this new email address.
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

