<div class="intro">
    <p><?=_('Mozilla Firefox is available for the <a href="http://www.mozilla.com/firefox/system-requirements.html" target="_new" title="Firefox System Requirements">Linux, Apple OSX, and Windows</a> family of operating systems. BYOB will generate your customized version of Firefox for all three platforms by default, and we encourage making all platforms available to your target audience.')?></p>
    <p><?=_('If, however, you only want to distribute a version for a certain platform(s), you can deselect the platforms you don\'t need below, and they won\'t be generated when you submit your distribution for approval.')?></p>
</div>
<div class="pane">
    <div>
    <fieldset><legend><?=_('Platforms')?></legend>

            <?php
                $osen = form::value('os');
                if (empty($osen)) $osen = array();
            ?>
            <ul class="repack-os">
                <?php foreach (Repack_Model::$os_choices as $name=>$label): ?>
                    <li>
                        <?= form::checkbox("os[]", $name, in_array($name, $osen)) ?>
                        <?= html::specialchars($label) ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </fieldset>
    </div>
</div>
