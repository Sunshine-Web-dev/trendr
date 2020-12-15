
// tipsy, facebook style tooltips for jquery
// version 1.0.0a
// (c) 2008-2010 jason frame [jason@onehackoranother.com]
// released under the MIT license

(function($) {
    
    function maybeCall(thing, ctx) {
        return (typeof thing == 'function') ? (thing.call(ctx)) : thing;
    };
    
    function isElementInDOM(ele) {
      while (ele = ele.parentNode) {
        if (ele == document) return true;
      }
      return false;
    };
    
    function Tipsy(element, options) {
        this.$element = $(element);
        this.options = options;
        this.enabled = true;
        this.fixTitle();
    };
    
    Tipsy.prototype = {
        show: function() {
            var title = this.getTitle();
            if (title && this.enabled) {
                var $tip = this.tip();
                
                $tip.find('.tipsy-inner')[this.options.html ? 'html' : 'text'](title);
                $tip[0].className = 'tipsy'; // reset classname in case of dynamic gravity
                $tip.remove().css({top: 0, left: 0, visibility: 'hidden', display: 'block'}).prependTo(document.body);
                
                var pos = $.extend({}, this.$element.offset(), {
                    width: this.$element[0].offsetWidth,
                    height: this.$element[0].offsetHeight
                });
                
                var actualWidth = $tip[0].offsetWidth,
                    actualHeight = $tip[0].offsetHeight,
                    gravity = maybeCall(this.options.gravity, this.$element[0]);
                
                var tp;
                switch (gravity.charAt(0)) {
                    case 'n':
                        tp = {top: pos.top + pos.height + this.options.offset, left: pos.left + pos.width / 2 - actualWidth / 2};
                        break;
                    case 's':
                        tp = {top: pos.top - actualHeight - this.options.offset, left: pos.left + pos.width / 2 - actualWidth / 2};
                        break;
                    case 'e':
                        tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth - this.options.offset};
                        break;
                    case 'w':
                        tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width + this.options.offset};
                        break;
                }
                
                if (gravity.length == 2) {
                    if (gravity.charAt(1) == 'w') {
                        tp.left = pos.left + pos.width / 2 - 15;
                    } else {
                        tp.left = pos.left + pos.width / 2 - actualWidth + 15;
                    }
                }
                
                $tip.css(tp).addClass('tipsy-' + gravity);
                $tip.find('.tipsy-arrow')[0].className = 'tipsy-arrow tipsy-arrow-' + gravity.charAt(0);
                if (this.options.className) {
                    $tip.addClass(maybeCall(this.options.className, this.$element[0]));
                }
                
                if (this.options.fade) {
                    $tip.stop().css({opacity: 0, display: 'block', visibility: 'visible'}).animate({opacity: this.options.opacity});
                } else {
                    $tip.css({visibility: 'visible', opacity: this.options.opacity});
                }
            }
        },
        
        hide: function() {
            if (this.options.fade) {
                this.tip().stop().fadeOut(function() { $(this).remove(); });
            } else {
                this.tip().remove();
            }
        },
        
        fixTitle: function() {
            var $e = this.$element;
            if ($e.attr('title') || typeof($e.attr('original-title')) != 'string') {
                $e.attr('original-title', $e.attr('title') || '').removeAttr('title');
            }
        },
        
        getTitle: function() {
            var title, $e = this.$element, o = this.options;
            this.fixTitle();
            var title, o = this.options;
            if (typeof o.title == 'string') {
                title = $e.attr(o.title == 'title' ? 'original-title' : o.title);
            } else if (typeof o.title == 'function') {
                title = o.title.call($e[0]);
            }
            title = ('' + title).replace(/(^\s*|\s*$)/, "");
            return title || o.fallback;
        },
        
        tip: function() {
            if (!this.$tip) {
                this.$tip = $('<div class="tipsy"></div>').html('<div class="tipsy-arrow"></div><div class="tipsy-inner"></div>');
                this.$tip.data('tipsy-pointee', this.$element[0]);
            }
            return this.$tip;
        },
        
        validate: function() {
            if (!this.$element[0].parentNode) {
                this.hide();
                this.$element = null;
                this.options = null;
            }
        },
        
        enable: function() { this.enabled = true; },
        disable: function() { this.enabled = false; },
        toggleEnabled: function() { this.enabled = !this.enabled; }
    };
    
    $.fn.tipsy = function(options) {
        
        if (options === true) {
            return this.data('tipsy');
        } else if (typeof options == 'string') {
            var tipsy = this.data('tipsy');
            if (tipsy) tipsy[options]();
            return this;
        }
        
        options = $.extend({}, $.fn.tipsy.defaults, options);
        
        function get(ele) {
            var tipsy = $.data(ele, 'tipsy');
            if (!tipsy) {
                tipsy = new Tipsy(ele, $.fn.tipsy.elementOptions(ele, options));
                $.data(ele, 'tipsy', tipsy);
            }
            return tipsy;
        }
        
        function enter() {
            var tipsy = get(this);
            tipsy.hoverState = 'in';
            if (options.delayIn == 0) {
                tipsy.show();
            } else {
                tipsy.fixTitle();
                setTimeout(function() { if (tipsy.hoverState == 'in') tipsy.show(); }, options.delayIn);
            }
        };
        
        function leave() {
            var tipsy = get(this);
            tipsy.hoverState = 'out';
            if (options.delayOut == 0) {
                tipsy.hide();
            } else {
                setTimeout(function() { if (tipsy.hoverState == 'out') tipsy.hide(); }, options.delayOut);
            }
        };
        
        if (!options.live) this.each(function() { get(this); });
        
        if (options.trigger != 'manual') {
            var binder   = options.live ? 'live' : 'bind',
                eventIn  = options.trigger == 'hover' ? 'mouseenter' : 'focus',
                eventOut = options.trigger == 'hover' ? 'mouseleave' : 'blur';
            this[binder](eventIn, enter)[binder](eventOut, leave);
        }
        
        return this;
        
    };
    
    $.fn.tipsy.defaults = {
        className: null,
        delayIn: 0,
        delayOut: 0,
        fade: false,
        fallback: '',
        gravity: 'n',
        html: false,
        live: false,
        offset: 0,
        opacity: 0.8,
        title: 'title',
        trigger: 'hover'
    };
    
    $.fn.tipsy.revalidate = function() {
      $('.tipsy').each(function() {
        var pointee = $.data(this, 'tipsy-pointee');
        if (!pointee || !isElementInDOM(pointee)) {
          $(this).remove();
        }
      });
    };
    
    // Overwrite this method to provide options on a per-element basis.
    // For example, you could store the gravity in a 'tipsy-gravity' attribute:
    // return $.extend({}, options, {gravity: $(ele).attr('tipsy-gravity') || 'n' });
    // (remember - do not modify 'options' in place!)
    $.fn.tipsy.elementOptions = function(ele, options) {
        return $.metadata ? $.extend({}, options, $(ele).metadata()) : options;
    };
    
    $.fn.tipsy.autoNS = function() {
        return $(this).offset().top > ($(document).scrollTop() + $(window).height() / 2) ? 's' : 'n';
    };
    
    $.fn.tipsy.autoWE = function() {
        return $(this).offset().left > ($(document).scrollLeft() + $(window).width() / 2) ? 'e' : 'w';
    };
    
    /**
     * yields a closure of the supplied parameters, producing a function that takes
     * no arguments and is suitable for use as an autogravity function like so:
     *
     * @param margin (int) - distance from the viewable region edge that an
     *        element should be before setting its tooltip's gravity to be away
     *        from that edge.
     * @param prefer (string, e.g. 'n', 'sw', 'w') - the direction to prefer
     *        if there are no viewable region edges effecting the tooltip's
     *        gravity. It will try to vary from this minimally, for example,
     *        if 'sw' is preferred and an element is near the right viewable 
     *        region edge, but not the top edge, it will set the gravity for
     *        that element's tooltip to be 'se', preserving the southern
     *        component.
     */
     $.fn.tipsy.autoBounds = function(margin, prefer) {
		return function() {
			var dir = {ns: prefer[0], ew: (prefer.length > 1 ? prefer[1] : false)},
			    boundTop = $(document).scrollTop() + margin,
			    boundLeft = $(document).scrollLeft() + margin,
			    $this = $(this);

			if ($this.offset().top < boundTop) dir.ns = 'n';
			if ($this.offset().left < boundLeft) dir.ew = 'w';
			if ($(window).width() + $(document).scrollLeft() - $this.offset().left < margin) dir.ew = 'e';
			if ($(window).height() + $(document).scrollTop() - $this.offset().top < margin) dir.ns = 's';

			return dir.ns + (dir.ew ? dir.ew : '');
		}
	};
    
})(jQuery);
  $(function() {
    $('a').tipsy({fade: true, gravity: 's'});
  });

$(window).scroll(function() {
	if ($(this).scrollTop() > 380) {
				$(' #contour-image ').addClass("slide");
				$(' #header ').addClass("selected");
				$(' #figure').addClass("slide");


		//$('.user img.portrait').addClass("slideUp");
		$('#cover').addClass("slide");
		//		$('.hashtitlex').addClass("slide");


	} else {
		$(' #contour-image').removeClass("slide");
				$(' #header ').removeClass("selected");

		//$('.user  img.portrait').removeClass("slideUp");
		$('#cover').removeClass("slide");

							//	$('.hashtitlex').removeClass("slide");
		$('#figure').removeClass("slide");


	}
});

var jq = jQuery;
var current_requests = new Array;
jq(document).ready(function() {
	jq("a.report-link").click(function() {
		var e = jq(this);
		var t = e.children(".inner");
		t.addClass("ajax-loader");
		var n = e.attr("href");
		var r = n.replace(/[^?]*\?(.*)report=(.*)/, "$1trsmod-ajax=$2");
		if (current_requests[r])
			return false;
		else
			current_requests[r] = true;
		jq.post(ajaxurl, r, function(i) {
			e.fadeOut(100, function() {
				switch (i.type) {
				case "success":
				case "fade warning":
					e.toggleClass("trs-unreport");
					e.toggleClass("trs-reported");
					if (e.hasClass("trs-unreport"))
						n = n.replace(/(.*)report=[^&]*(.*)/, "$1report=flag$2");
					else if (e.hasClass("trs-reported"))
						n = n.replace(/(.*)report=[^&]*(.*)/, "$1report=unflag$2");
					n = n.replace(/(.*)_key=[^&]*(.*)/, "$1_key=" + i.new_nonce + "$2");
					e.attr("href", n);
					if (!e.hasClass("no-text"))
						t.html(i.msg);
					if ("fade warning" == i.type) {
						$was_no_text = e.hasClass("no-text");
						window.setTimeout(function() {
							e.fadeOut(0, function() {
								if ($was_no_text) {
									t.html("");
									e.addClass("no-text")
								} else
									t.html(i.msg);
								e.fadeIn(0)
							})
						}, 25);
						t.html(i.fade_msg)
					}
					break;
				case "error":
				default:
					t.html(i.msg);
					e.removeClass("no-text")
				}
				t.removeClass("ajax-loader");
				jq(this).fadeIn(1);
				current_requests[r] = false
			})
		}, "json");
		return false
	})
})

// AJAX Functions
var jq = jQuery;

// Global variable to prevent multiple AJAX requests
var trs_ajax_request = null;

jq(document).ready( function() {
	/**** Page Load Actions *******************************************************/

	/* Hide Forums Post Form */
	//if ( '-1' == window.location.search.indexOf('new') && jq('div.forums').length )
	//	jq('div#new-topic-post').hide();
	//else
	//	jq('div#new-topic-post').show();

	/* Activity filter and scope set */
	trs_init_activity();

	/* Object filter and scope set. */
	var objects = [ 'members', 'groups', 'blogs', 'forums' ];
	trs_init_objects( objects );

	/* @mention Compose Scrolling */
	//if ( jq.query.get('r') && jq('textarea#field').length ) {
	//	jq('#post-controls').animate({height:'40px'});
	//	jq("form#post-box textarea").animate({height:'50px'});
		//jq.scrollTo( jq('textarea#field'), 500, { offset:-125, easing:'easeOutQuad' } );
		//jq('textarea#field').focus();
	//}

	/**** Activity Posting ********************************************************/

	/* Textarea focus */
//	jq('#field').focus( function(){
	//	jq("form#post-box textarea").animate({height:'50px'});
		//jq("#submit-post").prop("disabled", false);
	//});

	/* New posts */
	jq("input#submit-post").click( function() {
		var button = jq(this);
		var form = button.parent().parent().parent().parent();

		form.children().each( function() {
			if ( jq.nodeName(this, "textarea") || jq.nodeName(this, "input") )
				jq(this).prop( 'disabled', true );
		});

		/* Remove any errors */
		jq('div.error').remove();
		button.addClass('loading');
		button.prop('disabled', true);

		/* Default POST values */
		var object = '';
		var item_id = jq("#field-post-in").val();
		var content = jq("textarea#field").val();

		/* Set object for non-profile posts */
		if ( item_id > 0 ) {
			object = jq("#field-post-object").val();
		}

		jq.post( ajaxurl, {
			action: 'post_update',
			'cookie': encodeURIComponent(document.cookie),
			'_key_post_update': jq("input#_key_post_update").val(),
			'content': content,
			'object': object,
			'item_id': item_id
		},
		function(response) {

			form.children().each( function() {
				if ( jq.nodeName(this, "textarea") || jq.nodeName(this, "input") ) {
					jq(this).prop( 'disabled', false );
				}
			});

			/* Check for errors and append if found. */
			if ( response[0] + response[1] == '-1' ) {
				form.prepend( response.substr( 2, response.length ) );
				jq( 'form#' + form.attr('id') + ' div.error').hide().fadeIn( 200 );
			} else {
				if ( 0 == jq("ul.publish-piece").length ) {
					jq("div.error").slideUp(100).remove();
					jq("div#message").slideUp(100).remove();
					jq("div.activity").append( '<ul id="publish" class="publish-piece article-piece">' );
				}

				jq("ul#publish").prepend(response);
				jq("ul#publish li:first").addClass('new-update');

				//if ( 0 != jq("div#latest-update").length ) {
				//	var l = jq(" .broadcast-inn p").html();
				//	var v = jq("p a.view").attr('href');

				//	var ltext = jq(" .broadcast-inn p").text();

				//	var u = '';
				//	if ( ltext != '' )
				//		u = '&quot;' + l + '&quot; ';

				//	u += '<a href="' + v + '" rel="nofollow">' + TRS_DTheme.view + '</a>';

				//	jq("div#latest-update").slideUp(300,function(){
				//		jq("div#latest-update").html( u );
				//		jq("div#latest-update").slideDown(300);
				//	});
				//}

			//	jq("li.new-update").hide().slideDown( 300 );
			//	jq("li.new-update").removeClass( 'new-update' );
				//jq("textarea#field").val('');
			}

			//jq("#post-controls").animate({height:'0px'});
		//	jq("form#post-box textarea").animate({height:'20px'});
			//jq("#submit-post").prop("disabled", true).removeClass('loading');
		});

		return false;
	});

	/* List tabs event delegation */
	jq('div.activity-type-tabs').click( function(event) {
		var target = jq(event.target).parent();

		if ( event.target.nodeName == 'STRONG' || event.target.nodeName == 'SPAN' )
			target = target.parent();
		else if ( event.target.nodeName != 'A' )
			return false;

		/* Reset the page */
		jq.cookie( 'trs-activity-oldestpage', 1, {path: '/'} );

		/* Activity Stream Tabs */
		var scope = target.attr('id').substr( 9, target.attr('id').length );
		var filter = jq("#post-refine select").val();

		if ( scope == 'mentions' )
			jq( 'li#' + target.attr('id') + ' a strong' ).remove();

		trs_activity_request(scope, filter);

		return false;
	});

    /* Activity filter select */
    jq('#post-refine select , #activity_media_filter input[type="checkbox"]').change( function() {
        var selected_tab = jq( 'div.activity-type-tabs li.selected' );

        if ( !selected_tab.length )
            var scope = null;
        else
            var scope = selected_tab.attr('id').substr( 9, selected_tab.attr('id').length );

        var filter = jq('#post-refine select').val();

        trs_activity_request(scope, filter);
if(this.tagName = 'INPUT')
return true;
        return false;
    });

	/* Stream event delegation */
	//jq('div.activity').click( function(event) {
	//	var target = jq(event.target);
    jq(document).click( function(event) {
        var target = jq(event.target);
		/* Favoriting activity stream items */
		if ( target.hasClass('fav') || target.hasClass('unfav') ) {
			var type = target.hasClass('fav') ? 'fav' : 'unfav';
			var parent = target.parent().parent().parent();
			var parent_id = parent.attr('id').substr( 9, parent.attr('id').length );

			target.addClass('loading');

			jq.post( ajaxurl, {
				action: 'activity_mark_' + type,
				'cookie': encodeURIComponent(document.cookie),
				'id': parent_id
			},
			function(response) {
				target.removeClass('loading');

				target.fadeOut( 100, function() {
					jq(this).html(response);
					jq(this).attr('title', 'fav' == type ? TRS_DTheme.remove_fav : TRS_DTheme.mark_as_fav);
					jq(this).fadeIn(100);
				});

				if ( 'fav' == type ) {
					if ( !jq('div.contour-select li#activity-favorites').length )
						jq('div.contour-select ul li#activity-mentions').before( '<li id="activity-favorites"><a href="#">' + TRS_DTheme.my_favs + ' <span>0</span></a></li>');

					target.removeClass('fav');
					target.addClass('unfav');

					jq('div.contour-select ul li#activity-favorites span').html( Number( jq('div.contour-select ul li#activity-favorites span').html() ) + 1 );
				} else {
					target.removeClass('unfav');
					target.addClass('fav');

					jq('div.contour-select ul li#activity-favorites span').html( Number( jq('div.contour-select ul li#activity-favorites span').html() ) - 1 );

					if ( !Number( jq('div.contour-select ul li#activity-favorites span').html() ) ) {
						if ( jq('div.contour-select ul li#activity-favorites').hasClass('selected') )
							trs_activity_request( null, null );

						jq('div.contour-select ul li#activity-favorites').remove();
					}
				}

				if ( 'activity-favorites' == jq( 'div.contour-select li.selected').attr('id') )
					target.parent().parent().parent().slideUp(100);
			});

			return false;
		}

		/* Delete activity stream items */
		if ( target.hasClass('delete-activity') ) {
			var li        = target.parents('div.activity ul li');
			var id        = li.attr('id').substr( 9, li.attr('id').length );
			var link_href = target.attr('href');
			var nonce     = link_href.split('_key=');

			nonce = nonce[1];

			target.addClass('loading');

			jq.post( ajaxurl, {
				action: 'delete_activity',
				'cookie': encodeURIComponent(document.cookie),
				'id': id,
				'_key': nonce
			},
			function(response) {

				if ( response[0] + response[1] == '-1' ) {
					li.prepend( response.substr( 2, response.length ) );
					li.children('div#message').hide().fadeIn(300);
				} else {
					li.slideUp(300);
				}
			});

			return false;
		}

		/* Load more updates at the end of the page */
		if ( target.parent().hasClass('infinite') ) {
			jq(".infinite").addClass('loading');

			if ( null == jq.cookie('trs-activity-oldestpage') )
				jq.cookie('trs-activity-oldestpage', 1, {path: '/'} );

			var oldest_page = ( jq.cookie('trs-activity-oldestpage') * 1 ) + 1;

			jq.post( ajaxurl, {
				action: 'activity_get_older_updates',
				'cookie': encodeURIComponent(document.cookie),
				'page': oldest_page
			},
			function(response)
			{
				jq(".infinite").removeClass('loading');
				jq.cookie( 'trs-activity-oldestpage', oldest_page, {path: '/'} );
				jq("ul.publish-piece").append(response.contents);

				target.parent().hide();
				target.parent("#skeleton .infinite").show();

			}, 'json' );

			return false;
		}

	});

	// Activity "Read More" links
	jq('.activity-read-more a').live('click', function(event) {
		var target = jq(event.target);
		var link_id = target.parent().attr('id').split('-');
		var a_id = link_id[3];
		var type = link_id[0]; /* activity or acomment */

		var inner_class = type == 'acomment' ? 'acomment-content' : 'broadcast-inn';
		var a_inner = jq('li#' + type + '-' + a_id + ' .' + inner_class + ':first' );
		jq(target).addClass('loading');

		jq.post( ajaxurl, {
		action: 'get_single_activity_content',
		'activity_id': a_id
		},
		function(response) {
			jq(a_inner).slideUp(0).html(response).slideDown(0);
		});

		return false;
	});

	/**** Activity Comments *******************************************************/

	/* Hide all activity comment forms */
	jq('form.ac-form').hide();

	/* Hide excess comments */
	if ( jq('div.activity-comments').length )
		trs_dtheme_hide_comments();

	   /* Activity list event delegation */

    /*********************************************************
    
    Jang Updated This Code
    div.activity => document
    ***********************************************************/

    jq(document).click( function(event) {
        var target = jq(event.target);

        /* Comment / comment reply links */
        if ( target.hasClass('acomment-reply') || target.parent().hasClass('acomment-reply') ) {
            if ( target.parent().hasClass('acomment-reply') )
                target = target.parent();

            var id = target.attr('id');
            ids = id.split('-');

            var a_id = ids[2]
            var c_id = target.attr('href').substr( 10, target.attr('href').length );

            //var form = jq( '#ac-form-' + a_id );
            form = target.parent().parent().parent().find('#ac-form-'+a_id).eq(0);
            form.css( 'display', 'none' );
            form.removeClass('root');
            jq(document).find('.ac-form').each(function(){
                jq(this).hide();    
            })
            

            /* Hide any error messages */
            form.children('div').each( function() {
                if ( jq(this).hasClass( 'error' ) )
                    jq(this).hide();
            });


            if ( ids[1] != 'comment' ) {
                //jq(document).find()
                target.parent().parent().parent().find('div.activity-comments li#acomment-' + c_id).eq(0).append( form );
            } else {
                target.parent().parent().parent().find('li#activity-' + a_id + ' div.activity-comments').eq(0).append( form );
            }

            if ( form.parent().hasClass( 'activity-comments' ) )
                form.addClass('root');

            form.slideDown( 200 );
            jq.scrollTo( form, 500, { offset:-100, easing:'easeOutQuad' } );
            form.find('#ac-form-'+ids[2]+'textarea').eq(0).focus();
            // jq('#ac-form-' + ids[2] + ' textarea').focus();

            return false;
        }

        /**************************************************************
                JANG UPDATE START
    
        *****************************************************************/
        /* Activity comment posting */
        if ( target.attr('name') == 'ac_form_submit' ) {
            var form = target.parent().parent();
            var form_parent = form.parent();
            var form_id = form.attr('id').split('-');

            if ( !form_parent.hasClass('activity-comments') ) {
                var tmp_id = form_parent.attr('id').split('-');
                var comment_id = tmp_id[1];
            } else {
                var comment_id = form_id[2];
            }

            /* Hide any error messages */
            //jq( 'form#' + form + ' div.error').hide();
            target.addClass('loading').prop('disabled', true);

            jq.post( ajaxurl, {
                action: 'new_activity_comment',
                'cookie': encodeURIComponent(document.cookie),
                '_key_new_activity_comment': jq("input#_key_new_activity_comment").val(),
                'comment_id': comment_id,
                'form_id': form_id[2],
                'content': target.parent().find('textarea').eq(0).val()
            },
            function(response)
            {
                target.removeClass('loading');

                /* Check for errors and append if found. */
                if ( response[0] + response[1] == '-1' ) {
                    form.append( response.substr( 2, response.length ) ).hide().fadeIn( 200 );
                } else {
                    form.fadeOut( 200,
                        function() {
                            if ( 0 == form.parent().children('ul').length ) {
                                if ( form.parent().hasClass('activity-comments') )
                                    form.parent().prepend('<ul></ul>');
                                else
                                    form.parent().append('<ul></ul>');
                            }

                            form.parent().children('ul').append(response).hide().fadeIn( 200 );
                            form.children('textarea').val('');
                            form.parent().parent().addClass('has-comments');
                        }
                    );
                    target.parent().find('textarea').eq(0).val('');

                    /* Increase the "Reply (X)" button count */
                    target.parent().parent().parent().parent().find('a.acomment-reply span').eq(0).html( Number(target.parent().parent().parent().parent().find('a.acomment-reply span').eq(0).html() ) + 1 );
                }

                jq(target).prop("disabled", false);
            });
/*********************************************************************

    JANG UPDATE END please look at line number: 318, 348~354, 417~421

********************************************************************/
            return false;
        }

		/* Showing hidden comments - pause for half a second */
		if ( target.parent().hasClass('show-all') ) {
			target.parent().addClass('loading');

			setTimeout( function() {
				target.parent().parent().children('li').fadeIn(200, function() {
					target.parent().remove();
				});
			}, 600 );

			return false;
		}
	});

	/* Escape Key Press for cancelling comment forms */
	jq(document).keydown( function(e) {
		e = e || window.event;
		if (e.target)
			element = e.target;
		else if (e.srcElement)
			element = e.srcElement;

		if( element.nodeType == 3)
			element = element.parentNode;

		if( e.ctrlKey == true || e.altKey == true || e.metaKey == true )
			return;

		var keyCode = (e.keyCode) ? e.keyCode : e.which;

		if ( keyCode == 27 ) {
			if (element.tagName == 'TEXTAREA') {
				if ( jq(element).hasClass('ac-input') )
					jq(element).parent().parent().parent().slideUp( 200 );
			}
		}
	});

	/**** Directory Search ****************************************************/

	/* The search form on all directory pages */
	jq('div.dir-search').click( function(event) {
		if ( jq(this).hasClass('no-ajax') )
			return;

		var target = jq(event.target);

		if ( target.attr('type') == 'submit' ) {
			var css_id = jq('div.contour-select li.selected').attr('id').split( '-' );
			var object = css_id[0];

			trs_filter_request( object, jq.cookie('trs-' + object + '-filter'), jq.cookie('trs-' + object + '-scope') , 'div.' + object, target.parent().children('label').children('input').val(), 1, jq.cookie('trs-' + object + '-extras') );

			return false;
		}
	});

	/**** Tabs and Filters ****************************************************/

	/* When a navigation tab is clicked - e.g. | All Groups | My Groups | */
	jq('div.contour-select').click( function(event) {
		if ( jq(this).hasClass('no-ajax') )
			return;

		var target = jq(event.target).parent();

		if ( 'LI' == event.target.parentNode.nodeName && !target.hasClass('last') ) {
			var css_id = target.attr('id').split( '-' );
			var object = css_id[0];

			if ( 'activity' == object )
				return false;

			var scope = css_id[1];
			var filter = jq("#" + object + "-order-select select").val();
			var search_terms = jq("#" + object + "_search").val();

			trs_filter_request( object, filter, scope, 'div.' + object, search_terms, 1, jq.cookie('trs-' + object + '-extras') );

			return false;
		}
	});

	/* When the filter select box is changed re-query */
	jq('li.filter select').change( function() {
		if ( jq('div.contour-select li.selected').length )
			var el = jq('div.contour-select li.selected');
		else
			var el = jq(this);

		var css_id = el.attr('id').split('-');
		var object = css_id[0];
		var scope = css_id[1];
		var filter = jq(this).val();
		var search_terms = false;

		if ( jq('div.dir-search input').length )
			search_terms = jq('div.dir-search input').val();

		if ( 'friends' == object )
			object = 'members';

		trs_filter_request( object, filter, scope, 'div.' + object, search_terms, 1, jq.cookie('trs-' + object + '-extras') );

		return false;
	});

	/* All pagination links run through this function */
	jq('div#skeleton').click( function(event) {
		var target = jq(event.target);

		if ( target.hasClass('button') )
			return true;
         
        //modified to create lightbox effect for media 6-29-18  
		//if ( target.parent().parent().hasClass('pagination') && !target.parent().parent().hasClass('no-ajax') ) {
		if ( target.parent().parent().hasClass('pagination') && target.parent().parent().hasClass('no-ajax') ) {
	
			if ( target.hasClass('dots') || target.hasClass('current') )
				return false;

			if ( jq('div.contour-select li.selected').length )
				var el = jq('div.contour-select li.selected');
			else
				var el = jq('li.filter select');

			var page_number = 1;
			var css_id = el.attr('id').split( '-' );
			var object = css_id[0];
			var search_terms = false;

			if ( jq('div.dir-search input').length )
				search_terms = jq('div.dir-search input').val();

			if ( jq(target).hasClass('next') )
				var page_number = Number( jq('div.pagination span.current').html() ) + 1;
			else if ( jq(target).hasClass('prev') )
				var page_number = Number( jq('div.pagination span.current').html() ) - 1;
			else
				var page_number = Number( jq(target).html() );

			trs_filter_request( object, jq.cookie('trs-' + object + '-filter'), jq.cookie('trs-' + object + '-scope'), 'div.' + object, search_terms, page_number, jq.cookie('trs-' + object + '-extras') );

			return false;
		}

	});

	/** Invite Friends Interface ****************************************/

	/* Select a user from the list of friends and add them to the invite list */
	jq("div#invite-list input").click( function() {
		jq('.ajax-loader').toggle();

		var friend_id = jq(this).val();

		if ( jq(this).prop('checked') == true )
			var friend_action = 'invite';
		else
			var friend_action = 'uninvite';

		jq('div.contour-select li.selected').addClass('loading');

		jq.post( ajaxurl, {
			action: 'groups_invite_user',
			'friend_action': friend_action,
			'cookie': encodeURIComponent(document.cookie),
			'_key': jq("input#_key_invite_uninvite_user").val(),
			'friend_id': friend_id,
			'group_id': jq("input#group_id").val()
		},
		function(response)
		{
			if ( jq("#message") )
				jq("#message").hide();

			jq('.ajax-loader').toggle();

			if ( friend_action == 'invite' ) {
				jq('#friend-list').append(response);
			} else if ( friend_action == 'uninvite' ) {
				jq('#friend-list li#uid-' + friend_id).remove();
			}

			jq('div.contour-select li.selected').removeClass('loading');
		});
	});

	/* Remove a user from the list of users to invite to a group */
	jq("#friend-list li a.remove").live('click', function() {
		jq('.ajax-loader').toggle();

		var friend_id = jq(this).attr('id');
		friend_id = friend_id.split('-');
		friend_id = friend_id[1];

		jq.post( ajaxurl, {
			action: 'groups_invite_user',
			'friend_action': 'uninvite',
			'cookie': encodeURIComponent(document.cookie),
			'_key': jq("input#_key_invite_uninvite_user").val(),
			'friend_id': friend_id,
			'group_id': jq("input#group_id").val()
		},
		function(response)
		{
			jq('.ajax-loader').toggle();
			jq('#friend-list li#uid-' + friend_id).remove();
			jq('#invite-list input#f-' + friend_id).prop('checked', false);
		});

		return false;
	});

	/** Friendship Requests **************************************/

	/* Accept and Reject friendship request buttons */
	jq("ul#friend-list a.accept, ul#friend-list a.reject").click( function() {
		var button = jq(this);
		var li = jq(this).parents('ul#friend-list li');
		var action_div = jq(this).parents('li div.action');

		var id = li.attr('id').substr( 11, li.attr('id').length );
		var link_href = button.attr('href');

		var nonce = link_href.split('_key=');
			nonce = nonce[1];

		if ( jq(this).hasClass('accepted') || jq(this).hasClass('rejected') )
			return false;

		if ( jq(this).hasClass('accept') ) {
			var action = 'accept_friendship';
			action_div.children('a.reject').css( 'visibility', 'hidden' );
		} else {
			var action = 'reject_friendship';
			action_div.children('a.accept').css( 'visibility', 'hidden' );
		}

		button.addClass('loading');

		jq.post( ajaxurl, {
			action: action,
			'cookie': encodeURIComponent(document.cookie),
			'id': id,
			'_key': nonce
		},
		function(response) {
			button.removeClass('loading');

			if ( response[0] + response[1] == '-1' ) {
				li.prepend( response.substr( 2, response.length ) );
				li.children('div#message').hide().fadeIn(200);
			} else {
				button.fadeOut( 100, function() {
					if ( jq(this).hasClass('accept') ) {
						action_div.children('a.reject').hide();
						jq(this).html( TRS_DTheme.accepted ).fadeIn(50);
						jq(this).addClass('accepted');
					} else {
						action_div.children('a.accept').hide();
						jq(this).html( TRS_DTheme.rejected ).fadeIn(50);
						jq(this).addClass('rejected');
					}
				});
			}
		});

		return false;
	});

	/* Add / Remove friendship buttons */
	jq("div.friendship-button a").live('click', function() {
		jq(this).parent().addClass('loading');
		var fid = jq(this).attr('id');
		fid = fid.split('-');
		fid = fid[1];

		var nonce = jq(this).attr('href');
		nonce = nonce.split('?_key=');
		nonce = nonce[1].split('&');
		nonce = nonce[0];

		var thelink = jq(this);

		jq.post( ajaxurl, {
			action: 'addremove_friend',
			'cookie': encodeURIComponent(document.cookie),
			'fid': fid,
			'_key': nonce
		},
		function(response)
		{
			var action = thelink.attr('rel');
			var parentdiv = thelink.parent();

			if ( action == 'add' ) {
				jq(parentdiv).fadeOut(200,
					function() {
						parentdiv.removeClass('add_friend');
						parentdiv.removeClass('loading');
						parentdiv.addClass('pending');
						parentdiv.fadeIn(200).html(response);
					}
				);

			} else if ( action == 'remove' ) {
				jq(parentdiv).fadeOut(200,
					function() {
						parentdiv.removeClass('remove_friend');
						parentdiv.removeClass('loading');
						parentdiv.addClass('add');
						parentdiv.fadeIn(200).html(response);
					}
				);
			}
		});
		return false;
	} );

	/** Group Join / Leave Buttons **************************************/

	jq("div.group-button a").live('click', function() {
		var gid = jq(this).parent().attr('id');
		gid = gid.split('-');
		gid = gid[1];

		var nonce = jq(this).attr('href');
		nonce = nonce.split('?_key=');
		nonce = nonce[1].split('&');
		nonce = nonce[0];

		var thelink = jq(this);

		jq.post( ajaxurl, {
			action: 'joinleave_group',
			'cookie': encodeURIComponent(document.cookie),
			'gid': gid,
			'_key': nonce
		},
		function(response)
		{
			var parentdiv = thelink.parent();

			if ( !jq('body.directory').length )
				location.href = location.href;
			else {
				jq(parentdiv).fadeOut(200,
					function() {
						parentdiv.fadeIn(200).html(response);
					}
				);
			}
		});
		return false;
	} );

	/** Button disabling ************************************************/

	jq('div.pending').click(function() {
		return false;
	});

	/** Private Messaging ******************************************/

	/* AJAX send reply functionality */
	jq("input#send_reply_button").click(
		function() {
			var order = jq('#messages_order').val() || 'ASC',
				offset = jq('#message-recipients').offset();

			var button = jq("input#send_reply_button");
			jq(button).addClass('loading');

			jq.post( ajaxurl, {
				action: 'messages_send_reply',
				'cookie': encodeURIComponent(document.cookie),
				'_key': jq("input#send_message_nonce").val(),

				'content': jq("#message_content").val(),
				'send_to': jq("input#send_to").val(),
				'subject': jq("input#subject").val(),
				'thread_id': jq("input#thread_id").val()
			},
			function(response)
			{
				if ( response[0] + response[1] == "-1" ) {
					jq('form#send-reply').prepend( response.substr( 2, response.length ) );
				} else {
					jq('form#send-reply div#message').remove();
					jq("#message_content").val('');

					if ( 'ASC' == order ) {
						jq('form#send-reply').before( response );
					} else {
						jq('#message-recipients').after( response );
						jq(window).scrollTop(offset.top);
					}

					jq("div.new-message").hide().slideDown( 200, function() {
						jq('div.new-message').removeClass('new-message');
					});
				}
				jq(button).removeClass('loading');
			});

			return false;
		}
	);

	/* Marking private messages as read and unread */
	jq("a#mark_as_read, a#mark_as_unread").click(function() {
		var checkboxes_tosend = '';
		var checkboxes = jq("#message-threads tr td input[type='checkbox']");

		if ( 'mark_as_unread' == jq(this).attr('id') ) {
			var currentClass = 'read'
			var newClass = 'unread'
			var unreadCount = 1;
			var inboxCount = 0;
			var unreadCountDisplay = 'inline';
			var action = 'messages_markunread';
		} else {
			var currentClass = 'unread'
			var newClass = 'read'
			var unreadCount = 0;
			var inboxCount = 1;
			var unreadCountDisplay = 'none';
			var action = 'messages_markread';
		}

		checkboxes.each( function(i) {
			if(jq(this).is(':checked')) {
				if ( jq('tr#m-' + jq(this).attr('value')).hasClass(currentClass) ) {
					checkboxes_tosend += jq(this).attr('value');
					jq('tr#m-' + jq(this).attr('value')).removeClass(currentClass);
					jq('tr#m-' + jq(this).attr('value')).addClass(newClass);
					var thread_count = jq('tr#m-' + jq(this).attr('value') + ' td span.unread-count').html();

					jq('tr#m-' + jq(this).attr('value') + ' td span.unread-count').html(unreadCount);
					jq('tr#m-' + jq(this).attr('value') + ' td span.unread-count').css('display', unreadCountDisplay);

					var inboxcount = jq('tr.unread').length;

					jq('a#user-messages span').html( inboxcount );

					if ( i != checkboxes.length - 1 ) {
						checkboxes_tosend += ','
					}
				}
			}
		});
		jq.post( ajaxurl, {
			action: action,
			'thread_ids': checkboxes_tosend
		});
		return false;
	});

	/* Selecting unread and read messages in inbox */
	jq("select#message-type-select").change(
		function() {
			var selection = jq("select#message-type-select").val();
			var checkboxes = jq("td input[type='checkbox']");
			checkboxes.each( function(i) {
				checkboxes[i].checked = "";
			});

			switch(selection) {
				case 'unread':
					var checkboxes = jq("tr.unread td input[type='checkbox']");
				break;
				case 'read':
					var checkboxes = jq("tr.read td input[type='checkbox']");
				break;
			}
			if ( selection != '' ) {
				checkboxes.each( function(i) {
					checkboxes[i].checked = "checked";
				});
			} else {
				checkboxes.each( function(i) {
					checkboxes[i].checked = "";
				});
			}
		}
	);

	/* Bulk delete messages */
	jq("a#delete_inbox_messages, a#delete_sentbox_messages").click( function() {
		checkboxes_tosend = '';
		checkboxes = jq("#message-threads tr td input[type='checkbox']");

		jq('div#message').remove();
		jq(this).addClass('loading');

		jq(checkboxes).each( function(i) {
			if( jq(this).is(':checked') )
				checkboxes_tosend += jq(this).attr('value') + ',';
		});

		if ( '' == checkboxes_tosend ) {
			jq(this).removeClass('loading');
			return false;
		}

		jq.post( ajaxurl, {
			action: 'messages_delete',
			'thread_ids': checkboxes_tosend
		}, function(response) {
			if ( response[0] + response[1] == "-1" ) {
				jq('#message-threads').prepend( response.substr( 2, response.length ) );
			} else {
				jq('#message-threads').before( '<div id="message" class="updated"><p>' + response + '</p></div>' );

				jq(checkboxes).each( function(i) {
					if( jq(this).is(':checked') )
						jq(this).parent().parent().fadeOut(150);
				});
			}

			jq('div#message').hide().slideDown(150);
			jq("a#delete_inbox_messages, a#delete_sentbox_messages").removeClass('loading');
		});
		return false;
	});

	/* Close site wide notices in the sidebar */
	jq("a#close-notice").click( function() {
		jq(this).addClass('loading');
		jq('div#sidebar div.error').remove();

		jq.post( ajaxurl, {
			action: 'messages_close_notice',
			'notice_id': jq('.notice').attr('rel').substr( 2, jq('.notice').attr('rel').length )
		},
		function(response) {
			jq("a#close-notice").removeClass('loading');

			if ( response[0] + response[1] == '-1' ) {
				jq('.notice').prepend( response.substr( 2, response.length ) );
				jq( 'div#sidebar div.error').hide().fadeIn( 200 );
			} else {
				jq('.notice').slideUp( 100 );
			}
		});
		return false;
	});

	/* Removed 7-5-18 related to -> Admin Bar & trm_list_pages Javascript IE6 hover class */
	//jq("#Backend-WeaprEcqaKejUbRq-trendr-bar ul.main-nav li, #nav li").mouseover( function() {
	//	jq(this).addClass('sfhover');
	//});

	//jq("#Backend-WeaprEcqaKejUbRq-trendr-bar ul.main-nav li, #nav li").mouseout( function() {
	//	jq(this).removeClass('sfhover');
	//});

	/* Clear TRS cookies on logout */
	jq('a.logout').click( function() {
		//jq.cookie('trs-activity-scope', null, {path: '/'});
		//jq.cookie('trs-activity-filter', null, {path: '/'});
    	//jq.cookie( 'bp-activity-filter-checkbox',null,{path:'/'});

		jq.cookie('trs-activity-oldestpage', null, {path: '/'});

		var objects = [ 'members', 'groups', 'blogs', 'forums' ];
		jq(objects).each( function(i) {
			jq.cookie('trs-' + objects[i] + '-scope', null, {path: '/'} );
			jq.cookie('trs-' + objects[i] + '-filter', null, {path: '/'} );
			jq.cookie('trs-' + objects[i] + '-extras', null, {path: '/'} );
		});
	});
});

	//jq('#submit-login').click( function() {
		//jq.cookie('trs-activity-scope', 'following_recommend', {path: '/'});
		
	//});

	/* Clear TRS cookies on click my-account .tag - reset hashtag purposes - added 3/6/18 JS*/

/* Setup activity scope and filter based on the current cookie settings. */
function trs_init_activity() {
	/* Reset the page */
	jq.cookie( 'trs-activity-oldestpage', 1, {path: '/'} );
//asamir to handle the checkbox search with the dropdown filter 
    if ( null != jq.cookie('trs-activity-filter') && jq('#post-refine').length ){

        jq('#post-refine select option[value="' + jq.cookie('trs-activity-filter') + '"]').prop( 'selected', true );

    }
    if ( null != jq.cookie('trs-activity-filter-checkbox') && jq.cookie('trs-activity-filter-checkbox').length ){
        var checkboxsvalues = jq.cookie( 'trs-activity-filter-checkbox');
        checkboxsvalues = checkboxsvalues.split(',');
        for(i=0 ; i< checkboxsvalues.length ; i++){
            if(jq('#activity_media_filter input[action="'+checkboxsvalues[i]+'"]').length > 0)
            jq('#activity_media_filter input[action="'+checkboxsvalues[i]+'"]')[0].checked = true;
        }
    }
	/* Activity Tab Set */
	if ( jq('div.activity-type-tabs').length ) {
		jq('div.activity-type-tabs li').each( function() {
			jq(this).removeClass('selected');
		});
		jq('li#activity-' + jq.cookie('trs-activity-scope') + ', div.contour-select li.current').addClass('selected');
	}
}

/* Setup object scope and filter based on the current cookie settings for the object. */
function trs_init_objects(objects) {
	jq(objects).each( function(i) {
		if ( null != jq.cookie('trs-' + objects[i] + '-filter') && jq('li#' + objects[i] + '-order-select select').length )
			jq('li#' + objects[i] + '-order-select select option[value="' + jq.cookie('trs-' + objects[i] + '-filter') + '"]').prop( 'selected', true );

		if ( null != jq.cookie('trs-' + objects[i] + '-scope') && jq('div.' + objects[i]).length ) {
			jq('div.contour-select li').each( function() {
				jq(this).removeClass('selected');
			});
			jq('div.contour-select li#' + objects[i] + '-' + jq.cookie('trs-' + objects[i] + '-scope') + ', div.contour-select#object-c li.current').addClass('selected');
		}
	});
}

/* Filter the current content list (groups/members/blogs/topics) */
function trs_filter_request( object, filter, scope, target, search_terms, page, extras ) {
	if ( 'activity' == object )
		return false;

	if ( jq.query.get('s') && !search_terms )
		search_terms = jq.query.get('s');

	if ( null == scope )
		scope = 'all';

	/* Save the settings we want to remain persistent to a cookie */
	jq.cookie( 'trs-' + object + '-scope', scope, {path: '/'} );
	jq.cookie( 'trs-' + object + '-filter', filter, {path: '/'} );
	jq.cookie( 'trs-' + object + '-extras', extras, {path: '/'} );

	/* Set the correct selected nav and filter */
	jq('div.contour-select li').each( function() {
		jq(this).removeClass('selected');
	});
	jq('div.contour-select li#' + object + '-' + scope + ', div.contour-select#object-c li.current').addClass('selected');
	jq('div.contour-select li.selected').addClass('loading');
	jq('div.contour-select select option[value="' + filter + '"]').prop( 'selected', true );

	if ( 'friends' == object )
		object = 'members';

	if ( trs_ajax_request )
		trs_ajax_request.abort();

	trs_ajax_request = jq.post( ajaxurl, {
		action: object + '_filter',
		'cookie': encodeURIComponent(document.cookie),
		'object': object,
		'filter': filter,
		'search_terms': search_terms,
		'scope': scope,
		'page': page,
		'extras': extras
	},
	function(response)
	{
		jq(target).fadeOut( 100, function() {
			jq(this).html(response);
			jq(this).fadeIn(100);
	 	});
		jq('div.contour-select li.selected').removeClass('loading');
	});
}

/* Activity Loop Requesting */
function trs_activity_request(scope, filter) {
	/* Save the type and filter to a session cookie */
jq.cookie( 'trs-activity-filter',filter, {path: '/'} );
var checkboxs ="";
    jq('#activity_media_filter input[type="checkbox"]').each(function(index,elem){
                if(elem.checked){
                        // if(filter == "-1" || filter== null){
                        //  filter = this.attributes['action'].value;
                        // }else{
                        //  filter += ","+this.attributes['action'].value;
                        // }
                        var v = this.attributes['action'].value;

                        if(checkboxs == "")
                                checkboxs = this.attributes['action'].value;
                        else
                                checkboxs +=","+ this.attributes['action'].value;
                }
        });
  jq.cookie( 'trs-activity-filter-checkbox',(checkboxs), {path: '/'} );
    jq.cookie( 'trs-activity-scope', scope, {path: '/'} );


    jq.cookie( 'trs-activity-oldestpage', 1, {path: '/'} );

	/* Remove selected and loading classes from tabs */
	jq('div.contour-select li').each( function() {
		jq(this).removeClass('selected loading');
	});
	/* Set the correct selected nav and filter */
	jq('li#activity-' + scope + ', div.contour-select li.current').addClass('selected');
	jq('div#object-c.contour-select li.selected, div.activity-type-tabs li.selected').addClass('loading');
	jq('#post-refine select option[value="' + filter + '"]').prop( 'selected', true );

	/* Reload the activity stream based on the selection */
	jq('.widget_trs_activity_widget h2 span.ajax-loader').show();

	if ( trs_ajax_request )
		trs_ajax_request.abort();
if(checkboxs != "")
{
    if(filter == "-1" || filter == null){
        filter  = checkboxs;
    }else{
        filter  +=","+ checkboxs;
    }
}
	trs_ajax_request = jq.post( ajaxurl, {
		action: 'activity_widget_filter',
		'cookie': encodeURIComponent(document.cookie),
		'_key_activity_filter': jq("input#_key_activity_filter").val(),
		'scope': scope,
		'filter': filter
	},
	function(response)
	{
		jq('.widget_trs_activity_widget h2 span.ajax-loader').hide();

		jq('div.activity').fadeOut( 100, function() {
			jq(this).html(response.contents);
			jq(this).fadeIn(100);

			/* Selectively hide comments */
			trs_dtheme_hide_comments();
		});

		/* Update the feed link */
		if ( null != response.feed_url )
			jq('.directory div#subnav li.feed a, .home-page div#subnav li.feed a').attr('href', response.feed_url);

		jq('div.contour-select li.selected').removeClass('loading');

	}, 'json' );
}

/* Hide long lists of activity comments, only show the latest five root comments. */
function trs_dtheme_hide_comments() {
	var comments_divs = jq('div.activity-comments');

	if ( !comments_divs.length )
		return false;

	comments_divs.each( function() {
		if ( jq(this).children('ul').children('li').length < 5 ) return;

		var comments_div = jq(this);
		var parent_li = comments_div.parents('ul#publish > li');
		var comment_lis = jq(this).children('ul').children('li');
		var comment_count = ' ';

		if ( jq('li#' + parent_li.attr('id') + ' a.acomment-reply span').length )
			var comment_count = jq('li#' + parent_li.attr('id') + ' a.acomment-reply span').html();

		comment_lis.each( function(i) {
			/* Show the latest 5 root comments */
			if ( i < comment_lis.length - 5 ) {
				jq(this).addClass('hidden');
				jq(this).toggle();

				if ( !i )
					jq(this).before( '<li class="show-all"><a href="#' + parent_li.attr('id') + '/show-all/" title="' + TRS_DTheme.show_all_comments + '">' + TRS_DTheme.show_all + ' ' + comment_count + ' ' + TRS_DTheme.comments + '</a></li>' );
			}
		});

	});
}

/* Helper Functions */

function checkAll() {
	var checkboxes = document.getElementsByTagName("input");
	for(var i=0; i<checkboxes.length; i++) {
		if(checkboxes[i].type == "checkbox") {
			if($("check_all").checked == "") {
				checkboxes[i].checked = "";
			}
			else {
				checkboxes[i].checked = "checked";
			}
		}
	}
}

function clear(container) {
	if( !document.getElementById(container) ) return;

	var container = document.getElementById(container);

	if ( radioButtons = container.getElementsByTagName('INPUT') ) {
		for(var i=0; i<radioButtons.length; i++) {
			radioButtons[i].checked = '';
		}
	}

	if ( options = container.getElementsByTagName('OPTION') ) {
		for(var i=0; i<options.length; i++) {
			options[i].selected = false;
		}
	}

	return;
}

///openup modal form
$('.openup').click(function (e) {
        e.preventDefault();
 $('#post-intro').fadeToggle(200);
 $(".openup").toggleClass('selected');
      $(".dim").addClass('selected', 100, "easeOutSine");

  $('.dim').css({    display:'inline',
 position: 'fixed',
 
});

 $('html, body').css({    overflow: 'hidden',});    });

$(document).click(function (e) {
        if ($(e.target).closest('#post-intro').length > 0 || $(e.target).closest('.openup').length > 0) return;
        $('#post-intro').fadeOut(200);

  $('.dim').css({    display:'none',});
 $('html, body').css({    overflow: 'visible',});    });


    $(".dim-close").click(function(){
      $('#post-intro').fadeOut(280);  

  $('.dim').css({    display:'none',});
 $('html, body').css({    overflow: 'visible',});  });


/* Searchable Activity */

jQuery(document).ready(function($) {
	// check if the members/groups/activity etc drop down is available?
	if ($('form#search-form #search-which').length) {

		// do we have some cookie set?
		if ($.cookie('post-search-terms')) {
			// select activity from dd only if we are on activity directory
			if ($("body.publish div.publish").get(0) && !$('body').hasClass('post-full')) {// is

				$('form#search-form #search-which option[value="activity"]').prop('selected', true);
				$('form#search-form #search-terms').val($.cookie('post-search-terms'));

			} else {
				// clear cookie
				$.cookie('post-search-terms', '', {
					path : '/'
				});
			}
		}
		// when the search submit is clicked
		$('#search-submit').click(function() {
			// get and set/remove the cookie with the search term
			if ($('form#search-form #search-which').val() == "activity") {
				var search_terms = $('form#search-form #search-terms').val();

				if (search_terms.length == 0)
					$.cookie('post-search-terms', '', {
						path : '/'
					});

				else
					// let us keep the term
					$.cookie('post-search-terms', search_terms, {
						path : '/'
					});

			} else {
				$.cookie('post-search-terms', '', {
					path : '/'
				});
			}

		})

	}

});


        
// ///////////////////////MEDIA FILE UPLOAD TO BE
// CHANGED////////////////////////////////////
/**
 * http://github.com/valums/file-uploader
 * 
 * Multiple file upload component with progress-bar, drag-and-drop. © 2010
 * Andrew Valums ( andrew(at)valums.com )
 * 
 * Licensed under GNU GPL 2 or later, see license.txt.
 */

 /*
    *Revised by Virginie LE GUEN BERTHEAUME
    * added the library 'loadImage' - by BlueImp
    * added the function 'scaleImageAndRotate' in the ImageResizer
    * added the function 'scaleImageRaw' in the ImageResizer
*/
! function(e) {
    "use strict";

    function t(e, i, a) {
        var o, n = document.createElement("img");
        return n.onerror = function(o) {
            return t.onerror(n, o, e, i, a)
        }, n.onload = function(o) {
            return t.onload(n, o, e, i, a)
        }, "string" == typeof e ? (t.fetchBlob(e, function(i) {
            i ? (e = i, o = t.createObjectURL(e)) : (o = e, a && a.crossOrigin && (n.crossOrigin = a.crossOrigin)), n.src = o
        }, a), n) : t.isInstanceOf("Blob", e) || t.isInstanceOf("File", e) ? (o = n._objectURL = t.createObjectURL(e)) ? (n.src = o, n) : t.readFile(e, function(e) {
            var t = e.target;
            t && t.result ? n.src = t.result : i && i(e)
        }) : void 0
    }

    function i(e, i) {
        !e._objectURL || i && i.noRevoke || (t.revokeObjectURL(e._objectURL), delete e._objectURL)
    }
    var a = e.createObjectURL && e || e.URL && URL.revokeObjectURL && URL || e.webkitURL && webkitURL;
    t.fetchBlob = function(e, t, i) {
        t()
    }, t.isInstanceOf = function(e, t) {
        return Object.prototype.toString.call(t) === "[object " + e + "]"
    }, t.transform = function(e, t, i, a, o) {
        i(e, o)
    }, t.onerror = function(e, t, a, o, n) {
        i(e, n), o && o.call(e, t)
    }, t.onload = function(e, a, o, n, r) {
        i(e, r), n && t.transform(e, r, n, o, {})
    }, t.createObjectURL = function(e) {
        return !!a && a.createObjectURL(e)
    }, t.revokeObjectURL = function(e) {
        return !!a && a.revokeObjectURL(e)
    }, t.readFile = function(t, i, a) {
        if (e.FileReader) {
            var o = new FileReader;
            if (o.onload = o.onerror = i, a = a || "readAsDataURL", o[a]) return o[a](t), o
        }
        return !1
    }, "function" == typeof define && define.amd ? define(function() {
        return t
    }) : "object" == typeof module && module.exports ? module.exports = t : e.loadImage = t
}("undefined" != typeof window && window || this),
function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["./load-image"], e) : e("object" == typeof module && module.exports ? require("./load-image") : window.loadImage)
}(function(e) {
    "use strict";
    var t = e.transform;
    e.transform = function(i, a, o, n, r) {
        t.call(e, e.scale(i, a, r), a, o, n, r)
    }, e.transformCoordinates = function() {}, e.getTransformedOptions = function(e, t) {
        var i, a, o, n, r = t.aspectRatio;
        if (!r) return t;
        i = {};
        for (a in t) t.hasOwnProperty(a) && (i[a] = t[a]);
        return i.crop = !0, o = e.naturalWidth || e.width, n = e.naturalHeight || e.height, o / n > r ? (i.maxWidth = n * r, i.maxHeight = n) : (i.maxWidth = o, i.maxHeight = o / r), i
    }, e.renderImageToCanvas = function(e, t, i, a, o, n, r, s, l, d) {
        return e.getContext("2d").drawImage(t, i, a, o, n, r, s, l, d), e
    }, e.hasCanvasOption = function(e) {
        return e.canvas || e.crop || !!e.aspectRatio
    }, e.scale = function(t, i, a) {
        function o() {
            var e = Math.max((l || v) / v, (d || P) / P);
            e > 1 && (v *= e, P *= e)
        }

        function n() {
            var e = Math.min((r || v) / v, (s || P) / P);
            e < 1 && (v *= e, P *= e)
        }
        i = i || {};
        var r, s, l, d, c, u, f, g, h, m, p, S = document.createElement("canvas"),
            b = t.getContext || e.hasCanvasOption(i) && S.getContext,
            y = t.naturalWidth || t.width,
            x = t.naturalHeight || t.height,
            v = y,
            P = x;
        if (b && (f = (i = e.getTransformedOptions(t, i, a)).left || 0, g = i.top || 0, i.sourceWidth ? (c = i.sourceWidth, void 0 !== i.right && void 0 === i.left && (f = y - c - i.right)) : c = y - f - (i.right || 0), i.sourceHeight ? (u = i.sourceHeight, void 0 !== i.bottom && void 0 === i.top && (g = x - u - i.bottom)) : u = x - g - (i.bottom || 0), v = c, P = u), r = i.maxWidth, s = i.maxHeight, l = i.minWidth, d = i.minHeight, b && r && s && i.crop ? (v = r, P = s, (p = c / u - r / s) < 0 ? (u = s * c / r, void 0 === i.top && void 0 === i.bottom && (g = (x - u) / 2)) : p > 0 && (c = r * u / s, void 0 === i.left && void 0 === i.right && (f = (y - c) / 2))) : ((i.contain || i.cover) && (l = r = r || l, d = s = s || d), i.cover ? (n(), o()) : (o(), n())), b) {
            if ((h = i.pixelRatio) > 1 && (S.style.width = v + "px", S.style.height = P + "px", v *= h, P *= h, S.getContext("2d").scale(h, h)), (m = i.downsamplingRatio) > 0 && m < 1 && v < c && P < u)
                for (; c * m > v;) S.width = c * m, S.height = u * m, e.renderImageToCanvas(S, t, f, g, c, u, 0, 0, S.width, S.height), f = 0, g = 0, c = S.width, u = S.height, (t = document.createElement("canvas")).width = c, t.height = u, e.renderImageToCanvas(t, S, 0, 0, c, u, 0, 0, c, u);
            return S.width = v, S.height = P, e.transformCoordinates(S, i), e.renderImageToCanvas(S, t, f, g, c, u, 0, 0, v, P)
        }
        return t.width = v, t.height = P, t
    }
}),
function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["./load-image"], e) : e("object" == typeof module && module.exports ? require("./load-image") : window.loadImage)
}(function(e) {
    "use strict";
    var t = "undefined" != typeof Blob && (Blob.prototype.slice || Blob.prototype.webkitSlice || Blob.prototype.mozSlice);
    e.blobSlice = t && function() {
        return (this.slice || this.webkitSlice || this.mozSlice).apply(this, arguments)
    }, e.metaDataParsers = {
        jpeg: {
            65505: []
        }
    }, e.parseMetaData = function(t, i, a, o) {
        a = a || {}, o = o || {};
        var n = this,
            r = a.maxMetaDataSize || 262144;
        !!("undefined" != typeof DataView && t && t.size >= 12 && "image/jpeg" === t.type && e.blobSlice) && e.readFile(e.blobSlice.call(t, 0, r), function(t) {
            if (t.target.error) return console.log(t.target.error), void i(o);
            var r, s, l, d, c = t.target.result,
                u = new DataView(c),
                f = 2,
                g = u.byteLength - 4,
                h = f;
            if (65496 === u.getUint16(0)) {
                for (; f < g && ((r = u.getUint16(f)) >= 65504 && r <= 65519 || 65534 === r);) {
                    if (s = u.getUint16(f + 2) + 2, f + s > u.byteLength) {
                        console.log("Invalid meta data: Invalid segment size.");
                        break
                    }
                    if (l = e.metaDataParsers.jpeg[r])
                        for (d = 0; d < l.length; d += 1) l[d].call(n, u, f, s, o, a);
                    h = f += s
                }!a.disableImageHead && h > 6 && (c.slice ? o.imageHead = c.slice(0, h) : o.imageHead = new Uint8Array(c).subarray(0, h))
            } else console.log("Invalid JPEG file: Missing JPEG marker.");
            i(o)
        }, "readAsArrayBuffer") || i(o)
    }, e.hasMetaOption = function(e) {
        return e && e.meta
    };
    var i = e.transform;
    e.transform = function(t, a, o, n, r) {
        e.hasMetaOption(a) ? e.parseMetaData(n, function(r) {
            i.call(e, t, a, o, n, r)
        }, a, r) : i.apply(e, arguments)
    }
}),
function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["./load-image", "./load-image-meta"], e) : "object" == typeof module && module.exports ? e(require("./load-image"), require("./load-image-meta")) : e(window.loadImage)
}(function(e) {
    "use strict";
    "undefined" != typeof fetch && "undefined" != typeof Request && (e.fetchBlob = function(t, i, a) {
        if (e.hasMetaOption(a)) return fetch(new Request(t, a)).then(function(e) {
            return e.blob()
        }).then(i).catch(function(e) {
            console.log(e), i()
        });
        i()
    })
}),
function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["./load-image", "./load-image-meta"], e) : "object" == typeof module && module.exports ? e(require("./load-image"), require("./load-image-meta")) : e(window.loadImage)
}(function(e) {
    "use strict";
    e.ExifMap = function() {
        return this
    }, e.ExifMap.prototype.map = {
        Orientation: 274
    }, e.ExifMap.prototype.get = function(e) {
        return this[e] || this[this.map[e]]
    }, e.getExifThumbnail = function(e, t, i) {
        var a, o, n; {
            if (i && !(t + i > e.byteLength)) {
                for (a = [], o = 0; o < i; o += 1) n = e.getUint8(t + o), a.push((n < 16 ? "0" : "") + n.toString(16));
                return "data:image/jpeg,%" + a.join("%")
            }
            console.log("Invalid Exif data: Invalid thumbnail data.")
        }
    }, e.exifTagTypes = {
        1: {
            getValue: function(e, t) {
                return e.getUint8(t)
            },
            size: 1
        },
        2: {
            getValue: function(e, t) {
                return String.fromCharCode(e.getUint8(t))
            },
            size: 1,
            ascii: !0
        },
        3: {
            getValue: function(e, t, i) {
                return e.getUint16(t, i)
            },
            size: 2
        },
        4: {
            getValue: function(e, t, i) {
                return e.getUint32(t, i)
            },
            size: 4
        },
        5: {
            getValue: function(e, t, i) {
                return e.getUint32(t, i) / e.getUint32(t + 4, i)
            },
            size: 8
        },
        9: {
            getValue: function(e, t, i) {
                return e.getInt32(t, i)
            },
            size: 4
        },
        10: {
            getValue: function(e, t, i) {
                return e.getInt32(t, i) / e.getInt32(t + 4, i)
            },
            size: 8
        }
    }, e.exifTagTypes[7] = e.exifTagTypes[1], e.getExifValue = function(t, i, a, o, n, r) {
        var s, l, d, c, u, f, g = e.exifTagTypes[o];
        if (g) {
            if (s = g.size * n, !((l = s > 4 ? i + t.getUint32(a + 8, r) : a + 8) + s > t.byteLength)) {
                if (1 === n) return g.getValue(t, l, r);
                for (d = [], c = 0; c < n; c += 1) d[c] = g.getValue(t, l + c * g.size, r);
                if (g.ascii) {
                    for (u = "", c = 0; c < d.length && "\0" !== (f = d[c]); c += 1) u += f;
                    return u
                }
                return d
            }
            console.log("Invalid Exif data: Invalid data offset.")
        } else console.log("Invalid Exif data: Invalid tag type.")
    }, e.parseExifTag = function(t, i, a, o, n) {
        var r = t.getUint16(a, o);
        n.exif[r] = e.getExifValue(t, i, a, t.getUint16(a + 2, o), t.getUint32(a + 4, o), o)
    }, e.parseExifTags = function(e, t, i, a, o) {
        var n, r, s;
        if (i + 6 > e.byteLength) console.log("Invalid Exif data: Invalid directory offset.");
        else {
            if (n = e.getUint16(i, a), !((r = i + 2 + 12 * n) + 4 > e.byteLength)) {
                for (s = 0; s < n; s += 1) this.parseExifTag(e, t, i + 2 + 12 * s, a, o);
                return e.getUint32(r, a)
            }
            console.log("Invalid Exif data: Invalid directory size.")
        }
    }, e.parseExifData = function(t, i, a, o, n) {
        if (!n.disableExif) {
            var r, s, l, d = i + 10;
            if (1165519206 === t.getUint32(i + 4))
                if (d + 8 > t.byteLength) console.log("Invalid Exif data: Invalid segment size.");
                else if (0 === t.getUint16(i + 8)) {
                switch (t.getUint16(d)) {
                    case 18761:
                        r = !0;
                        break;
                    case 19789:
                        r = !1;
                        break;
                    default:
                        return void console.log("Invalid Exif data: Invalid byte alignment marker.")
                }
                42 === t.getUint16(d + 2, r) ? (s = t.getUint32(d + 4, r), o.exif = new e.ExifMap, (s = e.parseExifTags(t, d, d + s, r, o)) && !n.disableExifThumbnail && (l = {
                    exif: {}
                }, s = e.parseExifTags(t, d, d + s, r, l), l.exif[513] && (o.exif.Thumbnail = e.getExifThumbnail(t, d + l.exif[513], l.exif[514]))), o.exif[34665] && !n.disableExifSub && e.parseExifTags(t, d, d + o.exif[34665], r, o), o.exif[34853] && !n.disableExifGps && e.parseExifTags(t, d, d + o.exif[34853], r, o)) : console.log("Invalid Exif data: Missing TIFF marker.")
            } else console.log("Invalid Exif data: Missing byte alignment offset.")
        }
    }, e.metaDataParsers.jpeg[65505].push(e.parseExifData)
}),
function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["./load-image", "./load-image-exif"], e) : "object" == typeof module && module.exports ? e(require("./load-image"), require("./load-image-exif")) : e(window.loadImage)
}(function(e) {
    "use strict";
    e.ExifMap.prototype.tags = {
        
            Orientation: {
                1: "top-left",
                2: "top-right",
                3: "bottom-right",
                4: "bottom-left",
                5: "left-top",
                6: "right-top",
                7: "right-bottom",
                8: "left-bottom"
            }
        }, e.ExifMap.prototype.getText = function(e) {
            var t = this.get(e);
            switch (e) {
              
                case "Orientation":
                    return this.stringValues[e][t];

            }
            return String(t)
        },
        function(e) {
            var t, i = e.tags,
                a = e.map;
            for (t in i) i.hasOwnProperty(t) && (a[i[t]] = t)
        }(e.ExifMap.prototype), e.ExifMap.prototype.getAll = function() {
            var e, t, i = {};
            for (e in this) this.hasOwnProperty(e) && (t = this.tags[e]) && (i[t] = this.getText(t));
            return i
        }
}),
function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["./load-image", "./load-image-scale", "./load-image-meta"], e) : "object" == typeof module && module.exports ? e(require("./load-image"), require("./load-image-scale"), require("./load-image-meta")) : e(window.loadImage)
}(function(e) {
    "use strict";
    var t = e.hasCanvasOption,
        i = e.hasMetaOption,
        a = e.transformCoordinates,
        o = e.getTransformedOptions;
    e.hasCanvasOption = function(i) {
        return !!i.orientation || t.call(e, i)
    }, e.hasMetaOption = function(t) {
        return t && !0 === t.orientation || i.call(e, t)
    }, e.transformCoordinates = function(t, i) {
        a.call(e, t, i);
        var o = t.getContext("2d"),
            n = t.width,
            r = t.height,
            s = t.style.width,
            l = t.style.height,
            d = i.orientation;
        if (d && !(d > 8)) switch (d > 4 && (t.width = r, t.height = n, t.style.width = l, t.style.height = s), d) {
            case 2:
                o.translate(n, 0), o.scale(-1, 1);
                break;
            case 3:
                o.translate(n, r), o.rotate(Math.PI);
                break;
            case 4:
                o.translate(0, r), o.scale(1, -1);
                break;
            case 5:
                o.rotate(.5 * Math.PI), o.scale(1, -1);
                break;
            case 6:
                o.rotate(.5 * Math.PI), o.translate(0, -r);
                break;
            case 7:
                o.rotate(.5 * Math.PI), o.translate(n, -r), o.scale(-1, 1);
                break;
            case 8:
                o.rotate(-.5 * Math.PI), o.translate(-n, 0)
        }
    }, e.getTransformedOptions = function(t, i, a) {
        var n, r, s = o.call(e, t, i),
            l = s.orientation;
        if (!0 === l && a && a.exif && (l = a.exif.get("Orientation")), !l || l > 8 || 1 === l) return s;
        n = {};
        for (r in s) s.hasOwnProperty(r) && (n[r] = s[r]);
        switch (n.orientation = l, l) {
            case 2:
                n.left = s.right, n.right = s.left;
                break;
            case 3:
                n.left = s.right, n.top = s.bottom, n.right = s.left, n.bottom = s.top;
                break;
            case 4:
                n.top = s.bottom, n.bottom = s.top;
                break;
            case 5:
                n.left = s.top, n.top = s.left, n.right = s.bottom, n.bottom = s.right;
                break;
            case 6:
                n.left = s.top, n.top = s.right, n.right = s.bottom, n.bottom = s.left;
                break;
            case 7:
                n.left = s.bottom, n.top = s.right, n.right = s.top, n.bottom = s.left;
                break;
            case 8:
                n.left = s.bottom, n.top = s.left, n.right = s.top, n.bottom = s.right
        }
        return n.orientation > 4 && (n.maxWidth = s.maxHeight, n.maxHeight = s.maxWidth, n.minWidth = s.minHeight, n.minHeight = s.minWidth, n.sourceWidth = s.sourceHeight, n.sourceHeight = s.sourceWidth), n
    }
});
var qq = qq || {};

/**
 * Adds all missing properties from second obj to first obj
 */
qq.extend = function(first, second) {
    for ( var prop in second) {
        first[prop] = second[prop];
    }
};

/**
 * Searches for a given element in the array, returns -1 if it is not present.
 * 
 * @param {Number}
 *            [from] The index at which to begin the search
 */
qq.indexOf = function(arr, elt, from) {
    if (arr.indexOf)
        return arr.indexOf(elt, from);

    from = from || 0;
    var len = arr.length;

    if (from < 0)
        from += len;

    for (; from < len; from++) {
        if (from in arr && arr[from] === elt) {
            return from;
        }
    }
    return -1;
};

qq.getUniqueId = (function() {
    var id = 0;
    return function() {
        return id++;
    };
})();

//
// Events

qq.attach = function(element, type, fn) {
    if (element.addEventListener) {
        element.addEventListener(type, fn, false);
    } else if (element.attachEvent) {
        element.attachEvent('on' + type, fn);
    }
};
qq.detach = function(element, type, fn) {
    if (element.removeEventListener) {
        element.removeEventListener(type, fn, false);
    } else if (element.attachEvent) {
        element.detachEvent('on' + type, fn);
    }
};

qq.preventDefault = function(e) {
    if (e.preventDefault) {
        e.preventDefault();
    } else {
        e.returnValue = false;
    }
};

//
// Node manipulations

/**
 * Insert node a before node b.
 */
qq.insertBefore = function(a, b) {
    b.parentNode.insertBefore(a, b);
};
qq.remove = function(element) {
    element.parentNode.removeChild(element);
};

qq.contains = function(parent, descendant) {
    // compareposition returns false in this case
    if (parent == descendant)
        return true;

    if (parent.contains) {
        return parent.contains(descendant);
    } else {
        return !!(descendant.compareDocumentPosition(parent) & 8);
    }
};

/**
 * Creates and returns element from html string Uses innerHTML to create an
 * element
 */
qq.toElement = (function() {
    var div = document.createElement('div');
    return function(html) {
        div.innerHTML = html;
        var element = div.firstChild;
        div.removeChild(element);
        return element;
    };
})();

//
// Node properties and attributes

/**
 * Sets styles for an element. Fixes opacity in IE6-8.
 */
qq.css = function(element, styles) {
    if (styles.opacity != null) {
        if (typeof element.style.opacity != 'string' && typeof (element.filters) != 'undefined') {
            styles.filter = 'alpha(opacity=' + Math.round(100 * styles.opacity) + ')';
        }
    }
    qq.extend(element.style, styles);
};
qq.hasClass = function(element, name) {
    var re = new RegExp('(^| )' + name + '( |$)');
    return re.test(element.className);
};
qq.addClass = function(element, name) {
    if (!qq.hasClass(element, name)) {
        element.className += ' ' + name;
    }
};
qq.removeClass = function(element, name) {
    var re = new RegExp('(^| )' + name + '( |$)');
    element.className = element.className.replace(re, ' ').replace(/^\s+|\s+$/g, "");
};
qq.setText = function(element, text) {
    element.innerText = text;
    element.textContent = text;
};

//
// Selecting elements

qq.children = function(element) {
    var children = [], child = element.firstChild;

    while (child) {
        if (child.nodeType == 1) {
            children.push(child);
        }
        child = child.nextSibling;
    }

    return children;
};

qq.getByClass = function(element, className) {
    if (element.querySelectorAll) {
        return element.querySelectorAll('.' + className);
    }

    var result = [];
    var candidates = element.getElementsByTagName("*");
    var len = candidates.length;

    for ( var i = 0; i < len; i++) {
        if (qq.hasClass(candidates[i], className)) {
            result.push(candidates[i]);
        }
    }
    return result;
};

/**
 * obj2url() takes a json-object as argument and generates a querystring. pretty
 * much like jQuery.param()
 * 
 * how to use:
 * 
 * `qq.obj2url({a:'b',c:'d'},'http://any.url/upload?otherParam=value');`
 * 
 * will result in:
 * 
 * `http://any.url/upload?otherParam=value&a=b&c=d`
 * 
 * @param Object
 *            JSON-Object
 * @param String
 *            current querystring-part
 * @return String encoded querystring
 */
qq.obj2url = function(obj, temp, prefixDone) {
    var uristrings = [], prefix = '&', add = function(nextObj, i) {
        var nextTemp = temp ? (/\[\]$/.test(temp)) // prevent double-encoding
        ? temp : temp + '[' + i + ']' : i;
        if ((nextTemp != 'undefined') && (i != 'undefined')) {
            uristrings.push((typeof nextObj === 'object') ? qq.obj2url(nextObj, nextTemp, true)
                    : (Object.prototype.toString.call(nextObj) === '[object Function]') ? encodeURIComponent(nextTemp) + '=' + encodeURIComponent(nextObj())
                            : encodeURIComponent(nextTemp) + '=' + encodeURIComponent(nextObj));
        }
    };

    if (!prefixDone && temp) {
        prefix = (/\?/.test(temp)) ? (/\?$/.test(temp)) ? '' : '&' : '?';
        uristrings.push(temp);
        uristrings.push(qq.obj2url(obj));
    } else if ((Object.prototype.toString.call(obj) === '[object Array]') && (typeof obj != 'undefined')) {
        // we wont use a for-in-loop on an array (performance)
        for ( var i = 0, len = obj.length; i < len; ++i) {
            add(obj[i], i);
        }
    } else if ((typeof obj != 'undefined') && (obj !== null) && (typeof obj === "object")) {
        // for anything else but a scalar, we will use for-in-loop
        for ( var i in obj) {
            add(obj[i], i);
        }
    } else {
        uristrings.push(encodeURIComponent(temp) + '=' + encodeURIComponent(obj));
    }

    return uristrings.join(prefix).replace(/^&/, '').replace(/%20/g, '+');
};

//
//
// Uploader Classes
//
//

var qq = qq || {};

/**
 * Creates upload button, validates upload, but doesn't create file list or dd.
 */
qq.FileUploaderBasic = function(o) {
    this._options = {
        // set to true to see the server response
        debug : false,
        action : '/server/upload',
        params : {},
        button : null,
        multiple : true,
        maxConnections : 3,
        // validation
        allowedExtensions : [],
        resize : false,
        maxwidth : null,
        sizeLimit : 0,
        minSizeLimit : 0,
        // events
        // return false to cancel submit
        onSubmit : function(id, fileName) {
        },
        onProgress : function(id, fileName, loaded, total) {
        },
        onComplete : function(id, fileName, responseJSON) {
        },
        onCancel : function(id, fileName) {
        },
        // messages
        messages : {
            typeError : "{file} has invalid extension. Only {extensions} are allowed.",
            sizeError : "{file} is too large, maximum file size is {sizeLimit}.",
            minSizeError : "{file} is too small, minimum file size is {minSizeLimit}.",
            emptyError : "{file} is empty, please select files again without it.",
            onLeave : "The files are being uploaded, if you leave now the upload will be cancelled."
        },
        showMessage : function(message) {
            alert(message);
        },
        scaling : {
            // send the original file as well
            sendOriginal : true,

            // fox orientation for scaled images
            orient : true,

            // If null, scaled image type will match reference image type. This
            // value will be referred to
            // for any size record that does not specific a type.
            defaultType : null,

            failureText : "Failed to scale",

            includeExif : false,

            // metadata about each requested scaled version
            sizes : []
        }
    };
    qq.extend(this._options, o);

    // number of files being uploaded
    this._filesInProgress = 0;
    this._handler = this._createUploadHandler();

    if (this._options.button) {
        this._button = this._createUploadButton(this._options.button);
    }

    this._preventLeaveInProgress();
    this._scaler = (qq.Scaler && new qq.Scaler(this._options.scaling, qq.bind(this.log, this))) || {};
    if (this._scaler.enabled) {
        this._customNewFileHandler = qq.bind(this._scaler.handleNewFile, this._scaler);
    }

};

qq.FileUploaderBasic.prototype = {
    setParams : function(params) {
        this._options.params = params;
    },
    getInProgress : function() {
        return this._filesInProgress;
    },
    _createUploadButton : function(element) {
        var self = this;

        return new qq.UploadButton({
            element : element,
            multiple : this._options.multiple && qq.UploadHandlerXhr.isSupported(),
            onChange : function(input) {
                self._onInputChange(input);
            }
        });
    },
    _createUploadHandler : function() {
        var self = this, handlerClass;

        if (qq.UploadHandlerXhr.isSupported()) {
            handlerClass = 'UploadHandlerXhr';
        } else {
            handlerClass = 'UploadHandlerForm';
        }

        var handler = new qq[handlerClass]({
            debug : this._options.debug,
            action : this._options.action,
            maxConnections : this._options.maxConnections,
            maxwidth : this._options.maxwidth,
            resize : this._options.resize,
            onProgress : function(id, fileName, loaded, total) {
                self._onProgress(id, fileName, loaded, total);
                self._options.onProgress(id, fileName, loaded, total);
            },
            onComplete : function(id, fileName, result) {
                self._onComplete(id, fileName, result);
                self._options.onComplete(id, fileName, result);
            },
            onCancel : function(id, fileName) {
                self._onCancel(id, fileName);
                self._options.onCancel(id, fileName);
            }
        });

        return handler;
    },
    _preventLeaveInProgress : function() {
        var self = this;

        qq.attach(window, 'beforeunload', function(e) {
            if (!self._filesInProgress) {
                return;
            }

            var e = e || window.event;
            // for ie, ff
            e.returnValue = self._options.messages.onLeave;
            // for webkit
            return self._options.messages.onLeave;
        });
    },
    _onSubmit : function(id, fileName) {
        this._filesInProgress++;
    },
    _onProgress : function(id, fileName, loaded, total) {
    },
    _onComplete : function(id, fileName, result) {
        this._filesInProgress--;
        if (result.error) {
            this._options.showMessage(result.error);
        }
    },
    _onCancel : function(id, fileName) {
        this._filesInProgress--;
    },
    _onInputChange : function(input) {
        if (this._handler instanceof qq.UploadHandlerXhr) {
            this._uploadFileList(input.files);
        } else {
            if (this._validateFile(input)) {
                this._uploadFile(input);
            }
        }
        this._button.reset();
    },
    _uploadFileList : function(files) {
        for ( var i = 0; i < files.length; i++) {
            if (!this._validateFile(files[i])) {
                return;
            }
        }

        for ( var i = 0; i < files.length; i++) {
            this._uploadFile(files[i]);
        }
    },
    _uploadFile : function(fileContainer) {
        var id = this._handler.add(fileContainer);
        var fileName = this._handler.getName(id);

        if (this._options.onSubmit(id, fileName) !== false) {
            this._onSubmit(id, fileName);
            this._handler.upload(id, this._options.params);
        }
    },
    _validateFile : function(file) {
        var name, size;

        if (file.value) {
            // it is a file input
            // get input value and remove path to normalize
            name = file.value.replace(/.*(\/|\\)/, "");
        } else {
            // fix missing properties in Safari
            name = file.fileName != null ? file.fileName : file.name;
            size = file.fileSize != null ? file.fileSize : file.size;
        }

        if (!this._isAllowedExtension(name)) {
            this._error('typeError', name);
            return false;

        } else if (size === 0) {
            this._error('emptyError', name);
            return false;

        } else if (size && this._options.sizeLimit && size > this._options.sizeLimit) {
            this._error('sizeError', name);
            return false;

        } else if (size && size < this._options.minSizeLimit) {
            this._error('minSizeError', name);
            return false;
        }

        return true;
    },
    _error : function(code, fileName) {
        var message = this._options.messages[code];
        function r(name, replacement) {
            message = message.replace(name, replacement);
        }

        r('{file}', this._formatFileName(fileName));
        r('{extensions}', this._options.allowedExtensions.join(', '));
        r('{sizeLimit}', this._formatSize(this._options.sizeLimit));
        r('{minSizeLimit}', this._formatSize(this._options.minSizeLimit));

        this._options.showMessage(message);
    },
    _formatFileName : function(name) {
        if (name.length > 33) {
            name = name.slice(0, 19) + '...' + name.slice(-13);
        }
        return name;
    },
    _isAllowedExtension : function(fileName) {
        var ext = (-1 !== fileName.indexOf('.')) ? fileName.replace(/.*[.]/, '').toLowerCase() : '';
        var allowed = this._options.allowedExtensions;

        if (!allowed.length) {
            return true;
        }

        for ( var i = 0; i < allowed.length; i++) {
            if (allowed[i].toLowerCase() == ext) {
                return true;
            }
        }

        return false;
    },
    _formatSize : function(bytes) {
        var i = -1;
        do {
            bytes = bytes / 1024;
            i++;
        } while (bytes > 99);

        return Math.max(bytes, 0.1).toFixed(1) + [
                'kB', 'MB', 'GB', 'TB', 'PB', 'EB'
        ][i];
    }
};

/**
 * Class that creates upload widget with drag-and-drop and file list
 * 
 * @inherits qq.FileUploaderBasic
 */
qq.FileUploader = function(o) {
    // call parent constructor
    qq.FileUploaderBasic.apply(this, arguments);

    // additional options
    qq.extend(this._options, {
        element : null,
        // if set, will be used instead of qq-upload-list in template
        listElement : null,

        template : '<div class="qq-uploader">' + '<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>'
                + '<div class="qq-upload-button">Upload a file</div>' + '<ul class="qq-upload-list"></ul>' + '</div>',

        // template for one item in file list
        fileTemplate : '<li>' + '<span class="qq-upload-file"></span>' + '<span class="qq-upload-spinner"></span>'
                + '<a class="qq-upload-cancel" href="#">Cancel</a>' + '<span class="qq-upload-failed-text">Failed</span>' + '</li>',

        classes : {
            // used to get elements from templates
            button : 'qq-upload-button',
            drop : 'qq-upload-drop-area',
            dropActive : 'qq-upload-drop-area-active',
            list : 'qq-upload-list',

            file : 'qq-upload-file',
            spinner : 'qq-upload-spinner',
            // size : 'qq-upload-size',
            cancel : 'qq-upload-cancel',

            // added to list item when upload completes
            // used in css to hide progress spinner
            success : 'qq-upload-success',
            fail : 'qq-upload-fail'
        }
    });
    // overwrite options with user supplied
    qq.extend(this._options, o);

    this._element = this._options.element;
    this._element.innerHTML = this._options.template;
    this._listElement = this._options.listElement || this._find(this._element, 'list');

    this._classes = this._options.classes;

    this._button = this._createUploadButton(this._find(this._element, 'button'));

    this._bindCancelEvent();
    this._setupDragDrop();
};

// inherit from Basic Uploader
qq.extend(qq.FileUploader.prototype, qq.FileUploaderBasic.prototype);

qq.extend(qq.FileUploader.prototype, {
    /**
     * Gets one of the elements listed in this._options.classes
     */
    _find : function(parent, type) {
        var element = qq.getByClass(parent, this._options.classes[type])[0];
        if (!element) {
            // throw new Error('element not found ' + type);
        }

        return element;
    },
    _setupDragDrop : function() {
        var self = this, dropArea = this._find(this._element, 'drop');

        var dz = new qq.UploadDropZone({
            element : dropArea,
            onEnter : function(e) {
                qq.addClass(dropArea, self._classes.dropActive);
                e.stopPropagation();
            },
            onLeave : function(e) {
                e.stopPropagation();
            },
            onLeaveNotDescendants : function(e) {
                qq.removeClass(dropArea, self._classes.dropActive);
            },
            onDrop : function(e) {
                dropArea.style.display = 'none';
                qq.removeClass(dropArea, self._classes.dropActive);
                self._uploadFileList(e.dataTransfer.files);
            }
        });

        dropArea.style.display = 'none';

        qq.attach(document, 'dragenter', function(e) {
            if (!dz._isValidFileDrag(e))
                return;

            dropArea.style.display = 'block';
        });
        qq.attach(document, 'dragleave', function(e) {
            if (!dz._isValidFileDrag(e))
                return;

            var relatedTarget = document.elementFromPoint(e.clientX, e.clientY);
            // only fire when leaving document out
            if (!relatedTarget || relatedTarget.nodeName == "HTML") {
                dropArea.style.display = 'none';
            }
        });
    },
    _onSubmit : function(id, fileName) {
        qq.FileUploaderBasic.prototype._onSubmit.apply(this, arguments);
        this._addToList(id, fileName);
    },
    _onProgress : function(id, fileName, loaded, total) {
        qq.FileUploaderBasic.prototype._onProgress.apply(this, arguments);

        var item = this._getItemByFileId(id);
        var size = this._find(item, 'size');
        if (size) {
            size.style.display = 'inline';
            var text;
            if (loaded != total) {
                text = Math.round(loaded / total * 100) + '% from ' + this._formatSize(total);
            } else {
                text = this._formatSize(total);
            }

            qq.setText(size, text);
        }

    },
    _onComplete : function(id, fileName, result) {
        qq.FileUploaderBasic.prototype._onComplete.apply(this, arguments);

        // mark completed
        var item = this._getItemByFileId(id);
        qq.remove(this._find(item, 'cancel'));
        qq.remove(this._find(item, 'spinner'));

        if (result.success) {
            qq.addClass(item, this._classes.success);
        } else {
            qq.addClass(item, this._classes.fail);
        }
    },
    _addToList : function(id, fileName) {
        var item = qq.toElement(this._options.fileTemplate);
        item.qqFileId = id;

        var fileElement = this._find(item, 'file');

        qq.setText(fileElement, this._formatFileName(fileName));

        var sizeElement = this._find(item, 'size');
        if (sizeElement)
            sizeElement.style.display = 'none';

        this._listElement.appendChild(item);
    },
    _getItemByFileId : function(id) {
        var item = this._listElement.firstChild;

        // there can't be txt nodes in dynamically created list
        // and we can use nextSibling
        while (item) {
            if (item.qqFileId == id)
                return item;
            item = item.nextSibling;
        }
    },
    /**
     * delegate click event for cancel link
     */
    _bindCancelEvent : function() {
        var self = this, list = this._listElement;

        qq.attach(list, 'click', function(e) {
            e = e || window.event;
            var target = e.target || e.srcElement;

            if (qq.hasClass(target, self._classes.cancel)) {
                qq.preventDefault(e);

                var item = target.parentNode;
                self._handler.cancel(item.qqFileId);
                qq.remove(item);
            }
        });
    }
});

qq.UploadDropZone = function(o) {
    this._options = {
        element : null,
        onEnter : function(e) {
        },
        onLeave : function(e) {
        },
        // is not fired when leaving element by hovering descendants
        onLeaveNotDescendants : function(e) {
        },
        onDrop : function(e) {
        }
    };
    qq.extend(this._options, o);

    this._element = this._options.element;

    this._disableDropOutside();
    this._attachEvents();
};

qq.UploadDropZone.prototype = {
    _disableDropOutside : function(e) {
        // run only once for all instances
        if (!qq.UploadDropZone.dropOutsideDisabled) {

            qq.attach(document, 'dragover', function(e) {
                if (e.dataTransfer) {
                    e.dataTransfer.dropEffect = 'none';
                    e.preventDefault();
                }
            });

            qq.UploadDropZone.dropOutsideDisabled = true;
        }
    },
    _attachEvents : function() {
        var self = this;

        qq.attach(self._element, 'dragover', function(e) {
            if (!self._isValidFileDrag(e))
                return;

            var effect = e.dataTransfer.effectAllowed;
            if (effect == 'move' || effect == 'linkMove') {
                e.dataTransfer.dropEffect = 'move'; // for FF (only move
                // allowed)
            } else {
                e.dataTransfer.dropEffect = 'copy'; // for Chrome
            }

            e.stopPropagation();
            e.preventDefault();
        });

        qq.attach(self._element, 'dragenter', function(e) {
            if (!self._isValidFileDrag(e))
                return;

            self._options.onEnter(e);
        });

        qq.attach(self._element, 'dragleave', function(e) {
            if (!self._isValidFileDrag(e))
                return;

            self._options.onLeave(e);

            var relatedTarget = document.elementFromPoint(e.clientX, e.clientY);
            // do not fire when moving a mouse over a descendant
            if (qq.contains(this, relatedTarget))
                return;

            self._options.onLeaveNotDescendants(e);
        });

        qq.attach(self._element, 'drop', function(e) {
            if (!self._isValidFileDrag(e))
                return;

            e.preventDefault();
            self._options.onDrop(e);
        });
    },
    _isValidFileDrag : function(e) {
        var dt = e.dataTransfer,
        // do not check dt.types.contains in webkit, because it crashes safari 4
        isWebkit = navigator.userAgent.indexOf("AppleWebKit") > -1;

        // dt.effectAllowed is none in Safari 5
        // dt.types.contains check is for firefox
        return dt && dt.effectAllowed != 'none' && (dt.files || (!isWebkit && dt.types.contains && dt.types.contains('Files')));

    }
};

qq.UploadButton = function(o) {
    this._options = {
        element : null,
        // if set to true adds multiple attribute to file input
        multiple : false,
        // name attribute of file input
        name : 'file',
        onChange : function(input) {
        },
        hoverClass : 'qq-upload-button-hover',
        focusClass : 'qq-upload-button-focus'
    };

    qq.extend(this._options, o);

    this._element = this._options.element;

    // make button suitable container for input
    qq.css(this._element, {
        position : 'relative',
        overflow : 'hidden',
        // Make sure browse button is in the right side
        // in Internet Explorer
        direction : 'ltr'
    });

    this._input = this._createInput();
};

qq.UploadButton.prototype = {
    /* returns file input element */
    getInput : function() {
        return this._input;
    },
    /* cleans/recreates the file input */
    reset : function() {
        if (this._input.parentNode) {
            qq.remove(this._input);
        }

        qq.removeClass(this._element, this._options.focusClass);
        this._input = this._createInput();
    },
    _createInput : function() {
        var input = document.createElement("input");

        if (this._options.multiple) {
            input.setAttribute("multiple", "multiple");
        }

        input.setAttribute("type", "file");
        input.setAttribute("name", this._options.name);

        qq.css(input, {
            position : 'absolute',
            // in Opera only 'browse' button
            // is clickable and it is located at
            // the right side of the input
            right : 0,
            top : 0,
            fontFamily : 'Arial',
            // 4 persons reported this, the max values that worked for them were
            // 243, 236, 236, 118
            fontSize : '118px',
            margin : 0,
            padding : 0,
            cursor : 'pointer',
            opacity : 0
        });

        this._element.appendChild(input);

        var self = this;
        qq.attach(input, 'change', function() {
            self._options.onChange(input);
        });

        qq.attach(input, 'mouseover', function() {
            qq.addClass(self._element, self._options.hoverClass);
        });
        qq.attach(input, 'mouseout', function() {
            qq.removeClass(self._element, self._options.hoverClass);
        });
        qq.attach(input, 'focus', function() {
            qq.addClass(self._element, self._options.focusClass);
        });
        qq.attach(input, 'blur', function() {
            qq.removeClass(self._element, self._options.focusClass);
        });

        // IE and Opera, unfortunately have 2 tab stops on file input
        // which is unacceptable in our case, disable keyboard access
        if (window.attachEvent) {
            // it is IE or Opera
            input.setAttribute('tabIndex', "-1");
        }

        return input;
    }
};

/**
 * Class for uploading files, uploading itself is handled by child classes
 */
qq.UploadHandlerAbstract = function(o) {
    this._options = {
        debug : false,
        action : '/upload.php',
        // maximum number of concurrent uploads
        maxConnections : 999,
        onProgress : function(id, fileName, loaded, total) {
        },
        onComplete : function(id, fileName, response) {
        },
        onCancel : function(id, fileName) {
        }
    };
    qq.extend(this._options, o);

    this._queue = [];
    // params for files in queue
    this._params = [];
};
qq.UploadHandlerAbstract.prototype = {
    log : function(str) {
        if (this._options.debug && window.console)
            console.log('[uploader] ' + str);
    },
    /**
     * Adds file or file input to the queue
     * 
     * @returns id
     */
    add : function(file) {
    },
    /**
     * Sends the file identified by id and additional query params to the server
     */
    upload : function(id, params) {
        var len = this._queue.push(id);

        var copy = {};
        qq.extend(copy, params);
        this._params[id] = copy;

        // if too many active uploads, wait...
        if (len <= this._options.maxConnections) {
            this._upload(id, this._params[id]);
        }
    },
    /**
     * Cancels file upload by id
     */
    cancel : function(id) {
        this._cancel(id);
        this._dequeue(id);
    },
    /**
     * Cancells all uploads
     */
    cancelAll : function() {
        for ( var i = 0; i < this._queue.length; i++) {
            this._cancel(this._queue[i]);
        }
        this._queue = [];
    },
    /**
     * Returns name of the file identified by id
     */
    getName : function(id) {
    },
    /**
     * Returns size of the file identified by id
     */
    getSize : function(id) {
    },
    /**
     * Returns id of files being uploaded or waiting for their turn
     */
    getQueue : function() {
        return this._queue;
    },
    /**
     * Actual upload method
     */
    _upload : function(id) {
    },
    /**
     * Actual cancel method
     */
    _cancel : function(id) {
    },
    /**
     * Removes element from queue, starts upload of next
     */
    _dequeue : function(id) {
        var i = qq.indexOf(this._queue, id);
        this._queue.splice(i, 1);

        var max = this._options.maxConnections;

        if (this._queue.length >= max) {
            var nextId = this._queue[max - 1];
            this._upload(nextId, this._params[nextId]);
        }
    }
};

/**
 * Class for uploading files using form and iframe
 * 
 * @inherits qq.UploadHandlerAbstract
 */
qq.UploadHandlerForm = function(o) {
    qq.UploadHandlerAbstract.apply(this, arguments);

    this._inputs = {};
};
// @inherits qq.UploadHandlerAbstract
qq.extend(qq.UploadHandlerForm.prototype, qq.UploadHandlerAbstract.prototype);

qq.extend(qq.UploadHandlerForm.prototype, {
    add : function(fileInput) {
        fileInput.setAttribute('name', 'qqfile');
        var id = 'qq-upload-handler-iframe' + qq.getUniqueId();

        this._inputs[id] = fileInput;

        // remove file input from DOM
        if (fileInput.parentNode) {
            qq.remove(fileInput);
        }

        return id;
    },
    getName : function(id) {
        // get input value and remove path to normalize
        return this._inputs[id].value.replace(/.*(\/|\\)/, "");
    },
    _cancel : function(id) {
        this._options.onCancel(id, this.getName(id));

        delete this._inputs[id];

        var iframe = document.getElementById(id);
        if (iframe) {
            // to cancel request set src to something else
            // we use src="javascript:false;" because it doesn't
            // trigger ie6 prompt on https
            iframe.setAttribute('src', 'javascript:false;');

            qq.remove(iframe);
        }
    },
    _upload : function(id, params) {
        var input = this._inputs[id];

        if (!input) {
            throw new Error('file with passed id was not added, or already uploaded or cancelled');
        }

        var fileName = this.getName(id);

        var iframe = this._createIframe(id);
        var form = this._createForm(iframe, params);
        form.appendChild(input);

        var self = this;
        this._attachLoadEvent(iframe, function() {
            self.log('iframe loaded');

            var response = self._getIframeContentJSON(iframe);

            self._options.onComplete(id, fileName, response);
            self._dequeue(id);

            delete self._inputs[id];
            // timeout added to fix busy state in FF3.6
            setTimeout(function() {
                qq.remove(iframe);
            }, 1);
        });

        form.submit();
        qq.remove(form);

        return id;
    },
    _attachLoadEvent : function(iframe, callback) {
        qq.attach(iframe, 'load', function() {
            // when we remove iframe from dom
            // the request stops, but in IE load
            // event fires
            if (!iframe.parentNode) {
                return;
            }

            // fixing Opera 10.53
            if (iframe.contentDocument && iframe.contentDocument.body && iframe.contentDocument.body.innerHTML == "false") {
                // In Opera event is fired second time
                // when body.innerHTML changed from false
                // to server response approx. after 1 sec
                // when we upload file with iframe
                return;
            }

            callback();
        });
    },
    /**
     * Returns json object received by iframe from server.
     */
    _getIframeContentJSON : function(iframe) {
        // iframe.contentWindow.document - for IE<7
        var doc = iframe.contentDocument ? iframe.contentDocument : iframe.contentWindow.document, response;

        this.log("converting iframe's innerHTML to JSON");
        this.log("innerHTML = " + doc.body.innerHTML);

        try {
            response = eval("(" + doc.body.innerHTML + ")");
        } catch (err) {
            response = {};
        }

        return response;
    },
    /**
     * Creates iframe with unique name
     */
    _createIframe : function(id) {
        // We can't use following code as the name attribute
        // won't be properly registered in IE6, and new window
        // on form submit will open
        // var iframe = document.createElement('iframe');
        // iframe.setAttribute('name', id);

        var iframe = qq.toElement('<iframe src="javascript:false;" name="' + id + '" />');
        // src="javascript:false;" removes ie6 prompt on https

        iframe.setAttribute('id', id);

        iframe.style.display = 'none';
        document.body.appendChild(iframe);

        return iframe;
    },
    /**
     * Creates form, that will be submitted to iframe
     */
    _createForm : function(iframe, params) {
        // We can't use the following code in IE6
        // var form = document.createElement('form');
        // form.setAttribute('method', 'post');
        // form.setAttribute('enctype', 'multipart/form-data');
        // Because in this case file won't be attached to request
        var form = qq.toElement('<form method="post" enctype="multipart/form-data"></form>');

        var queryString = qq.obj2url(params, this._options.action);

        form.setAttribute('action', queryString);
        form.setAttribute('target', iframe.name);
        form.style.display = 'none';
        document.body.appendChild(form);

        return form;
    }
});

/**
 * Class for uploading files using xhr
 * 
 * @inherits qq.UploadHandlerAbstract
 */
qq.UploadHandlerXhr = function(o) {
    qq.UploadHandlerAbstract.apply(this, arguments);

    this._files = [];
    this._xhrs = [];

    // current loaded size in bytes for each file
    this._loaded = [];
};

// static method
qq.UploadHandlerXhr.isSupported = function() {
    var input = document.createElement('input');
    input.type = 'file';

    return ('multiple' in input && typeof File != "undefined" && typeof (new XMLHttpRequest()).upload != "undefined");
};

// @inherits qq.UploadHandlerAbstract
qq.extend(qq.UploadHandlerXhr.prototype, qq.UploadHandlerAbstract.prototype)

qq.extend(qq.UploadHandlerXhr.prototype, {
    /**
     * Adds file to the queue Returns id to use with upload, cancel
     */
    add : function(file) {
        if (!(file instanceof File)) {
            throw new Error('Passed obj in not a File (in qq.UploadHandlerXhr)');
        }

        return this._files.push(file) - 1;
    },
    getName : function(id) {
        var file = this._files[id];
        // fix missing name in Safari 4
        return file.fileName != null ? file.fileName : file.name;
    },
    getSize : function(id) {
        var file = this._files[id];
        return file.fileSize != null ? file.fileSize : file.size;
    },
    /**
     * Returns uploaded bytes for file identified by id
     */
    getLoaded : function(id) {
        return this._loaded[id] || 0;
    },
    /**
     * Sends the file identified by id and additional query params to the server
     * 
     * @param {Object}
     *            params name-value string pairs
     */
    _upload : function(id, params) {

        var file = this._files[id], name = this.getName(id), size = this.getSize(id);

        this._loaded[id] = 0;
        // Ensure it's an image
        if (file.type.match(/image.*/) && this._options.resize /*&& ((typeof FileReader)!='undefined')*/) {
            // console.log('An image has been loaded');

            var that = this;
            var pself = self;

            var whenImageLoaded = function( dataUrl ) {                
                that._loaded[id] = 0;

                // build query string
                params = params || {};
                params['filename'] = name;
                params['dataurl'] = dataUrl;

                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(e) {
                    if (e.lengthComputable) {
                        that._loaded[id] = e.loaded;
                        that._options.onProgress(id, name, e.loaded, e.total);
                    }
                }, false);

                xhr.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        // Do something with download progress
                    }
                }, false);

                $.ajax({
                    contentType : 'multipart/form-data',
                    xhr : function() {
                        return xhr;
                    },
                    type : 'POST',
                    url : that._options.action,
                    data : params,
                    complete : function(data) {
                        that._onComplete(id, xhr);
                    }
                });
            };

            ImageResizer.scaleImageRaw( file , 0.89 , that._options.maxwidth ? that._options.maxwidth : 2000 , whenImageLoaded );

        } else {

            var xhr = this._xhrs[id] = new XMLHttpRequest();
            var self = this;

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    self._loaded[id] = e.loaded;
                    self._options.onProgress(id, name, e.loaded, e.total);
                }
            };

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    self._onComplete(id, xhr);
                }
            };

            // build query string
            params = params || {};
            params['qqfile'] = name;
            var queryString = qq.obj2url(params, this._options.action);

            xhr.open("POST", queryString, true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.setRequestHeader("X-File-Name", encodeURIComponent(name));
            xhr.setRequestHeader("Content-Type", "application/octet-stream");
            xhr.send(file);
        }

    },
    _onComplete : function(id, xhr) {
        // the request was aborted/cancelled
        if (!this._files[id])
            return;

        var name = this.getName(id);
        var size = this.getSize(id);

        this._options.onProgress(id, name, size, size);

        if (xhr.status == 200) {
            this.log("xhr - server response received");
            this.log("responseText = " + xhr.responseText);

            var response;

            try {
                response = eval("(" + xhr.responseText + ")");
            } catch (err) {
                response = {};
            }

            this._options.onComplete(id, name, response);

        } else {
            this._options.onComplete(id, name, {});
        }

        this._files[id] = null;
        this._xhrs[id] = null;
        this._dequeue(id);
    },
    _cancel : function(id) {
        this._options.onCancel(id, this.getName(id));

        this._files[id] = null;

        if (this._xhrs[id]) {
            this._xhrs[id].abort();
            this._xhrs[id] = null;
        }
    }
});

function getImageBase64URL(e){var t=document.createElement("canvas");return context=t.getContext("2d"),context.drawImage(e,100,100),t.toDataURL("image/jpeg")}
var ImageResizer = {
    scaleImage: function(t, a, e, h) {
        var i = document.createElement("canvas"),
            d = t.width,
            n = t.height;
       
        for (i.width = d, i.height = n, i.getContext("2d").drawImage(t, 0, 0, d, n); i.width >= 2 * e;) i = this.getHalfScaleCanvas(i);
        i.width > e && (i = this.scaleCanvasWithAlgorithm(e, i));
        var r = i.toDataURL("image/jpeg", .89);
        resized = (getImageBase64URL(t), r), h(resized);
    },
    scaleImageRaw: function(t, quality, maxwidth, h) { /** t is the file */
        loadImage(t,function(a,e){
            var t="";
            try{t=a.toDataURL("image/jpeg",.89)}
            catch(a){}
            if(""===t)
                try{var i=parseInt(e.exif.get("Orientation"));ImageResizer.scaleImageAndRotate(a,quality,maxwidth,i,function(a){t=a,h(t)})}
            catch(e){var n=document.createElement("canvas"),o=n.getContext("2d");n.width=a.width,n.height=a.height,o.drawImage(a,0,0),t=n.toDataURL(),h(t)}else h(t)
        },{maxWidth:maxwidth,canvas:!0,pixelRatio:window.devicePixelRatio,downsamplingRatio:.5,orientation:!0} );
    },
    scaleImageAndRotate: function(t, a, e, h, i) {
        var d = new Image;
        ImageResizer.scaleImage(t, a, e, function(t) {
            if (!/^[1-8]$/.test(h)) return i(t);
            d.onload = function() {
                i(function(t, a, e, h, i, d) {
                    null == e && (e = 0), null == h && (h = 0), null == i && (i = t.width), null == d && (d = t.height);
                    var n = document.createElement("canvas"),
                        r = n.getContext("2d");
                    switch (n.width = i, n.height = d, r.save(), +a) {
                        case 1:
                            break;
                        case 2:
                            r.translate(i, 0), r.scale(-1, 1);
                            break;
                        case 3:
                            r.translate(i, d), r.rotate(1 * Math.PI);
                            break;
                        case 4:
                            r.translate(0, d), r.scale(1, -1);
                            break;
                        case 5:
                            n.width = d, n.height = i, r.rotate(.5 * Math.PI), r.scale(1, -1);
                            break;
                        case 6:
                            n.width = d, n.height = i, r.rotate(.5 * Math.PI), r.translate(0, -d);
                            break;
                        case 7:
                            n.width = d, n.height = i, r.rotate(1.5 * Math.PI), r.translate(-i, d), r.scale(1, -1);
                            break;
                        case 8:
                            n.width = d, n.height = i, r.translate(0, i), r.rotate(1.5 * Math.PI)
                    }
                    return r.drawImage(t, e, h, i, d), r.restore(), n
                }(d, h, 0, 0, d.width, d.height).toDataURL("image/jpeg"))
            }, d.src = t
        })
    },
    scaleCanvasWithAlgorithm: function(t, a) {
        var e = document.createElement("canvas"),
            h = t / a.width;
        e.width = a.width * h, e.height = a.height * h;
        var i = a.getContext("2d").getImageData(0, 0, a.width, a.height),
            d = e.getContext("2d").createImageData(e.width, e.height);
        return this.applyBilinearInterpolation(i, d, h), e.getContext("2d").putImageData(d, 0, 0), e
    },
    getHalfScaleCanvas: function(t) {
        var a = document.createElement("canvas");
        return a.width = t.width / 2, a.height = t.height / 2, a.getContext("2d").drawImage(t, 0, 0, a.width, a.height), a
    },
    applyBilinearInterpolation: function(t, a, e) {
        function h(t, a, e, h, i, d) {
            var n = 1 - i,
                r = 1 - d;
            return t * n * r + a * i * r + e * n * d + h * i * d
        }
        var i, d, n, r, g, c, l, s, o, w, m, u, I, v, f, M, C, p, b;
        for (i = 0; i < a.height; ++i)
            for (n = i / e, r = Math.floor(n), g = Math.ceil(n) > t.height - 1 ? t.height - 1 : Math.ceil(n), d = 0; d < a.width; ++d) c = d / e, l = Math.floor(c), s = Math.ceil(c) > t.width - 1 ? t.width - 1 : Math.ceil(c), o = 4 * (d + a.width * i), w = 4 * (l + t.width * r), m = 4 * (s + t.width * r), u = 4 * (l + t.width * g), I = 4 * (s + t.width * g), v = c - l, f = n - r, M = h(t.data[w], t.data[m], t.data[u], t.data[I], v, f), a.data[o] = M, C = h(t.data[w + 1], t.data[m + 1], t.data[u + 1], t.data[I + 1], v, f), a.data[o + 1] = C, p = h(t.data[w + 2], t.data[m + 2], t.data[u + 2], t.data[I + 2], v, f), a.data[o + 2] = p, b = h(t.data[w + 3], t.data[m + 3], t.data[u + 3], t.data[I + 3], v, f), a.data[o + 3] = b
    }
};



if (typeof FileReader != 'undefined') {
    $(document).on('submit', '#portrait-upload-form', function(e) {

        if (!$('#action', '#portrait-upload-form').val() == 'trs_portrait_upload' || $('#file', '#portrait-upload-form').length == 0) {
            return;
        }

        e.preventDefault();

        $('#portrait-upload-form #file,#portrait-upload-form #upload').prop('disabled', true).addClass('loading');

        var filelist = $('#file', '#portrait-upload-form')[0].files;

        if (!filelist) {
            alert('Please select an Image file first!');
            return;
        }

        var file = filelist[0];

        var maxwidth = 700;
        var quality = 0.89;

        ImageResizer.scaleImageRaw( file , quality, maxwidth , function( dataUrl ) {
            var fd = new FormData();
            fd.append('_key', $('#_key', '#portrait-upload-form').val());
            fd.append('_http_referer', $('[name="_http_referer"]', '#portrait-upload-form').val());
            fd.append('file', dataURItoBlob(dataUrl), file.name);
            fd.append('action', 'trs_portrait_upload');
            fd.append('upload', 'Save');

            $.ajax({
                url : document.location.href,
                data : fd,
                processData : false,
                contentType : false,
                type : 'POST',
                success : function(data) {
                    $("body").html(data);
                }
            });
        })

    });
 }


if (typeof FileReader != 'undefined') {
    $(document).bind('submit', '#cover_edit', function(e) {

        if (!$('#action', '#cover_edit').val() == 'trs_upload_profile_cover' || $('#cover_upload', '#cover_edit').length == 0) {
            return;
        }

        e.preventDefault();

       // $('#cover_edit #cover_upload,#cover_edit #upload').prop('disabled', true).addClass('loading');

        var filelist = $('#cover_upload', '#cover_edit')[0].files;

        if (!filelist) {
            alert('Please select an Image file first!');
            return;
        }

        var file = filelist[0];

        var maxwidth = 1500;
        var quality = 0.89;

        ImageResizer.scaleImageRaw( file , quality, maxwidth , function( dataUrl ) {
            var fd = new FormData();
            fd.append('_key', $('#_key', '#cover_edit').val());
            fd.append('_http_referer', $('[name="_http_referer"]', '#cover_edit').val());
            fd.append('file', dataURItoBlob(dataUrl), file.name);
           fd.append('action', 'trs_upload_profile_cover');
            fd.append('trscp_save_submit', 'Save');

            $.ajax({
                url : document.location.href,
                data : fd,
                processData : false,
                contentType : false,
                type : 'POST',
                success : function(data) {
                    $("body").html(data);

                   }
            });
 // $(document).bind('submit',function(e) { return true;  });

         $(document).unbind('submit', '#cover_edit').submit();

        })


    });
             //$(this).unbind('submit', '#cover_edit').submit()
 }

function dataURItoBlob(t){for(var r=atob(t.split(",")[1]),e=t.split(",")[0].split(":")[1].split(";")[0],n=new ArrayBuffer(r.length),l=new Uint8Array(n),a=0;a<r.length;a++)l[a]=r.charCodeAt(a);return new Blob([n],{type:e})}

//var Turbolinks = require("turbolinks")
//Turbolinks.start()
/* ScrollTo plugin - just inline and minified */
;(function(d){var k=d.scrollTo=function(a,i,e){d(window).scrollTo(a,i,e)};k.defaults={axis:'xy',duration:parseFloat(d.fn.jquery)>=1.3?0:1};k.window=function(a){return d(window)._scrollable()};d.fn._scrollable=function(){return this.map(function(){var a=this,i=!a.nodeName||d.inArray(a.nodeName.toLowerCase(),['iframe','#document','html','body'])!=-1;if(!i)return a;var e=(a.contentWindow||a).document||a.ownerDocument||a;return d.browser.safari||e.compatMode=='BackCompat'?e.body:e.documentElement})};d.fn.scrollTo=function(n,j,b){if(typeof j=='object'){b=j;j=0}if(typeof b=='function')b={onAfter:b};if(n=='max')n=9e9;b=d.extend({},k.defaults,b);j=j||b.speed||b.duration;b.queue=b.queue&&b.axis.length>1;if(b.queue)j/=2;b.offset=p(b.offset);b.over=p(b.over);return this._scrollable().each(function(){var q=this,r=d(q),f=n,s,g={},u=r.is('html,body');switch(typeof f){case'number':case'string':if(/^([+-]=)?\d+(\.\d+)?(px|%)?$/.test(f)){f=p(f);break}f=d(f,this);case'object':if(f.is||f.style)s=(f=d(f)).offset()}d.each(b.axis.split(''),function(a,i){var e=i=='x'?'Left':'Top',h=e.toLowerCase(),c='scroll'+e,l=q[c],m=k.max(q,i);if(s){g[c]=s[h]+(u?0:l-r.offset()[h]);if(b.margin){g[c]-=parseInt(f.css('margin'+e))||0;g[c]-=parseInt(f.css('border'+e+'Width'))||0}g[c]+=b.offset[h]||0;if(b.over[h])g[c]+=f[i=='x'?'width':'height']()*b.over[h]}else{var o=f[h];g[c]=o.slice&&o.slice(-1)=='%'?parseFloat(o)/100*m:o}if(/^\d+$/.test(g[c]))g[c]=g[c]<=0?0:Math.min(g[c],m);if(!a&&b.queue){if(l!=g[c])t(b.onAfterFirst);delete g[c]}});t(b.onAfter);function t(a){r.animate(g,j,b.easing,a&&function(){a.call(this,n,b)})}}).end()};k.max=function(a,i){var e=i=='x'?'Width':'Height',h='scroll'+e;if(!d(a).is('html,body'))return a[h]-d(a)[e.toLowerCase()]();var c='client'+e,l=a.ownerDocument.documentElement,m=a.ownerDocument.body;return Math.max(l[h],m[h])-Math.min(l[c],m[c])};function p(a){return typeof a=='object'?a:{top:a,left:a}}})(jQuery);

/* jQuery Easing Plugin, v1.3 - http://gsgd.co.uk/sandbox/jquery/easing/ */
jQuery.easing.jswing=jQuery.easing.swing;jQuery.extend(jQuery.easing,{def:"easeOutQuad",swing:function(e,f,a,h,g){return jQuery.easing[jQuery.easing.def](e,f,a,h,g)},easeInQuad:function(e,f,a,h,g){return h*(f/=g)*f+a},easeOutQuad:function(e,f,a,h,g){return -h*(f/=g)*(f-2)+a},easeInOutQuad:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f+a}return -h/2*((--f)*(f-2)-1)+a},easeInCubic:function(e,f,a,h,g){return h*(f/=g)*f*f+a},easeOutCubic:function(e,f,a,h,g){return h*((f=f/g-1)*f*f+1)+a},easeInOutCubic:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f+a}return h/2*((f-=2)*f*f+2)+a},easeInQuart:function(e,f,a,h,g){return h*(f/=g)*f*f*f+a},easeOutQuart:function(e,f,a,h,g){return -h*((f=f/g-1)*f*f*f-1)+a},easeInOutQuart:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f+a}return -h/2*((f-=2)*f*f*f-2)+a},easeInQuint:function(e,f,a,h,g){return h*(f/=g)*f*f*f*f+a},easeOutQuint:function(e,f,a,h,g){return h*((f=f/g-1)*f*f*f*f+1)+a},easeInOutQuint:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f*f+a}return h/2*((f-=2)*f*f*f*f+2)+a},easeInSine:function(e,f,a,h,g){return -h*Math.cos(f/g*(Math.PI/2))+h+a},easeOutSine:function(e,f,a,h,g){return h*Math.sin(f/g*(Math.PI/2))+a},easeInOutSine:function(e,f,a,h,g){return -h/2*(Math.cos(Math.PI*f/g)-1)+a},easeInExpo:function(e,f,a,h,g){return(f==0)?a:h*Math.pow(2,10*(f/g-1))+a},easeOutExpo:function(e,f,a,h,g){return(f==g)?a+h:h*(-Math.pow(2,-10*f/g)+1)+a},easeInOutExpo:function(e,f,a,h,g){if(f==0){return a}if(f==g){return a+h}if((f/=g/2)<1){return h/2*Math.pow(2,10*(f-1))+a}return h/2*(-Math.pow(2,-10*--f)+2)+a},easeInCirc:function(e,f,a,h,g){return -h*(Math.sqrt(1-(f/=g)*f)-1)+a},easeOutCirc:function(e,f,a,h,g){return h*Math.sqrt(1-(f=f/g-1)*f)+a},easeInOutCirc:function(e,f,a,h,g){if((f/=g/2)<1){return -h/2*(Math.sqrt(1-f*f)-1)+a}return h/2*(Math.sqrt(1-(f-=2)*f)+1)+a},easeInElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return -(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e},easeOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return g*Math.pow(2,-10*h)*Math.sin((h*k-i)*(2*Math.PI)/j)+l+e},easeInOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k/2)==2){return e+l}if(!j){j=k*(0.3*1.5)}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}if(h<1){return -0.5*(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e}return g*Math.pow(2,-10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j)*0.5+l+e},easeInBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*(f/=h)*f*((g+1)*f-g)+a},easeOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*((f=f/h-1)*f*((g+1)*f+g)+1)+a},easeInOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}if((f/=h/2)<1){return i/2*(f*f*(((g*=(1.525))+1)*f-g))+a}return i/2*((f-=2)*f*(((g*=(1.525))+1)*f+g)+2)+a},easeInBounce:function(e,f,a,h,g){return h-jQuery.easing.easeOutBounce(e,g-f,0,h,g)+a},easeOutBounce:function(e,f,a,h,g){if((f/=g)<(1/2.75)){return h*(7.5625*f*f)+a}else{if(f<(2/2.75)){return h*(7.5625*(f-=(1.5/2.75))*f+0.75)+a}else{if(f<(2.5/2.75)){return h*(7.5625*(f-=(2.25/2.75))*f+0.9375)+a}else{return h*(7.5625*(f-=(2.625/2.75))*f+0.984375)+a}}}},easeInOutBounce:function(e,f,a,h,g){if(f<g/2){return jQuery.easing.easeInBounce(e,f*2,0,h,g)*0.5+a}return jQuery.easing.easeOutBounce(e,f*2-g,0,h,g)*0.5+h*0.5+a}});

/* jQuery Cookie plugin */
jQuery.cookie=function(name,value,options){if(typeof value!='undefined'){options=options||{};if(value===null){value='';options.expires=-1;}var expires='';if(options.expires&&(typeof options.expires=='number'||options.expires.toUTCString)){var date;if(typeof options.expires=='number'){date=new Date();date.setTime(date.getTime()+(options.expires*24*60*60*1000));}else{date=options.expires;}expires='; expires='+date.toUTCString();}var path=options.path?'; path='+(options.path):'';var domain=options.domain?'; domain='+(options.domain):'';var secure=options.secure?'; secure':'';document.cookie=[name,'=',encodeURIComponent(value),expires,path,domain,secure].join('');}else{var cookieValue=null;if(document.cookie&&document.cookie!=''){var cookies=document.cookie.split(';');for(var i=0;i<cookies.length;i++){var cookie=jQuery.trim(cookies[i]);if(cookie.substring(0,name.length+1)==(name+'=')){cookieValue=decodeURIComponent(cookie.substring(name.length+1));break;}}}return cookieValue;}};

/* jQuery querystring plugin */
eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('M 6(A){4 $11=A.11||\'&\';4 $V=A.V===r?r:j;4 $1p=A.1p===r?\'\':\'[]\';4 $13=A.13===r?r:j;4 $D=$13?A.D===j?"#":"?":"";4 $15=A.15===r?r:j;v.1o=M 6(){4 f=6(o,t){8 o!=1v&&o!==x&&(!!t?o.1t==t:j)};4 14=6(1m){4 m,1l=/\\[([^[]*)\\]/g,T=/^([^[]+)(\\[.*\\])?$/.1r(1m),k=T[1],e=[];19(m=1l.1r(T[2]))e.u(m[1]);8[k,e]};4 w=6(3,e,7){4 o,y=e.1b();b(I 3!=\'X\')3=x;b(y===""){b(!3)3=[];b(f(3,L)){3.u(e.h==0?7:w(x,e.z(0),7))}n b(f(3,1a)){4 i=0;19(3[i++]!=x);3[--i]=e.h==0?7:w(3[i],e.z(0),7)}n{3=[];3.u(e.h==0?7:w(x,e.z(0),7))}}n b(y&&y.T(/^\\s*[0-9]+\\s*$/)){4 H=1c(y,10);b(!3)3=[];3[H]=e.h==0?7:w(3[H],e.z(0),7)}n b(y){4 H=y.B(/^\\s*|\\s*$/g,"");b(!3)3={};b(f(3,L)){4 18={};1w(4 i=0;i<3.h;++i){18[i]=3[i]}3=18}3[H]=e.h==0?7:w(3[H],e.z(0),7)}n{8 7}8 3};4 C=6(a){4 p=d;p.l={};b(a.C){v.J(a.Z(),6(5,c){p.O(5,c)})}n{v.J(1u,6(){4 q=""+d;q=q.B(/^[?#]/,\'\');q=q.B(/[;&]$/,\'\');b($V)q=q.B(/[+]/g,\' \');v.J(q.Y(/[&;]/),6(){4 5=1e(d.Y(\'=\')[0]||"");4 c=1e(d.Y(\'=\')[1]||"");b(!5)8;b($15){b(/^[+-]?[0-9]+\\.[0-9]*$/.1d(c))c=1A(c);n b(/^[+-]?[0-9]+$/.1d(c))c=1c(c,10)}c=(!c&&c!==0)?j:c;b(c!==r&&c!==j&&I c!=\'1g\')c=c;p.O(5,c)})})}8 p};C.1H={C:j,1G:6(5,1f){4 7=d.Z(5);8 f(7,1f)},1h:6(5){b(!f(5))8 d.l;4 K=14(5),k=K[0],e=K[1];4 3=d.l[k];19(3!=x&&e.h!=0){3=3[e.1b()]}8 I 3==\'1g\'?3:3||""},Z:6(5){4 3=d.1h(5);b(f(3,1a))8 v.1E(j,{},3);n b(f(3,L))8 3.z(0);8 3},O:6(5,c){4 7=!f(c)?x:c;4 K=14(5),k=K[0],e=K[1];4 3=d.l[k];d.l[k]=w(3,e.z(0),7);8 d},w:6(5,c){8 d.N().O(5,c)},1s:6(5){8 d.O(5,x).17()},1z:6(5){8 d.N().1s(5)},1j:6(){4 p=d;v.J(p.l,6(5,7){1y p.l[5]});8 p},1F:6(Q){4 D=Q.B(/^.*?[#](.+?)(?:\\?.+)?$/,"$1");4 S=Q.B(/^.*?[?](.+?)(?:#.+)?$/,"$1");8 M C(Q.h==S.h?\'\':S,Q.h==D.h?\'\':D)},1x:6(){8 d.N().1j()},N:6(){8 M C(d)},17:6(){6 F(G){4 R=I G=="X"?f(G,L)?[]:{}:G;b(I G==\'X\'){6 1k(o,5,7){b(f(o,L))o.u(7);n o[5]=7}v.J(G,6(5,7){b(!f(7))8 j;1k(R,5,F(7))})}8 R}d.l=F(d.l);8 d},1B:6(){8 d.N().17()},1D:6(){4 i=0,U=[],W=[],p=d;4 16=6(E){E=E+"";b($V)E=E.B(/ /g,"+");8 1C(E)};4 1n=6(1i,5,7){b(!f(7)||7===r)8;4 o=[16(5)];b(7!==j){o.u("=");o.u(16(7))}1i.u(o.P(""))};4 F=6(R,k){4 12=6(5){8!k||k==""?[5].P(""):[k,"[",5,"]"].P("")};v.J(R,6(5,7){b(I 7==\'X\')F(7,12(5));n 1n(W,12(5),7)})};F(d.l);b(W.h>0)U.u($D);U.u(W.P($11));8 U.P("")}};8 M C(1q.S,1q.D)}}(v.1o||{});',62,106,'|||target|var|key|function|value|return|||if|val|this|tokens|is||length||true|base|keys||else||self||false|||push|jQuery|set|null|token|slice|settings|replace|queryObject|hash|str|build|orig|index|typeof|each|parsed|Array|new|copy|SET|join|url|obj|search|match|queryString|spaces|chunks|object|split|get||separator|newKey|prefix|parse|numbers|encode|COMPACT|temp|while|Object|shift|parseInt|test|decodeURIComponent|type|number|GET|arr|EMPTY|add|rx|path|addFields|query|suffix|location|exec|REMOVE|constructor|arguments|undefined|for|empty|delete|remove|parseFloat|compact|encodeURIComponent|toString|extend|load|has|prototype'.split('|'),0,{}))
