<div>
    <p>Use the form fields below to describe your customized browser:</p>

    <?= 
    form::fieldset('browser details', array('class'=>'selected'), array( 
        form::field('input', 'user_title', 'Title', array('class'=>'required'), array(
            'required, max length 255 characters, '.
            'used to help identify your browser',
        )),
        form::field('textarea', 'description', 'Description', array(), array(
            'optional, max length 1000 characters'
        ))
    ))
    ?>
</div>
