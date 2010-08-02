<?php slot::set('head_title', sprintf(_('search :: %1$s'), html::specialchars(implode(' ', $terms)))) ?>
<?php slot::set('crumbs', _('search results')) ?>

<?php if (empty($results)): ?>
    <h3><?=_('No results.')?></h3>
<?php else: ?>
    <?php foreach ($results as $model_name=>$data): ?>
        <?= View::factory('search/elements/results')->set($data)->render() ?>
    <?php endforeach ?>
<?php endif ?>
