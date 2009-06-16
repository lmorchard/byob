/**
 * JS enhancements to ORM Manager pages
 */
if ('undefined' == typeof(window.ORM_Manager)) window.ORM_Manager = {};
ORM_Manager = function() {
    var $this = {

        /**
         * Initialize the page.
         */
        init: function() {

            $('.list_model').each(function() {
                $this.wireUpToggleAll();
            });

            return this;
        },

        /**
         * Wire up a link to toggle all checkboxes.
         */
        wireUpToggleAll: function() {

            // Add a select-all link
            $('.list_model th:first').append('<a href="#">all</a>');

            // Wire up select-all to toggle all checkboxes
            $('.list_model th:first a')
                .click(function() {
                    $('.list_model input[type=checkbox]').each(function() {
                        var box = $(this);
                        box.attr('checked', !box.attr('checked'));
                    });
                    return false;
                })

        },

    };
    return $this;
}().init();
