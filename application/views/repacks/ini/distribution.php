<?php
$r = $repack;
$partner_id = 'byob' . $r->created_by->screen_name;
?>
; Partner distribution.ini file for "<?= $r->title ?>"
; Author email: <?= $r->created_by->logins[0]->email . "\n" ?>
; UUID: <?= $r->uuid . "\n" ?>

[Global]
id=byob-<?= $r->created_by->screen_name . '-' . $r->short_name . "\n" ?>
version=<?= $r->version . "\n" ?>
about=Mozilla Firefox for <?= $r->created_by->org_name . "\n" ?>

[LocalizablePrefs]
app.partner.<?= $partner_id ?>=<?= $partner_id . "\n" ?>
browser.startup.homepage=<?= $r->url . '/startpage' . "\n" ?>
browser.startup.homepage_reset=<?= $r->url . '/firstrun' . "\n" ?>

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
