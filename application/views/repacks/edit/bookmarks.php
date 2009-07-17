<div>
    <p>You can add up to 5 custom bookmarks to your browser's bookmark menu.</p>
    <?php
        View::factory('repacks/elements/edit_bookmarks', array(
            'prefix' => 'bookmarks_menu'
        ))->render(true);
    ?>
</div>

<div>
    <p>You can add up to 3 custom bookmarks to your browser's link toolbar.</p>
    <?php
        View::factory('repacks/elements/edit_bookmarks', array(
            'prefix' => 'bookmarks_toolbar'
        ))->render(true);
    ?>
</div>
