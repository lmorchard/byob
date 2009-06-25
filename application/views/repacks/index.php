<h3>Browsers for <?= html::specialchars($profile->screen_name) ?></h3>
<ul>
    <?php foreach ($repacks as $repack): ?>
        <li><a href="<?= $repack->url() ?>"><?= html::specialchars($repack->title) ?></a>
    <?php endforeach ?>
</ul>
