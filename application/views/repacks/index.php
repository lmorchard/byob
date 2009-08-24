<?php 
    $u_screen_name = urlencode($profile->screen_name);
    $h_screen_name = html::specialchars($profile->screen_name);
?>
<?php slot::set('head_title', 'profile :: ' . $h_screen_name); ?>
<?php slot::set('crumbs', 'profile :: ' . $h_screen_name); ?>

<div class="intro">
    <h2><?=$h_screen_name?>'s profile</h2>
    <?php if (authprofiles::is_logged_in() && 
        authprofiles::get_profile('screen_name') == $profile->screen_name): ?>
        <?php
            $create_url = url::base() . "profiles/{$profile->screen_name}/browsers/create";
        ?>
        <form action="<?=$create_url?>" method="POST">
            <input type="image" name="confirm" id="confirm" value="yes" 
                src="<?=url::base()?>img/create-a-new-browser-button.gif" />
        </form>
    <?php endif ?>
</div>

<?php if ($profile->checkPrivilege('edit')): ?>
<div class="white_box_sidebar">
    <?php $settings_url = url::base() . "profiles/{$profile->screen_name}/settings/basics/"; ?>
    <h3><a href="<?=$settings_url?>">Account settings</a></h3>
    <ul class="actions">
        <li><a href="<?=$settings_url . 'changepassword'?>">Change login password</a></li>
        <li><a href="<?=$settings_url . 'changeemail'?>">Change login email</a></li>
        <li><a href="<?=$settings_url . 'changeemail'?>">Edit profile details</a></li>
    </ul>
</div>
<?php endif ?>

<div class="white_box">

    <div class="header">
        <?php if (authprofiles::get_profile('screen_name') == $profile->screen_name): ?>
            <h3>Your browsers</h3>
        <?php else: ?>
            <h3>Browsers by <?= html::specialchars($profile->screen_name) ?></h3>
        <?php endif ?>
    </div>

    <ul class="browsers">

        <?php if (empty($indexed_repacks)): ?>
            <li class="browser browser-none">None yet.</li>
        <?php endif ?>

        <?php foreach ($indexed_repacks as $uuid=>$repacks): ?>
            <?php
                $main_flag = isset($repacks['released']) ?
                    'released' : 'unreleased';
                $h_title = html::specialchars($repacks[$main_flag]->display_title);
                $h_url   = html::specialchars($repacks[$main_flag]->url);
            ?>
            <li class="browser">

                <h4 class="title"><a href="<?=$h_url?>"><?=$h_title?></a></h4>

                <ul class="branches">

                    <?php foreach (array('released', 'unreleased') as $key): ?>
                        <?php
                            if (empty($repacks[$key])) continue; 
                            $repack = $repacks[$key];
                            $h = html::escape_array(array_merge(
                                $repack->as_array(),
                                array(
                                    'kind' => ($repack->isRelease()) ?
                                        'Current release' : 'In-progress changes',
                                    'modified' => 
                                        date('M d Y', strtotime($repack->modified)),
                                )
                            ));
                        ?>
                        <li class="branch <?=$key?>">
                            <?=View::factory('repacks/elements/status')
                                ->set('repack', $repack)->render()?> 
                            <div class="meta">
                                <span class="kind"><?=$h['kind']?></span>
                                <span class="modified">Last modified <em><?=$h['modified']?></em></span>
                            </div>
                            <?=View::factory('repacks/elements/actions')
                                ->set('repack', $repack)->render() ?>
                        </li>
                    <?php endforeach ?>

                </ul>

            </li>
        <?php endforeach ?>

    </ul>

    <?php /*
    <ul class="browsers">

        <?php if (empty($indexed_repacks)): ?>
            <li>None yet.</li>
        <?php endif ?>

        <?php foreach ($indexed_repacks as $uuid=>$repacks): ?>
            <?php
                $primary_repack = null;
                $primary_flag = null;
                foreach (array('released', 'unreleased') as $key) {
                    if (!empty($repacks[$key]) && 
                            $repacks[$key]->checkPrivilege('view')) {
                        $primary_flag = $key;
                        $primary_repack = $repacks[$key];
                        continue;
                    }
                }
                if (empty($primary_repack)) continue;

                $h = html::escape_array(array_merge(
                    $primary_repack->as_array(),
                    array( 
                        'url' => 
                            $primary_repack->url(),
                        'modified' => 
                            date('M d Y', strtotime($primary_repack->modified)),
                    )
                ));
                $privs = $primary_repack->checkPrivileges(array(
                    'view', 'view_history', 'edit', 'delete', 'download', 'release',
                    'revert', 'approve', 'auto_approve', 'reject', 'cancel', 'begin',
                    'finish', 'fail', 'distributionini', 'repackcfg', 'repacklog',
                ));

            ?>
            <li class="browser">
                <?php
                    $display_state = null;
                    $state_name = $primary_repack->getStateName();
                    if ($privs['edit']) {
                        if (in_array($state_name, array('requested', 'pending', 'started', 'failed'))) {
                            $display_state = 'under_review';
                        } else if ('rejected' == $state_name) {
                            $display_state = 'changes_requested';
                        } else if ('released' == $state_name) {
                            $display_state = 'download';
                        }
                    }
                ?>

                <?php if (true ||null !== $display_state): ?>
                <div class="state"><?=$display_state . ' / ' . $state_name ?>
                    </div>
                <?php endif ?>

                <div class="title">
                    <h4><a href="<?=$h['url']?>"><?=$h['title']?></a></h4>
                    <?php if ($privs['edit']): ?>
                        <div class="edit"><a href="<?=$h['url']?>;edit">edit</a></div>
                    <?php endif ?>
                </div>

                <div class="release_meta">
                    <?php if (empty($repacks['released'])): ?>
                        <span>No current release</span>
                        <span>Last modified <?=$h['modified']?></span>
                    <?php else: ?>
                        <span>Released on <?=$h['modified']?></span> 
                    <?php endif ?>
                </div>

                <?php if ($privs['release']): ?>
                    <div class="release_actions">
                        <?php if (!$primary_repack->isRelease() &&
                                !$primary_repack->isLockedForChanges() &&
                                !$primary_repack->isPendingApproval()): ?>
                            <span class="release"><a href="<?=$h['url']?>;release">Request Release</a></span>
                        <?php else: ?>
                            <span class="no_release"><span>Request Release</span></span>
                        <?php endif ?>
                        <a class="firstrun" href="<?=$h['url']?>/firstrun">Preview first-run page</a>
                    </div>
                <?php endif ?>

            </li>
        <?php endforeach ?>

    </ul>
    */ ?>
</div>
