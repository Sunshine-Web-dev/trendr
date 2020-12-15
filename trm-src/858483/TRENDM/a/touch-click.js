
(function( $ ) {
        var hasTouch = "ontouchstart" in window,
        START_EVENT = hasTouch ? "touchstart" : "mousedown",
        MOVE_EVENT = hasTouch ? "touchmove" : "mousemove mouseover mouseout",
        END_EVENT = hasTouch ? "touchend" : "mouseup";
        
        var prepareMobileClick = function( e ){
            var bound = this.getBoundingClientRect();
            var getPoint = function(e) {
                return {
                        x : e.clientX ? e.clientX - bound.left : null || 
                                (!!e.originalEvent.changedTouches && e.originalEvent.changedTouches.length > 0 ? 
                                        e.originalEvent.changedTouches[0].clientX - bound.left : -1),
                        y : e.clientY ? e.clientY - bound.top : null || 
                                (!!e.originalEvent.changedTouches && e.originalEvent.changedTouches.length > 0 ? 
                                        e.originalEvent.changedTouches[0].clientY - bound.top : -1)
                }
            }
            
            var $target = $( this );
            
            // if button element pressed - all button's bound will valid to detect mobileclick event
            // if element isn't button - using intersection area of element bound and 40x40 bound
            // from touched place
            var isButton = e.target.tagName === 'BUTTON';
            var delta = 20;
            var startPoint = getPoint(e);
            
            $target.addClass('pressed');
            
            var touchmove = function(e) {
                var currentPoint = getPoint(e);

                if (currentPoint.x > bound.width || currentPoint.x < 0 
                        || currentPoint.y > bound.height || currentPoint.y < 0
                        || (!isButton && (currentPoint.x > startPoint.x + delta || currentPoint.x < startPoint.x - delta 
                                || currentPoint.y > startPoint.y + delta || currentPoint.y < startPoint.y - delta))) {
                    //console.log('touchmove destroy to '+$target.get(0).tagName + '; '+$target.attr('class'));
                    $target.off(MOVE_EVENT, touchmove);
                    $target.off(END_EVENT, touchend);
                    $target.off('touchcancel', touchcancel);
                    
                    $target.removeClass('pressed');
                }
            };
            
            $target.bind(MOVE_EVENT, touchmove);
            
            var touchend = function(e) {
                var currentPoint = getPoint(e);

                $target.off(MOVE_EVENT, touchmove);
                $target.off(END_EVENT, touchend);
                $target.off('touchcancel', touchcancel);

                if($target.length) {
                    $target.removeClass('pressed');
                    if (currentPoint.x <= bound.width && currentPoint.x >= 0 
                            && currentPoint.y <= bound.height && currentPoint.y >= 0) {
                        if (isButton || (currentPoint.x <= startPoint.x + delta && currentPoint.x >= startPoint.x - delta 
                                    && currentPoint.y <= startPoint.y + delta && currentPoint.y >= startPoint.y - delta)) {
                            $target.trigger( "mobileclick" );
                            $target.focus();
                        }
                    }
                }
            };
            
            $target.bind(END_EVENT, touchend);

            var touchcancel = function(e) {
                $target.off(MOVE_EVENT, touchmove);
                $target.off(END_EVENT, touchend);
                $target.off('touchcancel', touchcancel);

                if($target.length) {
                    $target.removeClass('pressed');
                }
            };

            $target.bind('touchcancel', touchcancel);
            
        };
        
        $.event.special.mobileclick = {
                 
                setup: function( eventData, namespaces, eventHandler ){
                    return;
                },
                
                add: function( handleObj ) {
                    $( this ).on( START_EVENT, handleObj.selector, prepareMobileClick );
                },
                
                remove: function( handleObj ) {
                    $( this ).off( START_EVENT, handleObj.selector, prepareMobileClick );
                },
                 
                teardown: function( namespaces ){
                    return;
                }
                 
        };
        
})( $ );

$('a').bind('mobileclick', function() {
})
// or
$(document).on('mobileclick', 'a', function() {
})
