<div class="intro">
    <p>
        With BYOB, you can bundle the 
        <a href="https://addons.mozilla.org/en-US/firefox/addon/10900" 
            target="_new"
            title="Personas for Firefox Extension">Personas for Firefox extension</a> 
        and (optionally) set a 
        default Persona to use with your version of Firefox. Personas are free, 
        easy-to-install “skins” for Firefox that make changing the look of the browser 
        as easy as changing your shirt. They're a great way to uniquely identify your 
        version of Firefox, and further information can be found at the 
        <a href="http://http://getpersonas.com/" 
            target="_new"
            title="Personas for Firefox">getpersonas.com</a> 
        web site, and if you'd like to develop a Persona 
        for inclusion with this distribution, a great place to start is the 
        <a href="http://getpersonas.com/demo_create" 
            target="_new"
            title="Personas Howto">Personas Howto</a> page.
    </p>
    <p>
        To include the extension, select the checkbox below. When selected, you 
        will also be given the option to specify a default Persona using that Persona's 
        unique URL (e.g. http://www.getpersonas.com/persona/34365).
    </p>
</div>
<div class="pane">
    <fieldset>
        <p>
            To include the Personas for Firefox extension with your customized 
            distribution, check this box.
        </p>
        <?= View::factory('repacks/elements/addons', 
            array('addons' => array($addons_by_id['10900']))
        )->render() ?>
    </fieldset>
    <fieldset class="addon-dependent addon-10900">
        <p>
            To specify a particular Persona, which will be loaded as a default 
            with your distribution, enter the Persona URL here.
        </p>
        <div>
            <?= form::input('persona_url', form::value('persona_url')) ?>
        </div>
    </fieldset>
</div>
