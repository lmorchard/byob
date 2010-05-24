<?php if ($bookmark['type'] != 'folder'): ?>
    <li class="bookmark <?=$bookmark['type']?>">
        <span class="title"><?=html::specialchars($bookmark['title'])?></span>
        <?php if ('livemark' == $bookmark['type']): ?>
            <a target="_new" class="feed" href="<?=html::specialchars($bookmark['feedLink'])?>"><?=html::specialchars($bookmark['feedLink'])?></a>
            <a target="_new" class="location" href="<?=html::specialchars($bookmark['siteLink'])?>"><?=html::specialchars($bookmark['siteLink'])?></a>
        <?php else: ?>
            <a target="_new" class="location" href="<?=html::specialchars($bookmark['link'])?>"><?=html::specialchars($bookmark['link'])?></a>
            <span class="description"><?=html::specialchars($bookmark['description'])?></span>
        <?php endif ?>
    </li>
<?php else: ?>
    <li class="folder">
        <h4><?=html::specialchars($bookmark['title'])?></h4>
        <ul>
            <?php if (empty($bookmark['items'])): ?>
                <li class="empty">None</li>
            <?php else: ?>
                <?php foreach ($bookmark['items'] as $idx=>$bookmark): ?>
                <?php 
                    View::factory('repacks/edit/review_bookmarks', array(
                        'bookmark' => $bookmark
                    ))->render(TRUE); 
                ?>
                <?php endforeach ?>
            <?php endif ?>
        </ul>
        
    </li>
<?php endif ?>
