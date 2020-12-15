/*!
 * jquery.customSelect() - v0.5.1
 * http://adam.co/lab/jquery/customselect/
 * 2014-03-19
 *
 * Copyright 2013 Adam Coulombe
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @license http://www.gnu.org/licenses/gpl.html GPL2 License
 */
(function ($) {
    'use strict';

    $.fn.extend({
        customSelect: function (options) {
            // filter out <= IE6
            if (typeof document.body.style.maxHeight === 'undefined') {
                return this;
            }
            var defaults = {
                    customClass: 'customSelect',
                    mapClass:    true,
                    mapStyle:    true
            },
            options = $.extend(defaults, options),
            prefix = options.customClass,
            changed = function ($select,customSelectSpan) {
                var currentSelected = $select.find(':selected'),
                customSelectIconInner = customSelectSpan.children('i:first'),
                customSelectSpanInner = customSelectSpan.children('span:first'),
                //html = '<i class="' +  currentSelected.attr('class') + '"></i>' + currentSelected.html() || '&nbsp;';
                html = currentSelected.html() || '&nbsp;';

                //customSelectIconInner.attr('class', currentSelected.attr('class'));
                $select.attr('title', html);
                customSelectSpanInner.html(html);

                

            },
            getClass = function(suffix){
                return prefix + suffix;
            };


        }
    });
})(jQuery);

