<div class="section">
    <h2>Maintenance actions</h2>
    <ul>
        <li><a href="<?=url::site("admin/rebuild")?>">Rebuild repacks with latest product version</a></li>
        <li><a href="<?=url::site("admin/approve")?>">Perform mass approvals</a></li>
    </ul>
</div>

<div class="section">
    <h2>Data management</h2>
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
                <a href="<?=url::site("admin/model/{$u['model_name']}")?>"><?=$h['model_title']?></a>
            </li>
        <?php endforeach ?>

    </ul>
</div>
