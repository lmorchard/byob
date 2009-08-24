<div class="intro">
    <p>Use the form fields below to describe your customized browser.</p>
</div>
<div class="pane">

    <div>
        <fieldset><legend>Browser details</legend>
        <div class="user_title">
            <p>Give this browser a short title to help identify it 
                (required, max length 255 characters):</p>
            <?= form::input('user_title', form::value('user_title')) ?>
        </div>
        <div class="description">
            <p>Describe this customized browser further 
                (optional, max length 1000 characters):</p>
            <?= form::textarea('description', form::value('description')) ?>
        </div>
        </fieldset>
    </div>

    <div>
        <fieldset><legend>Locales</legend>
            <?php
                $locales = form::value('locales');
                $locale_choices = Repack_Model::$locale_choices;
            ?>
            <ul class="repack-locale clearfix">
                <?php foreach ($locale_choices as $locale=>$label): ?>
                    <li>
                        <?= form::checkbox("locales[]", $locale, in_array($locale, $locales)) ?>
                        <?= html::specialchars($label) ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </fieldset>
    </div>

    <div>
        <fieldset><legend>Operating systems</legend>
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
