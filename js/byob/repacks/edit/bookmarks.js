/**
 * BYOB bookmarks editor
 */
BYOB_Repacks_Edit_Bookmarks = (function () {
    var $this = {

        // Is the editor ready yet?
        is_ready: false,

        selected_items: null,
        selected_item: null,
        selected_folder: null,

        // Data model for the bookmark editor.
        bookmarks: {
            menu: [ ],
            toolbar: [ ]
        },

        /**
         * Package initialization
         */
        init: function() {
            $(document).ready($this.ready);
            return $this;
        },

        /**
         * Page ready
         */
        ready: function() {
            $this.is_ready = true;

            $this.editor = $('.bookmarks-editor');
            $this.editor_id = $this.editor.attr('id');

            $this.injectIds();
            
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
            $.each($this.bookmarks, function (name, items) {
                $this.bookmarks[name] = (data[name]) ?
                    data[name] : [];
            });
            if ($this.is_ready) { $this.refresh(); }
        },

        /**
         * Update the bookmarks data form field for later submission.
         */
        updateBookmarksJSON: function() {
            var data = JSON.stringify($this.bookmarks, null, ' ');
            $('#bookmarks_json').val(data);
        },

        /**
         * Update the folder pane DOM with the current folder structure.
         */
        updateFolders: function () {

            $.each($this.bookmarks, function (root_name, bookmarks) {
                
                var root_el = $this.editor.find('.folders > .folder-' + root_name),
                    tmpl_el = $this.editor.find('.folders > .folder.template'),
                    par_el  = root_el.find('.subfolders');

                par_el.find('li:not(.template)').remove();
                    
                $.each(bookmarks, function (j, item) {
                    if ('folder' !== item.type) { return; }
                    tmpl_el.cloneTemplate({
                        '@id':    'fl-' + item.id,
                        '.title': item.title
                    }).appendTo(par_el);
                });

            });

            $this.editor.find('.folders').sortable('refresh');
        },

        /**
         * Update the bookmark pane DOM with a list of bookmarks.
         */
        updateBookmarks: function (items) {
            //var bm_root_el = $this.editor.find('.bookmarks tbody'),
            var bm_root_el = $this.editor.find('.bookmarks'),
                tmpl_el    = $this.editor.find('.bookmark.template');

            if (null == items) {
                items = $this.selected_items;
            } else {
                $this.selected_items = items;
            }

            bm_root_el.find('li:not(.template)').remove();

            $.each(items, function (i, item) {
                tmpl_el.cloneTemplate({
                    '@id':    item.id,
                    '@class': 'bookmark type-' + item.type,
                    '.title': item.title,
                    '.link':  item.link || item.feedLink
                }).appendTo(bm_root_el);
            });

            bm_root_el.sortable('refresh');
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
            $.each($this.bookmarks, function (root_name) {
                var folder = folders
                    .find('.folder-'+root_name+' > .subfolders');
                folder.sortable({
                    appendTo: 'body',
                    cursor:   'move',
                    axis:     'y',
                    items:    'li:not(.template)',
                    stop:     $this.performFolderMove
                });
            });

            // Connect all the root folder sortables to each other.
            $.each($this.bookmarks, function (from_name) {
                var from_id = '#'+$this.editor_id+' .folder-'+from_name+' > .subfolders',
                    to_ids = [];
                $.each($this.bookmarks, function (to_name) {
                    if (from_name === to_name) { return; }
                    to_ids.push('#'+$this.editor_id+' .folder-'+to_name+' > .subfolders');
                });
                $(from_id).sortable('option', 'connectWith', to_ids);
            });

            // Wire up root folders themselves as drop targets.
            folders.find('.root .title').droppable({
                'tolerance': 'pointer',
                'over': function (ev, ui) { 
                    // Only pay attention to elements from bookmark pane.
                    if (ui.draggable.hasClass('folder')) { return; }
                    $(this).parent().addClass('hover');
                },
                'out': function (ev, ui) { 
                    // Only pay attention to elements from bookmark pane.
                    if (ui.draggable.hasClass('folder')) { return; }
                    $(this).parent().removeClass('hover');
                },
                'drop': function (ev, ui) {
                    // Only pay attention to elements from bookmark pane.
                    if (ui.draggable.hasClass('folder')) { return; }
                    
                    var bm_item = $this.findById(ui.draggable.attr('id')),
                        parts   = $(this).parent().attr('id').split('-'),
                        dest    = $this.bookmarks[parts[1]];

                    // Ignore an attempt to drop the item where it already is.
                    if (-1 != dest.indexOf(bm_item.bookmark)) { return; }
                    
                    setTimeout(function () {
                        // HACK: Schedule the data model change and view refresh
                        // until after jQuery UI has had a chance to finish up.
                        bm_item.parent.splice(bm_item.index, 1);
                        dest.push(bm_item.bookmark);
                        $this.refresh();
                    }, 1);
                }
            });

        },

        /**
         * Event handler to perform a folder move at the data model level.
         */
        performFolderMove: function (ev, ui) {
            // Find the moved item, remove it from its context.
            var bm_item = $this.findById(ui.item.attr('id').replace('fl-',''));
            bm_item.parent.splice(bm_item.index, 1);

            // Update the data model to reflect the change in DOM structure
            var id, bm_dest = null;
            if ( id = ui.item.prev('li.folder').attr('id') ) {
                // Use the previous DOM node as context for data model move.
                bm_dest = $this.findById(id.replace('fl-',''));
                bm_dest.parent.splice(bm_dest.index + 1, 0, bm_item.bookmark);
            } else if ( id = ui.item.next('li.folder').attr('id') ) {
                // Use the next DOM node as context for data model move.
                bm_dest = $this.findById(id.replace('fl-',''));
                bm_dest.parent.splice(bm_dest.index, 0, bm_item.bookmark);
            } else {
                // Node was dropped into previously empty list, so just push.
                var parts = ui.item.parent().parent().attr('id')
                    .replace('fl-','').split('-');
                $this.bookmarks[parts[1]].unshift(bm_item.bookmark);
            }

            // Refresh selected folder and the serialized data.
            $this.selectFolder();
            $this.updateBookmarksJSON();
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
            $.each($this.bookmarks, function (to_name) {
                to_ids.push('#'+$this.editor_id+
                    ' .folder-'+to_name+' > .subfolders');
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

            var bm_item = $this.findById(target.attr('id'));
            $this.selected_item = bm_item;

            target.parent().find('.bookmark').removeClass('selected');
            target.addClass('selected');

            return false;
        },

        /**
         * Open the bookmark editor for an item on double click.
         */
        editBookmark: function (ev) {
            var target = $(ev.target);
            if (!target.hasClass('bookmark')) { target = target.parent(); }
            if (!target.hasClass('bookmark')) { return; }

            var bm_item = $this.findById(target.attr('id'));
            if ('folder' === bm_item.bookmark.type) {
                $this.summonFolderRename(bm_item.bookmark.id); 
            } else {
                $this.summonBookmarkEditor(bm_item.bookmark); 
            }

            return false;
        },

        /**
         * Update feedback in sortable lists while item being dragged.
         */
        updateItemFeedback: function (ev, ui) {
            var bm_item = $this.findById(ui.item.attr('id'));
            if ('folder' === bm_item.bookmark.type) {
                // No-op
            } else {
                ui.placeholder.prev('.folder').each(function () {
                    $this.editor.find('.hover').removeClass('hover');
                    $(this).addClass('hover');
                });
            }
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

            // Find the data item for the dragged node.
            var bm_item = $this.findById(ui.item.attr('id'));
            bm_item.parent.splice(bm_item.index, 1);

            // Update the data model to reflect the change in DOM structure
            var id, root_parent, bm_dest = null;
            if (id = ui.item.prev('.folder').attr('id')) {
                // This is a drop after a folder in the folder pane...
                ui.item.remove();
                bm_dest = $this.findById(id.replace('fl-',''));
                if ('folder' === bm_item.bookmark.type) {
                    // Folder dropped after a folder in folder pane.
                    bm_dest.parent.splice(bm_dest.index + 1, 0, bm_item.bookmark);
                } else {
                    // Bookmark item dropped into a folder in folder pane.
                    bm_dest.bookmark.items.push(bm_item.bookmark);
                }

            } else if (id = ui.item.next('.folder').attr('id')) {
                // This is a drop in front of a folder in the folder pane...
                ui.item.remove();
                bm_dest = $this.findById(id.replace('fl-',''));
                if ('folder' === bm_item.bookmark.type) {
                    // Folder dropped in front of a folder in folder pane.
                    bm_dest.parent.splice(bm_dest.index, 0, bm_item.bookmark);
                } else {
                    // Bookmark item dropped into a root folder in folder pane.
                    bm_dest.parent.unshift(bm_item.bookmark);
                }

            } else if ( id = ui.item.prev('.bookmark').attr('id') ) {
                // This drop moves an item/folder after another item.
                bm_dest = $this.findById(id);
                bm_dest.parent.splice(bm_dest.index + 1, 0, bm_item.bookmark);

            } else if ( id = ui.item.next('.bookmark').attr('id') ) {
                // This drop moves an item/folder before another item.
                bm_dest = $this.findById(id);
                bm_dest.parent.splice(bm_dest.index, 0, bm_item.bookmark);

            } else {
                // Seems not to be a valid drop, so restore the item.
                bm_item.parent.splice(bm_item.index, 0, bm_item.bookmark);
            }

            $this.refresh();
        },

        /**
         * Select a folder by ID and populate the bookmarks pane with its
         * items.
         */
        selectFolder: function (folder_id) {
            if (!folder_id) {
                // If no folder ID supplied, use the last selected one.
                folder_id = $this.selected_folder;
            } else {
                // Remember the supplied folder ID as last selected.
                $this.selected_folder = folder_id;
            }

            var folder_el = $('#'+folder_id), items;
            if (folder_el.hasClass('root')) {
                // HACK: If this is a root folder, find the root name from ID
                // naming convention.
                var id_parts = folder_id.split('-');
                items = $this.bookmarks[id_parts[1]];
            } else {
                // Find the folder bookmark by ID, use its items.
                items = $this.findById(folder_id.replace('fl-','')).bookmark.items || [];
            }

            // Deselect all folders, select this one.
            $this.editor.find('.folder').removeClass('selected');
            folder_el.addClass('selected');

            // Update the bookmarks pane from this folder.
            $this.updateBookmarks(items);
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
            if (!$this.selected_item) { return; }

            var item = $this.selected_item; 
            item.parent.splice(item.index, 1);
            $this.selected_item = null;

            $this.refresh();

            return false;
        },

        /**
         * Summon the bookmark editor for new bookmark creation.
         */
        newBookmark: function (ev) {
            $this.summonBookmarkEditor({});
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

            if (!item.type) {
                item.type = 'bookmark';
            }

            bm_editor
                .css({ 
                    left: pos.left - 24, 
                    top:  pos.top - e_h - 12 
                })
                .find('item_id').val('').end()
                .find('form').attr('class', item.type).end()
                .find('.error').removeClass('error').end()
                .find('input[name=type]').val(item.type).end()
                .find('input[type=hidden]').val('').end()
                .find('input[type=text]').val('').end()
                .show();

            $.each(item, function(name, val) {
                bm_editor.find('input[name='+name+']').val(val);
            });

            setTimeout(function () {
                bm_editor.find('input[name=title]').focus().end()
            }, 0.1);
        },

        /**
         * Attempt to save a new or existing bookmark from the editor form.
         */
        saveBookmark: function (ev) {
            var bm_editor = $('#bookmark_editor'),
                data = { title: '', type: '', link: '', feedLink: '' },
                errors = [];

            bm_editor.find('input').each(function (i) {
                var el = $(this),
                    name = el.attr('name');
                if (name) { data[name] = el.val(); }
            });

            if (!data.title)
                errors.push('title'); 
            if ('bookmark' == data.type && !data.link)
                errors.push('link'); 
            if ('livemark' == data.type && !data.feedLink)
                errors.push('feedlink'); 

            bm_editor.removeClass('error');
            $.each(errors, function (i, error) {
                bm_editor.find('.field_'+error).addClass('error');
            });

            if (errors.length > 0) { return; }
                
            var item = { 
                id: data.id || $this.generateId(),
                type: data.type,
                title: data.title,
                link: '',
                feedLink: '',
                siteLink: ''
            };

            if ('bookmark' == data.type) {
                item.link = data.link;
                item.description = data.description;
            } else if ('livemark' == data.type) {
                item.feedLink = data.feedLink;
                item.siteLink = data.siteLink;
            }

            var orig_item = $this.findById(data.id);
            if (!orig_item) {
                $this.selected_items.push(item);
            } else {
                $.each(item, function (name, val) {
                    orig_item.bookmark[name] = val;
                });
            }

            $this.updateBookmarks();
            $this.updateBookmarksJSON();
            
            bm_editor.hide();
        },

        /**
         * Insert a new folder.
         */
        newFolder: function (ev) {
            // Only allow new folders to be created in one of the roots.
            if (!$('#'+$this.selected_folder).hasClass('root')) { 
                return; 
            }

            var new_folder = {
                id: $this.generateId(),
                type: 'folder',
                title: 'New Folder',
                items: []
            };

            if ($this.selected_item) {
                // Insert the new folder after a selected item.
                $this.selected_item.parent
                    .splice($this.selected_item.index + 1, 0, new_folder);
            } else if ($this.selected_items) {
                // Append the new folder to the end of selected items.
                $this.selected_items.push(new_folder);
            }

            $this.refresh();
            $this.summonFolderRename(new_folder.id);

            return false;
        },

        /**
         * Bring up a text field to accept a new name for a folder.
         *
         * Only works for folders visible in the bookmark pane.
         */
        summonFolderRename: function (fl_id) {
            var folder_el = $('#'+fl_id),
                folder_pos = folder_el.offset(),
                folder_item = $this.findById(fl_id),
                editor = $('<input type="text" />');

            var accept = function () {
                folder_item.bookmark.title = editor.val();
                editor.remove();
                $this.refresh();
            }

            var reject = function () {
                editor.remove();
            }

            editor
                .appendTo('body')
                .css({
                    position: 'absolute',
                    left:     folder_pos.left + 34,
                    top:      folder_pos.top,
                    width:    '60ex',
                    zIndex:   1000
                })
                .val(folder_item.bookmark.title)
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

        /**
         * Generate a unique ID for a bookmark.
         *
         * @return string
         */
        generateId: function () {
            return 'bm-' + (new Date()).getTime() + '-' + (++($this.last_id));
        },
        last_id: 0,

        /**
         * Inject unique IDs into all bookmarks.
         *
         * @return string
         */
        injectIds: function (bookmarks) {
            if (!bookmarks) {
                // If no list of bookmarks, iterate through root folders.
                $.each($this.bookmarks, function (k,v) {
                    $this.injectIds(v);
                });
            } else {
                // Recursively inject IDs into each bookmark in the folder.
                $.each(bookmarks, function (idx, bookmark) {
                    if (!bookmark.id) {
                        bookmark.id = $this.generateId();
                    }
                    if (bookmark.items) {
                        $this.injectIds(bookmark.items);
                    } else {
                        if ('folder' == bookmark.type) {
                            // HACK: If somehow this folder-type item doesn't
                            // have an empty list already, give it one.
                            bookmark.items = [];
                        }
                    }
                });
            }
        },

        /**
         * Search recursively for a bookmark by ID with parent list context.
         */
        findById: function (id, bookmarks) {
            if (!bookmarks) {
                // No list of bookmarks, so start looking from root folders.
                var rv = null;
                $.each($this.bookmarks, function (root_name, bookmarks) {
                    if (null !== rv)
                        return; // Found a result, so stop looking.
                    rv = $this.findById(id, bookmarks);
                });
                return rv;
            } else {
                // Got a list of bookmarks, so start searching through it.
                var rv = null;
                $.each(bookmarks, function (idx, bookmark) {
                    if (null !== rv)
                        return; // Found a result, so stop looking.
                    if (bookmark.id == id) {
                        // Found the bookmark, so build the result.
                        rv = {
                            bookmark: bookmark,
                            parent:   bookmarks,
                            index:    bookmarks.indexOf(bookmark)
                        };
                    } else if (bookmark.items) {
                        // This bookmark has children, so try recursing.
                        rv = $this.findById(id, bookmark.items);
                    }
                });
                return rv;
            }
        },

        EOF:null
    };
    return $this.init();
}());
