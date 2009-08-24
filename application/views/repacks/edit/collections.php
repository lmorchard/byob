<div class="intro">
    <p>You can choose to pre-install the Add-on Collector add-on:</p>
</div>
<div class="pane">
    <fieldset>
        <?= View::factory('repacks/elements/addons', 
            array('addons' => array($addons_by_id['11950']))
        )->render() ?>
    </fieldset>
    <fieldset class="addon-dependent addon-11950">
        <p>
            You can choose a set of addons to suggest for installation in your 
            browser by using the collections feature of addons.mozilla.com
            (eg: https://addons.mozilla.org/en-US/firefox/collection/social)
        </p>

        <div>
            <?= form::input('addons_collection_url', form::value('addons_collection_url')) ?>
        </div>
    </fieldset>
</div>
