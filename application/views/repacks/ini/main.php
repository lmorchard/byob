<?php
$r = $repack;
$partner_id = 'byob' . $r->created_by_user->username;
?>
; Partner XPI configuration file for "<?= $r->title ?>"
; Author email: <?= $r->created_by_user->email . "\n" ?>
; UUID: <?= $r->uuid . "\n" ?>

name=byob-<?= $r->created_by_user->username ?>-<?= $r->short_name . "\n" ?>
version=<?= $r->version . "\n" ?>
ini.version=1.3
output=dex.xpi
locales=<?= join(',', $r->locales) ."\n" ?>

[Repack]
platforms=<?php
    $platforms = array();
    if ($r->repack_win) { array_push($platforms, "win"); }
    if ($r->repack_mac) { array_push($platforms, "mac"); }
    if ($r->repack_linux) { array_push($platforms, "unix"); }
    echo join(",", $platforms) . "\n"; 
?>
locales=<?= join(',', $r->locales) . "\n" ?>
firefox.version=<?= $r->product['version'] . "\n"; ?>
base.url=<?= $r->product['url'] . "\n" ?>
disable.migration=<?= (($r->product['disable_migration']) ? 'true' : 'false') . "\n"?>

[InstallPrefs]
minversion=<?= $r->min_version ?> 
maxversion=<?= $r->max_version ?> 
summary=<?= $r->title ?> 
description=<?= $r->description ?> 
creator=<?= $r->created_by ?> 
url=<?= $r->url() ?> 
hidden=<?= (($r->hidden) ? 'true' : 'false') . "\n" ?>

[GlobalLocalizablePrefs]
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
