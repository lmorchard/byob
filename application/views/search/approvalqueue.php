<?php slot::set('head_title', _('search :: approval queue'))?>
<?php slot::start('crumbs') ?>
    approval queue
<?php slot::end() ?>

<h2><?=_('Approval queue')?></h2>

<?php if (empty($rows)): ?>
    <h3><?=_('No results.')?></h3>
<?php else: ?>
    <?= View::factory('search/elements/results')->set(array(
        'rows'       => $rows,
        'model'      => $model,
        'pagination' => $pagination
    ))->render() ?>
<?php endif ?>
