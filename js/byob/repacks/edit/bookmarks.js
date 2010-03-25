/**
 * BYOB bookmarks editor
 */
/*jslint laxbreak: true */
BYOB_Repacks_Edit_Bookmarks = (function () {

    var $this = { };

    /**
     * Overall model for bookmarks, tracks all items.
     */
    $this.Model = {

        // Map of items by ID.
        items: {},

        // Counter facilitating unique IDs.
        last_id: 0,

        /**
         * Add an item to the root container, ensuring it has an ID.
         */
        add: function (item) {
            if (!item.id) { item.set('id', this.genId()); }
            this.items[item.id] = item;
            return this;
        },

        /**
         * Remove an item from the root container.
         */
        remove: function (item) {
            delete this.items[item.id];
        },

        /**
         * Find an item by ID.
         */
        find: function (id) {
            return this.items[id];
        },

        /**
         * Generate a unique ID.
         */
        genId: function () {
            return 'bm-' + 
                (new Date()).getTime() + '-' + 
                (++(this.last_id));
        },

        /**
         * Extract all the data from the container as plain objects.
         */
        extract: function () {
            var out = {}, roots = [];
            $.each(this.items, function (key, item) {
                if ('folder' == item.type && !item.parent) {
                    out[item.id] = item.extract();
                }
            });
            return out;
        },

        EOF: null
    };

    /**
     * Base class for all model items.
     */
    $this.Item = Class.extend({
        type: 'item',
        errors: false,
        parent: null,
        field_names: [ 'title', 'description' ],

        /**
         * Initialize this item from given object properties.
         */
        init: function (data) {
            var self = this;
            $.each(data, function(k,v) { self.set(k,v); });
            this.model = $this.Model;
            this.model.add(this);
        },

        /**
         * Mutator, delegates to set_{name} functions if found.
         */
        set: function (k,v) {
            return ('function' == typeof this['set_'+k]) ?
                this['set_'+k](v) : this[k] = v;
        },

        /**
         * Accessor, delegates to get_{name} functions if found.
         */
        get: function (k) {
            return ('function' == typeof this['get_'+k]) ?
                this['get_'+k]() : this[k];
        },

        /**
         * Extract public fields into a plain data object.
         */
        extract: function () {
            var self = this, 
                out = { 
                    id: this.id, 
                    type: this.type 
                };
            $.each(self.field_names, function (idx, name) {
                out[name] = self.get(name);
            });
            return out;
        },

        /**
         * Index accessor, defers to current parent.
         */
        get_index: function () {
            return this.parent.indexOf(this);
        },

        /**
         * Remove this item from its parent.
         */
        deleteSelf: function () {
            if (!this.parent) { return; }
            this.parent.remove(this);
            this.model.remove(this);
        },

        /**
         * Move this item to a new parent.
         */
        moveTo: function (new_parent) {
            if (!new_parent.isFolder()) { return false; }
            if (new_parent == this.parent) { return false; }
            if (new_parent.isFull()) { return false; }
            if (this.parent) { this.parent.remove(this); }
            new_parent.add(this);
            return true;
        },

        /**
         * Move this item before a given new sibling.
         */
        moveBefore: function (new_sibling) {
            var new_parent = new_sibling.parent;
            if (!new_parent.isFolder()) { return false; }
            if (new_sibling == this) { return false; }
            if (new_parent != this.parent && new_parent.isFull()) { 
                return false; 
            }
            if (this.parent) {
                this.parent.remove(this);
            }
            return new_sibling.parent.add(
                this, new_sibling.get('index')
            );
        },

        /**
         * Move this item after a given new sibling.
         */
        moveAfter: function (new_sibling) {
            var new_parent = new_sibling.parent;
            if (!new_parent.isFolder()) { return false; }
            if (new_sibling == this) { return false; }
            if (new_parent != this.parent && new_parent.isFull()) { 
                return false; 
            }
            if (this.parent) {
                this.parent.remove(this);
            }
            return new_sibling.parent.add(
                this, new_sibling.get('index') + 1
            );
        },

        EOF:null
    });

    /**
     * Basic bookmark item.
     */
    $this.Bookmark = $this.Item.extend({
        type: 'bookmark',
        field_names: [ 'title', 'link', 'description' ]
    });
    
    /**
     * Basic livemark item.
     */
    $this.Livemark = $this.Bookmark.extend({
        type: 'livemark',
        field_names: [ 'title', 'feedLink', 'siteLink' ]
    });
    
    /**
     * Basic folder item.
     */
    $this.Folder = $this.Item.extend({

        type: 'folder',
        field_names: [ 'title', 'description', 'items' ],

        items: [],

        type_map: {
            'default':  'Bookmark',
            'bookmark': 'Bookmark',
            'livemark': 'Livemark',
            'folder':   'Folder'
        },

        isFolder: function () { 
            return true; 
        },

        allowsSubFolders: function () {
            return false;
        },

        isFull: function () {
            return this.items.length >= 10;
        },

        factory: function (item, add) {
            var type = (item.type) ? item.type : 'default',
                Cls = $this[this.type_map[type]];
                obj = new Cls(item);
            obj.parent = this;
            if (add) { this.add(obj); }
            return obj;
        },

        set_items: function (items) {
            this.items = [];
            for (var i=0, item; item = items[i]; i++) {
                this.items.push(this.factory(item));
            }
            return this.items;
        },

        remove: function (item) {
            var idx = this.items.indexOf(item);
            item.parent = null;
            return this.items.splice(idx, 1);
        },

        add: function (item, idx) {
            item.parent = this;
            if (0 === idx) {
                this.items.unshift(item);
            } else if (idx > 0) {
                this.items.splice(idx, 0, item);
            } else {
                this.items.push(item);
            }
        },

        indexOf: function (item) {
            return this.items.indexOf(item);
        },

        extract: function () {
            var out = this._super(), 
                items_out = [];
            for (var i=0, item; item = this.items[i]; i++) {
                items_out.push(item.extract());
            }
            out.items = items_out;
            return out;
        },
        
        EOF:null
    });

    /**
     * Toolbar bookmarks folder
     */
    $this.ToolbarFolder = $this.Folder.extend({
        allowsSubFolders: function () {
            return true;
        },
        isFull: function () {
            return this.items.length >= 3;
        },
        EOF:null
    });
    
    /**
     * Menu bookmarks folder
     */
    $this.MenuFolder = $this.Folder.extend({
        allowsSubFolders: function () {
            return true;
        },
        isFull: function () {
            return this.items.length >= 5;
        },
        EOF:null
    });

    $.extend($this, {

        // Is the editor ready yet?
        is_ready: false,

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
        init: function () {
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
            if (data.menu) {
                $this.Model.add(new $this.MenuFolder({
                    id:    'editor1-menu',
                    title: 'Bookmarks Menu',
                    items: data.menu.items
                }));
            }
            if (data.toolbar) {
                $this.Model.add(new $this.ToolbarFolder({
                    id:    'editor1-toolbar',
                    title: 'Bookmarks Toolbar',
                    items: data.toolbar.items
                }));
            }
            if ($this.is_ready) { $this.refresh(); }
        },

        /**
         * Update the bookmarks data form field for later submission.
         */
        updateBookmarksJSON: function() {
            var extracted = $this.Model.extract(),
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
                    bookmarks = $this.Model.find(root_id).get('items');

                par_el.find('li:not(.template)').remove();
                root_el.removeClass('has_errors');

                $.each(bookmarks, function (j, item) {
                    if (!!item.errors) { root_el.addClass('has_errors'); }
                    if ('folder' !== item.type) { return; }
                    tmpl_el.cloneTemplate({
                        '@id':    'fl-' + item.id,
                        '@class': 'folder ' +
                            ( (!!item.errors) ? ' has_errors' : '' ),
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
            var bm_root_el = $this.editor.find('.bookmarks'),
                tmpl_el    = $this.editor.find('.bookmark.template');

            if (null === items) {
                items = $this.selected_folder.items;
            }

            bm_root_el.find('li:not(.template)').remove();

            $.each(items, function (i, item) {
                tmpl_el.cloneTemplate({
                    '@id':    item.id,
                    '@class': 'bookmark type-' + item.type + 
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
                    
                    var item = $this.Model.find(ui.draggable.attr('id')),
                        dest = $this.Model.find($(this).parent().attr('id'));

                    // Ignore an attempt to drop the item to own parent.
                    if (item.parent == dest) { return; }
                    
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
                bm_item = $this.Model.find(bm_id);

            if ( id = ui.item.prev('li.folder').attr('id') ) {
                // Use the previous DOM node as context for data model move.
                bm_item.moveAfter($this.Model.find(id.replace('fl-','')));
            } else if ( id = ui.item.next('li.folder').attr('id') ) {
                // Use the next DOM node as context for data model move.
                bm_item.moveBefore($this.Model.find(id.replace('fl-','')));
            } else {
                // Node was dropped into previously empty list, so just push.
                bm_item.moveTo(ui.item.parent().parent().attr('id').replace('fl-',''));
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

            $this.selected_item = $this.Model.find(target.attr('id'));

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

            var item = $this.Model.find(target.attr('id'));
            if ('folder' === item.type) {
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
            var item = $this.Model.find(ui.item.attr('id'));
            if ('folder' !== item.type) {
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

            var item = $this.Model.find(ui.item.attr('id'));

            var id, root_parent, dest = null;
            if (id = ui.item.prev('.folder').attr('id')) {
                dest = $this.Model.find(id.replace('fl-',''));
                if ('folder' === item.type) {
                    item.moveAfter(dest);
                } else {
                    item.moveTo(dest);
                }
            } else if (id = ui.item.next('.folder').attr('id')) {
                dest = $this.Model.find(id.replace('fl-',''));
                if ('folder' === item.type) {
                    item.moveBefore(dest);
                } else {
                    item.moveTo(dest);
                }
            } else if ( id = ui.item.prev('.bookmark').attr('id') ) {
                item.moveAfter($this.Model.find(id));
            } else if ( id = ui.item.next('.bookmark').attr('id') ) {
                item.moveBefore($this.Model.find(id));
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
                $this.selected_folder = folder = $this.Model.find(folder_id);
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

            var item_type = (item) ? item.type : 'bookmark';

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
                data = { title: '', type: '', link: '', feedLink: '' },
                errors = [];

            bm_editor.find('input').each(function (i) {
                var el = $(this),
                    name = el.attr('name');
                if (name) { data[name] = el.val(); }
            });

            // TODO: Fix validation
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
                
            if (data.id) {
                var item = $this.Model.find(data.id);
                $.each(data, function (name, val) {
                    item.set(name, val);
                });
            } else {
                $this.selected_folder.factory(data, true);
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

            var new_folder = new $this.Folder({
                type: 'folder',
                title: 'New Folder'
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
    });

    return $this.init();
}());
