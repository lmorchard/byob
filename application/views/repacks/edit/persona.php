<div class="intro">
    <p>You can choose to pre-install the Persona addon:</p>
</div>
<div class="pane">
    <div>
        <?= View::factory('repacks/elements/addons', 
            array('addons' => array($addons_by_id['10900']))
        )->render() ?>
    </div>
    <div class="addon-dependent addon-10900">
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
</div>
