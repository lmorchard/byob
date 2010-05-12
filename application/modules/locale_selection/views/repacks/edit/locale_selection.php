<?php
$locales = form::value('locales');
if (empty($locales)) $locales = array();
?>

<?php slot::start('head_end') ?>
    <?=html::stylesheet(array('application/modules/locale_selection/public/css/locale_selection.css'))?>
<?php slot::end() ?>

<?php slot::start('body_end') ?>
    <?=html::script(array(
        'application/modules/locale_selection/public/js/locale_selection.js',
    ))?>
    <script type="text/javascript">
        <?php
            $locale_names = array();
            $locales_by_name = array();
            foreach (locale_selection::get_all_locales() as $code=>$details) {
                $locale_names[] = $details['English'];
                $locales_by_name[$details['English']] = $code;
            }
        ?>
        BYOB_Repacks_Edit_LocaleSelection.loadLocales(
            <?= json_encode($locale_names) ?>, 
            <?= json_encode($locales_by_name) ?> 
        );
    </script>
<?php slot::end() ?>

<div class="intro">
    <p>
        BYOB is currently available in <?=locale_selection::count()?> locales.
        You can choose <strong>up to ten locales</strong> for your browser.
    </p>
</div>
<div class="pane">

    <div class="selections">
        <fieldset><legend>Selected locales:</legend>
            <ol class="locale-selections clearfix">
                <?php foreach ($locales as $locale): ?>
                    <?php
                        $details = locale_selection::get_locale_details($locale);
                        if (empty($details)) continue;
                    ?>
                    <li class="selected-locale">
                        <span class="name"><?= html::specialchars($details['English']) ?></span>
                        <?= form::hidden("locales[]", $locale) ?>
                        <a href="#" class="remove">Remove</a>
                    </li>
                <?php endforeach ?>
                <li class="template selected-locale">
                    <span class="name"></span>
                    <input type="hidden" value="" name="locales[]" />
                    <a href="#" class="remove">Remove</a>
                </li>
            </ol>
        </fieldset>
    </div>

    <div class="choices">

        <fieldset><legend>Choose from these common locales:</legend>
            <?php $popular_choices = locale_selection::get_popular_locales(); ?>
            <ul class="repack-locale popular-locales clearfix">
                <?php foreach ($popular_choices as $locale=>$details): ?>
                    <li>
                        <?= form::checkbox(array('id'=>'popular_locales_'.$locale, 'name'=>"popular_locales[]"), $locale, in_array($locale, $locales)) ?>
                        <label class="label" for="popular_locales_<?=html::specialchars($locale)?>"><?= html::specialchars($details['English']) ?></label>
                    </li>
                <?php endforeach ?>
            </ul>
        </fieldset>

        <fieldset><legend>Or search for a locale by name:</legend>
            <div class="locale-search-field">
                <input type="text" id="locale_search" name="locale_search" size="40"
                    title="Enter all or part of a locale's name" />
            </div>
        </fieldset>

    </div>

</div>
