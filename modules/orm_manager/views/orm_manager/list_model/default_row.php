<?php
$model_name        = $model->object_name;
$primary_key       = $model->primary_key;
$primary_key_value = $row->{$primary_key};
$column_index = 0;

$h = html::escape_array(compact(
    'primary_key', 'primary_key_value', 'model_name'
));
?>
<tr>
    <?php if (!isset($allow_batch) || $allow_batch): ?>
        <td class="select_row"><span>
            <input type="checkbox" name="select_row[]" 
                value="<?=$h['primary_key_value']?>" />
        </span></td>
    <?php endif ?>
    <?php foreach ($columns as $column_name=>$column_info): ?>
        <?php
            if (isset($column_views[$column_name])) {
                $column_view = $column_views[$column_name];
            } else {
                $column_view = method_exists($model, 'get_list_column_view') ?
                    $column_view = $model->get_list_column_view(
                        $view_base, $column_name, $column_info
                    ) :
                    View::factory("{$view_base}/list_model/default_column");
            }
        ?>
        <?=$column_view->set(array(
            'model'        => $model,
            'row'          => $row,
            'columns'      => $columns,
            'column_index' => ($column_index++),
            'column_name'  => $column_name,
            'column_info'  => $column_info,
        ))->render()?>
    <?php endforeach ?>
</tr>
