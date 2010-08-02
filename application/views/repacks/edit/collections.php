<div class="intro">
    <p><?=_('You can choose a set of addons to suggest for installation in your browser by using the collections feature of addons.mozilla.com (eg: https://addons.mozilla.org/en-US/firefox/collection/social)')?></p>
</div>
<div class="pane">
    <fieldset><legend><?=_('Add-ons Collection URL:')?></legend>
        <div>
            <?= form::input('addons_collection_url', form::value('addons_collection_url')) ?>
        </div>
    </fieldset>
</div>
