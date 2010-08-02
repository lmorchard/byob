<?php slot::set('is_popup', 'true') ?>
<?php
$edit_base = $repack->url() . ';edit?section=';
?>
<div class="part" id="part1">
    <div class="header">
        <h2>Review and Confirm:</h2>
    </div>
    <div class="content">
        <?php if (!$repack->isCustomized()): ?>
            <p class="warning"><?=_('You haven\'t performed any customizations to this browser beyond the default settings.  Please do so before submitting a request to build this browser.')?></p>
        <?php else: ?>
            <p><?=_('Please review your customizations detailed below before submitting your browser for build and approval:')?></p>
        <?php endif ?>

        <ul class="sections">

            <li class="section general">
                <h3>General <a target="_top" href="<?=$edit_base?>general"><?=_('edit')?></a></h3>
                <h4><?=html::specialchars($repack->title)?></h4>
                <p><?=html::specialchars($repack->description)?></p>
            </li>

            <li class="section platforms">
                <h3><?=_('Platforms')?> <a target="_top" href="<?=$edit_base?>platforms"><?=_('edit')?></a></h3>
                <ul>
                    <?php foreach ($repack->os as $name): ?>
                        <li><?=Repack_Model::$os_choices[$name]?></li>
                    <?php endforeach ?>
                </ul>
            </li>

            <?php
                /** Allow all modules to perform section rendering into a slot */
                $ev_data = array(
                    'repack' => $repack
                );
                Event::run('BYOB.repack.edit.review.renderSections', $ev_data);
                slot::output('BYOB.repack.edit.review.sections');
            ?>

            <li class="section bookmarks clearfix">
                <h3><?=_('Bookmarks')?> <a target="_top" href="<?=$edit_base?>bookmarks"><?=_('edit')?></a></h3>
                <ul>
                    <?php 
                        $bookmarks = $repack->bookmarks; 
                        $none = true;
                    ?>
                    <?php foreach (array('toolbar', 'menu') as $kind): ?>
                        <?php if (!empty($bookmarks[$kind])): ?>
                            <?php 
                                $none = false;
                                $bookmarks[$kind]['type'] = 'folder';
                                View::factory('repacks/edit/review_bookmarks', array(
                                    'bookmark' => $bookmarks[$kind]
                                ))->render(TRUE); 
                            ?>
                        <?php endif ?>
                    <?php endforeach ?>
                    <?php if ($none): ?>
                        <li class="empty"><?=_('None.')?></li>
                    <?php endif ?>
                </ul>
            </li>

            <li class="section collections">
                <h3><?=_('Collections')?> <a target="_top" href="<?=$edit_base?>collections"><?=_('edit')?></a></h3>
                <p<?=_('Collection URL:')?>>
                    <?php if (empty($repack->addons_collection_url)): ?>
                    <?=_('None.')?>
                    <?php else: ?>
                        <a href="<?=html::specialchars($repack->addons_collection_url)?>" target="_new"><?=html::specialchars($repack->addons_collection_url)?></a></p>
                    <?php endif ?>
            </li>

        <ul>
    </div>
    <div class="nav">
        <div class="prev button blue"><a class="popup_cancel" href="#"><?=_('&laquo;&nbsp; Cancel')?></a></div>
        <?php if ($repack->isCustomized()): ?>
        <div class="build button yellow"><a target="_top" href="<?=$repack->url()?>;release"><?=_('Build this browser')?></a></div>
        <?php endif ?>
    </div>
</div>
