/**
 * JS enhancements for addon management
 */
BYOB_Repacks_Edit_AddonManagement = (function () {

    var $this = {

        /**
         * Initialization
         */
        init: function () {
            $(document).ready($this.onready);
            return $this;
        },

        /**
         * Document ready handler.
         */
        onready: function () {

            // When the "No Persona" choice is selected, clear the persona URL
            // field for good measure.
            $('#persona_id_none').click(function () {
                $('.persona_url').val('');
            });

            // Make file upload fields styleable through magic and trickery.
            $('.pretty_upload').each(function () {

                var root_el = $(this),
                    input_el = root_el.find('input[type=file]');

                // Cram some styleable markup into the page as a fake file
                // upload widget.
                input_el.after($(
                    '<span class="fake_upload">' +
                        '<input class="text" type="text" />' +
                        '<button class="browse button grey">Browse</button>' +
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

        EOF: null
    };

    return $this.init();

})();
