<?php slot::set('head_title', 'customize :: ' . html::specialchars($repack->title)); ?>
<?php slot::start('crumbs') ?>
    <a href="<?= $repack->url() ?>"><?= html::specialchars($repack->title) ?></a> :: customize your browser
<?php slot::end() ?>

<?php
form::$data   = $form_data;
form::$errors = isset($form_errors) ? $form_errors : array();
?>

<?= form::open(url::current(), array('id'=>'wizard'), array()); ?>

<?php if (!empty(form::$errors)): ?>
<ul class="errors highlight">
    <?php foreach (form::$errors as $field=>$error): ?>
        <?php if (strpos($error, 'form_repacks_edit.bookmarks_') !== FALSE) continue; ?>
        <li class="<?= html::specialchars($field) ?>"><?= html::specialchars($error) ?></li>
    <?php endforeach ?>
</ul>
<?php endif ?>

<div class="accordion">

    <h3><a href="#">Basic details</a></h3>
    <div>
        <p>Use the form fields below to describe your customized browser:</p>

        <?= 
        form::fieldset('browser details', array('class'=>'selected'), array( 
            form::field('input', 'short_name', 'short name', array('class'=>'required'), array(
                'required, length between 3 and 128 characters'
            )),
            form::field('input', 'title', 'title', array('class'=>'required'), array(
                'required, length between 3 and 255 characters'
            )),
            form::field('textarea', 'description', 'description', array(), array(
                'optional, max length 1000 characters'
            ))
        ))
        ?>
    </div>

    <?php if (!$create): ?>

    <h3><a href="#">Would you like to customize the languages supported by your browser?</a></h3>

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

    <h3><a href="#">Would you like to choose the browser operating systems?</a></h3>

    <div>

        <p>You can choose the operating systems for which your browser
        will be made available.</p>

        <div>
            <?php
                $osen = form::value('os');
                if (empty($osen)) $osen = array();
            ?>
            <label for="os[]">Operating Systems:</label>
            <ul class="repack-os">
                <?php foreach (Repack_Model::$os_choices as $name=>$label): ?>
                    <li>
                        <?= form::checkbox("os[]", $name, in_array($name, $osen)) ?>
                        <?= html::specialchars($label) ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>

    </div>

    <h3><a href="#">Would you like to customize the first run page?</a></h3>
    <div>
        <p>You can customize the content displayed when your browser is started for the first time.</p>


        <?= 
        form::fieldset('first run page', array(), array( 
            form::field('textarea', 'firstrun_content', 'content'),
        ))
        ?>
    </div>

    <h3><a href="#">Would you like to customize the bookmarks menu?</a></h3>
    <div>
        <p>You can add up to 5 custom bookmarks to your browser's bookmark menu.</p>
        <?php
            View::factory('repacks/elements/edit_bookmarks', array(
                'prefix' => 'bookmarks_menu'
            ))->render(true);
        ?>
    </div>

    <h3><a href="#">Would you like to customize the bookmarks toolbar?</a></h3>

    <div>
        <p>You can add up to 3 custom bookmarks to your browser's link toolbar.</p>
        <?php
            View::factory('repacks/elements/edit_bookmarks', array(
                'prefix' => 'bookmarks_toolbar'
            ))->render(true);
        ?>
    </div>

    <h3><a href="#">Would you like to choose some addons to pre-install?</a></h3>

    <div>

        <p>You can choose some addons to come pre-installed in your browser:</p>

        <div>
            <?php
                $addons_selected = form::value('addons');
                if (empty($addons_selected)) $addons_selected = array();
            ?>
            <label for="addons[]">Addons:</label>
            <ul class="repack-addons">
                <?php foreach ($addons as $addon): ?>
                    <?php
                        $selected = in_array($addon->id, $addons_selected);
                        $h = html::escape_array(array(
                            'icon'    => $addon->icon,
                            'name'    => $addon->name,
                            'summary' => $addon->summary,
                        ));
                    ?>
                    <li class="addon">
                        <?= form::checkbox("addons[]", $addon->id, $selected) ?>
                        <img src="<?=$h['icon']?>" /> <?=$h['name']?>
                        <p><?=$h['summary']?></p>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>

    </div>

    <h3><a href="#">Would you like to choose a collection of addons to suggest for installation?</a></h3>

    <div>

        <p>You can choose a set of addons to suggest for installation in your 
        browser by using the collections feature of addons.mozilla.com</p>

        <?= 
        form::fieldset('addons collection', array(), array( 
            form::field('input', 'addons_collection_url', 'Collection URL'),
        ))
        ?>

    </div>

    <h3><a href="#">Would you like to select a Persona for customize your browser's appearance?</a></h3>

    <div>

        <p>You can choose a custom appearance to apply to your browser
        by selecting a Persona from getpersonas.com</p>

        <?= 
        form::fieldset('Personas for Firefox', array(), array( 
            form::field('input', 'persona_id', 'Persona ID'),
        ))
        ?>

    </div>

    <?php endif ?>
</div>

<ul class="nav">
    <?= form::field('submit', 'save', null, array(
        'value'=>$create ? 'create' : 'save and edit'
    )) ?>
    <?php if (!$create): ?>
        <?= form::field('submit', 'done', null, array('value'=>'save and finish')) ?>
        <?= form::field('submit', 'cancel', null, array('value'=>'cancel')) ?>
    <?php endif ?>
</ul>

<?= form::close() ?>
