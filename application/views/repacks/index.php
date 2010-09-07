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
            $create_url = url::site("profiles/{$profile->screen_name}/browsers/create");
        ?>
        <form action="<?=$create_url?>" method="POST">
        <button id="confirm" class="submit required button large yellow"><?=_('Create a New Browser')?></button>
        </form>
    <?php endif ?>
</div>

<?php if ($profile->checkPrivilege('edit')): ?>
<div class="white_box_sidebar">
    <?php $settings_url = url::site("profiles/{$profile->screen_name}/settings/basics"); ?>
    <h3><a href="<?=$settings_url?>"><?=_('Account settings')?></a></h3>
    <ul class="profile_actions">
        <li><a href="<?=$settings_url . '/changepassword'?>"><?=_('Change login password')?></a></li>
        <li><a href="<?=$settings_url . '/changeemail'?>"><?=_('Change login email')?></a></li>
        <li><a href="<?=$settings_url . '/details'?>"><?=_('Edit profile details')?></a></li>
    </ul>
</div>
<?php endif ?>

<div class="white_box">

    <div class="header">
        <?php if (authprofiles::get_profile('screen_name') == $profile->screen_name): ?>
            <h3><?=_('Your browsers')?></h3>
        <?php else: ?>
            <?php /* i18n: %1$s = profile screen name */ ?>
            <h3><?=sprintf(_('Browsers by %1$s'), $profile->screen_name)?></h3>
        <?php endif ?>
    </div>

    <ul class="browsers">

        <?php if (empty($indexed_repacks)): ?>
            <li class="browser browser-none"><?=_('None yet.')?></li>
        <?php endif ?>

        <?php foreach ($indexed_repacks as $uuid=>$repacks): ?>
            <?php
                $main_flag = isset($repacks['released']) ?
                    'released' : 'unreleased';
                $h_title = html::specialchars($repacks[$main_flag]->display_title);
                $h_url   = html::specialchars($repacks[$main_flag]->url);
            ?>
            <li class="browser">

                <h4 class="title"><?=$h_title?></h4>

                <ul class="branches">

                    <?php foreach (array('released', 'unreleased') as $key): ?>
                        <?php
                            if (empty($repacks[$key])) continue; 
                            $repack = $repacks[$key];
                            $h = html::escape_array(array_merge(
                                $repack->as_array(),
                                array(
                                    'kind' => ($repack->isRelease()) ?
                                        _('Current release') : _('In-progress changes'),
                                    'modified' => 
                                        /*i18n: Date format for last modified */            
                                        date(_('M d Y'), strtotime($repack->modified)),
                                )
                            ));
                        ?>
                        <li class="branch <?=$key?>">
                            <?=View::factory('repacks/elements/status')
                                ->set('repack', $repack)->render()?> 
                            <div class="meta">
                                <a href="<?=$repack->url()?>" class="kind"><?=$h['kind']?></a>
                                <?php /*i18n: %1$s = last modified */ ?>
                                <span class="modified"><?=sprintf(_('Last modified <em>%1$s</em>'), $h['modified'])?></span>
                            </div>
                            <?=View::factory('repacks/elements/actions')
                                ->set('repack', $repack)->render() ?>
                        </li>
                    <?php endforeach ?>

                </ul>

            </li>
        <?php endforeach ?>

    </ul>

</div>
