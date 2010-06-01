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
                $this.wireUpSaveButtons();
                $this.wireUpWizardTabs();
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

            $('.popup .popup_cancel').click(function (ev) {
                top.jQuery('#simplemodal-overlay').click();
            });

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
        wireUpSaveButtons: function() {
            
            // Set the show_review hidden field on the form.
            $('#save-and-review').each(function(ev) {
                $(this).click(function(ev) {
                    $('#show_review').val('true');
                    $('#wizard').submit();
                    ev.preventDefault();
                    return false;
                });
            });

            // Set the show_review hidden field on the form.
            $('#save-and-close').each(function(ev) {
                $(this).click(function(ev) {
                    $('#done').val('true');
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

        EOF:null
    };
    return $this;
}().init();
