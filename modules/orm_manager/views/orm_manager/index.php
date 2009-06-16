<ul>

    <?php foreach ($models as $model_name): ?>
    <?php 
        $u_model_name = rawurlencode($model_name);
        $h_model_name = html::specialchars($model_name);
    ?>
        <li>
            <a href="<?="{$url_base}/model/{$model_name}"?>"><?=$h_model_name?></a>
        </li>
    <?php endforeach ?>

</ul>
