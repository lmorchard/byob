<?php 
$type = isset($item['type']) ? $item['type'] : 'normal';
$fields = Repack_Model::$type_fields[$type];
?>
<li class="bookmark clearfix <?=$prefix?>_item">
    <span class="type <?= $type ?>">
        <?= html::specialchars($type) ?>
        <input type="hidden" name="<?= "{$prefix}_type[{$idx}]" ?>" 
            value="<?= html::specialchars($type) ?>" />
    </span>
    <table>
        <tr>
            <?php foreach ($fields as $name=>$label): ?>
                <?php 
                    $field_name = "{$prefix}_{$name}[{$idx}]";
                    $is_error = !empty(form::$errors[$field_name]); 
                ?>
                <th class="<?= html::specialchars($name) ?>">
                    <span class="<?= $is_error ? 'error' : '' ?>"><?= html::specialchars($label) ?></span>
                </th>
            <?php endforeach ?>
        </tr>
        <tr class="<?= $type ?>">
            <?php foreach ($fields as $name=>$label): ?>
                <?php 
                    $field_name = "{$prefix}_{$name}[{$idx}]";
                    $is_error = !empty(form::$errors[$field_name]); 
                ?>
                <td><span class="<?= $is_error ? 'error' : '' ?>"><?= 
                    form::input("{$prefix}_{$name}[{$idx}]", @$item[$name]) 
                ?></span></td>
            <?php endforeach ?>
        </tr>
    </table>
    <a href="#" title="delete this bookmark" class="delete">[X]</a>
</li>
