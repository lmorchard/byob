<?php if (!empty($email_verification_token_set)): ?>
    <p><?=_('Check your inbox for a link to verify this new email address.')?>
    </p>
<?php else: ?>
    <?php form::$errors = @$form_errors ?>
    <?= 
    form::build(null, array('class'=>'changeemail'), array(
        form::field('hidden', 'crumb', '', array('value'=>$crumb)),
        form::fieldset(_('Change email'), null, array(
            form::field('input', 'current_email', _('Current email address'), array('value'=>authprofiles::get_login('email'), 'disabled'=>'true')),
            form::field('input', 'new_email', _('New email address'), array('class'=>'required')),
            form::field('submit_button', 'change', null, array('button_params'=>array('class'=>'button yellow required'),'value'=>_('Change email')))
        ))
    )) 
    ?>
<?php endif ?>
