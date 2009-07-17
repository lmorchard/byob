<div>

    <p>You can choose some addons to come pre-installed in your browser:</p>

    <div>
        <?php
            $addons_selected = form::value('addons');
            if (empty($addons_selected)) $addons_selected = array();
        ?>
        <ul class="repack-addons">
            <?php foreach ($addons as $addon): ?>
                <?php
                    $selected = in_array($addon->id, $addons_selected);
                    $h = html::escape_array(array(
                        'icon'    => $addon->icon,
                        'name'    => $addon->name,
                        'summary' => $addon->summary,
                    ));
                ?>
                <li class="addon">
                    <?= form::checkbox("addons[]", $addon->id, $selected) ?>
                    <img src="<?=$h['icon']?>" /> <?=$h['name']?>
                    <p><?=$h['summary']?></p>
                </li>
            <?php endforeach ?>
        </ul>
    </div>

</div>
