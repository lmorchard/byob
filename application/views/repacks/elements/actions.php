<?php
$h = html::escape_array(array_merge(
    $repack->as_array(),
    array(
        'url' => $repack->url
    )
));
?>
<?php if ($repack->isRelease()): ?>

    <ul class="actions">
        <li><a href="<?=$h['url']?>">View details</a></li>
        <li><a href="<?=$h['url']?>;edit">Change details</a></li>
        <li><a href="<?=$h['url']?>;revert">Take down release</a></li>
    </ul>

<?php else: ?>

    <ul class="actions">
        <li><a href="<?=$h['url']?>">Preview details</a></li>

        <?php if (!$repack->isLockedForChanges()): ?>
            <li><a href="<?=$h['url']?>;edit">Continue editing</a></li>
            <li><a href="<?=$h['url']?>;delete">Abandon changes</a></li>
        <?php endif ?>

        <?php if ($repack->isPendingApproval()): ?>
            <li><a href="<?=$h['url']?>;cancel">Cancel release</a></li>
            <li><a href="<?=$h['url']?>;approve">Approve release</a></li>
            <li><a href="<?=$h['url']?>;reject">Reject release</a></li>
        <?php else: ?>
            <?php if (!$repack->isLockedForChanges()): ?>
                <li><a href="<?=$h['url']?>;release">Request release</a></li>

            <?php // TEMPORARY HACK AHOY! ?>
            <?php else: ?>
                <?php if ($repack->state == Repack_Model::$states['requested']): ?>
                    <li><a href="<?=$h['url']?>;begin">SIMULATE BUILD START</a></li>
                <?php endif ?>
                <?php if ($repack->state == Repack_Model::$states['started']): ?>
                    <li><a href="<?=$h['url']?>;fail">SIMULATE BUILD FAIL</a></li>
                    <li><a href="<?=$h['url']?>;finish">SIMULATE BUILD FINISH</a></li>
                <?php endif ?>

            <?php endif ?>
        <?php endif ?>
    </ul>

<?php endif ?>
