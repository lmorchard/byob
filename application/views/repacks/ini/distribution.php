<?php
$r = $repack;
$partner_id = 'byob' . $r->created_by_user->username;
?>
; Partner distribution.ini file for "<?= $r->title ?>"
; Author email: <?= $r->created_by_user->email . "\n" ?>
; UUID: <?= $r->uuid . "\n" ?>

[Global]
id=byob-<?= $r->created_by_user->username . '-' . $r->short_name . "\n" ?>
version=<?= $r->version . "\n" ?>
about=<?= $r->title . "\n" ?>

[LocalizablePrefs]
app.partner.<?= $partner_id ?>=<?= $partner_id . "\n" ?>
browser.startup.homepage=<?= $r->url() . '/startpage' . "\n" ?>
browser.startup.homepage_reset=<?= $r->url() . '/firstrun' . "\n" ?>

<?php if (!empty($r->bookmarks_menu)): ?>
[BookmarksMenu]
<?php 
foreach ($r->bookmarks_menu as $idx=>$bookmark) {
    View::factory('repacks/ini/bookmark', array(
        'idx' => $idx, 'bookmark' => $bookmark
    ))->render(TRUE);
}
?>
<?php endif ?>

<?php if (!empty($r->bookmarks_toolbar)): ?>
[BookmarksToolbar]
<?php 
foreach ($r->bookmarks_toolbar as $idx=>$bookmark) {
    View::factory('repacks/ini/bookmark', array(
        'idx' => $idx, 'bookmark' => $bookmark
    ))->render(TRUE);
}
?>
<?php endif ?>

# <? # do not edit this line, or add newlines after it ?>
