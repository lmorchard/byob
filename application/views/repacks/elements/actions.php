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

if ($repack->isRelease()) { 

    if ($privs['view'])
        $actions['/'] = "View details";
    if ($privs['edit'])
        $actions[';edit'] = "Change details";
    if ($privs['revert'])
        $actions[';revert'] = "Take down release";

} else { 

    if ($privs['view']) 
        $actions['/'] = "Preview details";
    if ($privs['edit'])
        $actions[';edit'] = "Continue editing";
    if ($privs['delete'])
        $actions[';delete'] = "Abandon changes";

    if ($repack->isPendingApproval()) { 

        if ($privs['cancel'])
            $actions[';cancel'] = "Cancel release";
        if ($privs['approve'])
            $actions[';approve'] = "Approve release";
        if ($privs['reject'])
            $actions[';reject'] = "Reject release";

    } else { 

        if (!$repack->isLockedForChanges()) { 
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

$actions['/firstrun'] = "Preview first-run page";

if ($privs['distributionini']) 
    $actions['/distribution.ini'] = "Preview distribution.ini";
if ($privs['repackcfg'])
    $actions['/repack.cfg'] = "Preview repack.cfg";
if ($privs['repacklog'])
    $actions['/repack.log'] = "View repack.log";

?>
<ul class="actions">
    <?php foreach ($actions as $url=>$title): ?>
        <li><a href="<?=$h['url'] . $url?>"><?=$title?></a></li>
    <?php endforeach ?>
</ul>
