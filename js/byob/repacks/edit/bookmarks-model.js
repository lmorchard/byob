/**
 * BYOB bookmarks editor data model
 */
/*jslint laxbreak: true */
BYOB_Repacks_Edit_Bookmarks_Model = (function () {
    var $this = {};

    /**
     * Package init
     */
    $this.init = function () {
        return $this;
    };

    /**
     * Overall model for bookmarks, tracks all items.
     */
    $this.Root = Class.extend({
        
        // Locales supplied by the page.
        locales: [ 'en-US' ],
        default_locale: 'en-US',
        selected_locale: 'en-US',

        // Map of items by ID.
        items: {},

        // Counter facilitating unique IDs.
        last_id: 0,

        /**
         * Map of types to classes.
         */
        type_map: {
            'default':  'Bookmark',
            'bookmark': 'Bookmark',
            'livemark': 'Livemark',
            'folder':   'Folder',
            'toolbar':  'ToolbarFolder',
            'menu':     'MenuFolder'
        },

        /**
         * Select a current locale.
         */
        selectLocale: function (new_locale) {
            if (this.locales.indexOf(new_locale) == -1) { return; }
            this.selected_locale = new_locale;
        },

        /**
         * Select a current locale.
         */
        selectLocale: function (new_locale) {
            if (this.locales.indexOf(new_locale) == -1) { return; }
            this.selected_locale = new_locale;
        },

        /**
         * Factory method, accepts a plain data object
         */
        factory: function (item) {
            var type = item.type || 'default',
                Cls =  $this[this.type_map[type]] || $this.Bookmark;
                obj =  new Cls(item, this);
            obj.set('id', item.id || this.genId());
            obj.model = this;
            return obj;
        },

        /**
         * Add an item to the root container, ensuring it has an ID.
         */
        add: function (item) {
            if (!(item instanceof $this.Item)) {
                item = this.factory(item);
            }
            this.items[item.id] = item;
            return item;
        },

        /**
         * Remove an item from the root container.
         */
        remove: function (item) {
            delete this.items[item.id];
        },

        /**
         * Replace the old item with the new item.
         */
        replace: function (old_item, new_item) {
            if (old_item.parent) {
                old_item.parent.replace(old_item, new_item);
            }
            new_item.id = old_item.id;
            this.items[new_item.id] = new_item;
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
                if ((item instanceof $this.Folder) && !item.parent) {
                    out[item.id] = item.extract();
                }
            });
            return out;
        },

        EOF: null
    });

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
        init: function (data, model) {
            var self = this;
            this.model = model;
            $.each(data, function(k,v) { self.set(k,v); });
        },

        /**
         * Whether or not this item contains other items.
         */
        isFolder: function () {
            return false;
        },

        /**
         * Mutator, delegates to set_{name} functions if found.
         */
        set: function (k, v, locale) {
            if ('function' == typeof this['set_'+k]) {
                return this['set_'+k](v,locale);
            }
            if (!locale || locale==this.model.selected_locale) {
                this[k] = v;
                return this[k];
            } else {
                // HACK: If the default locale hasn't been given a value yet,
                // use this one.
                if (!this[k]) { this[k] = v; }
                this[k+'.'+locale] = v;
                return v;
            }
        },

        /**
         * Accessor, delegates to get_{name} functions if found.
         */
        get: function (k, locale, use_default) {
            if ('function' == typeof this['get_'+k]) {
                return this['get_'+k](locale);
            }
            if (!locale || locale==this.model.selected_locale) {
                return this[k]; 
            } else {
                var value = this[k+'.'+locale];
                if (use_default && !value) {
                    value = this[k];
                }
                return value;
            }
        },

        /**
         * Extract public fields into a plain data object.
         */
        extract: function () {
            var self = this, 
                out = { 
                    id: this.get('id'), 
                    type: this.get('type') 
                };
            $.each(self.field_names, function (idx, name) {
                var default_val = self.get(name);
                out[name] = default_val;
                $.each(self.model.locales, function (idx, locale) {
                    var locale_field = name+'.'+locale,
                        val = self.get(locale_field);
                    if (val != default_val) {
                        out[locale_field] = val;
                    }
                });
            });
            return out;
        },

        /**
         * Validate the contents of this item.
         */
        validate: function () {
            var self = this, all_errors = {};
            for (var i=0,locale; locale = self.model.locales[i]; i++) {
                all_errors[locale] = self.validate_locale(locale);
            }
            return all_errors;
        },

        /**
         * Validate fields for a single locale.
         */
        validate_locale: function (locale) {
            var errors = {};
            if (!this.get('title', locale, true)) {
                errors.title = 'required';
            }
            return errors;
        },

        /**
         * Validate a URL for data entry
         */
        validateURL: function (url) {
            var parsed = $this.parseUri(url),
                allowed_protos = [ 'http', 'https', 'ftp' ],
                is_ok =
                    parsed.host && 
                    (-1 !== allowed_protos.indexOf(parsed.protocol));
            return is_ok;
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
        moveTo: function (new_parent, idx) {
            if (!new_parent.isFolder()) { return false; }
            if (new_parent == this.parent) { return false; }
            if (new_parent != this.parent && new_parent.isFull()) { 
                return false; 
            }

            if (this.parent) { this.parent.remove(this); }

            return new_parent.add(this, idx);
        },

        /**
         * Move this item near a new sibling.
         */
        moveToSibling: function (new_sibling, before) {
            if (new_sibling == this) { return false; }
            var new_parent = new_sibling.parent;

            if (!new_parent.isFolder()) { return false; }
            if (new_parent != this.parent && new_parent.isFull()) { 
                return false; 
            }

            // NOTE: Important that finding the sibling index happen after
            // removal. Indices will be thrown off if the item is being
            // moved within the same parent.
            if (this.parent) { this.parent.remove(this); }
            var new_idx = new_sibling.get('index') + ( before ? 0 : 1 );

            return new_parent.add(this, new_idx);
        },

        /**
         * Move this item before a given new sibling.
         */
        moveBefore: function (new_sibling) {
            return this.moveToSibling(new_sibling, true);
        },

        /**
         * Move this item after a given new sibling.
         */
        moveAfter: function (new_sibling) {
            return this.moveToSibling(new_sibling, false);
        },

        EOF:null
    });

    /**
     * Basic folder item.
     */
    $this.Folder = $this.Item.extend({

        field_names: [ 'title', 'description', 'items' ],

        init: function (data, model) {
            this.items = [];
            this._super(data, model);
        },

        get_type: function () { 
            return 'folder';
        },

        isFolder: function () { 
            return true; 
        },

        allowsSubFolders: function () {
            return false;
        },

        isFull: function () {
            return this.get('items').length >= 10;
        },

        set_items: function (items) {
            this.items = [];
            for (var i=0, item; item = items[i]; i++) {
                var obj = item instanceof $this.Item ?
                    item : this.model.add(item);
                this.add(obj);
            }
            return this.items;
        },

        remove: function (item) {
            var items = this.get('items');
            var idx = items.indexOf(item);
            item.parent = null;
            return items.splice(idx, 1);
        },

        add: function (item, idx) {
            if (this.isFull()) { return false; }

            var obj = item instanceof $this.Item ?
                item : this.model.add(item);
            obj.parent = this;
            var items = this.get('items');
            if (0 === idx) {
                items.unshift(obj);
            } else if (idx > 0) {
                items.splice(idx, 0, obj);
            } else {
                items.push(obj);
            }
            return obj;
        },

        replace: function (old_item, new_item) {
            var old_idx = this.indexOf(old_item);
            if (-1 !== old_idx) {
                var items = this.get('items');
                new_item.parent = this;
                items[old_idx] = new_item;
                this.model.remove(old_item);
            }
        },

        indexOf: function (item) {
            return this.get('items').indexOf(item);
        },

        extract: function () {
            var out = this._super(), 
                items_out = [],
                items = this.get('items');
            for (var i=0, item; item = items[i]; i++) {
                items_out.push(item.extract());
            }
            out.items = items_out;
            return out;
        },
        
        EOF:null
    });

    /**
     * Basic bookmark item.
     */
    $this.Bookmark = $this.Item.extend({
        get_type: function () { return 'bookmark'; },
        field_names: [ 'title', 'link', 'description' ],
        validate_locale: function (locale) {
            var errors = this._super(locale);
            if (!this.get('link', locale, true)) { 
                errors.link = 'required'; 
            }
            if (!this.validateURL(this.get('link', locale, true))) { 
                errors.link = 'invalid'; 
            }
            return errors;
        }
    });
    
    /**
     * Basic livemark item.
     */
    $this.Livemark = $this.Bookmark.extend({
        get_type: function () { return 'livemark'; },
        field_names: [ 'title', 'feedLink', 'siteLink' ],
        validate_locale: function (locale) {
            var errors = this._super(locale);
            delete errors.link;
            if (!this.get('feedLink', locale, true)) { 
                errors.feedLink = 'required'; 
            }
            if (!this.validateURL(this.get('feedLink', locale, true))) { 
                errors.feedLink = 'invalid'; 
            }
            if (!this.get('siteLink')) { 
                errors.siteLink = 'required'; 
            }
            if (!this.validateURL(this.get('siteLink', locale, true))) { 
                errors.siteLink = 'invalid'; 
            }
            return errors;
        }
    });

    /**
     * Folder type that can contain independent item sets per locale.
     * (Mainly for the toolbar and menu root folders)
     */
    $this.LocalizableFolder = $this.Folder.extend({
        get_items: function (locale) { 
            var m = this.model,
                key = ( m.selected_locale == m.default_locale ) ?
                    'items' : 'items.' + this.model.selected_locale;
            if (!this[key]) { this[key] = []; }
            return this[key];
        },
        set: function (k, v, locale) {
            if (k.indexOf('items.') === 0) {
                var locale = k.substr('items.'.length),
                    items = v;
                this[k] = [];
                for (var i=0, item; item = items[i]; i++) {
                    var obj = item instanceof $this.Item ?
                        item : this.model.add(item);
                    this[k].push(obj);
                    obj.parent = this;
                }
                return this[k];
            } else {
                return this._super(k, v, locale);
            }
        },
        extract: function () {
            var self = this, 
                out = self._super();
            $.each(self.model.locales, function (idx, locale) {
                var key = ( locale == self.model.default_locale ) ?
                        'items' : 'items.' + locale,
                    items_out = [],
                    items_in = self[key];
                if (!items_in) { return; }
                for (var i=0,item; item = items_in[i]; i++) {
                    items_out.push(item.extract());
                }
                out[key] = items_out;
            });
            return out;
        },
    });

    /**
     * Toolbar bookmarks folder
     */
    $this.ToolbarFolder = $this.LocalizableFolder.extend({
        get_type: function () { return 'toolbar'; },
        allowsSubFolders: function () {
            return true;
        },
        isFull: function () {
            return this.get('items').length >= 3;
        },
        EOF:null
    });
    
    /**
     * Menu bookmarks folder
     */
    $this.MenuFolder = $this.LocalizableFolder.extend({
        get_type: function () { return 'menu'; },
        allowsSubFolders: function () {
            return true;
        },
        isFull: function () {
            return this.get('items').length >= 5;
        },
        EOF:null
    });

    // see: http://blog.stevenlevithan.com/archives/parseuri
    // parseUri 1.2.2
    // (c) Steven Levithan <stevenlevithan.com>
    // MIT License

    $this.parseUri = function parseUri (str) {
        var	o   = $this.parseUri.options,
            m   = o.parser[o.strictMode ? "strict" : "loose"].exec(str),
            uri = {},
            i   = 14;

        while (i--) { uri[o.key[i]] = m[i] || ""; }

        uri[o.q.name] = {};
        uri[o.key[12]].replace(o.q.parser, function ($0, $1, $2) {
            if ($1) { uri[o.q.name][$1] = $2; }
        });

        return uri;
    };

    $this.parseUri.options = {
        strictMode: false,
        key: ["source","protocol","authority","userInfo","user","password","host","port","relative","path","directory","file","query","anchor"],
        q:   {
            name:   "queryKey",
            parser: /(?:^|&)([^&=]*)=?([^&]*)/g
        },
        parser: {
            strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
            loose:  /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
        }
    };


    return $this.init();
}());
