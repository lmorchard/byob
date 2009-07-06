<?php slot::set('head_title', 'search :: ' . html::specialchars(implode(' ', $terms))) ?>
<?php slot::start('crumbs') ?>
    search results
<?php slot::end() ?>

<?php if (empty($results)): ?>
    <h3>No results.</h3>
<?php else: ?>
    <?php foreach ($results as $model_name=>$data): ?>
        <?= View::factory('search/elements/results')->set($data)->render() ?>
    <?php endforeach ?>
<?php endif ?>
