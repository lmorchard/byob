<?php
$h = html::escape_array(array_merge(
    $repack->as_array(),
    array(
        'url' => $repack->url
    )
));
$privs = $repack->checkPrivileges();
?>
<?php if ($repack->isRelease()): ?>

    <ul class="actions">
        <?php if ($privs['view']): ?>
            <li><a href="<?=$h['url']?>">View details</a></li>
        <?php endif ?>
        <?php if ($privs['edit']): ?>
            <li><a href="<?=$h['url']?>;edit">Change details</a></li>
        <?php endif ?>
        <?php if ($privs['revert']): ?>
            <li><a href="<?=$h['url']?>;revert">Take down release</a></li>
        <?php endif ?>
        <?php if ($privs['distributionini']): ?>
            <li><a href="<?=$h['url']?>/distribution.ini">Preview distribution.ini</a></li>
        <?php endif ?>
        <?php if ($privs['repackcfg']): ?>
            <li><a href="<?=$h['url']?>/repack.cfg">Preview repack.cfg</a></li>
        <?php endif ?>
    </ul>

<?php else: ?>

    <ul class="actions">
        <?php if ($privs['view']): ?>
            <li><a href="<?=$h['url']?>">Preview details</a></li>
        <?php endif ?>

        <?php if ($privs['edit']): ?>
            <li><a href="<?=$h['url']?>;edit">Continue editing</a></li>
        <?php endif ?>
        <?php if ($privs['delete']): ?>
            <li><a href="<?=$h['url']?>;delete">Abandon changes</a></li>
        <?php endif ?>

        <?php if ($repack->isPendingApproval()): ?>
            <?php if ($privs['cancel']): ?>
                <li><a href="<?=$h['url']?>;cancel">Cancel release</a></li>
            <?php endif ?>
            <?php if ($privs['approve']): ?>
                <li><a href="<?=$h['url']?>;approve">Approve release</a></li>
            <?php endif ?>
            <?php if ($privs['reject']): ?>
                <li><a href="<?=$h['url']?>;reject">Reject release</a></li>
            <?php endif ?>
        <?php else: ?>
            <?php if (!$repack->isLockedForChanges()): ?>
                <?php if ($privs['release']): ?>
                    <li><a href="<?=$h['url']?>;release">Request release</a></li>
                <?php endif ?>
            <?php // TEMPORARY HACK AHOY! ?>
            <?php else: ?>
                <?php if ($repack->state == Repack_Model::$states['requested']): ?>
                    <?php if ($privs['begin']): ?>
                        <li><a href="<?=$h['url']?>;begin">Force build start state</a></li>
                    <?php endif ?>
                <?php endif ?>
                <?php if ($repack->state == Repack_Model::$states['started']): ?>
                    <?php if ($privs['fail']): ?>
                        <li><a href="<?=$h['url']?>;fail">Force build failure state</a></li>
                    <?php endif ?>
                    <?php if ($privs['finish']): ?>
                        <li><a href="<?=$h['url']?>;finish">Force build finish state</a></li>
                    <?php endif ?>
                <?php endif ?>

            <?php endif ?>
        <?php endif ?>
        <?php if ($privs['distributionini']): ?>
            <li><a href="<?=$h['url']?>/distribution.ini">Preview distribution.ini</a></li>
        <?php endif ?>
        <?php if ($privs['repackcfg']): ?>
            <li><a href="<?=$h['url']?>/repack.cfg">Preview repack.cfg</a></li>
        <?php endif ?>
        <?php if ($privs['repacklog']): ?>
            <li><a href="<?=$h['url']?>/repack.log">Preview repack.log</a></li>
        <?php endif ?>
    </ul>

<?php endif ?>
