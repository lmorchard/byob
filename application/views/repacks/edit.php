<?php
    $sections = array(
        'general'   => 'General',
        'locales'   => 'Locales',
        'platforms' => 'Platforms',
        'firstrun'  => 'First-run',
        'bookmarks' => 'Bookmarks',
        'addons'    => 'Addons',
        'persona'   => 'Persona',
    );
    if (!isset($section) || !isset($sections[$section])) {
        $section = 'general';
    }
    form::$data   = $form_data;
    form::$errors = isset($form_errors) ? $form_errors : array();
?>
<?php slot::set('head_title', 'customize :: ' . html::specialchars($repack->title)); ?>
<?php slot::start('crumbs') ?>
    <a href="<?= $repack->url() ?>"><?= html::specialchars($repack->title) ?></a> :: customize your browser
<?php slot::end() ?>

<?= form::open(url::current() . '?section=' . $section , array('id'=>'wizard'), array()); ?>
    <input type="hidden" name="changed" id="changed" value="false" />
    <input type="hidden" name="next_section" id="next_section" value="<?=url::current()?>" />

    <?php if (!empty(form::$errors)): ?>
    <ul class="errors highlight">
        <?php foreach (form::$errors as $field=>$error): ?>
            <?php if (strpos($error, 'form_repacks_edit.bookmarks_') !== FALSE) continue; ?>
            <li class="<?= html::specialchars($field) ?>"><?= html::specialchars($error) ?></li>
        <?php endforeach ?>
    </ul>
    <?php endif ?>

    <ul class="tabs clearfix">
        <?php foreach ($sections as $name => $title): ?>
            <?php 
                $url = url::base() . url::current() . '?section=' . $name;
                $classes = array();
                if ($name == $section) {
                    $classes[] = 'selected';
                }
                if (!empty($repack->changed_sections) && 
                        in_array($name, $repack->changed_sections)) {
                    $classes[] = 'changed';
                }
                $attr = (empty($classes)) ? '' : ' class="'.join(' ',$classes).'"';
            ?>
            <li<?=$attr?>>
                <a href="<?=$url?>"><?=$title?></a>
            </li>
        <?php endforeach ?>
    </ul>

    <div class="summary">
        <h4>Current browser summary:</h4>
        <ul class="changed">
            <?php if (!empty($repack->changed_sections)) foreach ($repack->changed_sections as $changed): ?>
                <li><?=$sections[$changed]?></li>
            <?php endforeach ?>
        </ul>
        <p><input type="submit" name="review" value="save and review" /></p>
    </div>


    <div class="section_pane">
        <?=View::factory('repacks/edit/' . $section)->render()?>

        <div class="section_nav">
            <?php
                $names = array_keys($sections);
                $pos = array_search($section, $names);

                $prev_name = ($pos == 0) ?
                    null : $names[$pos-1];
                $next_name = ($pos == count($names)-1) ?
                    null : $names[$pos+1];

                $base_url = url::base() . url::current() . '?section=';
            ?>
            <?php if (null !== $prev_name): ?>
                <div class="prev_section"><a href="<?=$base_url.$prev_name?>">previous</a></div>
            <?php endif ?>
            <?php if (null !== $next_name): ?>
                <div class="next_section"><a href="<?=$base_url.$next_name?>">next</a></div>
            <?php endif ?>
        </div>
    </div>



    <ul class="save_buttons">
        <?= form::field('submit', 'save', null, array('value'=>'save and edit')) ?>
        <?= form::field('submit', 'done', null, array('value'=>'save and finish')) ?>
    </ul>

<?= form::close() ?>
