/**
 * BYOB main JS enhancements
 */
if ('undefined' == typeof(window.BYOB_Main)) window.BYOB_Main = {};

(function($) {
    $.fn.listbuilder = function(options) {
        var root = $(this);

        root.find('.add').click(function(ev) {
            var choices = root.find('select.choices');
            var choice  = choices.val();
            var name    = choices.find('option[value='+choice+']').text()

            root.find('.template')
                .clone().removeClass('template')
                .find('input').val(choice).end()
                .find('span').text(name).end()
                .appendTo(root.find('.list'));

            $('#changed').val('true');

            return false;
        });

        // Event delegation for delete links
        root.find('.list').click(function(ev) {
            var t = $(ev.target);
            if (t.hasClass('delete')) {
                $('#changed').val('true');
                t.parent().remove();
                return false;
            }
        });

    }
})(jQuery);

BYOB_Main = function() {
    var $this = {

        /**
         * Initialize the page.
         */
        init: function() {

            $('.errors').effect('highlight', {color: '#ff9999'}, 4000, function() {
            });

            // Highlight the notification, then remove after a bit.
            $('.message').effect('highlight', {}, 3000, function() {
                //var n = $(this);
                //setTimeout(function() { n.hide('highlight') }, 2000);
            });

            $('.listbuilder').listbuilder();

            // Kill all the templates with invisible form fields.
            $('form').submit(function() {
                $(this).find('.template').remove();    
            });

            $('.ctrl_repacks_act_edit').each(function() {
                // Wire up the UI for the repack editing page.
                $this.wireUpRepackBookmarks();
                $this.wireUpRepackLocales();

                $('.save_buttons').hide();

                $('input').add('textarea')
                    .change(function() {
                        $('#changed').val('true');
                        return true;
                    });

                $('.tabs a').add('.section_nav a').click(function() {
                    // Wire up nav tabs to submit the form after setting 
                    // a hidden field with the name of the next section.
                    var href = $(this).attr('href');
                    var section = href.substr(href.indexOf('=')+1);
                    $('#next_section').val(section);
                    $('#wizard').submit();
                    return false;
                });

            });

            $('form .account').each(function() {
                $this.wireUpHideShowOrg($(this));
            });

            return this;
        },

        /**
         * Wire up the type (other) input field to appear when "other" is
         * selected in the associated drop down.
         */
        wireUpHideShowOrg: function(org_fieldset) {

            // Enable / disable organization fields on personal checkbox toggle.
            var org_personal = org_fieldset.find('#is_personal');
            var personal_cb = function(ev) {
                var checked = ( $('#is_personal:checked').length > 0 );
                $.each([ 'org_name', 'org_type', 'org_type_other' ], function() {
                    if (checked) {
                        $('#'+this).attr('disabled', true)
                            .parent().removeClass('required');
                    } else {
                        $('#'+this).removeAttr('disabled');
                        $('#'+this).parent().addClass('required');
                    }
                });
            };
            org_personal.each(personal_cb).change(personal_cb);

            var org_type_other = org_fieldset.find('#org_type_other');

            var cb = function() {
                var org_type = $(this);
                if ('other' == org_type.val()) {
                    // Show the 'other' field.
                    org_type_other.parent().show();
                } else {
                    // Clear out and hide the 'other' field.
                    org_type_other.val('').parent().hide();
                }
            }

            // Both set the initial visibility of the other field, and set up
            // to update on changes to the dropdown.
            org_fieldset.find('#org_type').each(cb).change(cb);

        },
       
        /**
         * Wire up the locales selection UI.
         */
        wireUpRepackLocales: function() {

            // Wire up the add link to insert new cloned locale.
            $('.locales-add .add').click(function(ev) {
                var choices = $('.locales-add select[name=locale_choices]');
                var locale  = choices.val();

                if ( $('.locales input[value='+locale+']').length > 0) {
                    // Ignore attempts to add multiples of a locale.
                    return false;
                }

                var name = choices.find('option[value='+locale+']').text()

                $('.locales .template')
                    .clone().removeClass('template')
                    .find('input').val(locale).end()
                    .find('span').text(name).end()
                    .appendTo('.locales');
                $('#changed').val('true');

                return false;
            });

            // Event delegation for delete links on locales
            $('.locales').click(function(ev) {
                var t = $(ev.target);
                if (t.hasClass('delete')) {
                    t.parent().remove();
                    $('#changed').val('true');
                    return false;
                }
            });

        },

        /**
         * Wire up bookmark management UI.
         */
        wireUpRepackBookmarks: function() {

            // TODO: Put this somewhere more central.
            var item_limits = {
                'bookmarks_menu_item': 5,
                'bookmarks_toolbar_item': 3
            };

            // Set up drag and drop sorting for bookmarks
            $('.bookmarks').sortable();

            // Wire up add links to insert cloned new bookmarks
            $('.bookmark-add .add').click(function() {

                // Clone a new bookmark from template.
                var new_bm = $(this).next().find('li:first')
                    .clone().removeClass('template');

                // Find the destination list relative to this link.
                var dest_list = $(this).parent().parent().prev();

                // Check and enforce bookmark limits.
                var allow_add = true;
                $.each(item_limits, function(name, limit) {
                    if (new_bm.hasClass(name)) {
                        var existing_items = dest_list.find('.'+name);
                        if (existing_items.length >= limit) {
                            allow_add = false;
                        }
                    }
                });

                // Only add the cloned item if allowed...
                if (allow_add) new_bm.appendTo(dest_list);

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
