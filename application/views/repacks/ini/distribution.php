<?php
$r = $repack;
$partner_id = 'byob' . $r->profile->screen_name;
?>
; Partner distribution.ini file for "<?= $r->title ?>"
; Author email: <?= $r->profile->logins[0]->email . "\n" ?>
; UUID: <?= $r->uuid . "\n" ?>

[Global]
id=byob-<?= $r->profile->screen_name . '-' . $r->short_name . "\n" ?>
version=<?= $r->version . "\n" ?>
about=Mozilla Firefox for <?= $r->profile->org_name . "\n" ?>

[LocalizablePrefs]
app.partner.<?= $partner_id ?>=<?= $partner_id . "\n" ?>
browser.startup.homepage=<?= $r->url . '/startpage' . "\n" ?>
browser.startup.homepage_reset=<?= $r->url . '/firstrun' . "\n" ?>

<?php $bookmarks_menu = $r->bookmarks_menu; ?>
<?php if (!empty($bookmarks_menu)): ?>
[BookmarksMenu]
<?php 
foreach ($bookmarks_menu as $idx=>$bookmark) {
    View::factory('repacks/ini/bookmark', array(
        'idx' => $idx, 'bookmark' => $bookmark
    ))->render(TRUE);
}
?>
<?php endif ?>

<?php $bookmarks_toolbar = $r->bookmarks_toolbar; ?>
<?php if (!empty($bookmarks_toolbar)): ?>
[BookmarksToolbar]
<?php 
foreach ($bookmarks_toolbar as $idx=>$bookmark) {
    View::factory('repacks/ini/bookmark', array(
        'idx' => $idx, 'bookmark' => $bookmark
    ))->render(TRUE);
}
?>
<?php endif ?>

# <? # do not edit this line, or add newlines after it ?>
