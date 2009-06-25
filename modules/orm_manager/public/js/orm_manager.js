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

            $this.wireUpToggleAll();

            return this;
        },

        /**
         * Wire up a link to toggle all checkboxes.
         */
        wireUpToggleAll: function() {

            // Add a select-all link to each table
            $('.list_model').each(function() {
                var list_model = $(this);
                var th = list_model.find('th:first span');
                th.append('<a class="select-all" href="#">all</a>');
                th.find('a.select-all')
                    .click(function() {
                        list_model.find('input[type=checkbox]').each(function() {
                            var box = $(this);
                            box.attr('checked', !box.attr('checked'));
                        });
                        return false;
                    });
            });

        },

    };
    return $this;
}().init();
