/**
 * jQuery cloneTemplate plugin, v0.0
 * lorchard@mozilla.com
 *
 * Clone template elements and populate them from a data object.
 * 
 * The data object keys of which are assumed to be CSS selectors.  Each
 * selector may end with an @-prefixed name to identify an attribute.
 *
 * An element or attribute matched by the selector will have its
 * content replaced by the value of the data object for the selector.
 */
jQuery.fn.extend( {

    cloneTemplate: function (data) {
        var clones = [];

        this.each(function (i) {
            
            // Create the clone, squirrel it away.
            var tmpl = $(this).clone().removeClass('template');
            clones.push(tmpl[0]);

            // Iterate through all the data keys...
            for (key in data) { if (data.hasOwnProperty(key)) {
                
                var value     = data[key], 
                    at_pos    = -1,
                    attr_name = false;

                // Skip populating values that are false or undefined
                if (false === value || 'undefined' == typeof value) { 
                    continue; 
                }

                // If the key ends with an @attr name, strip it.
                if (-1 !== (at_pos = key.indexOf('@'))) {
                    attr_name = key.substring(at_pos + 1);
                    key = key.substring(0, at_pos);
                }

                // Attempt to find the placeholder by selector
                var el = (key) ? tmpl.find(key) : tmpl;
                if (!el.length) { continue; }

                if (attr_name) {
                    // Set the attribute, if we had an attribute name.
                    el.attr(attr_name, value);
                } else {
                    if ('string' === typeof value) {
                        // Strings work as HTML replacements.
                        el.html(value);
                    } else if ('undefined' != typeof value.nodeType) {
                        // Elements become content replacements.
                        el.empty().append(value);
                    }
                }

            }}

        });

        return this.pushStack(clones, 'cloneTemplate', this.selector); 
    }

});
