<?php
/**
 * @package    BYOB
 * @subpackage i18n
 * @author     l.m.orchard <lorchard@mozilla.com>
 */
$lang = array(
    'short_name' => array(
        'default' => 
            'A short name consisting of letters and numbers is required',
    ),
    'user_title' => array(
        'default' =>
            'A title is required.',
    ),
    'description' => array(
        'length' =>
            'Description must be no longer than 1000 characters'
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
    'addons_collection_url' => array(
        'default' =>
            'Invalid URL for addon collection'
    ),
    'persona_url' => array(
        'default' =>
            'Invalid URL for persona selection'
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
