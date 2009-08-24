<div class="intro">
    <p>
        Most of the meta data about your customized version of Firefox will be 
        automatically generated using your profile information. The information in this 
        section is for your use, is publicly viewable, and should describe who this 
        browser is tailored for. When referring to the this browser (e.g. on a website, 
        blog post, communique, etc.), you should use the name 
        "<em><?=html::specialchars($repack->title)?></em>".
    </p>

    <p>
        The Title is a label that is used for identifying customized version of 
        Firefox within the BYOB application. If you have multiple customizations 
        defined, it's intended to help differentiate between those builds. 
        Similarly, the descriptive text allows you to provide a lengthy description 
        of your customizations. 
    </p>
</div>
<div class="pane">

    <div>
        <fieldset><legend>Browser details</legend>
            <div class="user_title">
                <p>Enter a one or two word identifier for this version of Firefox. 
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

    <?php /*
    <div>
        <fieldset><legend>Locales</legend>
            <p>
                Mozilla Firefox has been localized 
                <a target="_new" href="http://mozilla.com/firefox/all.html" 
                    title="All versions of Firefox">for many languages</a>, 
                and it is our intent for BYOB to eventually support all of 
                them. For this version of BYOB, you can specify up to ten (10) 
                locales.
            </p>

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
        <fieldset><legend>Platforms</legend>
            <p>
                Mozilla Firefox is available for the 
                <a href="http://www.mozilla.com/firefox/system-requirements.html" 
                    target="_new"
                    title="Firefox System Requirements">Linux, Apple OSX, and
                     Windows</a> family of operating systems. BYOB will 
                generate your customized version of Firefox for all three 
                platforms by default, and we encourage making 
                all platforms available to your target audience. 
            </p>
            <p>
                If, however, you only want to distribute a version for a 
                certain platform(s), you can deselect the platforms you don't 
                need below, and they won't be generated when you submit your 
                distribution for approval.
            </p>

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
     */ ?>

</div>
