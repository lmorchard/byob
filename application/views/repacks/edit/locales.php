<div>
    <p>You can choose for what locales your browser will be localized.</p>

    <p>Based on your current browser's language preferences, some selections may
    have already been provided.</p>

    <?php
        $locales = form::value('locales');
        $locale_choices = Repack_Model::$locale_choices;
    ?>
    <ul class="locales">
        <?php if (!empty($locales)) foreach ($locales as $locale): ?>
            <li class="locale">
                <input type="hidden" name="locales[]" value="<?= html::specialchars($locale) ?>" />
                <a href="#" class="delete">[x]</a>
                <span>
                    <?= html::specialchars( @$locale_choices[$locale] ) ?>
                </span>
            </li>
        <?php endforeach ?>
        <li class="locale template">
            <input type="hidden" name="locales[]" value="" />
            <a href="#" class="delete">[x]</a>
            <span></span>
        </li>
    </ul>
    <div class="locales-add">
        <select name="locale_choices">
            <?php foreach ($locale_choices as $locale=>$label): ?>
                <option value="<?= html::specialchars($locale) ?>"><?= html::specialchars($label) ?></option>
            <?php endforeach ?>
        </select>
        <a href="#" class="add">+ add locale</a>
    </div>

</div>

