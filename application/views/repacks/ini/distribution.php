<?php
$r = $repack;
$firstrun_url = str_replace("https://", "http://", $r->releaseUrl()) . '/firstrun';
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

[Preferences]
app.partner.<?= $partner_id ?>=<?= $partner_id . "\n" ?>
startup.homepage_welcome_url="<?= $firstrun_url ?>"
<?php if ($r->persona->loaded): ?>
extensions.personas.initial="<?= addslashes($r->persona->json) ?>"
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
