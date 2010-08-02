<div class="intro">
    <p><?=_('You can customize the content displayed when your browser is started for the first time.')?></p>
</div>
<div class="pane">
    <div>
        <?= 
        form::fieldset(_('first run page'), array(), array( 
            form::field('textarea', 'firstrun_content', 'content'),
        ))
        ?>
    </div>

    <div>
        <p><?=_('You can choose a set of addons to suggest for installation in your browser by using the collections feature of addons.mozilla.com')?></p>

        <?= 
        form::fieldset('addons collection', array(), array( 
            form::field('input', 'addons_collection_url', 'Collection URL', array(), array(
                _('(eg: https://addons.mozilla.org/en-US/firefox/collection/social)')
            )),
        ))
        ?>
    <div>
<div>
