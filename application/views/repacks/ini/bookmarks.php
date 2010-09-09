<?php
    $sub_folders = array();
?>

[Bookmarks<?= $set_id ?>]
<?php foreach ($bookmarks as $idx=>$bookmark): ?>
<?php View::factory('repacks/ini/bookmark', array(
    'idx' => $idx+1, 
    'bookmark' => $bookmark,
    'repack' => $repack,
    'locale' => $locale,
))->render(TRUE); ?>
<?php
    if ('folder' == $bookmark['type'] && !empty($bookmark['items'])) {
        $sub_folders[] = View::factory('repacks/ini/bookmarks', array(
            'set_id' => "Folder-{$bookmark['id']}",
            'bookmarks' => $bookmark['items'],
            'repack' => $repack,
            'locale' => $locale,
        ))->render(FALSE);
    }
?>
<?php endforeach ?>
<?php 
    foreach ($sub_folders as $sub_folder) {
        echo $sub_folder;
    }
?>
