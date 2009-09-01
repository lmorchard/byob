<div class="intro">
    <p>
        Mozilla Firefox has been localized 
        <a target="_new" href="http://mozilla.com/firefox/all.html" 
            title="All versions of Firefox">for many languages</a>, 
        and it is our intent for BYOB to eventually support all of 
        them. For this version of BYOB, you can specify up to ten (10) 
        locales.
    </p>
</div>
<div class="pane">

    <div>
        <fieldset><legend>Locales</legend>

            <?php
                $locales = form::value('locales');
                if (empty($locales)) $locales = array();
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

</div>
