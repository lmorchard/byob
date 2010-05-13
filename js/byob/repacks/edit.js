/**
 * JS enhancements for repack editor
 */
BYOB_Repacks_Edit = (function () {

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

            $('.sub-tab-set .sub-tabs').bind('click', function (ev) {
                var target = $(ev.target);
                if ('a' == target.attr('tagName').toLowerCase()) {
                    $('.sub-tabs li').removeClass('selected');
                    target.blur().parent().addClass('selected');
                    $('.sub-tab-content').removeClass('selected');
                    $(target.attr('href'))
                        .addClass('selected')
                        .find('iframe')
                        .each(function () {
                            if (this.contentWindow.adjustHeight) {
                                this.contentWindow.adjustHeight();
                            }
                        })
                        .end();

                    return false;
                }
            });

        },

        EOF: null
    };

    return $this.init();

})();
