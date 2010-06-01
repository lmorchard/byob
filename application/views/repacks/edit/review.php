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
            <p class="warning">
                You haven't performed any customizations to this browser
                beyond the default settings.  Please do so before submitting
                a request to build this browser.
            </p>
        <?php else: ?>
            <p>
                Please review your customizations detailed below before 
                submitting your browser for build and approval:
            </p>
        <?php endif ?>

        <ul class="sections">

            <li class="section general">
                <h3>General <a target="_top" href="<?=$edit_base?>general">edit</a></h3>
                <h4><?=html::specialchars($repack->title)?></h4>
                <p><?=html::specialchars($repack->description)?></p>
            </li>

            <li class="section platforms">
                <h3>Platforms <a target="_top" href="<?=$edit_base?>platforms">edit</a></h3>
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
                <h3>Bookmarks <a target="_top" href="<?=$edit_base?>bookmarks">edit</a></h3>
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
                        <li class="empty">None.</li>
                    <?php endif ?>
                </ul>
            </li>

            <li class="section collections">
                <h3>Collections <a target="_top" href="<?=$edit_base?>collections">edit</a></h3>
                <p>Collection URL:
                    <?php if (empty($repack->addons_collection_url)): ?>
                        None
                    <?php else: ?>
                        <a href="<?=html::specialchars($repack->addons_collection_url)?>" target="_new"><?=html::specialchars($repack->addons_collection_url)?></a></p>
                    <?php endif ?>
            </li>

        <ul>
    </div>
    <div class="nav">
        <div class="prev button blue"><a class="popup_cancel" href="#">&laquo;&nbsp; Cancel</a></div>
        <?php if ($repack->isCustomized()): ?>
            <div class="build button yellow"><a target="_top" href="<?=$repack->url()?>;release">Build this browser</a></div>
        <?php endif ?>
    </div>
</div>
