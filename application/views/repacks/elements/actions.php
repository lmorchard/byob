<?php
$h = html::escape_array(array_merge(
    $repack->as_array(),
    array(
        'url' => $repack->url
    )
));
$privs = $repack->checkPrivileges(array(
    'view', 'view_history', 'edit', 'delete', 'download', 'release',
    'revert', 'approve', 'auto_approve', 'reject', 'cancel', 'begin',
    'finish', 'fail', 'distributionini', 'repackcfg', 'repacklog',
));
$actions = array();
$previews = array();

/*
if ($privs['view'])
    $actions['/'] = "View details";
 */

if ($repack->isRelease()) { 

    if ($privs['edit'])
        $actions[';edit'] = "Change details";
    if ($privs['revert'])
        $actions[';revert'] = "Take down release";

} else { 

    if ($repack->isPendingApproval()) { 

        if ($privs['cancel'])
            $actions[';cancel'] = "Cancel release";
        if ($privs['approve'])
            $actions[';approve'] = "Approve release";
        if ($privs['reject'])
            $actions[';reject'] = "Reject release";

    } else { 

        if (!$repack->isLockedForChanges()) { 
            if ($privs['edit'])
                $actions[';edit'] = "Continue editing";
            if ($privs['delete'])
                $actions[';delete'] = "Abandon changes";
            if ($privs['release']) { 
                $actions[';release'] = "Request release";
            } else { 
                if ($repack->state == Repack_Model::$states['requested']) { 
                    if ($privs['begin'])
                        $actions[';begin'] = "Force build start state";
                } 
                if ($repack->state == Repack_Model::$states['started']) { 
                    if ($privs['fail']) 
                        $actions[';fail'] = "Force build failure state";
                    if ($privs['finish'])
                        $actions[';finish'] = "Force build finish state";
                } 

            } 
        } 

    } 
}

$previews['/firstrun'] = "First-run page";

if ($privs['distributionini']) 
    $previews['/distribution.ini'] = "distribution.ini";
if ($privs['repackcfg'])
    $previews['/repack.cfg'] = "repack.cfg";
if ($privs['repacklog'])
    $previews['/repack.log'] = "repack.log";

?>
<?php if (!empty($actions)): ?>
<div class="main_actions">
    <?php if ($repack->isRelease()): ?>
        <h3>Current release</h3>
    <?php else: ?>
        <h3>In-progress changes to current release</h3>
    <?php endif ?>
    <ul class="actions clearfix">
        <?php foreach ($actions as $url=>$title): ?>
            <li><a href="<?=$h['url'] . $url?>"><?=$title?></a></li>
        <?php endforeach ?>
    </ul>
</div>
<?php endif ?>
<?php if (!empty($previews)): ?>
<div class="main_previews clearfix">
    <h4>Preview:</h4>
    <ul class="previews">
        <?php $first = true; ?>
        <?php foreach ($previews as $url=>$title): ?>
            <li<?=($first)?' class="first"':''?>><a href="<?=$h['url'] . $url?>"><?=$title?></a></li>
            <?php if ($first) $first = false; ?>
        <?php endforeach ?>
    </ul>
</div>
<?php endif ?>
