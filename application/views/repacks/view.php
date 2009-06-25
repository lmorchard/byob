<?php slot::set('head_title', ' :: view :: ' . html::specialchars($repack->title)); ?>
<?php slot::start('page_title') ?>
    :: view :: <a href="<?= url::base() . url::current() ?>"><?= html::specialchars($repack->title) ?></a>
<?php slot::end() ?>

<?php 
View::factory('repacks/details')
    ->set('repack', $repack)->render(true); 
?>

<?php if (AuthProfiles::get_profile('id') == $repack->created_by->id): ?>

    <?php $rp_url = $repack->url; ?>
    <div>
        <h3>Admin options</h3>
        <ul class="options">
            <li><a href="<?= $rp_url . ';edit' ?>">Edit details.</a></li>
            <li><a href="<?= $rp_url . ';delete' ?>">Delete this browser.</a></li>
            <li><a href="<?= $rp_url . ';release' ?>">Generate a release.</a></li>
            <li><a href="<?= $rp_url . '/startpage' ?>">Preview the start page.</a></li>
            <li><a href="<?= $rp_url . '/firstrun' ?>">Preview the first run page.</a></li>
            <li><a href="<?= $rp_url . '/xpi-config.ini' ?>">View the xpi-config.ini</a></li>
            <li><a href="<?= $rp_url . '/distribution.ini' ?>">View the distribution.ini</a></li>
        </ul>
    </div>

    <?php if (!empty($queued)): ?>
    <div>
        <h3>Releases in Progress</h3>
        <ul class="pending">
            <?php foreach ($queued as $q): ?>
                <li>
                    <span><?= html::specialchars($q['repack']->product['name']) ?></span>
                    <span><?= html::specialchars($q['repack']->product['version']) ?></span>
                    (
                    rev <span><?= html::specialchars($q['repack']->version) ?></span>,
                    scheduled at <span><?= html::specialchars($q['msg']->created) ?></span>
                    )
                </li>
            <?php endforeach ?>
        </ul>
    </div>
    <?php endif ?>

<?php endif ?>

<div>
    <h3>Releases</h3>
    <ul class="releases">
        <?php if (empty($releases)): ?>
            <li>No releases available.</li>
        <?php else: ?>
            <?php foreach ($releases as $release): ?>
                <li>
                    <span class="rev">rev <?= html::specialchars($release['rev']) ?></span>
                    <ul class="files">
                        <?php foreach ($release['files'] as $file): ?>
                            <?php $url = "downloads/{$repack->uuid}/{$release['rev']}/{$file}"; ?>
                            <li><a href="<?= url::base() . $url ?>"><?= html::specialchars($file) ?></a></li>
                        <?php endforeach ?>
                    </ul>
                </li>
            <?php endforeach ?>
        <?php endif ?>
    </ul>
</div>
