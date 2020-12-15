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

                customSelectIconInner.attr('class', currentSelected.attr('class'));
                $select.attr('title', html);
                customSelectSpanInner.html(html);

                if (currentSelected.attr('disabled')) {
                    customSelectSpan.addClass(getClass('DisabledOption'));
                } else {
                    customSelectSpan.removeClass(getClass('DisabledOption'));
                }

                setTimeout(function () {
                    customSelectSpan.removeClass(getClass('Open'));
                    $(document).unbind('mouseup.customSelect');
                }, 60);
            },
            getClass = function(suffix){
                return prefix + suffix;
            };

            return this.each(function () {
                var $select = $(this);
                if ($select.hasClass('hasCustomSelect'))
                    return;
                var customSelectInnerSpan = $('<span />').addClass(getClass('Inner')),
                    customIconInnerSpan = $('<i />'),
                    dropdownIcon = $('<i class="fa fa-caret-down"></i>'),
                    customSelectSpan = $('<span />');

                $select.after(customSelectSpan.append(customIconInnerSpan).append(customSelectInnerSpan).append(dropdownIcon));

                customSelectSpan.addClass(prefix);

                if (options.mapClass) {
                    customSelectSpan.addClass($select.attr('class'));
                }
                if (options.mapStyle) {
                    customSelectSpan.attr('style', $select.attr('style'));
                }

                $select
                    .addClass('hasCustomSelect')
                    .bind('render.customSelect', function () {
                        changed($select,customSelectSpan);
                        $select.css('width','');

                         // fix  click issue when #whats-new-options is hidden (since trs 2.4.0) outerWidth = 0
                      

                        var selectBoxWidth = parseInt($select.outerWidth(), 10) -
                                (parseInt(customSelectSpan.outerWidth(), 10) -
                                    parseInt(customSelectSpan.width(), 10));

                        // Set to inline-block before calculating outerHeight
                        customSelectSpan.css({
                            display: 'inline-block'
                        });

                        var selectBoxHeight = customSelectSpan.outerHeight();

                        if ($select.attr('disabled')) {
                            customSelectSpan.addClass(getClass('Disabled'));
                        } else {
                            customSelectSpan.removeClass(getClass('Disabled'));
                        }

                        customSelectInnerSpan.css({

                            display: 'inline-block'
                        });

                        $select.css({
                            '-webkit-appearance': 'menulist-button',
                            width:                customSelectSpan.outerWidth(),
                            position:             'absolute',
                            opacity:              0,
                            height:               selectBoxHeight,
                            fontSize:             customSelectSpan.css('font-size')
                        });

                        // reset #what-news-options display
         

                    })
                    .bind('change.customSelect', function () {
                        customSelectSpan.addClass(getClass('Changed'));
                        changed($select,customSelectSpan);
                    })
                    .bind('keyup.customSelect', function (e) {
                        if(!customSelectSpan.hasClass(getClass('Open'))){
                            $select.trigger('blur.customSelect');
                            $select.trigger('focus.customSelect');
                        }else{
                            if(e.which==13||e.which==27){
                                changed($select,customSelectSpan);
                            }
                        }
                    })
                    .bind('mousedown.customSelect', function () {
                        customSelectSpan.removeClass(getClass('Changed'));
                    })
                    .bind('mouseup.customSelect', function (e) {

                        if( !customSelectSpan.hasClass(getClass('Open'))){
                            // if FF and there are other selects open, just apply focus
                            if($('.'+getClass('Open')).not(customSelectSpan).length>0 && typeof InstallTrigger !== 'undefined'){
                                $select.trigger('focus.customSelect');
                            }else{
                                customSelectSpan.addClass(getClass('Open'));
                                e.stopPropagation();
                                $(document).one('mouseup.customSelect', function (e) {
                                    if( e.target != $select.get(0) && $.inArray(e.target,$select.find('*').get()) < 0 ){
                                        $select.trigger('blur.customSelect');
                                    }else{
                                        changed($select,customSelectSpan);
                                    }
                                });
                            }
                        }
                    })
                    .bind('focus.customSelect', function () {
                        customSelectSpan.removeClass(getClass('Changed')).addClass(getClass('Focus'));
                    })
                    .bind('blur.customSelect', function () {
                        customSelectSpan.removeClass(getClass('Focus')+' '+getClass('Open'));
                    })
                    .bind('mouseenter.customSelect', function () {
                        customSelectSpan.addClass(getClass('Hover'));
                    })
                    .bind('mouseleave.customSelect', function () {
                        customSelectSpan.removeClass(getClass('Hover'));
                    })
                    .trigger('render.customSelect');
            });
        }
    });
})(jQuery);
