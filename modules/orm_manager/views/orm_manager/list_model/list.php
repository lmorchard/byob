<?php
if (empty($list_columns)) {
    $list_columns = (method_exists($model, 'get_list_columns')) ?
        $model->get_list_columns() : $model->table_columns;
}

if (empty($actions_view)) {
    $actions_view = (method_exists($model, 'get_list_actions_view')) ?
        $model->get_list_row_view($view_base) :
        View::factory("{$view_base}/list_model/default_actions");
}

if (empty($row_view)){
    $row_view = (method_exists($model, 'get_list_row_view')) ?
        $model->get_list_row_view($view_base) :
        View::factory("{$view_base}/list_model/default_row");
}
?>
<form method="POST">
    <?=$actions_view->set(array('model' => $model))->render()?>
    <table>
        <thead>
            <tr>
                <?php if (!isset($allow_batch) || $allow_batch): ?>
                    <th class="batch_select"><span> </span></th>
                <?php endif ?>
                <?php foreach ($list_columns as $c_name=>$c_info): ?>
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
                    'columns'   => $list_columns,
                    'model'     => $model,
                ))->render()?>
            <?php endforeach ?>
        </tbody>
    </table>
</form>
