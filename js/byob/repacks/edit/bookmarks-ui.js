/**
 * BYOB bookmarks editor UI
 *
 * Interactions:
 *      add a new bookmark
 *      add a new livemark
 *      add a new folder
 *      edit a bookmark
 *      edit a bookmark, change to a livemark
 *      drag and drop reorder bookmark pane items
 *      drag and drop reorder folder pane items
 *      drag and drop a bookmark pane bookmark into folder pane folder
 *      drag and drop a bookmark pane folder into folder pane folder
 * 
 */
/*jslint laxbreak: true */
BYOB_Repacks_Edit_Bookmarks_UI = (function () {

    var _ = BYOB_Main.gettext;

    var $Model = BYOB_Repacks_Edit_Bookmarks_Model;

    var $this = {

        // Data model for the UI.
        model: null,

        // Is the editor ready yet?
        is_ready: false,

        selected_item: null,
        selected_folder: null,

        /**
         * Package initialization
         */
        init: function () {
            $this.model = new BYOB_Repacks_Edit_Bookmarks_Model.Root();
            $(document).ready($this.ready);
            return $this;
        },

        /**
         * Page ready
         */
        ready: function () {
            $this.is_ready = true;

            $this.editor = $('.bookmarks-editor');
            $this.editor_id = $this.editor.attr('id');

            $this.wireUpFolders();
            $this.wireUpBookmarks();
            $this.wireUpBookmarkEditor();

            $this.updateFolders();
            $this.selectFolder($this.editor_id + '-toolbar');
            $this.updateBookmarksJSON();
        },

        /**
         * Accept bookmark data, presumably as part of page load.
         */
        loadData: function (data) {
            $this.model = new BYOB_Repacks_Edit_Bookmarks_Model.Root();

            var menu_items = ( data && data.menu && data.menu.items ) ?
                data.menu.items : [];
            $this.model.add({
                type:  'menu',
                id:    'editor1-menu',
                title: 'Bookmarks Menu',
                items: menu_items
            });

            var toolbar_items = ( data && data.toolbar && data.toolbar.items ) ?
                data.toolbar.items : [];
            $this.model.add({
                type:  'toolbar',
                id:    'editor1-toolbar',
                title: 'Bookmarks Toolbar',
                items: toolbar_items
            });

            if ($this.is_ready) { $this.refresh(); }
        },

        /**
         * Flash a relevant note.
         */
        flashNote: function (name) {
            $('.notes .'+name).effect('highlight', {}, 3000);
        },

        /**
         * Update the bookmarks data form field for later submission.
         */
        updateBookmarksJSON: function() {
            var extracted = $this.model.extract(),
                bookmarks = {
                    toolbar: extracted['editor1-toolbar'],
                    menu:    extracted['editor1-menu']
                },
                data = JSON.stringify(bookmarks, null, ' ');
            $('#bookmarks_json').val(data);
        },

        /**
         * Update the folder pane DOM with the current folder structure.
         */
        updateFolders: function () {

            $this.editor.find('.folders .root').each(function () {
                
                var root_el   = $(this),
                    root_id   = root_el.attr('id'),
                    par_el    = root_el.find('.subfolders'),
                    tmpl_el   = $this.editor.find('.folders > .folder.template'),
                    bookmarks = $this.model.find(root_id).get('items');

                par_el.find('li:not(.template)').remove();
                root_el.removeClass('has_errors');
                root_el.find('.count').text(''+bookmarks.length);

                $.each(bookmarks, function (j, item) {
                    if (!!item.errors) { root_el.addClass('has_errors'); }
                    if (!item.isFolder()) { return; }
                    tmpl_el.cloneTemplate({
                        '@id':    'fl-' + item.id,
                        '@class': 'folder ' +
                            ( (!!item.errors) ? ' has_errors' : '' ),
                        '.title': item.title,
                        '.count': ''+item.items.length
                    }).appendTo(par_el);
                });

            });

            $this.editor.find('.folders').sortable('refresh');
        },

        /**
         * Update the bookmark pane DOM with a list of bookmarks.
         */
        updateBookmarks: function (items) {
            var bm_root_el = $this.editor.find('.bookmarks'),
                tmpl_el    = $this.editor.find('.bookmark.template');

            if (null === items) {
                items = $this.selected_folder.items;
            }

            bm_root_el.find('li:not(.template)').remove();

            $.each(items, function (i, item) {
                tmpl_el.cloneTemplate({
                    '@id':    item.id,
                    '@class': 'bookmark type-' + item.get('type') + 
                        ( (!!item.errors) ? ' has_errors' : '' ),
                    '.title': item.title,
                    '.link':  item.link || item.feedLink
                }).appendTo(bm_root_el);
            });

            bm_root_el.sortable('refresh');

            // Nothing selected on update of bookmarks, so disable delete button.
            $this.editor.find('.delete-selected').addClass('disabled');
            
            // If the selected folder is full, turn off the new items buttons.
            if ($this.selected_folder) {
                if ($this.selected_folder.isFull()) {
                    $this.editor.find('.new-bookmark').addClass('disabled');
                    $this.editor.find('.new-folder').addClass('disabled');
                } else {
                    if ($this.selected_folder.allowsSubFolders()) {
                        $this.editor.find('.new-folder').removeClass('disabled');
                    } else {
                        $this.editor.find('.new-folder').addClass('disabled');
                    }
                    $this.editor.find('.new-bookmark').removeClass('disabled');
                }
            }

        },

        /**
         * Refresh the current view from data model.
         */
        refresh: function () {
            $this.updateFolders();
            $this.selectFolder();
            $this.updateBookmarksJSON();
        },

        /**
         * Wire up the UI handlers for the folders pane.
         */
        wireUpFolders: function () {

            var folders = $this.editor.find('.folders');

            // Wire up the folders as clickable to populate bookmarks pane.
            folders.click(function (ev) {
                var target = $(ev.target);
                if (target.hasClass('title')) { target = target.parent(); }
                if (!target.hasClass('folder')) { return; }

                $this.selectFolder(target.attr('id'));
            });

            // Wire up the root subfolder containers as sortables.
            folders.find('.root .subfolders').sortable({
                appendTo: 'body',
                cursor:   'move',
                axis:     'y',
                items:    'li:not(.template)',
                stop:     $this.performFolderMove
            });

            // Connect all the root folder sortables to each other.
            folders.find('.root').each(function () {
                var from_id = '#' + this.id + ' .subfolders',
                    to_ids = [];
                folders.find('.root').each(function () {
                    var to_id = '#' + this.id + ' .subfolders';
                    if (from_id != to_id) { to_ids.push(to_id); }
                });
                $(from_id).sortable('option', 'connectWith', to_ids);
            });

            // Wire up root folders themselves as drop targets, but just for
            // items from the bookmark pane.
            folders.find('.root .title').droppable({
                'tolerance': 'pointer',
                'over': function (ev, ui) { 
                    if (!ui.draggable.hasClass('folder')) {
                        $(this).parent().addClass('hover');
                    }
                },
                'out': function (ev, ui) { 
                    $(this).parent().removeClass('hover');
                },
                'drop': function (ev, ui) {
                    if (ui.draggable.hasClass('folder')) { return; }
                    
                    var item = $this.model.find(ui.draggable.attr('id')),
                        dest = $this.model.find($(this).parent().attr('id'));

                    // HACK: Schedule the data model change and view refresh
                    // until after jQuery UI has had a chance to finish up.
                    setTimeout(function () {
                        item.moveTo(dest);
                        $this.refresh();
                    }, 1);
                }
            });

        },

        /**
         * Event handler to perform a folder move at the data model level.
         */
        performFolderMove: function (ev, ui) {
            var bm_id   = ui.item.attr('id').replace('fl-',''),
                bm_item = $this.model.find(bm_id),
                dest, success;

            if ( id = ui.item.prev('li.folder').attr('id') ) {
                dest    = $this.model.find(id.replace('fl-',''));
                success = bm_item.moveAfter(dest);
            } else if ( id = ui.item.next('li.folder').attr('id') ) {
                dest    = $this.model.find(id.replace('fl-',''));
                success = bm_item.moveBefore(dest);
            } else {
                var root_id = ui.item.parent().parent().attr('id');
                dest = $this.model.find(root_id);
                success = bm_item.moveTo(dest);
            }

            if (!success) {
                if (dest.parent instanceof $Model.ToolbarFolder) {
                    $this.flashNote('toolbar-limit');
                } else if (dest.parent instanceof $Model.MenuFolder) {
                    $this.flashNote('menu-limit');
                } else {
                    $this.flashNote('folder-limit');
                }
            }

            $this.refresh();
        },

        /**
         * Wire up the UI handlers for the bookmarks pane.
         */
        wireUpBookmarks: function () {

            $this.editor.find('.bookmarks')
                .click($this.selectBookmark)
                .dblclick($this.editBookmark)
                .sortable({
                    appendTo: 'body',
                    cursor:   'move',
                    items:    'li:not(.template)',
                    sort:     $this.updateItemFeedback,
                    stop:     $this.performItemMove
                }).end();

            // Connect the bookmark pane to all the folder sortables
            var from_id = '#'+$this.editor_id+' .bookmarks',
                to_ids = [];
            $this.editor.find('.folders .root').each(function () {
                to_ids.push('#' + this.id + ' .subfolders');
            });
            $(from_id).sortable('option', 'connectWith', to_ids);

        },

        /**
         * Select a bookmark on single click.
         */
        selectBookmark: function (ev) {
            var target = $(ev.target);
            if (!target.hasClass('bookmark')) { target = target.parent(); }
            if (!target.hasClass('bookmark')) { return; }

            $this.selected_item = $this.model.find(target.attr('id'));

            target.parent().find('.bookmark').removeClass('selected');
            target.addClass('selected');
            $this.editor.find('.delete-selected').removeClass('disabled');

            return false;
        },

        /**
         * Open the bookmark editor for an item on double click.
         */
        editBookmark: function (ev) {
            var target = $(ev.target);
            if (!target.hasClass('bookmark')) { target = target.parent(); }
            if (!target.hasClass('bookmark')) { return; }

            var item = $this.model.find(target.attr('id'));
            if (item.isFolder()) {
                $this.summonFolderRename(item); 
            } else {
                $this.summonBookmarkEditor(item); 
            }

            return false;
        },

        /**
         * Update feedback in sortable lists while item being dragged.
         */
        updateItemFeedback: function (ev, ui) {
            var item = $this.model.find(ui.item.attr('id'));
            if (item.isFolder()) { return; }
            ui.placeholder.prev('.folder').each(function () {
                $this.editor.find('.hover').removeClass('hover');
                $(this).addClass('hover');
            });
        },

        /**
         * Event handler to perform a folder move at the data model level.
         *
         * This is kind of gnarly, since it involves drops into both the folder
         * sidebar and the item pane.  Since only one level of folder is allowed,
         * folders and items have to be treated differently for drag gestures 
         * that imply reordering versus structure changes.
         */
        performItemMove: function (ev, ui) {
            $this.editor.find('.hover').removeClass('hover');

            var item = $this.model.find(ui.item.attr('id'));

            var id, root_parent, subfolder = false, dest = null, success = false;
            if (id = ui.item.prev('.folder').attr('id')) {
                dest = $this.model.find(id.replace('fl-',''));
                if (item.isFolder()) {
                    success = item.moveAfter(dest);
                } else {
                    subfolder = true;
                    success = item.moveTo(dest);
                }
            } else if (id = ui.item.next('.folder').attr('id')) {
                dest = $this.model.find(id.replace('fl-',''));
                if (item.isFolder()) {
                    success = item.moveBefore(dest);
                } else {
                    subfolder = true;
                    success = item.moveTo(dest);
                }
            } else if ( id = ui.item.prev('.bookmark').attr('id') ) {
                success = item.moveAfter($this.model.find(id));
            } else if ( id = ui.item.next('.bookmark').attr('id') ) {
                success = item.moveBefore($this.model.find(id));
            }

            if (!success && dest) {
                if (subfolder) {
                    //$this.flashNote('folder-limit');
                } else if (dest.parent instanceof $Model.ToolbarFolder) {
                    $this.flashNote('toolbar-limit');
                } else if (dest.parent instanceof $Model.MenuFolder) {
                    $this.flashNote('menu-limit');
                }
            }

            $this.refresh();
        },

        /**
         * Select a folder by ID and populate the bookmarks pane with its
         * items.
         */
        selectFolder: function (folder_id) {
            var folder = null;
            if (folder_id) {
                folder_id = folder_id.replace('fl-', '');
                $this.selected_folder = folder = $this.model.find(folder_id);
            } else {
                folder = $this.selected_folder;
            }

            if (folder.parent) {
                $this.editor.find('.new-folder').addClass('disabled');
            } else {
                $this.editor.find('.new-folder').removeClass('disabled');
            }

            // Deselect all folders, select this one.
            $this.editor.find('.folder').removeClass('selected');
            $('#' + (folder.parent ? 'fl-' : '') + folder.id).addClass('selected');

            // Update the bookmarks pane from this folder.
            $this.updateBookmarks(folder.items);
        },

        /**
         * Wire up events and handlers to support the bookmark editor
         */
        wireUpBookmarkEditor: function () {
            var bm_editor = $('#bookmark_editor');

            $this.editor
                .find('.new-bookmark').click($this.newBookmark).end()
                .find('.new-folder').click($this.newFolder).end()
                .find('.delete-selected').click($this.deleteSelectedItem).end();

            bm_editor
                .find('form').submit(function () { return false; }).end()
                .find('.cancel').click(function(ev) { bm_editor.hide(); }).end()
                .find('.save').click($this.saveBookmark).end();

            $.each(['bookmark','livemark'], function (i, type) {
                bm_editor.find('button[name=type_'+type+']')
                    .click(function (ev) {
                        bm_editor
                            .find('form').attr('class', type).end()
                            .find('input[name=type]').val(type).end();
                    }).end();
            });
        },

        /**
         * Attempt to delete the current selected item in bookmark pane.
         */
        deleteSelectedItem: function (ev) {
            // Bail if this is disabled.
            if ($(this).hasClass('disabled')) { return; }
            // Bail if no item selected.
            if (!$this.selected_item) { return; }

            $this.selected_item.deleteSelf();
            $this.selected_item = null;
            $this.refresh();

            return false;
        },

        /**
         * Summon the bookmark editor for new bookmark creation.
         */
        newBookmark: function (ev) {
            // Bail if this is disabled.
            if ($(this).hasClass('disabled')) { return; }
            $this.summonBookmarkEditor();
            return false;
        },

        /**
         * Summon and populate the bookmark editor.
         */
        summonBookmarkEditor: function (item) {
            var new_bm_link = $this.editor.find('.new-bookmark'),
                bm_editor = $('#bookmark_editor'),
                pos = new_bm_link.offset(),
                l_h = new_bm_link.height(),
                e_h = bm_editor.height();

            var item_type = (item) ? item.get('type') : 'bookmark';

            bm_editor
                .css({ 
                    left: pos.left - 24, 
                    top:  pos.top - e_h - 12 
                })
                .find('item_id').val('').end()
                .find('form').attr('class', item_type).end()
                .find('.error').removeClass('error').end()
                .find('input[name=type]').val(item_type).end()
                .find('input[type=hidden]').val('').end()
                .find('input[type=text]').val('').end()
                .show();

            if (item) {
                $.each(item.extract(), function(name, val) {
                    bm_editor.find('input[name='+name+']').val(val);
                });
            }

            setTimeout(function () {
                bm_editor.find('input[name=title]').focus().end();
            }, 0.1);
        },

        /**
         * Attempt to save a new or existing bookmark from the editor form.
         */
        saveBookmark: function (ev) {
            var bm_editor = $('#bookmark_editor'),
                data = {},
                errors = {},
                edited_item = null,
                has_errors = false,
                new_item = null;

            bm_editor.find('input').each(function (i) {
                var el = $(this),
                    name = el.attr('name');
                if ('id' == name) { 
                    edited_item = $this.model.find(el.val());
                } else if (name) { 
                    data[name] = el.val(); 
                }
            });

            new_item = $this.model.add(data);
            errors   = new_item.validate();

            bm_editor.removeClass('error');
            $.each(errors, function (field, error) {
                has_errors = true;
                bm_editor.find('.field_'+field).addClass('error');
            });

            if (has_errors > 0) { 
                $this.model.remove(new_item);
                return; 
            }

            if (edited_item) {
                edited_item.replaceSelf(new_item);
            } else {
                $this.selected_folder.add(new_item);
            }

            $this.refresh();
            bm_editor.hide();
        },

        /**
         * Insert a new folder.
         */
        newFolder: function (ev) {

            // Bail if this is disabled.
            if ($(this).hasClass('disabled')) { return; }

            var new_folder = $this.model.add({
                type: 'folder',
                title: _('New Folder')
            });

            if ($this.selected_item) {
                new_folder.moveAfter($this.selected_item);
            } else if ($this.selected_folder) {
                new_folder.moveTo($this.selected_folder);
            }

            $this.refresh();
            $this.summonFolderRename(new_folder);

            return false;
        },

        /**
         * Bring up a text field to accept a new name for a folder.
         *
         * Only works for folders visible in the bookmark pane.
         */
        summonFolderRename: function (folder_item) {
            var folder_el  = $('#'+folder_item.id),
                folder_pos = folder_el.offset(),
                editor     = $('<input type="text" />');

            var accept = function () {
                folder_item.set('title', editor.val());
                editor.remove();
                $this.refresh();
            };

            var reject = function () {
                editor.remove();
            };

            editor
                .appendTo('body')
                .css({
                    position: 'absolute',
                    left:     folder_pos.left + 34,
                    top:      folder_pos.top,
                    width:    '60ex',
                    zIndex:   1000
                })
                .val(folder_item.get('title'))
                .select()
                .focus()
                .blur(accept)
                .keyup(function (ev) {
                    switch (ev.keyCode) {
                        case 13: accept(); break;
                        case 27: reject(); break;
                    }
                });
        },

        EOF:null
    };

    return $this.init();
}());
