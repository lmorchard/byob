<div>

    <p>You can choose a custom appearance to apply to your browser
    by choosing a Persona:</p>

    <?= 
    form::fieldset('Personas for Firefox', array(), array( 
        form::field('input', 'persona_url', 'Persona URL', array(), array(
            '(eg. http://www.getpersonas.com/persona/34365)',
        ))
    ))
    ?>

</div>
