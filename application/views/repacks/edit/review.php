<?php slot::set('is_popup', 'true') ?>
<?php
$edit_base = $repack->url() . ';edit?section=';
?>
<div class="part" id="part1">
    <div class="header">
        <h2>Review and Confirm: 1 of 2</h2>
    </div>
    <div class="content">
        <p>Please review your customizations detailed below before 
        submitting your browser for build and approval:</p>

        <ul class="sections">

            <li class="section general">
                <h3>General <a target="_top" href="<?=$edit_base?>general">edit</a></h3>
                <h4><?=html::specialchars($repack->title)?></h4>
                <p><?=html::specialchars($repack->description)?></p>
            </li>

            <li class="section locales">
                <h3>Locales <a target="_top" href="<?=$edit_base?>locales">edit</a></h3>
                <ul>
                    <?php foreach ($repack->locales as $name): ?>
                        <li><?=Repack_Model::$locale_choices[$name]?></li>
                    <?php endforeach ?>
                </ul>
            </li>

            <li class="section platforms">
                <h3>Platforms <a target="_top" href="<?=$edit_base?>platforms">edit</a></h3>
                <ul>
                    <?php foreach ($repack->os as $name): ?>
                        <li><?=Repack_Model::$os_choices[$name]?></li>
                    <?php endforeach ?>
                </ul>
            </li>

            <li class="section personas clearfix">
                <h3>Personas <a target="_top" href="<?=$edit_base?>persona">edit</a></h3>
                <?php if (in_array('10900', $repack->addons)): ?>
                    <p>Persona URL: <a href="<?=html::specialchars($repack->persona_url)?>" target="_new"><?=html::specialchars($repack->persona_url)?></a></p>
                    <p><span style="color: green">&#x2714;</span> The Personas add-on will be installed.</p>
                <?php else: ?>
                    <p><span style="color: red">&#x2716;</span> The Personas add-on will be not installed.</p>
                <?php endif ?>
            </li>

        <ul>
    </div>
    <div class="nav">
        <div class="next"><a href="#part2">Next</a></div>
    </div>
</div>

<div class="part" id="part2">
    <div class="header">
        <h2>Review and Confirm: 2 of 2</h2>
    </div>

    <div class="content">

        <p>Please review your customizations detailed below before 
        submitting your browser for build and approval:</p>

        <ul class="sections">

            <li class="section collections">
                <h3>Collections <a target="_top" href="<?=$edit_base?>collections">edit</a></h3>
                <?php if (in_array('11950', $repack->addons)): ?>
                    <p>Collection URL: <a href="<?=html::specialchars($repack->addons_collection_url)?>" target="_new"><?=html::specialchars($repack->addons_collection_url)?></a></p>
                    <p><span style="color: green">&#x2714;</span> The Add-on Collector add-on will be installed.</p>
                <?php else: ?>
                    <p><span style="color: red">&#x2716;</span> The Add-on Collector add-on will be not installed.</p>
                <?php endif ?>
            </li>

            <li class="section bookmarks clearfix">
                <h3>Bookmarks <a target="_top" href="<?=$edit_base?>bookmarks">edit</a></h3>

                <?php foreach (array('menu', 'toolbar') as $kind): ?>
                    <div class="<?=$kind?>">
                        <h4><?=ucfirst($kind)?></h4>
                        <ul>
                            <?php if (empty($repack->{'bookmarks_'.$kind})): ?>
                                <li>None</li>
                            <?php else: ?>
                                <?php foreach ($repack->{'bookmarks_'.$kind} as $idx=>$bookmark): ?>
                                    <li class="bookmark <?=$bookmark['type']?>">
                                        <span class="title"><?=html::specialchars($bookmark['title'])?></span>
                                        <?php if ('live' == $bookmark['type']): ?>
                                            <a target="_new" class="feed" href="<?=html::specialchars($bookmark['feed'])?>"><?=html::specialchars($bookmark['feed'])?></a>
                                            <a target="_new" class="location" href="<?=html::specialchars($bookmark['location'])?>"><?=html::specialchars($bookmark['location'])?></a>
                                        <?php else: ?>
                                            <a target="_new" class="location" href="<?=html::specialchars($bookmark['location'])?>"><?=html::specialchars($bookmark['location'])?></a>
                                            <span class="description"><?=html::specialchars($bookmark['description'])?></span>
                                        <?php endif ?>
                                    </li>
                                <?php endforeach ?>
                            <?php endif ?>
                        </ul>
                    </div>
                <?php endforeach ?>

            </li>

        <ul>
    </div>
    <div class="nav">
        <div class="prev"><a href="#part1">Previous</a></div>
        <div class="build"><a target="_top" href="<?=$repack->url()?>;release"><img src="<?=url::base()?>img/build-this-browser.gif" /></a></div>
    </div>
</div>
