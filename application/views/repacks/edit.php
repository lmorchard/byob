<?php
    $sections = Mozilla_BYOB_EditorRegistry::getSections();
    if (!isset($section) || !isset($sections[$section])) {
        $section = 'general';
    }
    form::$data   = $form_data;
    form::$errors = isset($form_errors) ? $form_errors : array();

    $classes = array();
    if (empty(form::$errors) && 'true' == $show_review) {
        $classes[] = 'show_review';
    }

    $section_editor = View::factory('repacks/edit/' . $section)->render();
?>
<?php slot::set('head_title', 'customize :: ' . html::specialchars($repack->display_title)); ?>
<?php slot::start('crumbs') ?>
    <a href="<?= $repack->url() ?>"><?= html::specialchars($repack->display_title) ?></a> :: customize your browser
<?php slot::end() ?>

<?= form::open(url::current() . '?section=' . $section , array('class'=>join(' ', $classes), 'id'=>'wizard'), array()); ?>
    <input type="hidden" name="changed" id="changed" value="false" />
    <input type="hidden" name="show_review" id="show_review" value="false" />
    <input type="hidden" name="next_section" id="next_section" value="<?=$section?>" />
<?php if (!slot::get('is_popup')): ?>

    <div class="summary">
        <?php if (!empty(form::$errors)): ?>
        <h4>Problems:</h4>
        <ul class="errors highlight">
            <?php foreach (form::$errors as $field=>$error): ?>
                <li class="error_<?= html::specialchars($field) ?>"><?= html::specialchars($error) ?></li>
            <?php endforeach ?>
        </ul>
        <?php endif ?>

        <h4>Sections changed this session</h4>
        <ul class="changed_sections">
            <?php if (!empty($repack->changed_sections)):?>
                <?php foreach ($repack->changed_sections as $idx=>$changed): ?>
                    <?php if (!isset($sections[$changed])) continue ?>
                    <li class="changed_section">
                        <h5><?=$sections[$changed]?></h5>
                    </li>
                <?php endforeach ?>
            <?php else: ?>
                <li class="changed_section"><h5>None yet.</h5></li>
            <?php endif ?>
        </ul>
        <p class="submit">
            <a id="save-and-review" href="<?=url::base() . url::current()?>?section=review"><img src="<?=url::base()?>img/save-and-review.gif" alt="Save and Review" /></a></p>
    </div>

    <div class="section section-<?= $section ?>">

        <ul class="tabs clearfix">
            <?php $first = true ?>
            <?php foreach ($sections as $name => $title): ?>
                <?php 
                    if (false === $title) continue;

                    $url = url::base() . url::current() . '?section=' . $name;
                    $classes = array();
                    if (true === $first) {
                        $first = false;
                        $classes[] = 'first';
                    }
                    if ($name == $section) {
                        $classes[] = 'selected';
                    }
                    if (!empty(form::$errors) && $name == $section) {
                        $classes[] = 'error';
                    } else if (!empty($repack->changed_sections) && 
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

        <div class="section_content">
<?php endif ?>
            <?=$section_editor?>
<?php if (!slot::get('is_popup')): ?>
        </div>

        <div class="section_nav clearfix">
            <?php
                $names = array_keys($sections);
                $pos = array_search($section, $names);

                $prev_name = ($pos == 0) ?
                    null : $names[$pos-1];
                $next_name = ($pos == count($names)-1) ?
                    null : $names[$pos+1];

                $base_url = url::base() . url::current() . '?section=';
            ?>
            <?php if (null !== $prev_name && false !== $sections[$prev_name]): ?>
                <div class="prev_section"><a href="<?=$base_url.$prev_name?>">Previous</a></div>
            <?php endif ?>
            <?php if (null !== $next_name && false !== $sections[$next_name]): ?>
                <div class="next_section"><a href="<?=$base_url.$next_name?>">Next</a></div>
            <?php endif ?>
        </div>
    </div>

    <ul class="save_buttons">
        <?= form::field('submit', 'save', null, array('value'=>'save and edit')) ?>
        <?= form::field('submit', 'done', null, array('value'=>'save and finish')) ?>
    </ul>

<?php endif ?>
<?= form::close() ?>
<?=slot::get('after_form')?>
