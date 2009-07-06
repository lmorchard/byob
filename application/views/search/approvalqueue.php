<?php slot::set('head_title', 'search :: approval queue')?>
<?php slot::start('crumbs') ?>
    approval queue
<?php slot::end() ?>

<h2>Approval queue</h2>

<?php if (empty($rows)): ?>
    <h3>No results.</h3>
<?php else: ?>
    <?= View::factory('search/elements/results')->set(array(
        'rows'       => $rows,
        'model'      => $model,
        'pagination' => $pagination
    ))->render() ?>
<?php endif ?>
