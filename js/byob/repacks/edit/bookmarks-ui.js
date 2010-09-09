/**
 * BYOB bookmarks editor UI
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

        selected_locale: null,
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

            $this.wireUpLocaleSelector();
            $this.wireUpFolders();
            $this.wireUpBookmarks();
            $this.wireUpBookmarkEditor();

            $this.updateFolders();
            $this.selectFolder($this.editor_id + '-toolbar');
            $this.updateBookmarksJSON();
            $this.highlightLocaleTabsWithContent();
        },

        /**
         * Accept bookmark data, presumably as part of page load.
         */
        loadData: function (data) {
            var bookmarks_data = data.bookmarks;

            $this.model = new BYOB_Repacks_Edit_Bookmarks_Model.Root();

            $this.model.locales = data.locales;
            $this.model.default_locale = data.default_locale;

            $this.model.add($.extend(bookmarks_data.menu || {}, {
                type:  'menu',
                id:    'editor1-menu',
                title: _('Bookmarks Menu')
            }));

            $this.model.add($.extend(bookmarks_data.toolbar || {}, {
                type:  'toolbar',
                id:    'editor1-toolbar',
                title: _('Bookmarks Toolbar')
            }));

            $this.model.selectLocale(data.default_locale);
            $this.selected_locale = data.default_locale;

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
                        '.title': item.get('title', $this.selected_locale, true),
                        '.count': ''+item.get('items').length
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
                items = $this.selected_folder.get('items');
            }

            bm_root_el.find('li:not(.template)').remove();

            $.each(items, function (i, item) {
                tmpl_el.cloneTemplate({
                    '@id':    item.id,
                    '@class': 'bookmark type-' + item.get('type') + 
                        ( (!!item.errors) ? ' has_errors' : '' ),
                    '.title': item.get('title', $this.selected_locale, true),
                    '.link':  item.get('link', $this.selected_locale, true) || 
                        item.get('feedLink', $this.selected_locale, true)
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
            $this.highlightLocaleTabsWithContent();
        },

        /**
         * Wire up handler for clicks on the view locale tabs.
         */
        wireUpLocaleSelector: function () {
            $('.locale-selector').click(function (ev) {
                var el = $(ev.target),
                    new_locale = el.attr('data-locale');
                $this.switchViewLocale(new_locale);
                return false;
            });
        },

        /**
         * Switch the view to anew locale.
         */
        switchViewLocale: function (new_locale) {
            if (-1 == $this.model.locales.indexOf(new_locale)) { return; }
            $('.locale-selector li').removeClass('selected');
            $('.locale-selector li a[data-locale='+new_locale+']')
                .parent().addClass('selected');
            $this.selected_locale = new_locale;
            $this.model.selectLocale(new_locale);
            $this.selectFolder($this.editor_id + '-toolbar');
            $this.refresh();
        },

        /**
         * Run through the model and highlight the locale tabs that have content.
         */
        highlightLocaleTabsWithContent: function () {
            $.each($this.model.locales, function (idx, locale) {
                var suff = (locale == $this.model.default_locale) ? '' : '.' + locale,
                    item = $('.bookmarks-editor .locale-selector a[data-locale="'+locale+'"]');
                var has_content = false;
                $.each(['toolbar','menu'], function (idx, name) {
                    var items = $this.model.items['editor1-'+name]['items'+suff];
                    if (items && items.length > 0) { has_content = true; }
                });
                item.parent()[has_content ? 'addClass' : 'removeClass']('has_content');
            });
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
                $this.selected_item = null;
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
            $this.updateBookmarks(folder.get('items'));
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
                .find('.save').click($this.saveBookmark).end()
                .find('.locale_buttons').click($this.switchBookmarkEditorLocale).end()
                .find('input[name=type]').change(function (ev) {
                    bm_editor.find('form').attr('class', $(this).val()).end();
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

            if ($this.model.default_locale == $this.selected_locale) {
                bm_editor.find('.field_locale').show();
            } else {
                bm_editor.find('.field_locale').hide();
            }

            bm_editor
                .css({ 
                    left: pos.left - 24, 
                    top:  pos.top - e_h - 12 
                })
                .find('item_id').val('').end()
                .find('form').attr('class', item_type).end()
                .find('.error').removeClass('error').end()
                .find('.field_type input#type-'+item_type).attr('checked',true).end()
                .find('input[type=hidden]').val('').end()
                .find('input[type=text]').val('').attr('data-original','').end()
                .show();

            if (item) {
                
                // Set the ID, since we have one
                bm_editor.find('input[name=id]').val(item.get('id'));
                
                // Come up with defaults for all fields, consult default locale
                // if necessary.
                var defaults = {};
                $.each(item.field_names, function (idx, name) {
                    defaults[name] = item.get(name);
                });

                // Populate the editor form by scanning through all item fields
                // combined with known locales, filling in defaults where
                // necessary.
                var data = item.extract();
                $.each(item.field_names, function (idx, name) {
                    $.each($this.model.locales, function (idx, locale) {
                        var l_name = name+'.'+locale,
                            value  = item.get(name, locale) || defaults[name];
                        bm_editor.find('input[name='+l_name+']')
                            .attr('data-original', value);
                        bm_editor.find('input[name='+l_name+']')
                            .val(value);
                    });
                });

            }

            setTimeout(function () {
                $this.switchBookmarkEditorLocale(null, $this.selected_locale);
            }, 0.1);

        },

        /**
         * Make the editing fields for a button's locale visible when pressed.
         */
        switchBookmarkEditorLocale: function (ev, locale) {
            if (!locale) {
                var button = $(ev.target);
                if ('BUTTON' != button.attr('tagName')) { return; }
                locale = button.attr('data-locale');
            }
            if (-1 == $this.model.locales.indexOf(locale)) { return; }
                
            var bm_editor = $('#bookmark_editor');
            bm_editor.find('.locale_buttons button').removeClass('selected');
            bm_editor.find('.locale_buttons button.locale-'+locale).addClass('selected');
            bm_editor.find('.editor_fields input[type=text]').hide();
            bm_editor.find('.editor_fields input[type=text].locale-'+locale).show();
        },

        /**
         * Attempt to save a new or existing bookmark from the editor form.
         */
        saveBookmark: function (ev) {
            var bm_editor = $('#bookmark_editor'),
                all_errors = {},
                has_errors = false;

            // Try finding an item to be edited (eg. this isn't new)
            var edited_id = bm_editor.find('input[name=id]').val(),
                edited_item = $this.model.find(edited_id);

            var new_item = null;
            if (edited_item) {
                // Use the original item's data, but mind that the type could
                // have changed.
                var data = edited_item.extract();
                data.type = bm_editor.find('input[name=type]:checked').val();
                new_item = $this.model.factory(data);
            } else {
                // No original item, so we're clear to create a fresh one.
                new_item = $this.model.factory({
                    'type': bm_editor.find('input[name=type]').val()
                });
            }

            // Run through all the field names combined with locales, look for
            // changed editor fields and set those as properties on the new
            // item.
            $.each(new_item.field_names, function (idx, field_name) {
                $.each($this.model.locales, function (idx, locale) {
                    var l_name   = field_name+'.'+locale,
                        field_el = bm_editor.find('input[name='+l_name+']'),
                        orig_val = field_el.attr('data-original'),
                        curr_val = field_el.val(),
                        changed  = ( curr_val != orig_val );
                    if (changed) {
                        new_item.set(field_name, curr_val, locale);
                    }
                });
            });

            all_errors = new_item.validate();

            bm_editor.find('.editor_fields li.error')
                .removeClass('error')
            bm_editor.find('.locale_buttons button.error')
                .removeClass('error');

            $.each(all_errors, function (locale, errors) {
                var locale_has_errors = false;
                $.each(errors, function (field, error) {
                    locale_has_errors = has_errors = true;
                    bm_editor.find('input[name='+field+'.'+locale+']').parent()
                        .addClass('error');
                });
                if (locale_has_errors) {
                    bm_editor.find('.locale_buttons button.locale-'+locale)
                        .addClass('error');
                }
            });

            if (has_errors) { return; }

            if (edited_id) {
                $this.model.replace(edited_item, new_item);
            } else {
                $this.model.add(new_item);
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
                folder_item.set('title', editor.val(), $this.selected_locale);
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
                .val(folder_item.get('title', $this.selected_locale, true))
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
