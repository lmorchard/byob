<li class="section locales">
    <h3>Locales <a target="_top" href="<?=$repack->url()?>;edit?section=locales">edit</a></h3>
    <ul>
        <?php foreach ($repack->locales as $name): ?>
            <li><?=html::specialchars(locale_selection::$locale_details->getEnglishNameForLocale($name))?></li>
        <?php endforeach ?>
    </ul>
</li>
