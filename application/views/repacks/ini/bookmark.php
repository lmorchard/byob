<?php
    $locale_suff = ($repack->default_locale == $locale) ? '' : $locale.'.';
?>
<?php foreach (array('type', 'title', 'link', 'description', 'siteLink', 'feedLink') as $field_name): ?>
<?php if (!empty($bookmark[$field_name])):?>
item.<?=$locale_suff.$idx?>.<?=$field_name?>=<?=$bookmark[$field_name]."\n"?>
<?php endif ?>
<?php foreach ($repack->locales as $locale): ?>
<?php if (!empty($bookmark[$field_name.'.'.$locale])):?>
item.<?=$locale_suff.$idx?>.<?=$field_name?>.<?=$locale?>=<?=$bookmark[$field_name.'.'.$locale]."\n"?>
<?php endif ?>
<?php endforeach ?>
<?php endforeach ?>
<?php if ('folder' == $bookmark['type'] && !empty($bookmark['items'])): ?>
item.<?=$locale_suff.$idx?>.folderId=<?=$bookmark['id']."\n"?>
<?php endif ?>
