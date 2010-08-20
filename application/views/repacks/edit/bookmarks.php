<?php
    // TODO: Make this a setting / configuration
    $default_locale = 'en-US';
    $bookmarks_json = json_encode(form::value('bookmarks'));
    $locales_json = json_encode($repack->locales);
?>
<?php slot::start('body_end') ?>
    <?=html::script(array(
        'js/byob/repacks/edit/bookmarks-model.js',
        'js/byob/repacks/edit/bookmarks-ui.js'
    ))?>
    <script type="text/javascript">
        BYOB_Repacks_Edit_Bookmarks_UI.loadData({
            "bookmarks": <?=$bookmarks_json?>,
            "locales": <?=$locales_json?>,
            "default_locale": "<?=$default_locale?>"
        });
    </script>
<?php slot::end() ?>
<div class="intro">
    <p><?=_('Add and organize default bookmarks.')?></p>
</div>
<div class="pane">
    <?php $has_locales = (!empty($repack->locales) && count($repack->locales)>1); ?>
    <div id="editor1" class="bookmarks-editor <?= ($has_locales) ? '' : 'nolocales' ?>">
        <?php if ($has_locales): ?>
            <div class="locale-selector">
                <ul class="locales clearfix">
                    <?php foreach ($repack->locales as $locale): ?>
                        <?php $selected = ( $locale == $default_locale ) ?>
                        <li class="<?= $selected ? 'selected' : '' ?>">
                            <a href="#" data-locale="<?=$locale?>"><?=$locale?></a>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>
        <ul class="folders">
            <li id="editor1-toolbar" class="folder root folder-toolbar">
                <span class="count-wrapper">(<span class="count">0</span>)</span>
                <span class="title"><?=_('Bookmark Toolbar')?></span>
                <ul id="sub-toolbar" class="subfolders">
                </ul>
            </li>
            <li id="editor1-menu" class="folder root folder-menu">
                <span class="count-wrapper">(<span class="count">0</span>)</span>
                <span class="title"><?=_('Bookmark Menu')?></span>
                <ul id="sub-menu" class="subfolders">
                </ul>
            </li>
            <li class="folder template">
                <span class="count-wrapper">(<span class="count">0</span>)</span>
                <span class="title">subfolder</span>
            </li>
        </ul>
        <ul class="bookmarks clearfix">
            <li class="bookmark template">
                <span class="title"></span>
                <!--<span class="link"></span>-->
            </li>
        </ul>
        <ul class="controls clearfix">
            <li class="new-folder"><a href="#"><?=_('+ New Folder')?></a></li>
            <li class="new-bookmark"><a href="#"><?=_('+ New Bookmark')?></a></li>
            <li class="delete-selected"><a href="#"><?=_('x Delete Selected')?></a></li>
        </ul>
    </div>

    <textarea id="bookmarks_json" name="bookmarks_json"></textarea>

    <ul class="errors">
    </ul>

    <div class="instructions">
        <h3><?=_('Notes:')?></h3>
        <ul class="notes">
            <li class="toolbar-limit"><?=_('The Bookmark Toolbar has a limit of 3 items.')?></li>
            <li class="menu-limit"><?=_('The Bookmark Menu has a limit of 5 items.')?></li>
            <li class="folder-locations"><?=_('Folders may be placed in either the Bookmark Toolbar or Menu.')?></li>
            <li class="folder-no-subfolders"><?=_('Creation of sub-folders within folders is not supported.')?></li>
            <li class="folder-minimum"><?=_('A folder must contain at least 1 item.')?></li>
            <li class="folder-limit"><?=_('A folder may contain up to 10 items.')?></li>
        </ul>
    </div>

</div>

<?php slot::start('after_form') ?>
    <div id="bookmark_editor">
        <form class="bookmark" autocomplete="false">
            <input type="hidden" name="id" value="" />
            <ul class="editor_fields">
                <li class="field_type">
                    <input type="radio" name="type" value="bookmark" id="type-bookmark" /><label class="type-bookmark" for="type-bookmark"><?=_('Bookmark')?></label>
                    <input type="radio" name="type" value="livemark" id="type-livemark" /><label class="type-livemark" for="type-livemark"><?=_('Livemark')?></label>
                </li>
                <?php if ($has_locales): ?>
                <li class="field_locale">
                    <div class="locale_buttons">
                    <?php foreach ($repack->locales as $locale): ?>
                        <?php $selected = ( $locale == $default_locale ) ?>
                        <button class="locale locale-<?=$locale?> <?= $selected ? 'selected' : '' ?>" 
                            data-locale="<?=$locale?>"><?=$locale?></button>
                    <?php endforeach ?>
                    </div>
                </li>
                <?php endif ?>
                <?php $editor_fields = array( 
                    'title'       => _("Title"), 
                    'link'        => _("URL"), 
                    'description' => _("Description"),
                    'feedlink'    => _("Feed URL"), 
                    'sitelink'    => _("Site URL"),
                );?>
                <?php foreach ($editor_fields as $field_name=>$field_label): ?>
                    <li class="field_<?=$field_name?> required">
                        <label for="<?=$field_name?>"><?=$field_label?></label>
                        <?php foreach ($repack->locales as $locale): ?>
                            <?php $selected = ( $locale == $default_locale ) ?>
                            <input class="text field-<?=$field_name?> locale-<?=$locale?>" type="text" 
                                name="<?=$field_name?>.<?=$locale?>" data-original="" value="" />
                        <?php endforeach ?>
                    </li>
                <?php endforeach ?>
                <li>
                    <ul class="errors">
                        <li class="error template">...</li>
                    </ul>
                </li>
                <li class="controls">
                    <button class="button cancel"><?=_('Cancel')?></button>
                    <button class="button yellow save"><?=_('Save')?></button>
                </li>
            </ul>
        </form>
    </div>
<?php slot::end() ?>
