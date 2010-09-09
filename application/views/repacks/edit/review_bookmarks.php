<?php if ($bookmark['type'] != 'folder'): ?>
    <li class="bookmark <?=$bookmark['type']?>">
        <?php
            $fields = ('livemark' == $bookmark['type']) ? 
                array(
                    'title' => array(_('Title'), 'title', False),
                    'feedLink' => array(_('Feed'), 'feed', True),
                    'siteLink' => array(_('Site'), 'location', True),
                )
                :
                array(
                    'title' => array(_('Title'), 'title', False),
                    'link' => array(_('Link'), 'location', True),
                    'description' => array(_('Description'), 'description', False),
                );
        ?>
        <table>
        <?php foreach ($fields as $name => $spec): ?>
            <?php list($label, $cls, $is_link) = $spec; ?>
            <?php foreach ($repack->locales as $locale): ?>
                <?php
                    $is_default = ($repack->default_locale == $locale);
                    $l_name = $is_default ? $name : "{$name}.{$locale}";
                    $locale_label = $is_default ? '' : 
                        ' <span class="locale">(' . html::specialchars($locale) . ')</span> ';
                    if (empty($bookmark[$l_name])) continue;
                    $h_val = html::specialchars($bookmark[$l_name]);
                ?>
                <tr class="field"><th class="label"><?=$label.$locale_label?></th><td>
                <?php if (!$is_link): ?>
                    <span class="<?=$cls?>"><?=$h_val?></span>
                <?php else: ?>
                    <a target="_new" class="<?=$cls?>" href="<?=$h_val?>"><?=$h_val?></a>
                <?php endif ?></td></tr>
            <?php endforeach ?>
        <?php endforeach ?>
        </table>
    </li>
<?php else: ?>
    <?php
        $items_name = (empty($locale) || $repack->default_locale == $locale) ?
            'items' : "items.{$locale}";
    ?>
    <li class="folder">
        <h4><?=html::specialchars($bookmark['title'])?></h4>
        <ul>
            <?php if (empty($bookmark[$items_name])): ?>
                <li class="empty"><?=_('None')?></li>
            <?php else: ?>
                <?php foreach ($bookmark[$items_name] as $idx=>$bookmark): ?>
                <?php 
                    View::factory('repacks/edit/review_bookmarks', array(
                        'bookmark' => $bookmark, 'repack' => $repack
                    ))->render(TRUE); 
                ?>
                <?php endforeach ?>
            <?php endif ?>
        </ul>
        
    </li>
<?php endif ?>
