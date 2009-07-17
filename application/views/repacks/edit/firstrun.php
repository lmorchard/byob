<div>
    <p>You can customize the content displayed when your browser is started for the first time.</p>


    <?= 
    form::fieldset('first run page', array(), array( 
        form::field('textarea', 'firstrun_content', 'content'),
    ))
    ?>
</div>

<div>

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
