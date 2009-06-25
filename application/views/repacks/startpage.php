<?php slot::set('head_title', ' :: ' . html::specialchars($repack->title)); ?>
<?php slot::start('page_title') ?>
    :: <a href="<?= url::base() . url::current() ?>"><?= html::specialchars($repack->title) ?></a>
<?php slot::end() ?>

<div class="homepage_content">
    <p><?= $content ?></p>
</div>

<?php if (!empty($feed_items)): ?>
    <ul class="feed_items">
        <?php foreach ($feed_items as $item): ?>
            <li>
                <h3><a href="<?= html::specialchars($item['link']) ?>"><?= html::specialchars($item['title']) ?></a></h3>
                <p><?= ($item['description']) ?></p>
            </li>
        <?php endforeach?>
    </ul>
<?php endif ?>
