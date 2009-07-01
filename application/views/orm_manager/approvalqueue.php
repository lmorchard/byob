<div class="list_model">

    <ul class="nav">
        <?php if (!empty($pagination)): ?>
            <li class="pagination">
                <?=$pagination->render('digg')?>
            </li>
        <?php endif ?>
    </ul>

    <h3>Browser build approval queue</h3>

    <?= View::factory("{$view_base}/list_model/list")->render() ?>

    <?php if (!empty($pagination)): ?>
        <?=$pagination->render('digg')?>
    <?php endif ?>

</div>

