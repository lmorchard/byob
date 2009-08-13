<div class="search-results">
    <h3><?=html::specialchars($model->model_title) ?></h3>

    <?php if (!empty($pagination)): ?>
        <?=$pagination->render('digg')?>
    <?php endif ?>

    <table>
        <thead>
            <tr>
                <?php foreach ($model->search_column_names as $idx=>$col_name): ?>
                    <?php 
                        $h_title = html::specialchars(
                            $model->table_column_titles[$col_name]
                        );
                    ?>
                    <th><?=$h_title?></th>
                <?php endforeach ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <?php
                    if (method_exists($model, 'as_list_array')) {
                        $vals = $row->as_list_array();
                    } else {
                        $vals = call_user_func_array(
                            array('arr', 'extract'),
                            array_merge(
                                array($row->as_array()), 
                                $model->search_column_names
                            )
                        );
                    }
                    $h = html::escape_array($vals);
                ?>
                <tr>
                    <?php foreach ($model->search_column_names as $idx=>$col_name): ?>
                        <?php $h_val = $h[$col_name] ?>
                        <td>
                            <?php if (is_array($h_val)): ?>
                                <a href="<?=$h_val[0]?>"><?=$h_val[1]?></a>
                            <?php else: ?>
                                <?=$h_val?>&nbsp;
                            <?php endif ?>
                        </td>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <?php if (!empty($pagination)): ?>
        <?=$pagination->render('digg')?>
    <?php endif ?>

</div>


