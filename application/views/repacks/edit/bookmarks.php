<?php
    $bookmarks_json = json_encode(form::value('bookmarks'));
?>
<?php slot::start('body_end') ?>
    <?=html::script(array(
        'js/byob/repacks/edit/bookmarks-model.js',
        'js/byob/repacks/edit/bookmarks-ui.js'
    ))?>
    <script type="text/javascript">
        BYOB_Repacks_Edit_Bookmarks_UI.loadData(<?=$bookmarks_json?>);
    </script>
<?php slot::end() ?>
<div class="intro">
    <p>Add and organize default bookmarks.</p>
</div>
<div class="pane">

    <div class="bookmarks-editor" id="editor1">
        <ul class="folders">
            <li id="editor1-toolbar" class="folder root folder-toolbar">
                <span class="count-wrapper">(<span class="count">0</span>)</span>
                <span class="title">Bookmark Toolbar</span>
                <ul id="sub-toolbar" class="subfolders">
                </ul>
            </li>
            <li id="editor1-menu" class="folder root folder-menu">
                <span class="count-wrapper">(<span class="count">0</span>)</span>
                <span class="title">Bookmark Menu</span>
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
            <li class="new-folder"><a href="#">+ New Folder</a></li>
            <li class="new-bookmark"><a href="#">+ New Bookmark</a></li>
            <li class="delete-selected"><a href="#">x Delete Selected</a></li>
        </ul>
    </div>

    <textarea id="bookmarks_json" name="bookmarks_json"></textarea>

    <ul class="errors">
    </ul>

    <div class="instructions">
        <h3>Notes:</h3>
        <ul class="notes">
            <li class="toolbar-limit">The Bookmark Toolbar has a limit of 3 items.</li>
            <li class="menu-limit">The Bookmark Menu has a limit of 5 items.</li>
            <li class="folder-locations">Folders may be placed in either the Bookmark Toolbar or Menu.</li>
            <li class="folder-no-subfolders">Creation of sub-folders within folders is not supported.</li>
            <li class="folder-minimum">A folder must contain at least 1 item.</li>
            <li class="folder-limit">A folder may contain up to 10 items.</li>
        </ul>
    </div>

</div>

<?php slot::start('after_form') ?>
    <div id="bookmark_editor">
        <form class="bookmark">
            <input type="hidden" name="id" value="" />
            <input type="hidden" name="type" value="bookmark" />
            <ul class="editor_fields">
                <li class="field_type">
                    <label for="type">Type</label>
                    <button class="button type_bookmark" name="type_bookmark">Bookmark</button>
                    <button class="button type_livemark" name="type_livemark">Livemark</button>
                </li>
                <li class="field_title required">
                    <label for="title">Title</label>
                    <input class="text" type="text" name="title" />
                </li>
                <li class="field_link required">
                    <label for="link">URL</label>
                    <input class="text" type="text" name="link" />
                </li>
                <li class="field_description">
                    <label for="description">Description</label>
                    <input class="text" type="text" name="description" />
                </li>
                <li class="field_feedlink required">
                    <label for="feedLink">Feed URL</label>
                    <input class="text" type="text" name="feedLink" />
                </li>
                <li class="field_sitelink required">
                    <label for="siteLink">Site URL</label>
                    <input class="text" type="text" name="siteLink" />
                </li>
                <li>
                    <ul class="errors">
                        <li class="error template">...</li>
                    </ul>
                </li>
                <li class="controls">
                    <button class="button cancel">Cancel</button>
                    <button class="button yellow save">Save</button>
                </li>
            </ul>
        </form>
    </div>
<?php slot::end() ?>
