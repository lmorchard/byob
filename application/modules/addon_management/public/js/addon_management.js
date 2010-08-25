/**
 * JS enhancements for addon management
 */
BYOB_Repacks_Edit_AddonManagement = (function () {

    var _ = BYOB_Main.gettext;

    var $this = {

        // Indexed map of selections in sidebar to choices in UI.
        _selections_map: [],

        max_extensions: 2,
        max_search_plugins: 3,
        locales: [ 'en-US' ],
        default_locale: 'en-US',
        current_locale: 'en-US',

        /**
         * Initialization
         */
        init: function () {
            $(document).ready($this.onready);
            $(document).unload($this.onunload);
            return $this;
        },

        /**
         * Do some cleanup on page unload.
         */
        onunload: function () {
            $this._selections_map = null;
        },

        /**
         * Document ready handler.
         */
        onready: function () {

            $this.wireUpLocaleSelectors();
            $this.wireUpUploads();
            $this.wireUpSelectionsPane();
            $this.updateSelectionsPane(true);
            $this.setupPrettyUploads();

            // When the "No Persona" choice is selected, clear the persona URL
            // field for good measure.
            $('.personas li input').click(function () {
                $('.persona_url').val('');
                $this.updateSelectionsPane();
            });

            $('#tab-personas .persona_url')
                .bind('keypress', function (ev) {
                    if (13 == ev.keyCode) {
                        $this.updateSelectionsPane();
                        $('.personas li input').attr('checked', false);
                        $(this).blur();
                        return false;
                    }
                });
        },

        /**
         * Wire up clicks in locale selectors to switch locales.
         */
        wireUpLocaleSelectors: function () {
            $('#tab-searchengines').each(function () {
                var tab = $(this);
                tab.find('.locale-selector').each(function () {
                    var selector = $(this);
                    selector.click(function (ev) {
                        var el = $(ev.target);
                        if ('A' != el.attr('tagName')) { return; }
                        $this.switchSearchEnginesLocale(el.attr('data-locale'));
                    });
                });
            });
        },

        /**
         * Switch the search engines locale, including the upload iframe.
         */
        switchSearchEnginesLocale: function (locale) {
            if (locale) { $this.current_locale = locale; }
            else { locale = $this.current_locale; }

            $('#tab-searchengines .locale-selector')
                .find('li')
                    .removeClass('selected').end()
                .find('li a[data-locale='+locale+']').parent()
                    .addClass('selected').end();

            $('#tab-searchengines .available-options li').hide();
            $('#tab-searchengines .available-options li.locale-'+locale).show();

            $('#tab-searchplugins-upload').each(function () {
                if (!this.contentWindow.$) { return; }
                var $F = this.contentWindow.$;
                $F('form input[name=locale]').val(locale);
                $F('.uploads li.by-locale').hide();
                $F('.uploads li.locale-'+locale).show();
                this.contentWindow.adjustHeight();
            });
        },

        /**
         * Set up the list of uploads in iframes for hover and removal.
         */
        wireUpUploads: function () {
            $('.upload_form ul.uploads');
        },

        /**
         * Make the file upload fields pretty.
         */
        setupPrettyUploads: function () {

            // Make file upload fields styleable through magic and trickery.
            $('.pretty_upload').each(function () {

                var root_el = $(this),
                    input_el = root_el.find('input[type=file]');

                // Cram some styleable markup into the page as a fake file
                // upload widget.
                input_el.after($(
                    '<span class="fake_upload">' +
                        '<input class="text" type="text" />' +
                        '<button class="browse button grey">' +
                        _('Browse') +
                        '</button>' +
                    '</span>'
                ));

                var text_el = root_el.find('.fake_upload input[type=text]')
                    browse_el = root_el.find('.fake_upload .browse');

                // Hide the file upload element, but make the fake text field
                // track any value changes in the file upload.
                input_el
                    .addClass('hidden')
                    .change(function (ev) {
                        text_el.val(input_el.val());
                    });

                // Whenever the fake button is clicked, proxy to the real file
                // upload field.
                browse_el
                    .click(function (ev) {
                        input_el.click();
                        return false;
                    });

            });

        },

        /**
         * Wire up the interaction handlers for the selections pane.
         */
        wireUpSelectionsPane: function () {

            // Any click on an input element under .choices (eg. checkboxen,
            // radio buttons) should trigger a sidebar update.
            $('.choices input')
                .click($this.updateSelectionsPane);

            $('.selections')
                // Delegated click on the remove link should trigger removal.
                .bind('click', function (ev) {
                    var target = $(ev.target);
                    if (target.parent().hasClass('remove_link'))
                        target = target.parent();
                    if (target.hasClass('remove_link')) {
                        var item = target.parent();
                        return $this.removeSelection(item);
                    }
                });

        },

        /**
         * Remove the given selection as appropriate to its particular type.
         */
        removeSelection: function (item) {
            var idx = item.attr('data-selection-index'),
                choice = $this._selections_map[idx];

            if (item.hasClass('theme')) {

                // Click the no-theme selection.
                $('#theme_id_none').click();

            } else if (item.hasClass('persona_url')) {

                $('#tab-personas .persona_url').val('');

            } else if (item.hasClass('persona')) {

                // Click the no-persona selection.
                $('#persona_id_none').click();

            } else if (item.hasClass('extensionUpload')) {

                // HACK: Temporarily switch to extensions tab, submit form 
                // to delete the extension, switch back to the original tab.
                // Leaving the iframe hidden seems to prevent form submission.
                var old = $('ul.sub-tabs li.selected a');
                $('ul.sub-tabs li a[href=#tab-extensions]').click();
                choice.find('form.delete button').click();
                old.click();

            } else if (item.hasClass('searchpluginUpload')) {

                // HACK: Temporarily switch to search engines tab, submit form
                // to delete the extension, switch back to the original tab.
                // Leaving the iframe hidden seems to prevent form submission.
                var old = $('ul.sub-tabs li.selected a');
                $('ul.sub-tabs li a[href=#tab-searchengines]').click();
                choice.find('form.delete button').click();
                old.click();

            } else {

                // All other add-on types can be de-selected by un-checking 
                // the appropriate element.
                choice.find('input:checkbox').attr('checked', false);

            }

            $this.updateSelectionsPane();
            return false;
        },

        /**
         * Update the contents of the selection pane from the choices made in
         * the UI.
         */
        updateSelectionsPane: function (no_validate) {
            var list = $('.selections .addon-selections'),
                tmpl = list.find('.template');

            var extension_uploads = 
                    $('#tab-extensions-upload').contents().find('.uploads li'),
                extensions_checked =
                    $('.extensions li input:checked'),
                extension_count =
                    extension_uploads.length + extensions_checked.length;

            if ( (true!==no_validate) && (false!==$this.max_extensions) &&
                    extension_count > $this.max_extensions ) {
                return false;
            }

            var se_has_problem = false;
            $.each($this.locales, function (idx, locale) {
                var searchplugin_uploads = $('#tab-searchplugins-upload').contents()
                        .find('.uploads li.locale-'+locale),
                    searchplugins_checked =
                        $('.searchplugins li.locale-'+locale+' input:checked'),
                    searchplugin_count =
                        /*searchplugin_uploads.length +*/ searchplugins_checked.length;
                if ( true!==no_validate && (false !== $this.max_search_plugins) &&
                        searchplugin_count > $this.max_search_plugins ) {
                    se_has_problem = true;
                    setTimeout(function () {
                        console.log(searchplugin_uploads);
                        console.log(searchplugins_checked);
                    }, 1000);
                }
            });
            if (se_has_problem) return false;

            $this._selections_map = [];
            list.find('li:not(.template)').remove();

            extension_uploads
                .each(function () {
                    var item = $(this);
                    $this._selections_map.push(item);
                    tmpl.cloneTemplate({
                        '@class': 'extensionUpload',
                        '@data-selection-index': $this._selections_map.length - 1,
                        '.name': item.find('.name').text()
                    }).appendTo(list);
                });

            extensions_checked
                .each(function () {
                    var item = $(this).parent();
                    $this._selections_map.push(item);
                    tmpl.cloneTemplate({
                        '@class': 'extension',
                        '@data-selection-index': $this._selections_map.length - 1,
                        '.name': item.find('.name').text()
                    }).appendTo(list);
                });

            $('#tab-searchplugins-upload').contents()
                .find('.uploads li')
                .each(function () {
                    var item = $(this);
                    $this._selections_map.push(item);
                    tmpl.cloneTemplate({
                        '@class': 'searchpluginUpload',
                        '@data-selection-index': $this._selections_map.length - 1,
                        '.name': item.find('.name').text()
                    }).appendTo(list);
                });

            $('.searchplugins li input:checked')
                .each(function () {
                    var item = $(this).parent();
                    $this._selections_map.push(item);
                    tmpl.cloneTemplate({
                        '@class': 'searchplugin',
                        '@data-selection-index': $this._selections_map.length - 1,
                        '.name': item.find('.name').text()
                    }).appendTo(list);
                });

            var persona_url_defined = false;
            $('#tab-personas .persona_url')
                .each(function () {
                    var item = $(this),
                        url = item.val();
                    if (url) {
                        persona_url_defined = true;
                        $this._selections_map.push(item);
                        tmpl.cloneTemplate({
                            '@class': 'persona',
                            '@data-selection-index': $this._selections_map.length - 1,
                            '.name': "Custom Persona URL"
                        }).appendTo(list);
                    }
                });

            if (!persona_url_defined) {
                $('.personas li input:checked')
                    .each(function () {
                        var item = $(this).parent();
                        if (item.hasClass('none')) return;
                        $this._selections_map.push(item);
                        tmpl.cloneTemplate({
                            '@class': 'persona',
                            '@data-selection-index': $this._selections_map.length - 1,
                            '.name': item.find('.name').text()
                        }).appendTo(list);
                    });
            }

            $('.themes li input:checked')
                .each(function () {
                    var item = $(this).parent();
                    if (item.hasClass('none')) return;
                    $this._selections_map.push(item);
                    tmpl.cloneTemplate({
                        '@class': 'theme',
                        '@data-selection-index': $this._selections_map.length - 1,
                        '.name': item.find('.name').text()
                    }).appendTo(list);
                });

            // HACK: Sweep through the added list items, look for class name
            // transitions and tag items with divider class
            var last_cls = '';
            list.find('li:not(.template)').each(function () {
                var item = $(this),
                    curr_cls = item.attr('class');
                if ((last_cls != '') && (curr_cls != last_cls)) {
                    item.addClass('divider');
                }
                last_cls = curr_cls;
            });

            return true;
        },

        EOF: null
    };

    return $this.init();

})();
