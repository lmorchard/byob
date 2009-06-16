<?php
$is_error      = !empty($errors[$column_name]);
$error_message = $is_error ? $errors[$column_name] : '';

$h = html::escape_array(compact(
    'column_name', 'column_value', 'error_message'
));
?>
<tr class="<?=$is_error?'error':''?>">
    <th><span><?=$h['column_name']?></span></th>
    <td>
        <?php if ($column_name == $model->primary_key): ?>
            <span class="primary_key"><?=$h['column_value']?></span>
        <?php else: ?>

            <?php
                $field_type = 'text';
                if ('string' == $column_info['type']) {
                    if (!empty($column_info['format'])) {
                        $field_type = 'date';
                    } elseif (empty($column_info['length'])) {
                        $field_type = 'textarea';
                    }
                }
            ?>

            <?php if ('text' == $field_type): ?>

                <input type="text" size="70" class="text"
                     name="<?=$h['column_name']?>" value="<?=$h['column_value']?>" />
            
            <?php elseif ('date' == $field_type): ?>

                <?php // TODO: Calendar widget? ?>
                <input type="text" size="70" class="text"
                     name="<?=$h['column_name']?>" value="<?=$h['column_value']?>" />

            <?php elseif ('textarea' == $field_type): ?>

                <textarea rows="5" cols="70" class="textarea"
                    name="<?=$h['column_name']?>"><?=$h['column_value']?></textarea>

            <?php endif ?>

            <?php if ($is_error): ?>
                <p class="error_message"><?=$h['error_message']?></p>
            <?php endif ?>

        <?php endif ?>
    </td>
</tr>
