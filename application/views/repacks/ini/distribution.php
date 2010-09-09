<?php
$r = $repack;
$default_locale = empty($r->default_locale) ? 'en-US' : $r->default_locale;
$dist_id = "byob-{$r->profile->screen_name}-{$r->short_name}";
$partner_id = 'byob' . $r->profile->screen_name;
?>
; Partner distribution.ini file for "<?= $r->title ?>"
; Author email: <?= @$r->profile->logins[0]->email . "\n" ?>
; UUID: <?= $r->uuid . "\n" ?>

[Global]
id=<?=$dist_id ."\n" ?>
version=<?= $r->version . "\n" ?>
about=<?= $r->title . "\n" ?>

[Preferences]
app.partner.<?= $partner_id ?>=<?= $partner_id . "\n" ?>

<?php
$bookmarks = $r->bookmarks;
if (!empty($r->addons_collection_url)) {
    // bug 511869: If there's an addon collection URL, inject a 
    // "Recommended Addons" bookmark into the toolbar.
    $bookmarks['toolbar']['items'][] = array(
        'id'    => 'recommended-addons',
        'type'  => 'bookmark',
        'title' => 'Recommended Addons',
        'description' => 'Recommended Addons',
        'link'  => $r->addons_collection_url,
    );
}
?>
<?php foreach (array('menu', 'toolbar') as $kind): ?>
<?php if (!empty($bookmarks[$kind]) && !empty($bookmarks[$kind]['items'])): ?>
<?php 
    View::factory('repacks/ini/bookmarks', array(
        'set_id' => ucfirst($kind), 
        'bookmarks' => $bookmarks[$kind]['items'],
        'repack' => $repack,
    ))->render(TRUE); 
?>
<?php endif ?>
<?php endforeach ?>

[LocalizablePreferences]
<?php
if ($r->addons_collection_url) {
    $firstrun_url = str_replace("https://", "http://", 
        $r->releaseUrl(null, '%LOCALE%')) . '/firstrun';
} else {
    $firstrun_url = null;
}
?>
<?php if ($firstrun_url): ?>
startup.homepage_welcome_url="<?= $firstrun_url ?>"
<?php endif ?>

# <? # do not edit this line, or add newlines after it ?>
