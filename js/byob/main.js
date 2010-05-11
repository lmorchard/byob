/**
 * BYOB main JS enhancements
 */
BYOB_Main = function() {
    var $this = {

        /**
         * Initialize the page.
         */
        init: function() {

            $('input[title],textarea[title]').inputHint();

            /*
            $('.errors').effect('highlight', {color: '#ff9999'}, 4000, function() {
            });
            */

            // Highlight the notification, then remove after a bit.
            $('.message').effect('highlight', {}, 3000, function() {
                //var n = $(this);
                //setTimeout(function() { n.hide('highlight') }, 2000);
            });

            // Kill all the templates with invisible form fields.
            $('form').submit(function() {
                $(this).find('.template').remove();    
            });

            $('.ctrl_repacks_act_edit').each(function() {
                // Wire up the UI for the repack editing page.
                $('.save_buttons').hide();
                $this.wireUpSaveAndConfirm();
                $this.wireUpRepackLocales();
                $this.wireUpWizardTabs();
                $this.wireUpAddonDependent();
            });

            $('form .account').each(function() {
                $this.wireUpHideShowOrg($(this));
            });

            $('.auth .login').add('.login_inline').click(function(ev) {
                var width  = 528,
                    height = 281,
                    href   = $('.auth .login').attr('href') + 
                        '?popup&jump=%2Flogin%3Fpopup%26gohome';
                $.modal(
                    '<iframe style="border:0" scrolling="no" src="'+href+'"' +
                        ' height="'+height+'" width="'+width+'">', 
                    {
                        overlayClose:true,
                        containerCss: { height: height, width: width }
                    }
                );
                ev.preventDefault();
            });

            $('#ctrl_auth_profiles_act_login #login_name').focus();

            return this;
        },

        /**
         * Wire up the save and review button to work with a modal dialog.
         *
         * This is done by submitting the form to the server first, giving
         * the app a chance to validate and save the current form data.
         *
         * If the data is not valid, the review dialog is not triggered and
         * errors are displayed normally.
         */
        wireUpSaveAndConfirm: function() {
            
            // Set the show_review hidden field on the form.
            $('#save-and-review').each(function(ev) {
                $(this).click(function(ev) {
                    $('#show_review').val('true');
                    $('#wizard').submit();
                    ev.preventDefault();
                    return false;
                });
            });

            // Present the modal dialog if the page has the trigger CSS class
            // included by the server-side.  
            $(document).ready(function() {
                $('.show_review').each(function() {
                    var width  = 580,
                        height = 550,
                        href   = $('#save-and-review').attr('href');
                    $.modal(
                        '<iframe style="border:0" scrolling="no" src="'+href+'"' +
                            ' height="'+height+'" width="'+width+'">', 
                        { overlayClose:true }
                    );
                });
            });

        },

        /**
         * Wire up the type (other) input field to appear when "other" is
         * selected in the associated drop down.
         */
        wireUpHideShowOrg: function(org_fieldset) {

            // Enable / disable organization fields on personal checkbox toggle.
            var org_personal = org_fieldset.find('input[name=is_personal]');
            var personal_cb = function(ev) {
                var checked = ( $('input[name=is_personal]:checked').val() == 1);
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
        * Wire up the submission tabs for the wizard
        */
       wireUpWizardTabs: function() {
            $('input').add('textarea')
                .change(function() {
                    $('#changed').val('true');
                    return true;
                });

            $('.tab-tabs a').add('.section_nav a').click(function() {
                // Wire up nav tabs to submit the form after setting 
                // a hidden field with the name of the next section.
                var href = $(this).attr('href');
                var section = href.substr(href.indexOf('=')+1);
                $('#next_section').val(section);
                $('#wizard').submit();
                return false;
            });
        },

        /**
         * Wire up hide/show for div dependent on the selection of an addon.
         */
        wireUpAddonDependent: function() {
            var addons = $('.addon input[type=checkbox]');
            $('.addon-dependent').each(function() {
                var dep = $(this);
                addons.each(function() {
                    var check = $(this);
                    if (dep.hasClass('addon-'+check.val())) {
                        var cb = function(ev) {
                            var checked = ( check.attr('checked') );
                            if (checked) dep.show();
                            else dep.hide();
                        };
                        check.each(cb).change(cb);
                    }
                });
            });
        },

        EOF:null
    };
    return $this;
}().init();

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

$(document).ready(function() { 
    $('.listbuilder').listbuilder();
});

