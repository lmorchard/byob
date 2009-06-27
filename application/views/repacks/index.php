<?php if (authprofiles::is_logged_in() && 
    authprofiles::get_profile('screen_name') == $profile->screen_name): ?>
    <p>
        <?php
            $create_url = url::base() .
                "profiles/{$profile->screen_name}/browsers;create";
        ?>
        <a href="<?=$create_url?>">Create new browser</a>
    </p>
<?php endif ?>

<h3>Browsers by <?= html::specialchars($profile->screen_name) ?></h3>

<ul>

    <?php if (empty($indexed_repacks)): ?>
        <li>None yet.</li>
    <?php endif ?>

    <?php foreach ($indexed_repacks as $uuid=>$repacks): ?>
        <?php
            $main_flag = isset($repacks['released']) ?
                'released' : 'unreleased';
            $h_title = html::specialchars($repacks[$main_flag]->title);
            $h_url   = html::specialchars($repacks[$main_flag]->url);
        ?>
        <li>

            <a href="<?=$h_url?>"><?=$h_title?></a>
            <ul>

                <?php if (empty($repacks['released'])): ?>
                    <li><span>No current release.</span></li>
                <?php endif ?>

                <?php foreach ($repacks as $key=>$repack): ?>
                    <li>
                        <span>
                            <?=View::factory('repacks/elements/status')
                                ->set('repack', $repack)->render()?> 
                        </span>
                        <?=View::factory('repacks/elements/actions')
                            ->set('repack', $repack)->render() ?>
                    </li>
                <?php endforeach ?>

            </ul>

        </li>
    <?php endforeach ?>

</ul>
