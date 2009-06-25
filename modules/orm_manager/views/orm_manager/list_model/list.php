<?php
$columns = (method_exists($model, 'get_list_columns')) ?
    $model->get_list_columns() : $model->table_columns;

$row_view = (method_exists($model, 'get_list_row_view')) ?
    $model->get_list_row_view($view_base) :
    View::factory("{$view_base}/list_model/default_row");
?>
<form method="POST">
    <ul class="controls">
        <li><input type="submit" name="batch_delete" id="batch_delete" 
            value="Delete" /></li>
    </ul>
    <table>
        <thead>
            <tr>
                <th><span> </span></th>
                <?php foreach ($columns as $c_name=>$c_info): ?>
                    <?php
                        if (isset($model->table_column_titles) &&
                            isset($model->table_column_titles[$c_name])) {
                            $c_name = $model->table_column_titles[$c_name];
                        }
                    ?>
                    <th><?= html::specialchars($c_name) ?></th>
                <?php endforeach ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row_index=>$row): ?>
                <?=$row_view->set(array(
                    'row_index' => $row_index,
                    'row'       => $row,
                    'columns'   => $columns,
                    'model'     => $model,
                ))->render(true)?>
            <?php endforeach ?>
        </tbody>
    </table>
</form>
