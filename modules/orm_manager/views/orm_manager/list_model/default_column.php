<?php
$model_name   = $model->object_name;
$primary_key  = $model->primary_key;
$column_value = $row->{$column_name};

$h = html::escape_array(compact(
    'column_value'
));
?>
<?php if ($column_name == $primary_key): ?>
    <?php
        $u = html::urlencode_array(compact(
            'column_value', 'model_name'
        ));
        $view_url = "{$url_base}/model/{$u['model_name']}/edit/{$u['column_value']}" .
            (!empty($pagination) ? "?return_page={$pagination->current_page}" : '');
    ?>
    <td class="primary_key"><a href="<?=$view_url?>"><?=$h['column_value']?></a></td>
<?php else: ?>
    <td><span><?=$h['column_value']?></span></td>
<?php endif ?>
