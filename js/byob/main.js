/**
 * JS enhancements to ORM Manager pages
 */
if ('undefined' == typeof(window.BYOB_Main)) window.BYOB_Main = {};
BYOB_Main = function() {
    var $this = {

        /**
         * Initialize the page.
         */
        init: function() {

            // Highlight the notification, then remove after a bit.
            $('.notification').effect('highlight', {}, 1000, function() {
                //var n = $(this);
                //setTimeout(function() { n.hide('highlight') }, 2000);
            });

            // Kill all the templates with invisible form fields.
            $('form').submit(function() {
                $(this).find('.template').remove();    
            });

            $('.ctrl_repacks_act_edit').each(function() {
                // Wire up the UI for the repack editing page.
                $this.wireUpRepackAccordion();
                $this.wireUpRepackBookmarks();
                $this.wireUpRepackLocales();
            });

            $('.register .organization').each(function() {
                $this.wireUpHideShowOrgTypeOther($(this));
            });

            return this;
        },

        /**
         * Wire up the type (other) input field to appear when "other" is
         * selected in the associated drop down.
         */
        wireUpHideShowOrgTypeOther: function(org_fieldset) {

            var org_type_other = org_fieldset.find('#org_type_other');
            org_type_other.parent().hide();

            org_fieldset.find('#org_type').change(function() {
                var org_type = $(this);
                if ('other' == org_type.val()) {
                    // Show the 'other' field.
                    org_type_other.parent().show();
                } else {
                    // Clear out and hide the 'other' field.
                    org_type_other.val('').parent().hide();
                }
            });

        },

        /**
         * Wire up the accordion UI for the repack editing form, with
         * cookie-based memory of last opened section.
         */
        wireUpRepackAccordion: function() {

            var cookie_name = 'ctrl_repacks_act_edit_accordion_idx';

            // Set up the accordion for the editing form
            $('.accordion').accordion({
                autoHeight: false,
                change: function(event, ui) {
                    // Remember the accordion title on change.
                    $this.last_accordion_header = ui.newHeader.text();
                }
            });

            // Retain active accordion pane cookie when save clicked.
            $('#save').click(function() {
                $.cookies.set(cookie_name, $this.last_accordion_header);
                return true;
            });

            // Discard active accordion pane cookie when done clicked.
            $('#done').click(function() {
                $.cookies.set(cookie_name, '');
                return true;
            });

            // If this is a creation form, delete the cookie.
            $('#save[value=create]').each(function() {
                $.cookies.set(cookie_name, '');
            });

            // If a previous title is set in a cookie, activate it.
            var prev_title = $.cookies.get(cookie_name);
            if (prev_title) {
                $('.accordion').accordion(
                    'activate', $('h3:contains('+prev_title+')')
                )
            }

        },
       
        /**
         * Wire up the locales selection UI.
         */
        wireUpRepackLocales: function() {

            // Wire up the add link to insert new cloned locale.
            $('.locales-add .add').click(function(ev) {
                var choices = $('.locales-add select[name=locale_choices]');
                var locale  = choices.val();
                var name    = choices.find('option[value='+locale+']').text()

                $('.locales .template')
                    .clone().removeClass('template')
                    .find('input').val(locale).end()
                    .find('span').text(name).end()
                    .appendTo('.locales');

                return false;
            });

            // Event delegation for delete links on locales
            $('.locales').click(function(ev) {
                var t = $(ev.target);
                if (t.hasClass('delete')) {
                    t.parent().remove();
                    return false;
                }
            });

        },

        /**
         * Wire up bookmark management UI.
         */
        wireUpRepackBookmarks: function() {

            // Set up drag and drop sorting for bookmarks
            $('.bookmarks').sortable();

            // Wire up add links to insert cloned new bookmarks
            $('.bookmark-add .add').click(function() {
                var new_bm = $(this).next().find('li:first')
                    .clone().removeClass('template')
                    .appendTo($(this).parent().parent().prev())
                return false;
            });

            // Event delegation for delete links on bookmarks
            $('.bookmarks').click(function(ev) {
                var t = $(ev.target);
                if (t.hasClass('delete')) {
                    t.parent().remove();
                    return false;
                }
            });

        },

        EOF:null
    };
    return $this;
}().init();
