<ul>

    <?php foreach ($models as $model): ?>
    <?php 
        $model_name = $model->object_name;
        $model_title = empty($model->model_title) ?
            $model_name : $model->model_title;

        $h = html::escape_array(compact(
            'model_name', 'model_title'
        ));
        $u = html::urlencode_array(compact(
            'model_name', 'model_title'
        ));
    ?>
        <li>
            <a href="<?="{$url_base}/model/{$u['model_name']}"?>"><?=$h['model_title']?></a>
        </li>
    <?php endforeach ?>

</ul>
