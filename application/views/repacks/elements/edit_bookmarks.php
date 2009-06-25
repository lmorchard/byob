<?php
$items = form::value($prefix, array());
?>
<ul class="bookmarks"><?php 
    if (!empty($items)) foreach ($items as $idx => $item) {
        View::factory('repacks/elements/edit_bookmark', array(
            'prefix' => $prefix,
            'item'   => $item,
            'idx'    => $idx
        ))->render(true);
    }
?></ul>

<div class="bookmark-add-options">
    <?php foreach (Repack_Model::$bookmark_types as $name=>$label): ?>
    <div class="bookmark-add bookmark-add-<?= html::specialchars($name) ?>">
        <a class="add" href="#">Add a new <?= html::specialchars($label) ?> bookmark</a>
        <ul class="template">
        <?php
            View::factory('repacks/elements/edit_bookmark', array(
                'prefix' => $prefix,
                'item'   => array('type'=>$name),
                'idx'    => ''
            ))->render(true);
        ?>
        </ul>
    </div>
    <?php endforeach ?>
</div>
