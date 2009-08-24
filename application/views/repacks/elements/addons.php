<?php
$addons_displayed = array();
$addons_selected = form::value('addons');
if (empty($addons_selected)) $addons_selected = array();
?>
<ul class="repack-addons"><?php foreach ($addons as $addon): ?>
    <?php
    $addons_displayed[] = $addon->id;
    $selected = in_array($addon->id, $addons_selected);
    $h = html::escape_array(array(
        'icon'    => $addon->icon,
        'name'    => $addon->name,
        'summary' => $addon->summary,
    ));
    ?>
    <li class="addon">
        <?= form::checkbox("addons[]", $addon->id, $selected) ?>
        <span class="title">
            <img src="<?=$h['icon']?>" />
            <span><?=$h['name']?></span>
        </span>
        <p><?=$h['summary']?></p>
    </li>
<?php endforeach ?></ul>
<?php foreach ($addons_selected as $id): ?>
    <?php if (in_array($id, $addons_displayed)) continue; ?>
    <input type="hidden" name="addons[]" value="<?=$id?>" />
<?php endforeach ?>
