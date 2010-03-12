<div class="intro">
    <p>
        With BYOB, you can bundle certain addons with your browser.
    </p>
</div>
<div class="pane">

    <fieldset><legend>Personas</legend>
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

    <fieldset><legend>Search Plugins</legend>
        <p>You can upload up to 3 search plugins <a href="https://developer.mozilla.org/en/Creating_OpenSearch_plugins_for_Firefox" target="_new">in OpenSearch XML format</a>.</p>
        <iframe id="search_plugin_uploads" scrolling="no"
            src="<?= $repack->url('edit_searchplugins')  ?>"></iframe>
    </fieldset>

</div>
