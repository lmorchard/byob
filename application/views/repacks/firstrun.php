<?php slot::set('head_title', ' :: ' . html::specialchars($repack->title)); ?>
<?php slot::start('page_title') ?>
    :: <a href="<?= url::base() . url::current() ?>"><?= html::specialchars($repack->title) ?></a>
<?php slot::end() ?>

<h2>Welcome to <?= html::specialchars($repack->title) ?></h2>

<h3>Suggested addons:</h3>

<?php if (!empty($repack->collection_addons)): ?>
    <ul class="repack-addons">
        <?php foreach ($repack->collection_addons as $addon): ?>
            <?php
                $h = html::escape_array(array(
                    'id'      => $addon->id,
                    'icon'    => $addon->icon,
                    'name'    => $addon->name,
                    'summary' => $addon->summary,
                ));
            ?>
            <li class="addon">
                <a href="https://addons.mozilla.org/en-US/firefox/addon/<?=$h['id']?>" target="_new"><img src="<?=$h['icon']?>" /> <?=$h['name']?></a>
                <p><?=$h['summary']?></p>
            </li>
        <?php endforeach ?>
    </ul>
<?php endif ?>
