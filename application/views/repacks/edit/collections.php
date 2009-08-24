<div class="intro">
    <p>You can choose to pre-install the Add-on Collector add-on:</p>
</div>
<div class="pane">
    <div>
        <p>You can choose to pre-install the Add-on Collector add-on:</p>
        <?= View::factory('repacks/elements/addons', 
            array('addons' => array($addons_by_id['11950']))
        )->render() ?>
    </div>
    <div class="addon-dependent addon-11950">
        <p>You can choose a set of addons to suggest for installation in your 
        browser by using the collections feature of addons.mozilla.com</p>

        <?= 
        form::fieldset('addons collection', array(), array( 
            form::field('input', 'addons_collection_url', 'Collection URL', array(), array(
                '(eg: https://addons.mozilla.org/en-US/firefox/collection/social)'
            )),
        ))
        ?>
    </div>
</div>
