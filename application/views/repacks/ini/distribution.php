<?php
$r = $repack;
$dist_id = "byob-{$r->profile->screen_name}-{$r->short_name}";
$partner_id = 'byob' . $r->profile->screen_name;
?>
; Partner distribution.ini file for "<?= $r->title ?>"
; Author email: <?= $r->profile->logins[0]->email . "\n" ?>
; UUID: <?= $r->uuid . "\n" ?>

[Global]
id=<?=$dist_id ."\n" ?>
version=<?= $r->version . "\n" ?>
about=<?= $r->title . "\n" ?>

[LocalizablePrefs]
app.partner.<?= $partner_id ?>=<?= $partner_id . "\n" ?>
<?php if (!empty($r->firstrun_content)): ?>
browser.startup.homepage_reset=<?= $r->url . '/firstrun' . "\n" ?>
<?php endif ?>

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

<?php $r->bookmarks_toolbar = $r->bookmarks_toolbar; ?>
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
