<?php
/**
 *
 */
$lang = array(
    'short_name' => array(
        'default' => 
            'A short name consisting of letters and numbers is required',
    ),
    'title' => array(
        'default' =>
            'A title is required.',
    ),
    'locales' => array(
        'default' =>
            'At least one locale is required'
    ),
    'os' => array(
        'default' =>
            'At least one OS platform is required'
    ),
    'bookmarks_menu' => array(
        'default' =>
            'Problems in bookmark menu items.'
    ),
    'bookmarks_toolbar' => array(
        'default' =>
            'Problems in bookmark menu items.'
    ),
);

// Since there are multiple fields for bookmarks, programmatically construct 
// the error messages.
$limits = array(
    'bookmarks_menu' => array('Bookmark menu item #%s: ', 5),
    'bookmarks_toolbar' => array('Bookmark toolbar item #%s: ', 3)
);
$field_default_msgs = array(
    'title'    => 'Title is required',
    'location' => 'Valid URL for location is required',
    'feed'     => 'Valid URL for feed is required',
);
foreach ($limits as $kind=>$stuff) {
    list($title, $count) = $stuff;
    foreach ($field_default_msgs as $field=>$msg) {
        for ($idx=0; $idx<$count; $idx++) {
            $lang["{$kind}_{$field}[{$idx}]"] = array(
                'default' => sprintf($title, $idx+1) . $msg
            );
        }
    }
}
