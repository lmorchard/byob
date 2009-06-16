<?php
$is_error      = !empty($errors['new_password']);
$error_message = $is_error ? $errors['new_password'] : '';

$h = html::escape_array(compact(
    'column_name', 'column_value', 'error_message'
));
?>
<tr class="<?=$is_error?'error':''?>">
    <th><span>Change password?</span></th>
    <td>
        <input type="checkbox" name="change_password" />
        <?php if ($is_error): ?>
            <p class="error_message"><?=$h['error_message']?></p>
        <?php endif ?>
    </td>
</tr>
<tr class="<?=$is_error?'error':''?>">
    <th><span>New password</span></th>
    <td>
        <input type="password" size="70" class="text"
             name="new_password" value="" />
    </td>
</tr>
<tr class="<?=$is_error?'error':''?>">
    <th><span>New password (confirm)</span></th>
    <td>
        <input type="password" size="70" class="text"
             name="new_password_confirm" value="" />
    </td>
</tr>
