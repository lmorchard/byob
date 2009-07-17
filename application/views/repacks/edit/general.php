<div>
    <p>Use the form fields below to describe your customized browser:</p>

    <?= 
    form::fieldset('browser details', array('class'=>'selected'), array( 
        form::field('textarea', 'description', 'description', array(), array(
            'optional, max length 1000 characters'
        ))
    ))
    ?>
</div>
