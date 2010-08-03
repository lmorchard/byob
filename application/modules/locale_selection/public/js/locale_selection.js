/**
 * JS enhancements for locale selection
 */
BYOB_Repacks_Edit_LocaleSelection = (function () {

    var $this = {

        MAX_LOCALES: 10,

        labels: [],
        codes_by_label: {},

        /**
         * Initialization
         */
        init: function () {
            $(document).ready($this.onready);
            return $this;
        },

        /**
         * Load up the locale names and codes for autocompletion.
         */
        loadLocales: function (labels, codes_by_label) {
            $this.labels = labels;
            $this.codes_by_label = codes_by_label;
        },

        /**
         * Document ready handler.
         */
        onready: function () {

            // Wire up checkboxes for popular locale selections.
            $('.popular-locales')
                .bind('click', function (ev) {
                    var target = $(ev.target);
                    if ('checkbox' == target.attr('type')) {
                        var locale  = target.val(),
                            label   = target.parent().find('.label').text(),
                            checked = target.attr('checked'),
                            func    = checked ? 'select' : 'deselect',
                            success = $this[func+'_locale'](locale, label);
                        if (!success) target.attr('checked', !checked);
                    }
                });

            // Wire up hover and removal link for selected locales.
            $('.locale-selections')
                .bind('mouseover', function (ev) {
                    var target = $(ev.target);
                    if (target.hasClass('selected-locale')) {
                        $('.selected-locale').removeClass('hover');
                        target.addClass('hover');
                    }
                })
                .bind('click', function (ev) {
                    var target = $(ev.target);
                    if (target.hasClass('remove')) {
                        var locale = target.parent().find('input').val();
                            success = $this.deselect_locale(locale);
                    }
                });

            // Wire up locale search for autocomplete and enter key.
            $('input#locale_search')
                .autocomplete({
                    source: $this.labels
                })
                .bind('keypress', function (ev) {
                    if (13 == ev.keyCode) {
                        $this.commitLocaleSearch();
                        return false;
                    }
                });

            $('.locale-search-field .add')
                .click($this.commitLocaleSearch);

            // If the maximum number of locales has been selected, disable choices.
            var selected = 
                $('.locale-selections li:not(.template) input[type=hidden]');
            if (selected.length >= $this.MAX_LOCALES)
                $('.choices input').attr('disabled', true);

        },

        /**
         * Use the current contents of locale search to add a locale.
         */
        commitLocaleSearch: function () {
            var target = $('input#locale_search'),
                label = target.val(),
                locale = $this.codes_by_label[label];
            if (locale) {
                $this.select_locale(locale, label);
            }
            target.val('');
            return false;
        },

        /**
         * Select a locale by code and label.
         */
        select_locale: function (locale, label) {
            // Ensure no more than max locales are selected.
            var selected = 
                $('.locale-selections li:not(.template) input[type=hidden]');
            if (selected.length >= $this.MAX_LOCALES) return false;

            // Do not add a new item for a locale already present.
            var existing = $('.locale-selections input[value='+locale+']');
            if (existing.length > 0) return false;

            $('.popular-locales input[value='+locale+']')
                .attr('checked', true);

            // Add a selected locale by cloning the template.
            $('.locale-selections .template').cloneTemplate({
                '.name': label, 
                'input @value': locale 
            }).appendTo('.locale-selections');

            if (selected.length + 1 >= $this.MAX_LOCALES) {
                $('.choices input').attr('disabled', true);
            }

            return true;
        },

        /**
         * Remove a selected locale, if present.
         */
        deselect_locale: function (locale) {
            $('.selected-locale input[value='+locale+']')
                .parent().remove();
            $('.popular-locales input[value='+locale+']')
                .attr('checked', false);
            $('.choices input').attr('disabled', false);
            return true;
        },

        EOF: null
    };

    return $this.init();

})();
