<?php foreach (array('type', 'title', 'link', 'description', 'siteLink', 'feedLink') as $field_name): ?>
<?php if (!empty($bookmark[$field_name])):?>
item.<?=$idx?>.<?=$field_name?>=<?=$bookmark[$field_name]."\n"?>
<?php endif ?>
<?php foreach ($repack->locales as $locale): ?>
<?php if (!empty($bookmark[$field_name.'.'.$locale])):?>
item.<?=$idx?>.<?=$field_name?>.<?=$locale?>=<?=$bookmark[$field_name.'.'.$locale]."\n"?>
<?php endif ?>
<?php endforeach ?>
<?php endforeach ?>
<?php if ('folder' == $bookmark['type'] && !empty($bookmark['items'])): ?>
item.<?=$idx?>.folderId=<?=$bookmark['id']."\n"?>
<?php endif ?>
