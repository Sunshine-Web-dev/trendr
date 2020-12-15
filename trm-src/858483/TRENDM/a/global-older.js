
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
			e.fadeOut(0, function() {
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
    if ( '-1' == window.location.search.indexOf('new') && jq('div.forums').length )
        jq('div#new-topic-post').hide();
    else
        jq('div#new-topic-post').show();

    /* Activity filter and scope set */
    trs_init_activity();

    /* Object filter and scope set. */
    var objects = [ 'members', 'groups', 'blogs', 'forums' ];
    trs_init_objects( objects );

    /* @mention Compose Scrolling */
    if ( jq.query.get('r') && jq('textarea#field').length ) {
        jq('#post-controls').animate({height:'40px'});
        jq("form#post-box textarea").animate({height:'50px'});
        jq.scrollTo( jq('textarea#field'), 500, { offset:-125, easing:'easeOutQuad' } );
        jq('textarea#field').focus();
    }

    /**** Activity Posting ********************************************************/

    /* Textarea focus */
    jq('#field').focus( function(){
     //   jq("#post-controls").animate({height:'40px'});
       // jq("form#post-box textarea").animate({height:'50px'});
        jq("#submit-post").prop("disabled", false);
    });
//asamir to handle the adv section
        jq('#isad').change(function(){
                if(this.checked){
                    jq('#duration').css('display','block');
                        jq('#period_in_min').val(60);
                }else{
                    jq('#duration').css('display','none');
                    jq('#period_in_min').val(0);
                }
        });
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
        var item_id = jq("#whats-new-post-in").val();
        var content = jq("textarea#field").val();

        /* Set object for non-profile posts */
        if ( item_id > 0 ) {
            object = jq("#whats-new-post-object").val();
        }
//asamir send period param to server
        jq.post( ajaxurl, {
            action: 'post_update',
            'cookie': encodeURIComponent(document.cookie.split("-"+location.pathname).join("")),
            '_key_post_update': jq("input#_key_post_update").val(),
            'content': content,
            'object': object,
            'item_id': item_id,
            'period_in_min':form.find('#period_in_min').val()
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

                if ( 0 != jq("div#latest-update").length ) {
                    var l = jq("ul#publish li.new-update .broadcast-field .broadcast-inn p").html();
                    var v = jq("ul#publish li.new-update .broadcast-field .broadcast-top p a.view").attr('href');

                    var ltext = jq("ul#publish li.new-update .broadcast-field .broadcast-inn p").text();

                    var u = '';
                    if ( ltext != '' )
                        u = '&quot;' + l + '&quot; ';

                    u += '<a href="' + v + '" rel="nofollow">' + TRS_DTheme.view + '</a>';

                    jq("div#latest-update").slideUp(300,function(){
                        jq("div#latest-update").html( u );
                        jq("div#latest-update").slideDown(300);
                    });
                }

                jq("li.new-update").hide().slideDown( 300 );
                jq("li.new-update").removeClass( 'new-update' );
                jq("textarea#field").val('');

                //asamir reset the view
                jq('#period_in_min').val(0);
                jq('#duration').css('display','none');
                jq('#isad')[0].checked = false;
            }

            //jq("#post-controls").animate({height:'0px'});
            //jq("form#post-box textarea").animate({height:'20px'});
            jq("#submit-post").prop("disabled", true).removeClass('loading');
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
    jq('#post-refine select ,  ul> li > #activity_media_filter > input[type="checkbox"]').change( function() {
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

                target.fadeOut( 0, function() {
                    jq(this).html(response);
                    jq(this).attr('title', 'fav' == type ? TRS_DTheme.remove_fav : TRS_DTheme.mark_as_fav);
                    jq(this).fadeIn(0);
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
                    target.parent().parent().parent().slideUp(0);
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
            jq("#skeleton li.infinite").addClass('loading');

            if ( null == jq.cookie('trs-activity-oldestpage') )
                jq.cookie('trs-activity-oldestpage', 1, {path: '/'} );

            var oldest_page = ( jq.cookie('trs-activity-oldestpage') * 1 ) + 1;

            jq.post( ajaxurl, {
                action: 'activity_get_older_updates',
                'cookie': encodeURIComponent(document.cookie.split("-"+location.pathname).join("")),
                'page': oldest_page
            },
            function(response)
            {
                jq("#skeleton li.infinite").removeClass('loading');
                jq.cookie( 'trs-activity-oldestpage', oldest_page, {path: '/'} );
                jq("#skeleton ul.publish-piece").append(response.contents);
                
                $('.following .broadcast-knobs').hide();
                $('.article-piece .activity').css('margin-top','-2px');

                target.parent().hide();
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
            jq(a_inner).slideUp(300).html(response).slideDown(300);
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
           // jq.scrollTo( form, 500, { offset:-100, easing:'easeOutQuad' } );
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
                    form.append( response.substr( 2, response.length ) ).hide().fadeIn( 0 );
                } else {
                    form.fadeOut( 0,
                        function() {
                            if ( 0 == form.parent().children('ul').length ) {
                                if ( form.parent().hasClass('activity-comments') )
                                    form.parent().prepend('<ul></ul>');
                                else
                                    form.parent().append('<ul></ul>');
                            }

                            form.parent().children('ul').append(response).hide().fadeIn( 0 );
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
   /* Deleting an activity comment */
        if ( target.hasClass('acomment-delete') ) {
            var link_href = target.attr('href');
            var comment_li = target.parent().parent();
            var form = comment_li.parents('div.activity-comments').children('form');

            var nonce = link_href.split('_key=');
                nonce = nonce[1];

            var comment_id = link_href.split('cid=');
                comment_id = comment_id[1].split('&');
                comment_id = comment_id[0];

            target.addClass('loading');

            /* Remove any error messages */
            jq('div.activity-comments ul div.error').remove();

            /* Reset the form position */
            comment_li.parents('div.activity-comments').append(form);

            jq.post( ajaxurl, {
                action: 'delete_activity_comment',
                'cookie': encodeURIComponent(document.cookie),
                '_key': nonce,
                'id': comment_id
            },
            function(response)
            {
                /* Check for errors and append if found. */
                if ( response[0] + response[1] == '-1' ) {
                    comment_li.prepend( response.substr( 2, response.length ) ).hide().fadeIn( 200 );
                } else {
                    var children = jq( 'li#' + comment_li.attr('id') + ' ul' ).children('li');
                    var child_count = 0;
                    jq(children).each( function() {
                        if ( !jq(this).is(':hidden') )
                            child_count++;
                    });
                    comment_li.fadeOut(0);

                    /* Decrease the "Reply (X)" button count */
                    var parent_li = comment_li.parents('ul#publish > li');
                    jq('li#' + parent_li.attr('id') + ' a.acomment-reply span').html( jq('li#' + parent_li.attr('id') + ' a.acomment-reply span').html() - ( 1 + child_count ) );
                }
            });

            return false;
        }
        /* Showing hidden comments - pause for half a second */
        if ( target.parent().hasClass('show-all') ) {
            target.parent().addClass('loading');

            setTimeout( function() {
                target.parent().parent().children('li').fadeIn(0, function() {
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

        if ( target.parent().parent().hasClass('pagination') && !target.parent().parent().hasClass('no-ajax') ) {
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

    /**** New Forum Directory Post **************************************/

    /* Hit the "New Topic" button on the forums directory page */
    jq('a.show-hide-new').click( function() {
        if ( !jq('div#new-topic-post').length )
            return false;

        if ( jq('div#new-topic-post').is(":visible") )
            jq('div#new-topic-post').slideUp(200);
        else
            jq('div#new-topic-post').slideDown(200, function() { jq('#topic_title').focus(); } );

        return false;
    });

    /* Cancel the posting of a new forum topic */
    jq('input#submit_topic_cancel').click( function() {
        if ( !jq('div#new-topic-post').length )
            return false;

        jq('div#new-topic-post').slideUp(200);
        return false;
    });

    /* Clicking a forum tag */
    jq('div#forum-directory-tags a').click( function() {
        trs_filter_request( 'forums', 'tags', jq.cookie('trs-forums-scope'), 'div.forums', jq(this).html().replace( /&nbsp;/g, '-' ), 1, jq.cookie('trs-forums-extras') );
        return false;
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
                li.children('div#message').hide().fadeIn(0);
            } else {
                button.fadeOut( 0, function() {
                    if ( jq(this).hasClass('accept') ) {
                        action_div.children('a.reject').hide();
                        jq(this).html( TRS_DTheme.accepted ).fadeIn(0);
                        jq(this).addClass('accepted');
                    } else {
                        action_div.children('a.accept').hide();
                        jq(this).html( TRS_DTheme.rejected ).fadeIn(0);
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
                jq(parentdiv).fadeOut(0,
                    function() {
                        parentdiv.removeClass('add_friend');
                        parentdiv.removeClass('loading');
                        parentdiv.addClass('pending');
                        parentdiv.fadeIn(0).html(response);
                    }
                );

            } else if ( action == 'remove' ) {
                jq(parentdiv).fadeOut(0,
                    function() {
                        parentdiv.removeClass('remove_friend');
                        parentdiv.removeClass('loading');
                        parentdiv.addClass('add');
                        parentdiv.fadeIn(0).html(response);
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
                jq(parentdiv).fadeOut(0,
                    function() {
                        parentdiv.fadeIn(0).html(response);
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
                        jq(this).parent().parent().fadeOut(0);
                });
            }

            jq('div#message').hide().slideDown(0);
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
                jq( 'div#sidebar div.error').hide().fadeIn( 0 );
            } else {
                jq('.notice').slideUp( 0 );
            }
        });
        return false;
    });

    /* Admin Bar & trm_list_pages Javascript IE6 hover class */
   // jq("#trm-admin-bar ul.main-nav li, #nav li").mouseover( function() {
        //jq(this).addClass('sfhover');
    //});

    //jq("#trm-admin-bar ul.main-nav li, #nav li").mouseout( function() {
       // jq(this).removeClass('sfhover');
    //});

    /* Clear TRS cookies on logout */
    jq('a.logout').click( function() {
        jq.cookie('trs-activity-scope', null, {path: '/'});
        jq.cookie('trs-activity-filter', null, {path: '/'});
//+ asamir add to hanlde the checkbox after refresh
        jq.cookie( 'trs-activity-filter-checkbox',null,{path:'/'});
        jq.cookie('trs-activity-oldestpage', null, {path: '/'});

        var objects = [ 'members', 'groups', 'blogs', 'forums' ];
        jq(objects).each( function(i) {
            jq.cookie('trs-' + objects[i] + '-scope', null, {path: '/'} );
            jq.cookie('trs-' + objects[i] + '-filter', null, {path: '/'} );
            jq.cookie('trs-' + objects[i] + '-extras', null, {path: '/'} );
        });
    });
});

/* Setup activity scope and filter based on the current cookie settings. */
function trs_init_activity() {
    /* Reset the page */
    jq.cookie( 'trs-activity-oldestpage', 1, {path: '/'} );
//asamir to handle the checkbox search with the dropdown filter
    if ( jq('#post-refine').length ){

        if((browser() == "Edge" || browser() == "IE") && null != jq.cookie('trs-activity-filter'+'-'+location.pathname)){
            jq('#post-refine select option[value="' + jq.cookie('trs-activity-filter'+'-'+location.pathname) + '"]').prop( 'selected', true );
        }else if(null != jq.cookie('trs-activity-filter')){
            jq('#post-refine select option[value="' + jq.cookie('trs-activity-filter') + '"]').prop( 'selected', true );
        }
    }
    if (jq('#activity_media_filter input').length ){
        var checkboxsvalues = "";
            if((browser() == "Edge" || browser() == "IE") && null != jq.cookie('trs-activity-filter-checkbox'+'-'+location.pathname)){
                      checkboxsvalues = jq.cookie( 'trs-activity-filter-checkbox'+'-'+location.pathname);
            }else if( null != jq.cookie('trs-activity-filter-checkbox')){
                  checkboxsvalues = jq.cookie( 'trs-activity-filter-checkbox');
            }
            if(checkboxsvalues != ""){
                    checkboxsvalues = checkboxsvalues.split(',');
                    for(i=0 ; i< checkboxsvalues.length ; i++){
                        if(jq('#activity_media_filter input[action="'+checkboxsvalues[i]+'"]').length > 0)
                        jq('#activity_media_filter input[action="'+checkboxsvalues[i]+'"]')[0].checked = true;
                    }
            }
    }
    /* Activity Tab Set */
    if ( (null != jq.cookie('trs-activity-scope') || null != jq.cookie('trs-activity-scope'+'-'+location.pathname)) && jq('div.activity-type-tabs').length ) {
        jq('div.activity-type-tabs li').each( function() {
            jq(this).removeClass('selected');
        });
if(browser() == "Edge" || browser() == "IE"){
    jq('li#activity-' + jq.cookie('trs-activity-scope'+'-'+location.pathname) + ', div.contour-select li.current').addClass('selected');
}else{
        jq('li#activity-' + jq.cookie('trs-activity-scope') + ', div.contour-select li.current').addClass('selected');
}

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
            jq('div.contour-select li#' + objects[i] + '-' + jq.cookie('trs-' + objects[i] + '-scope') + ', div.contour-select#object-nav li.current').addClass('selected');
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
    if(browser() == "Edge" || browser() == "IE"){
            jq.cookie( 'trs-' + object + '-scope'+'-'+location.pathname, scope );
            jq.cookie( 'trs-' + object + '-filter'+'-'+location.pathname, filter );
            jq.cookie( 'trs-' + object + '-extras', extras );
    }else{
            jq.cookie( 'trs-' + object + '-scope', scope, {path: location.pathname} );
            jq.cookie( 'trs-' + object + '-filter', filter, {path: location.pathname} );
            jq.cookie( 'trs-' + object + '-extras', extras, {path: '/'} );
        }
    /* Set the correct selected nav and filter */
    jq('div.contour-select li').each( function() {
        jq(this).removeClass('selected');
    });
    jq('div.contour-select li#' + object + '-' + scope + ', div.contour-select#object-nav li.current').addClass('selected');
    jq('div.contour-select li.selected').addClass('loading');
    jq('div.contour-select select option[value="' + filter + '"]').prop( 'selected', true );

    if ( 'friends' == object )
        object = 'members';

    if ( trs_ajax_request )
        trs_ajax_request.abort();

    trs_ajax_request = jq.post( ajaxurl, {
        action: object + '_filter',
        'cookie': encodeURIComponent(document.cookie.split("-"+location.pathname).join("")),
        'object': object,
        'filter': filter,
        'search_terms': search_terms,
        'scope': scope,
        'page': page,
        'extras': extras
    },
    function(response)
    {
        jq(target).fadeOut( 0, function() {
            jq(this).html(response);
            jq(this).fadeIn(0);
        });
        jq('div.contour-select li.selected').removeClass('loading');
    });
}

/* Activity Loop Requesting *   https://css-tricks.com/snippets/javascript/get-url-and-url-parts-in-javascript/ */

function trs_activity_request(scope, filter) {

var checkboxs ="";
    jq(' ul> li > #activity_media_filter > input[type="checkbox"]').each(function(index,elem){
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
        if(browser() == "Edge" || browser() == "IE"){
                jq.cookie( 'trs-activity-filter'+'-'+location.pathname, filter );
                jq.cookie( 'trs-activity-filter-checkbox'+'-'+location.pathname,(checkboxs) );
                jq.cookie( 'trs-activity-scope'+'-'+location.pathname, scope );

            }else if ($.browser.mozilla){

                jq.cookie( 'trs-activity-filter',filter, {path: "/"} );
            jq.cookie( 'trs-activity-filter-checkbox',(checkboxs), {path:   "/"} );
                jq.cookie( 'trs-activity-scope', scope, {path:  "/"} );

            //jq.cookie( 'trs-activity-filter',filter, {expires: 0,path: '/'} );
           // jq.cookie( 'trs-activity-filter-checkbox',(checkboxs), {expires: 0,path: '/'} );
            //jq.cookie( 'trs-activity-scope', scope, {expires: 0,path: '/'} );path: location.pathname.split('window.location.host') 
            }else  {
                                jq.cookie( 'trs-activity-filter',filter, {path: location.pathname.split('/') } );
            jq.cookie( 'trs-activity-filter-checkbox',(checkboxs), {path: location.pathname.split('/') } );
                jq.cookie( 'trs-activity-scope', scope, {path: location.pathname.split('/') } );
                
}
    jq.cookie( 'trs-activity-oldestpage', 1, {path: '/'} );

    /* Remove selected and loading classes from tabs */
    jq('div.contour-select li').each( function() {
        jq(this).removeClass('selected loading');
    });
    /* Set the correct selected nav and filter */
    jq('li#activity-' + scope + ', div.contour-select li.current').addClass('selected');
    jq('div#object-nav.contour-select li.selected, div.activity-type-tabs li.selected').addClass('loading');

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
        'cookie': encodeURIComponent(document.cookie.split("-"+location.pathname).join("")),
        '_key_activity_filter': jq("input#_key_activity_filter").val(),
        'scope': scope,
        'filter': filter
    },
    function(response)
    {

        jq('.widget_trs_activity_widget h2 span.ajax-loader').hide();

        jq('div.activity').fadeOut( 0, function() {
            jq(this).html(response.contents);
            jq(this).fadeIn(0);

            /* Selectively hide comments */
            trs_dtheme_hide_comments();
        });

        /* Upda   /* Update the feed link */
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
/**
 * Gets the browser name or returns an empty string if unknown.
 * This function also caches the result to provide for any
 * future calls this function has.
 *
 * @returns {string}
 */
var browser = function() {
    // Return cached result if avalible, else get result then cache it.
    if (browser.prototype._cachedResult)
        return browser.prototype._cachedResult;

    // Opera 8.0+
    var isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;

    // Firefox 1.0+
    var isFirefox = typeof InstallTrigger !== 'undefined';

    // Safari 3.0+ "[object HTMLElementConstructor]"
    var isSafari = /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || safari.pushNotification);

    // Internet Explorer 6-11
    var isIE = /*@cc_on!@*/false || !!document.documentMode;

    // Edge 20+
    var isEdge = !isIE && !!window.StyleMedia;

    // Chrome 1+
    var isChrome = !!window.chrome && !!window.chrome.webstore;

    // Blink engine detection
    var isBlink = (isChrome || isOpera) && !!window.CSS;

    return browser.prototype._cachedResult =
        isOpera ? 'Opera' :
        isFirefox ? 'Firefox' :
        isSafari ? 'Safari' :
        isChrome ? 'Chrome' :
        isIE ? 'IE' :
        isEdge ? 'Edge' :
        isBlink ? 'Blink' :
        "Don't know";
};


$(document).ready(function(){
    $("#notify").click(function(){
        $("#notify").toggleClass('selected', 100, "easeOutSine");
  });

});

$(function() {

    $("#notify").on('click', tapHandler);

    function tapHandler(event) {
        $('#notify dl li').toggle();


    }
});





        
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
        multiple : false,
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
                + '<div class="qq-upload-button">Photo/Video</div>' + '<ul class="qq-upload-list"></ul>' + '</div>',

        // template for one item in file list
        fileTemplate : '<li>' + '<span class="qq-upload-file"></span>' + '<span class="qq-upload-spinner"></span>'
                + '<a class="qq-upload-cancel" href="#"></a>' + '<span class="qq-upload-failed-text">Failed</span>' + '</li>',

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
        
        $('#submit-post').hide();

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
                        $("#med_cancel_action").show();

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






function dataURItoBlob(t){for(var r=atob(t.split(",")[1]),e=t.split(",")[0].split(":")[1].split(";")[0],n=new ArrayBuffer(r.length),l=new Uint8Array(n),a=0;a<r.length;a++)l[a]=r.charCodeAt(a);return new Blob([n],{type:e})}






(function(e) {
    var t, n, i, o, r, a, s, l = "Close",
        c = "BeforeClose",
        d = "AfterClose",
        u = "BeforeAppend",
        p = "MarkupParse",
        f = "Open",
        m = "Change",
        g = "mfp",
        h = "." + g,
        v = "mfp-ready",
        C = "mfp-removing",
        y = "mfp-prevent-close",
        w = function() {},
        b = !!window.jQuery,
        I = e(window),
        x = function(e, n) {
            t.ev.on(g + e + h, n)
        },
        k = function(t, n, i, o) {
            var r = document.createElement("div");
            return r.className = "mfp-" + t, i && (r.innerHTML = i), o ? n && n.appendChild(r) : (r = e(r), n && r.appendTo(n)), r
        },
        T = function(n, i) {
            t.ev.triggerHandler(g + n, i), t.st.callbacks && (n = n.charAt(0).toLowerCase() + n.slice(1), t.st.callbacks[n] && t.st.callbacks[n].apply(t, e.isArray(i) ? i : [i]))
        },
        E = function(n) {
            return n === s && t.currTemplate.closeBtn || (t.currTemplate.closeBtn = e(t.st.closeMarkup.replace("%title%", t.st.tClose)), s = n), t.currTemplate.closeBtn
        },
        _ = function() {
            e.magnificPopup.instance || (t = new w, t.init(), e.magnificPopup.instance = t)
        },
        S = function() {
            var e = document.createElement("p").style,
                t = ["ms", "O", "Moz", "Webkit"];
            if (void 0 !== e.transition) return !0;
            for (; t.length;)
                if (t.pop() + "Transition" in e) return !0;
            return !1
        };
    w.prototype = {
        constructor: w,
        init: function() {
            var n = navigator.appVersion;
            t.isIE7 = -1 !== n.indexOf("MSIE 7."), t.isIE8 = -1 !== n.indexOf("MSIE 8."), t.isLowIE = t.isIE7 || t.isIE8, t.isAndroid = /android/gi.test(n), t.isIOS = /iphone|ipad|ipod/gi.test(n), t.supportsTransition = S(), t.probablyMobile = t.isAndroid || t.isIOS || /(Opera Mini)|Kindle|webOS|BlackBerry|(Opera Mobi)|(Windows Phone)|IEMobile/i.test(navigator.userAgent), o = e(document), t.popupsCache = {}
        },
        open: function(n) {
            i || (i = e(document.body));
            var r;
            if (n.isObj === !1) {
                t.items = n.items.toArray(), t.index = 0;
                var s, l = n.items;
                for (r = 0; l.length > r; r++)
                    if (s = l[r], s.parsed && (s = s.el[0]), s === n.el[0]) {
                        t.index = r;
                        break
                    }
            } else t.items = e.isArray(n.items) ? n.items : [n.items], t.index = n.index || 0;
            if (t.isOpen) return t.updateItemHTML(), void 0;
            t.types = [], a = "", t.ev = n.mainEl && n.mainEl.length ? n.mainEl.eq(0) : o, n.key ? (t.popupsCache[n.key] || (t.popupsCache[n.key] = {}), t.currTemplate = t.popupsCache[n.key]) : t.currTemplate = {}, t.st = e.extend(!0, {}, e.magnificPopup.defaults, n), t.fixedContentPos = "auto" === t.st.fixedContentPos ? !t.probablyMobile : t.st.fixedContentPos, t.st.modal && (t.st.closeOnContentClick = !1, t.st.closeOnBgClick = !1, t.st.showCloseBtn = !1, t.st.enableEscapeKey = !1), t.bgOverlay || (t.bgOverlay = k("bg").on("click" + h, function() {
                t.close()
            }), t.wrap = k("wrap").attr("tabindex", -1).on("click" + h, function(e) {
                t._checkIfClose(e.target) && t.close()
            }), t.container = k("container", t.wrap)), t.contentContainer = k("skeleton"), t.st.preloader && (t.preloader = k("preloader", t.container, t.st.tLoading));
            var c = e.magnificPopup.modules;
            for (r = 0; c.length > r; r++) {
                var d = c[r];
                d = d.charAt(0).toUpperCase() + d.slice(1), t["init" + d].call(t)
            }
            T("BeforeOpen"), t.st.showCloseBtn && (t.st.closeBtnInside ? (x(p, function(e, t, n, i) {
                n.close_replaceWith = E(i.type)
            }), a += " mfp-close-btn-in") : t.wrap.append(E())), t.st.alignTop && (a += " mfp-align-top"), t.fixedContentPos ? t.wrap.css({
                overflow: t.st.overflowY,
                overflowX: "hidden",
                overflowY: t.st.overflowY
            }) : t.wrap.css({
                top: I.scrollTop(),
                position: "absolute"
            }), (t.st.fixedBgPos === !1 || "auto" === t.st.fixedBgPos && !t.fixedContentPos) && t.bgOverlay.css({
                height: o.height(),
                position: "absolute"
            }), t.st.enableEscapeKey && o.on("keyup" + h, function(e) {
                27 === e.keyCode && t.close()
            }), I.on("resize" + h, function() {
                t.updateSize()
            }), t.st.closeOnContentClick || (a += " mfp-auto-cursor"), a && t.wrap.addClass(a);
            var u = t.wH = I.height(),
                m = {};
            if (t.fixedContentPos && t._hasScrollBar(u)) {
                var g = t._getScrollbarSize();
                g && (m.marginRight = g)
            }
            t.fixedContentPos && (t.isIE7 ? e("body, html").css("overflow", "hidden") : m.overflow = "hidden");
            var C = t.st.mainClass;
            return t.isIE7 && (C += " mfp-ie7"), C && t._addClassToMFP(C), t.updateItemHTML(), T("BuildControls"), e("html").css(m), t.bgOverlay.add(t.wrap).prependTo(t.st.prependTo || i), t._lastFocusedEl = document.activeElement, setTimeout(function() {
                t.content ? (t._addClassToMFP(v), t._setFocus()) : t.bgOverlay.addClass(v), o.on("focusin" + h, t._onFocusIn)
            }, 16), t.isOpen = !0, t.updateSize(u), T(f), n
        },
        close: function() {
            t.isOpen && (T(c), t.isOpen = !1, t.st.removalDelay && !t.isLowIE && t.supportsTransition ? (t._addClassToMFP(C), setTimeout(function() {
                t._close()
            }, t.st.removalDelay)) : t._close())
        },
        _close: function() {
            T(l);
            var n = C + " " + v + " ";
            if (t.bgOverlay.detach(), t.wrap.detach(), t.container.empty(), t.st.mainClass && (n += t.st.mainClass + " "), t._removeClassFromMFP(n), t.fixedContentPos) {
                var i = {
                    marginRight: ""
                };
                t.isIE7 ? e("body, html").css("overflow", "") : i.overflow = "", e("html").css(i)
            }
            o.off("keyup" + h + " focusin" + h), t.ev.off(h), t.wrap.attr("class", "mfp-wrap").removeAttr("style"), t.bgOverlay.attr("class", "mfp-bg"), t.container.attr("class", "mfp-container"), !t.st.showCloseBtn || t.st.closeBtnInside && t.currTemplate[t.currItem.type] !== !0 || t.currTemplate.closeBtn && t.currTemplate.closeBtn.detach(), t._lastFocusedEl && e(t._lastFocusedEl).focus(), t.currItem = null, t.content = null, t.currTemplate = null, t.prevHeight = 0, T(d)
        },
        updateSize: function(e) {
            if (t.isIOS) {
                var n = document.documentElement.clientWidth / window.innerWidth,
                    i = window.innerHeight * n;
                t.wrap.css("height", i), t.wH = i
            } else t.wH = e || I.height();
            t.fixedContentPos || t.wrap.css("height", t.wH), T("Resize")
        },
        updateItemHTML: function() {
            var n = t.items[t.index];
            t.contentContainer.detach(), t.content && t.content.detach(), n.parsed || (n = t.parseEl(t.index));
            var i = n.type;
            if (T("BeforeChange", [t.currItem ? t.currItem.type : "", i]), t.currItem = n, !t.currTemplate[i]) {
                var o = t.st[i] ? t.st[i].markup : !1;
                T("FirstMarkupParse", o), t.currTemplate[i] = o ? e(o) : !0
            }
            r && r !== n.type && t.container.removeClass("mfp-" + r + "-holder");
            var a = t["get" + i.charAt(0).toUpperCase() + i.slice(1)](n, t.currTemplate[i]);
            t.appendContent(a, i), n.preloaded = !0, T(m, n), r = n.type, t.container.prepend(t.contentContainer), T("AfterChange")
        },
        appendContent: function(e, n) {
            t.content = e, e ? t.st.showCloseBtn && t.st.closeBtnInside && t.currTemplate[n] === !0 ? t.content.find(".mfp-close").length || t.content.append(E()) : t.content = e : t.content = "", T(u), t.container.addClass("mfp-" + n + "-holder"), t.contentContainer.append(t.content)
        },
        parseEl: function(n) {
            var i, o = t.items[n];
            if (o.tagName ? o = {
                    el: e(o)
                } : (i = o.type, o = {
                    data: o,
                    src: o.src
                }), o.el) {
                for (var r = t.types, a = 0; r.length > a; a++)
                    if (o.el.hasClass("mfp-" + r[a])) {
                        i = r[a];
                        break
                    }
                o.src = o.el.attr("data-mfp-src"), o.src || (o.src = o.el.attr("href"))
            }
            return o.type = i || t.st.type || "inline", o.index = n, o.parsed = !0, t.items[n] = o, T("ElementParse", o), t.items[n]
        },
        addGroup: function(e, n) {
            var i = function(i) {
                i.mfpEl = this, t._openClick(i, e, n)
            };
            n || (n = {});
            var o = "click.magnificPopup";
            n.mainEl = e, n.items ? (n.isObj = !0, e.off(o).on(o, i)) : (n.isObj = !1, n.delegate ? e.off(o).on(o, n.delegate, i) : (n.items = e, e.off(o).on(o, i)))
        },
        _openClick: function(n, i, o) {
            var r = void 0 !== o.midClick ? o.midClick : e.magnificPopup.defaults.midClick;
            if (r || 2 !== n.which && !n.ctrlKey && !n.metaKey) {
                var a = void 0 !== o.disableOn ? o.disableOn : e.magnificPopup.defaults.disableOn;
                if (a)
                    if (e.isFunction(a)) {
                        if (!a.call(t)) return !0
                    } else if (a > I.width()) return !0;
                n.type && (n.preventDefault(), t.isOpen && n.stopPropagation()), o.el = e(n.mfpEl), o.delegate && (o.items = i.find(o.delegate)), t.open(o)
            }
        },
        updateStatus: function(e, i) {
            if (t.preloader) {
                n !== e && t.container.removeClass("mfp-s-" + n), i || "loading" !== e || (i = t.st.tLoading);
                var o = {
                    status: e,
                    text: i
                };
                T("UpdateStatus", o), e = o.status, i = o.text, t.preloader.html(i), t.preloader.find("a").on("click", function(e) {
                    e.stopImmediatePropagation()
                }), t.container.addClass("mfp-s-" + e), n = e
            }
        },
        _checkIfClose: function(n) {
            if (!e(n).hasClass(y)) {
                var i = t.st.closeOnContentClick,
                    o = t.st.closeOnBgClick;
                if (i && o) return !0;
                if (!t.content || e(n).hasClass("mfp-close") || t.preloader && n === t.preloader[0]) return !0;
                if (n === t.content[0] || e.contains(t.content[0], n)) {
                    if (i) return !0
                } else if (o && e.contains(document, n)) return !0;
                return !1
            }
        },
        _addClassToMFP: function(e) {
            t.bgOverlay.addClass(e), t.wrap.addClass(e)
        },
        _removeClassFromMFP: function(e) {
            this.bgOverlay.removeClass(e), t.wrap.removeClass(e)
        },
        _hasScrollBar: function(e) {
            return (t.isIE7 ? o.height() : document.body.scrollHeight) > (e || I.height())
        },
        _setFocus: function() {
            (t.st.focus ? t.content.find(t.st.focus).eq(0) : t.wrap).focus()
        },
        _onFocusIn: function(n) {
            return n.target === t.wrap[0] || e.contains(t.wrap[0], n.target) ? void 0 : (t._setFocus(), !1)
        },
        _parseMarkup: function(t, n, i) {
            var o;
            i.data && (n = e.extend(i.data, n)), T(p, [t, n, i]), e.each(n, function(e, n) {
                if (void 0 === n || n === !1) return !0;
                if (o = e.split("_"), o.length > 1) {
                    var i = t.find(h + "-" + o[0]);
                    if (i.length > 0) {
                        var r = o[1];
                        "replaceWith" === r ? i[0] !== n[0] && i.replaceWith(n) : "img" === r ? i.is("img") ? i.attr("src", n) : i.replaceWith('<img src="' + n + '" class="' + i.attr("class") + '" />') : i.attr(o[1], n)
                    }
                } else t.find(h + "-" + e).html(n)
            })
        },
        _getScrollbarSize: function() {
            if (void 0 === t.scrollbarSize) {
                var e = document.createElement("div");
                e.id = "mfp-sbm", e.style.cssText = "width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;", document.body.appendChild(e), t.scrollbarSize = e.offsetWidth - e.clientWidth, document.body.removeChild(e)
            }
            return t.scrollbarSize
        }
    }, e.magnificPopup = {
        instance: null,
        proto: w.prototype,
        modules: [],
        open: function(t, n) {
            return _(), t = t ? e.extend(!0, {}, t) : {}, t.isObj = !0, t.index = n || 0, this.instance.open(t)
        },
        close: function() {
            return e.magnificPopup.instance && e.magnificPopup.instance.close()
        },
        registerModule: function(t, n) {
            n.options && (e.magnificPopup.defaults[t] = n.options), e.extend(this.proto, n.proto), this.modules.push(t)
        },
        defaults: {
            disableOn: 0,
            key: null,
            midClick: !1,
            mainClass: "",
            preloader: !0,
            focus: "",
            closeOnContentClick: !1,
            closeOnBgClick: !0,
            closeBtnInside: !0,
            showCloseBtn: !0,
            enableEscapeKey: !0,
            modal: !1,
            alignTop: !1,
            removalDelay: 0,
            prependTo: null,
            fixedContentPos: "auto",
            fixedBgPos: "auto",
            overflowY: "auto",
            closeMarkup: '<button title="%title%" type="button" class="mfp-close">&times;</button>',
            tClose: "Close (Esc)",
            tLoading: "Loading..."
        }
    }, e.fn.magnificPopup = function(n) {
        _();
        var i = e(this);
        if ("string" == typeof n)
            if ("open" === n) {
                var o, r = b ? i.data("magnificPopup") : i[0].magnificPopup,
                    a = parseInt(arguments[1], 10) || 0;
                r.items ? o = r.items[a] : (o = i, r.delegate && (o = o.find(r.delegate)), o = o.eq(a)), t._openClick({
                    mfpEl: o
                }, i, r)
            } else t.isOpen && t[n].apply(t, Array.prototype.slice.call(arguments, 1));
        else n = e.extend(!0, {}, n), b ? i.data("magnificPopup", n) : i[0].magnificPopup = n, t.addGroup(i, n);
        return i
    };
    var P, O, z, M = "inline",
        B = function() {
            z && (O.after(z.addClass(P)).detach(), z = null)
        };
    e.magnificPopup.registerModule(M, {
        options: {
            hiddenClass: "hide",
            markup: "",
            tNotFound: "Content not found"
        },
        proto: {
            initInline: function() {
                t.types.push(M), x(l + "." + M, function() {
                    B()
                })
            },
            getInline: function(n, i) {
                if (B(), n.src) {
                    var o = t.st.inline,
                        r = e(n.src);
                    if (r.length) {
                        var a = r[0].parentNode;
                        a && a.tagName && (O || (P = o.hiddenClass, O = k(P), P = "mfp-" + P), z = r.after(O).detach().removeClass(P)), t.updateStatus("ready")
                    } else t.updateStatus("error", o.tNotFound), r = e("<div>");
                    return n.inlineElement = r, r
                }
                return t.updateStatus("ready"), t._parseMarkup(i, {}, n), i
            }
        }
    });
    var F, H = "ajax",
        L = function() {
            F && i.removeClass(F)
        },
        A = function() {
            L(), t.req && t.req.abort()
        };
    e.magnificPopup.registerModule(H, {
        options: {
            settings: null,
            cursor: "mfp-ajax-cur",
            tError: '<a href="%url%">The content</a> could not be loaded.'
        },
        proto: {
            initAjax: function() {
                t.types.push(H), F = t.st.ajax.cursor, x(l + "." + H, A), x("BeforeChange." + H, A)
            },
            getAjax: function(n) {
                F && i.addClass(F), t.updateStatus("loading");
                var o = e.extend({
                    url: n.src,
                    success: function(i, o, r) {
                        var div = document.createElement('div');
                        div.innerHTML = i;
                        var scripts = div.getElementsByTagName('script');
                       
                        for(index=scripts.length-1;index>=0;index--)
                        {
                            scripts[index].parentNode.removeChild(scripts[index]);
                        }
                        
                        var a = {
                            data: div.innerHTML,
                            xhr: r
                        };
                        T("ParseAjax", a), t.appendContent(e(a.data), H), n.finished = !0, L(), t._setFocus(), setTimeout(function() {
                            t.wrap.addClass(v)
                        }, 16), t.updateStatus("ready"), T("AjaxContentAdded")
                    },
                    error: function() {
                        L(), n.finished = n.loadError = !0, t.updateStatus("error", t.st.ajax.tError.replace("%url%", n.src))
                    }
                }, t.st.ajax.settings);
                return t.req = e.ajax(o), ""
            }
        }
    });
    var j, N = function(n) {
        if (n.data && void 0 !== n.data.title) return n.data.title;
        var i = t.st.image.titleSrc;
        if (i) {
            if (e.isFunction(i)) return i.call(t, n);
            if (n.el) return n.el.attr(i) || ""
        }
        return ""
    };
    e.magnificPopup.registerModule("image", {
        options: {
            markup: '<div class="mfp-figure"><div class="mfp-close"></div><figure><div class="mfp-img"></div><figcaption><div class="mfp-bottom-bar"><div class="mfp-title"></div><div class="mfp-counter"></div></div></figcaption></figure></div>',
            cursor: "mfp-zoom-out-cur",
            titleSrc: "title",
            verticalFit: !0,
            tError: '<a href="%url%">The image</a> could not be loaded.'
        },
        proto: {
            initImage: function() {
                var e = t.st.image,
                    n = ".image";
                t.types.push("image"), x(f + n, function() {
                    "image" === t.currItem.type && e.cursor && i.addClass(e.cursor)
                }), x(l + n, function() {
                    e.cursor && i.removeClass(e.cursor), I.off("resize" + h)
                }), x("Resize" + n, t.resizeImage), t.isLowIE && x("AfterChange", t.resizeImage)
            },
            resizeImage: function() {
                var e = t.currItem;
                if (e && e.img && t.st.image.verticalFit) {
                    var n = 0;
                    t.isLowIE && (n = parseInt(e.img.css("padding-top"), 10) + parseInt(e.img.css("padding-bottom"), 10)), e.img.css("max-height", t.wH - n)
                }
            },
            _onImageHasSize: function(e) {
                e.img && (e.hasSize = !0, j && clearInterval(j), e.isCheckingImgSize = !1, T("ImageHasSize", e), e.imgHidden && (t.content && t.content.removeClass("mfp-loading"), e.imgHidden = !1))
            },
            findImageSize: function(e) {
                var n = 0,
                    i = e.img[0],
                    o = function(r) {
                        j && clearInterval(j), j = setInterval(function() {
                            return i.naturalWidth > 0 ? (t._onImageHasSize(e), void 0) : (n > 200 && clearInterval(j), n++, 3 === n ? o(10) : 40 === n ? o(50) : 100 === n && o(500), void 0)
                        }, r)
                    };
                o(1)
            },
            getImage: function(n, i) {
                var o = 0,
                    r = function() {
                        n && (n.img[0].complete ? (n.img.off(".mfploader"), n === t.currItem && (t._onImageHasSize(n), t.updateStatus("ready")), n.hasSize = !0, n.loaded = !0, T("ImageLoadComplete")) : (o++, 200 > o ? setTimeout(r, 100) : a()))
                    },
                    a = function() {
                        n && (n.img.off(".mfploader"), n === t.currItem && (t._onImageHasSize(n), t.updateStatus("error", s.tError.replace("%url%", n.src))), n.hasSize = !0, n.loaded = !0, n.loadError = !0)
                    },
                    s = t.st.image,
                    l = i.find(".mfp-img");
                if (l.length) {
                    var c = document.createElement("img");
                    c.className = "mfp-img", n.img = e(c).on("load.mfploader", r).on("error.mfploader", a), c.src = n.src, l.is("img") && (n.img = n.img.clone()), c = n.img[0], c.naturalWidth > 0 ? n.hasSize = !0 : c.width || (n.hasSize = !1)
                }
                return t._parseMarkup(i, {
                    title: N(n),
                    img_replaceWith: n.img
                }, n), t.resizeImage(), n.hasSize ? (j && clearInterval(j), n.loadError ? (i.addClass("mfp-loading"), t.updateStatus("error", s.tError.replace("%url%", n.src))) : (i.removeClass("mfp-loading"), t.updateStatus("ready")), i) : (t.updateStatus("loading"), n.loading = !0, n.hasSize || (n.imgHidden = !0, i.addClass("mfp-loading"), t.findImageSize(n)), i)
            }
        }
    });
    var W, R = function() {
        return void 0 === W && (W = void 0 !== document.createElement("p").style.MozTransform), W
    };
    e.magnificPopup.registerModule("zoom", {
        options: {
            enabled: !1,
            easing: "ease-in-out",
            duration: 300,
            opener: function(e) {
                return e.is("img") ? e : e.find("img")
            }
        },
        proto: {
            initZoom: function() {
                var e, n = t.st.zoom,
                    i = ".zoom";
                if (n.enabled && t.supportsTransition) {
                    var o, r, a = n.duration,
                        s = function(e) {
                            var t = e.clone().removeAttr("style").removeAttr("class").addClass("mfp-animated-image"),
                                i = "all " + n.duration / 1e3 + "s " + n.easing,
                                o = {
                                    position: "fixed",
                                    zIndex: 9999,
                                    left: 0,
                                    top: 0,
                                    "-webkit-backface-visibility": "hidden"
                                },
                                r = "transition";
                            return o["-webkit-" + r] = o["-moz-" + r] = o["-o-" + r] = o[r] = i, t.css(o), t
                        },
                        d = function() {
                            t.content.css("visibility", "visible")
                        };
                    x("BuildControls" + i, function() {
                        if (t._allowZoom()) {
                            if (clearTimeout(o), t.content.css("visibility", "hidden"), e = t._getItemToZoom(), !e) return d(), void 0;
                            r = s(e), r.css(t._getOffset()), t.wrap.append(r), o = setTimeout(function() {
                                r.css(t._getOffset(!0)), o = setTimeout(function() {
                                    d(), setTimeout(function() {
                                        r.remove(), e = r = null, T("ZoomAnimationEnded")
                                    }, 16)
                                }, a)
                            }, 16)
                        }
                    }), x(c + i, function() {
                        if (t._allowZoom()) {
                            if (clearTimeout(o), t.st.removalDelay = a, !e) {
                                if (e = t._getItemToZoom(), !e) return;
                                r = s(e)
                            }
                            r.css(t._getOffset(!0)), t.wrap.append(r), t.content.css("visibility", "hidden"), setTimeout(function() {
                                r.css(t._getOffset())
                            }, 16)
                        }
                    }), x(l + i, function() {
                        t._allowZoom() && (d(), r && r.remove(), e = null)
                    })
                }
            },
            _allowZoom: function() {
                return "image" === t.currItem.type
            },
            _getItemToZoom: function() {
                return t.currItem.hasSize ? t.currItem.img : !1
            },
            _getOffset: function(n) {
                var i;
                i = n ? t.currItem.img : t.st.zoom.opener(t.currItem.el || t.currItem);
                var o = i.offset(),
                    r = parseInt(i.css("padding-top"), 10),
                    a = parseInt(i.css("padding-bottom"), 10);
                o.top -= e(window).scrollTop() - r;
                var s = {
                    width: i.width(),
                    height: (b ? i.innerHeight() : i[0].offsetHeight) - a - r
                };
                return R() ? s["-moz-transform"] = s.transform = "translate(" + o.left + "px," + o.top + "px)" : (s.left = o.left, s.top = o.top), s
            }
        }
    });
    var Z = "iframe",
        q = "//about:blank",
        D = function(e) {
            if (t.currTemplate[Z]) {
                var n = t.currTemplate[Z].find("iframe");
                n.length && (e || (n[0].src = q), t.isIE8 && n.css("display", e ? "block" : "none"))
            }
        };
    e.magnificPopup.registerModule(Z, {
        options: {
            markup: '<div class="mfp-iframe-scaler"><div class="mfp-close"></div><iframe class="mfp-iframe" src="//about:blank" frameborder="0" allowfullscreen></iframe></div>',
            srcAction: "iframe_src",
            patterns: {
                youtube: {
                    index: "youtube.com",
                    id: "v=",
                    src: "//www.youtube.com/embed/%id%?autoplay=1"
                },
                vimeo: {
                    index: "vimeo.com/",
                    id: "/",
                    src: "//player.vimeo.com/video/%id%?autoplay=1"
                },
                gmaps: {
                    index: "//maps.google.",
                    src: "%id%&output=embed"
                }
            }
        },
        proto: {
            initIframe: function() {
                t.types.push(Z), x("BeforeChange", function(e, t, n) {
                    t !== n && (t === Z ? D() : n === Z && D(!0))
                }), x(l + "." + Z, function() {
                    D()
                })
            },
            getIframe: function(n, i) {
                var o = n.src,
                    r = t.st.iframe;
                e.each(r.patterns, function() {
                    return o.indexOf(this.index) > -1 ? (this.id && (o = "string" == typeof this.id ? o.substr(o.lastIndexOf(this.id) + this.id.length, o.length) : this.id.call(this, o)), o = this.src.replace("%id%", o), !1) : void 0
                });
                var a = {};
                return r.srcAction && (a[r.srcAction] = o), t._parseMarkup(i, a, n), t.updateStatus("ready"), i
            }
        }
    });
    var K = function(e) {
            var n = t.items.length;
            return e > n - 1 ? e - n : 0 > e ? n + e : e
        },
        Y = function(e, t, n) {
            return e.replace(/%curr%/gi, t + 1).replace(/%total%/gi, n)
        };
    e.magnificPopup.registerModule("gallery", {
        options: {
            enabled: !1,
            arrowMarkup: '<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>',
            preload: [0, 2],
            navigateByImgClick: !0,
            arrows: !0,
            tPrev: "Previous (Left arrow key)",
            tNext: "Next (Right arrow key)",
            tCounter: "%curr% of %total%"
        },
        proto: {
            initGallery: function() {
                var n = t.st.gallery,
                    i = ".mfp-gallery",
                    r = Boolean(e.fn.mfpFastClick);
                return t.direction = !0, n && n.enabled ? (a += " mfp-gallery", x(f + i, function() {
                    n.navigateByImgClick && t.wrap.on("click" + i, ".mfp-img", function() {
                        return t.items.length > 1 ? (t.next(), !1) : void 0
                    }), o.on("keydown" + i, function(e) {
                        37 === e.keyCode ? t.prev() : 39 === e.keyCode && t.next()
                    })
                }), x("UpdateStatus" + i, function(e, n) {
                    n.text && (n.text = Y(n.text, t.currItem.index, t.items.length))
                }), x(p + i, function(e, i, o, r) {
                    var a = t.items.length;
                    o.counter = a > 1 ? Y(n.tCounter, r.index, a) : ""
                }), x("BuildControls" + i, function() {
                    if (t.items.length > 1 && n.arrows && !t.arrowLeft) {
                        var i = n.arrowMarkup,
                            o = t.arrowLeft = e(i.replace(/%title%/gi, n.tPrev).replace(/%dir%/gi, "left")).addClass(y),
                            a = t.arrowRight = e(i.replace(/%title%/gi, n.tNext).replace(/%dir%/gi, "right")).addClass(y),
                            s = r ? "mfpFastClick" : "click";
                        o[s](function() {
                            t.prev()
                        }), a[s](function() {
                            t.next()
                        }), t.isIE7 && (k("b", o[0], !1, !0), k("a", o[0], !1, !0), k("b", a[0], !1, !0), k("a", a[0], !1, !0)), t.container.append(o.add(a))
                    }
                }), x(m + i, function() {
                    t._preloadTimeout && clearTimeout(t._preloadTimeout), t._preloadTimeout = setTimeout(function() {
                        t.preloadNearbyImages(), t._preloadTimeout = null
                    }, 16)
                }), x(l + i, function() {
                    o.off(i), t.wrap.off("click" + i), t.arrowLeft && r && t.arrowLeft.add(t.arrowRight).destroyMfpFastClick(), t.arrowRight = t.arrowLeft = null
                }), void 0) : !1
            },
            next: function() {
                t.direction = !0, t.index = K(t.index + 1), t.updateItemHTML()
            },
            prev: function() {
                t.direction = !1, t.index = K(t.index - 1), t.updateItemHTML()
            },
            goTo: function(e) {
                t.direction = e >= t.index, t.index = e, t.updateItemHTML()
            },
            preloadNearbyImages: function() {
                var e, n = t.st.gallery.preload,
                    i = Math.min(n[0], t.items.length),
                    o = Math.min(n[1], t.items.length);
                for (e = 1;
                    (t.direction ? o : i) >= e; e++) t._preloadItem(t.index + e);
                for (e = 1;
                    (t.direction ? i : o) >= e; e++) t._preloadItem(t.index - e)
            },
            _preloadItem: function(n) {
                if (n = K(n), !t.items[n].preloaded) {
                    var i = t.items[n];
                    i.parsed || (i = t.parseEl(n)), T("LazyLoad", i), "image" === i.type && (i.img = e('<img class="mfp-img" />').on("load.mfploader", function() {
                        i.hasSize = !0
                    }).on("error.mfploader", function() {
                        i.hasSize = !0, i.loadError = !0, T("LazyLoadError", i)
                    }).attr("src", i.src)), i.preloaded = !0
                }
            }
        }
    });
    var U = "retina";
    e.magnificPopup.registerModule(U, {
            options: {
                replaceSrc: function(e) {
                    return e.src.replace(/\.\w+$/, function(e) {
                        return "@2x" + e
                    })
                },
                ratio: 1
            },
            proto: {
                initRetina: function() {
                    if (window.devicePixelRatio > 1) {
                        var e = t.st.retina,
                            n = e.ratio;
                        n = isNaN(n) ? n() : n, n > 1 && (x("ImageHasSize." + U, function(e, t) {
                            t.img.css({
                                "max-width": t.img[0].naturalWidth / n,
                                width: "100%"
                            })
                        }), x("ElementParse." + U, function(t, i) {
                            i.src = e.replaceSrc(i, n)
                        }))
                    }
                }
            }
        }),
        function() {
            var t = 1e3,
                n = "ontouchstart" in window,
                i = function() {
                    I.off("touchmove" + r + " touchend" + r)
                },
                o = "mfpFastClick",
                r = "." + o;
            e.fn.mfpFastClick = function(o) {
                return e(this).each(function() {
                    var a, s = e(this);
                    if (n) {
                        var l, c, d, u, p, f;
                        s.on("touchstart" + r, function(e) {
                            u = !1, f = 1, p = e.originalEvent ? e.originalEvent.touches[0] : e.touches[0], c = p.clientX, d = p.clientY, I.on("touchmove" + r, function(e) {
                                p = e.originalEvent ? e.originalEvent.touches : e.touches, f = p.length, p = p[0], (Math.abs(p.clientX - c) > 10 || Math.abs(p.clientY - d) > 10) && (u = !0, i())
                            }).on("touchend" + r, function(e) {
                                i(), u || f > 1 || (a = !0, e.preventDefault(), clearTimeout(l), l = setTimeout(function() {
                                    a = !1
                                }, t), o())
                            })
                        })
                    }
                    s.on("click" + r, function() {
                        a || o()
                    })
                })
            }, e.fn.destroyMfpFastClick = function() {
                e(this).off("touchstart" + r + " click" + r), n && I.off("touchmove" + r + " touchend" + r)
            }
        }(), _()
})(window.jQuery || window.Zepto);








        // Ref: http://ajtroxell.com/use-magnific-popup-with-wordpress-now/ - Method of calling magnific for trendr posts
        jQuery(document).ready(function($) {

            $.magnificPopup.page = 1;
            $.magnificPopup.current_page = 1;

            $.magnificPopup.proto.next = function(){

                if($.magnificPopup.instance.index == $.magnificPopup.instance.items.length - 1)
                {
                    $.ajax({
                        url:ajaxurl,
                        data:{
                            action:'activity_get_older_updates',
                            'cookie': encodeURIComponent(document.cookie),
                            page:$.magnificPopup.page + 1},
                        type:'post',
                        success:function(res){
                            res = JSON.parse(res);
                            $.magnificPopup.instance.index = $.magnificPopup.instance.items.length;

                            // $.magnificPopup.instance.items = $.magnificPopup.instance.items.slice(0,$.magnificPopup.content_count);

                            $content_enable = true;
                            if($.magnificPopup.instance.items.length == $.magnificPopup.content_count)
                            {
                                $content_enable = false;
                            }
                            $(res.contents).find('.view').each(function(index){
                                if(!$content_enable)
                                {
                                    $.magnificPopup.instance.items.push($(this)[0]);
                                }
                                else
                                {
                                    $.magnificPopup.instance.items[$.magnificPopup.content_count + index] = $(this)[0];
                                }
                            })

                            if($.magnificPopup.instance.items.length == $.magnificPopup.content_count)
                            {
                                $.magnificPopup.instance.index = 0;
                                $.magnificPopup.proto.updateItemHTML();
                            }
                            else
                            {
                                $.magnificPopup.page ++;
                                $.magnificPopup.instance.index = $.magnificPopup.content_count;
                                $.magnificPopup.proto.updateItemHTML();
                            }

                        }
                    })
                }
                else
                {
                    $.magnificPopup.instance.index++;
                    $.magnificPopup.proto.updateItemHTML();
                }
            }

            $.magnificPopup.proto.prev = function(){
                if($.magnificPopup.instance.index == $.magnificPopup.content_count && $.magnificPopup.page > $.magnificPopup.current_page + 1)
                    {
                            $.ajax({
                                url:ajaxurl,
                                type:'post',
                                data:{
                                    action:'activity_get_older_updates',
                                    'cookie': encodeURIComponent(document.cookie),
                                    page:$.magnificPopup.page - 1
                                },
                                success:function(res)
                                {
                                    res = JSON.parse(res);

                                    var items = [];
                                    $(res.contents).find('.view').each(function(){
                                        items.push($(this)[0]);
                                    })

                                    $.magnificPopup.instance.items = $.magnificPopup.instance.items.slice(0,$.magnificPopup.content_count);
                                    $.magnificPopup.page --;
                                    $.magnificPopup.instance.items = $.magnificPopup.instance.items.concat($.magnificPopup.instance.items,items);
                                    $.magnificPopup.instance.index = $.magnificPopup.instance.items.length-1;

                                    $.magnificPopup.proto.updateItemHTML();
                                }

                            })
                        }
                    else
                    {
                        if($.magnificPopup.instance.index >= 0)
                        {
                            $.magnificPopup.instance.index --;
                            $.magnificPopup.proto.updateItemHTML();
                        }

                    }

                }


            function imoc_init(){
                $.magnificPopup.instance.items = [];
                $.magnificPopup.page = ( $.cookie('trs-activity-oldestpage') * 1 );
                $.magnificPopup.current_page = ( $.cookie('trs-activity-oldestpage') * 1 );

                // Single Image
                $('.broadcast-inn').each(function(){      

                    //single image popup
                    //if ($(this).parents('.magnific-view').length == 0) { //check that it's not part of a gallery

                        $(this).addClass('magnific-view'); //Add a class


                        $('.magnific-view').magnificPopup({
                            type:'ajax',

                            gallery: {enabled:true},
                            closeOnBgClick:false,
                            "mfp-prevent-close":false,

                            callbacks: {
                                open: function() {
                                $('.mfp-description').append(this.currItem.el.attr('alt'));



                                $.magnificPopup.content_count = $.magnificPopup.instance.items.length;
                                  },

                                  afterChange: function() {



                                    $('.mfp-description').empty().append(this.currItem.el.attr('alt'));     

                                        setTimeout(function() {


         jQuery(document).ready(function(a){var b=/\[med_video\]https?:\/\/(?:www\.)?youtu(?:be\.com|\.be)\/(?:watch\?v=|v\/)?([A-Za-z0-9_\-]+)([a-zA-Z&=;_+0-9*#\-]*?)\[\/med_video\]/;
        var c='<div data-address="$1" class="youtube" style="background: url(https://i4.ytimg.com/vi/$1/hqdefault.jpg)"><span></span></div>';
        var d='<iframe data-address="$1"  class="youtube" src="https://www.youtube.com/embed/$1?enablejsapi=1&hd=1&autohide=1&autoplay=1" frameborder="0" allowfullscreen></iframe>';
        a(".mfp-container .broadcast-inn").each(function(){var d=a(this);d.html(d.html().replace(b,c))});a(".mfp-container .broadcast-inn").delegate("div.youtube","hover",function(){var b=a(this);b.replaceWith(d.replace(/\$1/g,b.attr("data-address")))})})


    }, 450); 




                                  }
                                },
                                image: {
                                             markup: '<div class="iframe-popup">'+
                                            '<iframe class="mfp-iframe" frameborder="0" scrolling="no"" onload="resizeIframe(this)" allowtransparency="true" allowfullscreen></iframe>'+
                                            '<div class="mfp-close"></div>'+
                                          '</div>'
                                          }
                            });
                //  }



                });



            }
            imoc_init();
            window.imoc_init = imoc_init;
//asamir add try and catch to prevent js error that prevent privacy with media to work

            try{

                onElementHeightChange(document.body, function(){
                    imoc_init();
                });
            }catch(err){};

            $('#activity-filter-by').change(function(){
                window.setTimeout(imoc_init(),1000);
            });
            $(' ul> li > #activity_media_filter > input[type="checkbox"]').change(function(){
                window.setTimeout(imoc_init(),1000);
            });

            $('.contour-select ul li').click(function(){
                window.setTimeout(imoc_init(),1000);
            });
    });







/* Users block */

if ( typeof jq == "undefined" )
    var jq = jQuery;

jq(document).ready( function() {
    jq("a.block, a.unblock").live( 'click', function() {
        var link = jq(this);
        var type = link.attr('class');
        var uid = link.attr('id');
        var nonce = link.attr('href');
        var action = '';
        var target = link.attr('data-ref');
        // add the loading class for TRS 1.2.x only
        if ( TRS_DTheme.mention_explain )
            link.addClass('loading');

        jq.get( target,
        function(response) {
            var response = JSON.parse(response);
            if(response.res == true){

                    jq(link.parent()).fadeOut(200, function() {



                        if(link.hasClass('block')){
                                link.removeClass('block');
                                link.html( 'Unblock' );
                                link.addClass('unblock');
                      }else if(link.hasClass('unblock')){
                                link.removeClass('unblock');
                                link.html( 'Block' );
                                link.addClass('block');
                      }
                        if(response.lnk){
                            link.attr('data-ref',response.lnk);
                        }else{
                            link.addClass("pending disabled");
                            link.html( 'User successfully unblocked' );
                            link.parent().addClass('pending');
                        }
                        jq(this).fadeIn(200);

                    });
                }else{

                }
        });
        return false;
    } );

    jq("a.disabled").live( 'click', function() {
        return false;
    });
} );

/* featured posts*/
// AJAX Functions
var jq = jQuery;
                               // TODO implement this. Global variable to prevent multiple AJAX requests

jq(document).ready(function trsLike() {
    "use strict";
    jq('.fp_promote,.fp_unpromote').live('click', function() {

      var id, type;
        id = jq(this).attr('id');
        if(jq(this).hasClass('fp_promote')){
            var period_in_min  =parseInt( prompt("Please enter the promotion in mintus", "1"));
            if(isNaN(period_in_min)) return false;
            type = 'activity_fp_promote';
        }else  if(jq(this).hasClass('fp_unpromote')){
          type = 'activity_fp_unpromote';
        }
                               // Used to get the id of the entity liked or unliked

        jq(this).addClass('loading');

        jq.post(ajaxurl, {
            action: 'promote_process_ajax',                            // TODO this could be named clearer
            'type':type ,
            'id': id,
            'period_in_min':period_in_min
        },
            function( data ) {
                // jq('#' + id).fadeOut(100, function() {
                    jq('#' + id).html(data).removeClass('loading');
                // });
                if(data == 'Promote'){
                  jq('#' + id).removeClass('fp_unpromote').addClass('fp_promote');
                }else if(data == 'Unpromote'){
                  jq('#' + id).addClass('fp_unpromote').removeClass('fp_promote');
                }
                if(!jq('#' + id).hasClass('multiaction')){
                  jq('#' + id).removeClass('fp_unpromote').removeClass('fp_promote').addClass("disabled");
                }
                console.log(data);
            });

        return false;
    });

});


/* Follow function*/

if ( typeof jq == "undefined" ) {
    var jq = jQuery;
}

jq( function() {
    var profileHeader   = jq("#item-buttons");
    var memberLoop      = jq("#members-list").parent();
    var groupMemberLoop = jq("#member-list").parent();

    profileHeader.on("click", ".follow-button a", function() {
        trs_follow_button_action( jq(this), 'profile' );
        return false;
    });

    memberLoop.on("click", ".follow-button a", function() {
        trs_follow_button_action( jq(this), 'member-loop' );
        return false;
    });

    groupMemberLoop.on("click", ".follow-button a", function() {
        trs_follow_button_action( jq(this) );
        return false;
    });

    function trs_follow_button_action( scope, context ) {
        var link   = scope;
        var uid    = link.attr('id');
        var nonce  = link.attr('href');
        var action = '';

        uid    = uid.split('-');
        action = uid[0];
        uid    = uid[1];

        nonce = nonce.split('?_key=');
        nonce = nonce[1].split('&');
        nonce = nonce[0];

        jq.post( ajaxurl, {
            action: 'trs_' + action,
            'uid': uid,
            '_key': nonce
        },
        function(response) {
            jq( link.parent()).fadeOut(200, function() {
                // toggle classes
                if ( action == 'unfollow' ) {
                    link.parent().removeClass( 'following' ).addClass( 'not-following' );
                } else {
                    link.parent().removeClass( 'not-following' ).addClass( 'following' );
                }

                // add ajax response
                link.parent().html( response );

                // increase / decrease counts
                var count_wrapper = false;
                if ( context == 'profile' ) {
                    count_wrapper = jq("#user-members-followers span");

                } else if ( context == 'member-loop' ) {
                    // a user is on their own profile
                    if ( ! jq.trim( profileHeader.text() ) ) {
                        count_wrapper = jq("#user-members-following span");

                    // this means we're on the member directory
                    } else {
                        count_wrapper = jq("#members-following span");
                    }
                }

                if ( count_wrapper.length ) {
                    if ( action == 'unfollow' ) {
                        count_wrapper.text( ( count_wrapper.text() >> 0 ) - 1 );
                    } else if ( action == 'follow' ) {
                        count_wrapper.text( ( count_wrapper.text() >> 0 ) + 1 );
                    }
                }

                jq(this).fadeIn(200);
            });
        });
    }
} );


/* Friend Suggest*/

jQuery(document).ready(function(){
var j=jQuery;
j(".suggested-friend-item-list span.remove-friend-suggestion a").live('click',function(){
//hide the suggestion
var li=j(this).parent().parent().parent();
j(li).remove();
var url = j(this).attr('href');
var nonce=get_var_in_url(url,"_keys");
var suggested_user_id=get_var_in_url(url,"suggest_id");
 j.post(ajaxurl,{
                 action:"friend_suggest_remove_suggestion",
                 cookie:encodeURIComponent(document.cookie),
                 'suggestion_id':suggested_user_id,
                 '_keys':nonce
                  },
                function(){
                    //nothing here
                
              });


return false;

});

//helper
function get_var_in_url(url,name){
    var urla=url.split("?");
    var qvars=urla[1].split("&");//so we hav an arry of name=val,name=val
    for(var i=0;i<qvars.length;i++){
        var qv=qvars[i].split("=");
        if(qv[0]==name)
            return qv[1];
      }
      return '';
}
});



/* Group Suggest*/

jQuery(document).ready(function(){
var j=jQuery;
j(".suggested-group-item-list span.remove-group-suggestion a").live('click',function(){
//hide the suggestion
var li=j(this).parent().parent().parent();
j(li).remove();
var url = j(this).attr('href');
var nonce=get_var_in_url(url,"_keys");
var suggested_group_id=get_var_in_url(url,"suggest_id");
 j.post(ajaxurl,{
                 action:"group_suggest_remove_suggestion",
                 cookie:encodeURIComponent(document.cookie),
                 'suggestion_id':suggested_group_id,
                 '_keys':nonce
                  },
                function(){
                    //nothing here
                
              });


return false;

});

function get_var_in_url(url,name){
    var urla=url.split("?");
    var qvars=urla[1].split("&");//so we hav an arry of name=val,name=val
    for(var i=0;i<qvars.length;i++){
        var qv=qvars[i].split("=");
        if(qv[0]==name)
            return qv[1];
      }
      return '';
}
});






/*global $, jQuery, ajaxurl, like, like_message,trs_like_terms_like_message,trs_like_terms_unlike_message,trs_like_terms_like, unlike_message, view_likes, hide_likes, unlike_1, trs_like_terms, trs_like_terms_unlike_1, trs_like_terms_view_likes, trs_like_terms_hide_likes*/

jQuery(document).ready(function() {
    "use strict";
    jQuery('.author-box').each(function() {
        var id = jQuery(this).attr('id');
        jQuery(this).append(jQuery(id + ' .like-box'));
    });

    jQuery('.like, .unlike, .like_blogpost, .unlike_blogpost').live('click', function() {
        var type = jQuery(this).attr('class'), id = jQuery(this).attr('id');

        jQuery(this).addClass('loading');

        jQuery.post(ajaxurl, {
            action: 'activity_like',
            'cookie': encodeURIComponent(document.cookie),
            'type': type,
            'id': id
        },
            function(data) {
                jQuery('#' + id).fadeOut(100, function() {
                    jQuery(this).html(data).removeClass('loading').fadeIn(100);
                });

                // Swap from like to unlike
                var newID, pureID;
                if (type === 'like') {
                    newID = id.replace("like", "unlike");
                    jQuery('#' + id).removeClass('like').addClass('unlike').attr('title', trs_like_terms_unlike_message).attr('id', newID);
                } else if (type === 'like_blogpost') {
                    newID = id.replace("like", "unlike");
                    jQuery('#' + id).removeClass('like_blogpost').addClass('unlike_blogpost').attr('title', trs_like_terms_unlike_message).attr('id', newID);
                } else if (type === 'unlike_blogpost') {
                    newID = id.replace("unlike", "like");
                    jQuery('#' + id).removeClass('unlike_blogpost').addClass('like_blogpost').attr('title', trs_like_terms_unlike_message).attr('id', newID);
                } else {
                    newID = id.replace("unlike", "like");
                    jQuery('#' + id).removeClass('unlike').addClass('like').attr('title', trs_like_terms_like_message).attr('id', newID);
                }

                // Nobody else liked this, so remove the 'View Likes'
                if (data === trs_like_terms_like) {
                    pureID = id.replace("unlike-activity-", "");
                    jQuery('.view-likes#view-likes-' + pureID).remove();
                    jQuery('.users-who-like#users-who-like-' + pureID).remove();
                }

                // Show the 'View Likes' if user is first to like
                if (data === trs_like_terms_unlike_1) {
                    pureID = id.replace("like-activity-", "");
                    jQuery('li#activity-' + pureID + ' .activity-meta').append('<a href="" class="button view-likes" id="view-likes-' + pureID + '">' + trs_like_terms_view_likes + '</a><p class="users-who-like" id="users-who-like-' + pureID + '"></p>');
                }

            });

        return false;
    });

    jQuery('.view-likes').on('click', function() {
        var type, id, parentID;
        type = jQuery(this).attr('class');
        id = jQuery(this).attr('id');
        parentID = id.replace("view-likes", "users-who-like");

        if (!jQuery(this).hasClass('open')) {

            jQuery(this).addClass('loading');
            jQuery.post(ajaxurl, {
                action: 'activity_like',
                'cookie': encodeURIComponent(document.cookie),
                'type': type,
                'id': id
            },
                function(data) {
                    jQuery('#' + id).html(trs_like_terms_hide_likes).removeClass('loading').addClass('open');
                    jQuery('#' + parentID).html(data).slideDown('fast');
                });
            return false;

        }
        jQuery(this).html(trs_like_terms_view_likes).removeClass('loading, open');
        jQuery('#' + parentID).slideUp('fast');
        return false;

    });
});




/* Jcrop */
(function(a){a.Jcrop=function(d,A){var d=d,A=A;if(typeof(d)!=="object"){d=a(d)[0]}if(typeof(A)!=="object"){A={}}if(!("trackDocument" in A)){A.trackDocument=a.browser.msie?false:true;if(a.browser.msie&&a.browser.version.split(".")[0]=="8"){A.trackDocument=true}}if(!("keySupport" in A)){A.keySupport=a.browser.msie?false:true}var U={trackDocument:false,baseClass:"jcrop",addClass:null,bgColor:"black",bgOpacity:0.6,borderOpacity:0.4,handleOpacity:0.5,handlePad:5,handleSize:9,handleOffset:5,edgeMargin:14,aspectRatio:0,keySupport:true,cornerHandles:true,sideHandles:true,drawBorders:true,dragEdges:true,boxWidth:0,boxHeight:0,boundary:8,animationDelay:20,swingSpeed:3,allowSelect:true,allowMove:true,allowResize:true,minSelect:[0,0],maxSize:[0,0],minSize:[0,0],onChange:function(){},onSelect:function(){}};var H=U;z(A);var W=a(d);var al=W.clone().removeAttr("id").css({position:"absolute"});al.width(W.width());al.height(W.height());W.after(al).hide();T(al,H.boxWidth,H.boxHeight);var Q=al.width(),O=al.height(),Z=a("<div />").width(Q).height(O).addClass(C("holder")).css({position:"relative",backgroundColor:H.bgColor}).insertAfter(W).append(al);if(H.addClass){Z.addClass(H.addClass)}var I=a("<img />").attr("src",al.attr("src")).css("position","absolute").width(Q).height(O);var k=a("<div />").width(K(100)).height(K(100)).css({zIndex:310,position:"absolute",overflow:"hidden"}).append(I);var L=a("<div />").width(K(100)).height(K(100)).css("zIndex",320);var y=a("<div />").css({position:"absolute",zIndex:300}).insertBefore(al).append(k,L);var t=H.boundary;var b=ae().width(Q+(t*2)).height(O+(t*2)).css({position:"absolute",top:l(-t),left:l(-t),zIndex:290}).mousedown(ac);var x,ah,p,S;var M,e,n=true;var ad=D(al),r,an,am,B,ab;var aa=function(){var aq=0,aC=0,ap=0,aB=0,au,ar;function aw(aF){var aF=at(aF);ap=aq=aF[0];aB=aC=aF[1]}function av(aF){var aF=at(aF);au=aF[0]-ap;ar=aF[1]-aB;ap=aF[0];aB=aF[1]}function aE(){return[au,ar]}function ao(aH){var aG=aH[0],aF=aH[1];if(0>aq+aG){aG-=aG+aq}if(0>aC+aF){aF-=aF+aC}if(O<aB+aF){aF+=O-(aB+aF)}if(Q<ap+aG){aG+=Q-(ap+aG)}aq+=aG;ap+=aG;aC+=aF;aB+=aF}function ax(aF){var aG=aD();switch(aF){case"ne":return[aG.x2,aG.y];case"nw":return[aG.x,aG.y];case"se":return[aG.x2,aG.y2];case"sw":return[aG.x,aG.y2]}}function aD(){if(!H.aspectRatio){return aA()}var aH=H.aspectRatio,aO=H.minSize[0]/M,aN=H.minSize[1]/e,aG=H.maxSize[0]/M,aQ=H.maxSize[1]/e,aI=ap-aq,aP=aB-aC,aJ=Math.abs(aI),aK=Math.abs(aP),aL=aJ/aK,aF,aM;if(aG==0){aG=Q*10}if(aQ==0){aQ=O*10}if(aL<aH){aM=aB;w=aK*aH;aF=aI<0?aq-w:w+aq;if(aF<0){aF=0;h=Math.abs((aF-aq)/aH);aM=aP<0?aC-h:h+aC}else{if(aF>Q){aF=Q;h=Math.abs((aF-aq)/aH);aM=aP<0?aC-h:h+aC}}}else{aF=ap;h=aJ/aH;aM=aP<0?aC-h:aC+h;if(aM<0){aM=0;w=Math.abs((aM-aC)*aH);aF=aI<0?aq-w:w+aq}else{if(aM>O){aM=O;w=Math.abs(aM-aC)*aH;aF=aI<0?aq-w:w+aq}}}if(aF>aq){if(aF-aq<aO){aF=aq+aO}else{if(aF-aq>aG){aF=aq+aG}}if(aM>aC){aM=aC+(aF-aq)/aH}else{aM=aC-(aF-aq)/aH}}else{if(aF<aq){if(aq-aF<aO){aF=aq-aO}else{if(aq-aF>aG){aF=aq-aG}}if(aM>aC){aM=aC+(aq-aF)/aH}else{aM=aC-(aq-aF)/aH}}}if(aF<0){aq-=aF;aF=0}else{if(aF>Q){aq-=aF-Q;aF=Q}}if(aM<0){aC-=aM;aM=0}else{if(aM>O){aC-=aM-O;aM=O}}return last=az(ay(aq,aC,aF,aM))}function at(aF){if(aF[0]<0){aF[0]=0}if(aF[1]<0){aF[1]=0}if(aF[0]>Q){aF[0]=Q}if(aF[1]>O){aF[1]=O}return[aF[0],aF[1]]}function ay(aI,aK,aH,aJ){var aM=aI,aL=aH,aG=aK,aF=aJ;if(aH<aI){aM=aH;aL=aI}if(aJ<aK){aG=aJ;aF=aK}return[Math.round(aM),Math.round(aG),Math.round(aL),Math.round(aF)]}function aA(){var aG=ap-aq;var aF=aB-aC;if(x&&(Math.abs(aG)>x)){ap=(aG>0)?(aq+x):(aq-x)}if(ah&&(Math.abs(aF)>ah)){aB=(aF>0)?(aC+ah):(aC-ah)}if(S&&(Math.abs(aF)<S)){aB=(aF>0)?(aC+S):(aC-S)}if(p&&(Math.abs(aG)<p)){ap=(aG>0)?(aq+p):(aq-p)}if(aq<0){ap-=aq;aq-=aq}if(aC<0){aB-=aC;aC-=aC}if(ap<0){aq-=ap;ap-=ap}if(aB<0){aC-=aB;aB-=aB}if(ap>Q){var aH=ap-Q;aq-=aH;ap-=aH}if(aB>O){var aH=aB-O;aC-=aH;aB-=aH}if(aq>Q){var aH=aq-O;aB-=aH;aC-=aH}if(aC>O){var aH=aC-O;aB-=aH;aC-=aH}return az(ay(aq,aC,ap,aB))}function az(aF){return{x:aF[0],y:aF[1],x2:aF[2],y2:aF[3],w:aF[2]-aF[0],h:aF[3]-aF[1]}}return{flipCoords:ay,setPressed:aw,setCurrent:av,getOffset:aE,moveOffset:ao,getCorner:ax,getFixed:aD}}();var X=function(){var aw,ar,aC,aB,aK=370;var av={};var aO={};var aq=false;var aA=H.handleOffset;if(H.drawBorders){av={top:ax("hline").css("top",a.browser.msie?l(-1):l(0)),bottom:ax("hline"),left:ax("vline"),right:ax("vline")}}if(H.dragEdges){aO.t=aJ("n");aO.b=aJ("s");aO.r=aJ("e");aO.l=aJ("w")}H.sideHandles&&aF(["n","s","e","w"]);H.cornerHandles&&aF(["sw","nw","ne","se"]);function ax(aR){var aS=a("<div />").css({position:"absolute",opacity:H.borderOpacity}).addClass(C(aR));k.append(aS);return aS}function ap(aR,aS){var aT=a("<div />").mousedown(c(aR)).css({cursor:aR+"-resize",position:"absolute",zIndex:aS});L.append(aT);return aT}function aD(aR){return ap(aR,aK++).css({top:l(-aA+1),left:l(-aA+1),opacity:H.handleOpacity}).addClass(C("handle"))}function aJ(aT){var aW=H.handleSize,aX=aA,aV=aW,aS=aW,aU=aX,aR=aX;switch(aT){case"n":case"s":aS=K(100);break;case"e":case"w":aV=K(100);break}return ap(aT,aK++).width(aS).height(aV).css({top:l(-aU+1),left:l(-aR+1)})}function aF(aR){for(i in aR){aO[aR[i]]=aD(aR[i])}}function aH(aY){var aT=Math.round((aY.h/2)-aA),aS=Math.round((aY.w/2)-aA),aW=west=-aA+1,aV=aY.w-aA,aU=aY.h-aA,aR,aX;"e" in aO&&aO.e.css({top:l(aT),left:l(aV)})&&aO.w.css({top:l(aT)})&&aO.s.css({top:l(aU),left:l(aS)})&&aO.n.css({left:l(aS)});"ne" in aO&&aO.ne.css({left:l(aV)})&&aO.se.css({top:l(aU),left:l(aV)})&&aO.sw.css({top:l(aU)});"b" in aO&&aO.b.css({top:l(aU)})&&aO.r.css({left:l(aV)})}function az(aR,aS){I.css({top:l(-aS),left:l(-aR)});y.css({top:l(aS),left:l(aR)})}function aQ(aR,aS){y.width(aR).height(aS)}function at(){var aR=aa.getFixed();aa.setPressed([aR.x,aR.y]);aa.setCurrent([aR.x2,aR.y2]);aN()}function aN(){if(aB){return ay()}}function ay(){var aR=aa.getFixed();aQ(aR.w,aR.h);az(aR.x,aR.y);H.drawBorders&&av.right.css({left:l(aR.w-1)})&&av.bottom.css({top:l(aR.h-1)});aq&&aH(aR);aB||aP();H.onChange(Y(aR))}function aP(){y.show();al.css("opacity",H.bgOpacity);aB=true}function aL(){aM();y.hide();al.css("opacity",1);aB=false}function ao(){if(aq){aH(aa.getFixed());L.show()}}function aG(){aq=true;if(H.allowResize){aH(aa.getFixed());L.show();return true}}function aM(){aq=false;L.hide()}function aI(aR){(B=aR)?aM():aG()}function aE(){aI(false);at()}var au=ae().mousedown(c("move")).css({cursor:"move",position:"absolute",zIndex:360});k.append(au);aM();return{updateVisible:aN,update:ay,release:aL,refresh:at,setCursor:function(aR){au.css("cursor",aR)},enableHandles:aG,enableOnly:function(){aq=true},showHandles:ao,disableHandles:aM,animMode:aI,done:aE}}();var P=function(){var ap=function(){},ar=function(){},aq=H.trackDocument;if(!aq){b.mousemove(ao).mouseup(at).mouseout(at)}function ax(){b.css({zIndex:450});if(aq){a(document).mousemove(ao).mouseup(at)}}function aw(){b.css({zIndex:290});if(aq){a(document).unbind("mousemove",ao).unbind("mouseup",at)}}function ao(ay){ap(F(ay))}function at(ay){ay.preventDefault();ay.stopPropagation();if(r){r=false;ar(F(ay));H.onSelect(Y(aa.getFixed()));aw();ap=function(){};ar=function(){}}return false}function au(az,ay){r=true;ap=az;ar=ay;ax();return false}function av(ay){b.css("cursor",ay)}al.before(b);return{activateHandlers:au,setCursor:av}}();var ak=function(){var ar=a('<input type="radio" />').css({position:"absolute",left:"-30px"}).keypress(ao).blur(at),au=a("<div />").css({position:"absolute",overflow:"hidden"}).append(ar);function ap(){if(H.keySupport){ar.show();ar.focus()}}function at(av){ar.hide()}function aq(aw,av,ax){if(H.allowMove){aa.moveOffset([av,ax]);X.updateVisible()}aw.preventDefault();aw.stopPropagation()}function ao(aw){if(aw.ctrlKey){return true}ab=aw.shiftKey?true:false;var av=ab?10:1;switch(aw.keyCode){case 37:aq(aw,-av,0);break;case 39:aq(aw,av,0);break;case 38:aq(aw,0,-av);break;case 40:aq(aw,0,av);break;case 27:X.release();break;case 9:return true}return nothing(aw)}if(H.keySupport){au.insertBefore(al)}return{watchKeys:ap}}();function l(ao){return""+parseInt(ao)+"px"}function K(ao){return""+parseInt(ao)+"%"}function C(ao){return H.baseClass+"-"+ao}function D(ao){var ap=a(ao).offset();return[ap.left,ap.top]}function F(ao){return[(ao.pageX-ad[0]),(ao.pageY-ad[1])]}function E(ao){if(ao!=an){P.setCursor(ao);an=ao}}function f(aq,at){ad=D(al);P.setCursor(aq=="move"?aq:aq+"-resize");if(aq=="move"){return P.activateHandlers(R(at),o)}var ao=aa.getFixed();var ap=q(aq);var ar=aa.getCorner(q(ap));aa.setPressed(aa.getCorner(ap));aa.setCurrent(ar);P.activateHandlers(G(aq,ao),o)}function G(ap,ao){return function(aq){if(!H.aspectRatio){switch(ap){case"e":aq[1]=ao.y2;break;case"w":aq[1]=ao.y2;break;case"n":aq[0]=ao.x2;break;case"s":aq[0]=ao.x2;break}}else{switch(ap){case"e":aq[1]=ao.y+1;break;case"w":aq[1]=ao.y+1;break;case"n":aq[0]=ao.x+1;break;case"s":aq[0]=ao.x+1;break}}aa.setCurrent(aq);X.update()}}function R(ap){var ao=ap;ak.watchKeys();return function(aq){aa.moveOffset([aq[0]-ao[0],aq[1]-ao[1]]);ao=aq;X.update()}}function q(ao){switch(ao){case"n":return"sw";case"s":return"nw";case"e":return"nw";case"w":return"ne";case"ne":return"sw";case"nw":return"se";case"se":return"nw";case"sw":return"ne"}}function c(ao){return function(ap){if(H.disabled){return false}if((ao=="move")&&!H.allowMove){return false}r=true;f(ao,F(ap));ap.stopPropagation();ap.preventDefault();return false}}function T(at,ap,ar){var ao=at.width(),aq=at.height();if((ao>ap)&&ap>0){ao=ap;aq=(ap/at.width())*at.height()}if((aq>ar)&&ar>0){aq=ar;ao=(ar/at.height())*at.width()}M=at.width()/ao;e=at.height()/aq;at.width(ao).height(aq)}function Y(ao){return{x:parseInt(ao.x*M),y:parseInt(ao.y*e),x2:parseInt(ao.x2*M),y2:parseInt(ao.y2*e),w:parseInt(ao.w*M),h:parseInt(ao.h*e)}}function o(ap){var ao=aa.getFixed();if(ao.w>H.minSelect[0]&&ao.h>H.minSelect[1]){X.enableHandles();X.done()}else{X.release()}P.setCursor(H.allowSelect?"crosshair":"default")}function ac(ao){if(H.disabled){return false}if(!H.allowSelect){return false}r=true;ad=D(al);X.disableHandles();E("crosshair");var ap=F(ao);aa.setPressed(ap);P.activateHandlers(aj,o);ak.watchKeys();X.update();ao.stopPropagation();ao.preventDefault();return false}function aj(ao){aa.setCurrent(ao);X.update()}function ae(){var ao=a("<div></div>").addClass(C("tracker"));a.browser.msie&&ao.css({opacity:0,backgroundColor:"white"});return ao}function s(aG){var aB=aG[0]/M,ap=aG[1]/e,aA=aG[2]/M,ao=aG[3]/e;if(B){return}var az=aa.flipCoords(aB,ap,aA,ao);var aE=aa.getFixed();var ar=initcr=[aE.x,aE.y,aE.x2,aE.y2];var aq=H.animationDelay;var ax=ar[0];var aw=ar[1];var aA=ar[2];var ao=ar[3];var aD=az[0]-initcr[0];var au=az[1]-initcr[1];var aC=az[2]-initcr[2];var at=az[3]-initcr[3];var ay=0;var av=H.swingSpeed;X.animMode(true);var aF=function(){return function(){ay+=(100-ay)/av;ar[0]=ax+((ay/100)*aD);ar[1]=aw+((ay/100)*au);ar[2]=aA+((ay/100)*aC);ar[3]=ao+((ay/100)*at);if(ay<100){aH()}else{X.done()}if(ay>=99.8){ay=100}ai(ar)}}();function aH(){window.setTimeout(aF,aq)}aH()}function J(ao){ai([ao[0]/M,ao[1]/e,ao[2]/M,ao[3]/e])}function ai(ao){aa.setPressed([ao[0],ao[1]]);aa.setCurrent([ao[2],ao[3]]);X.update()}function z(ao){if(typeof(ao)!="object"){ao={}}H=a.extend(H,ao);if(typeof(H.onChange)!=="function"){H.onChange=function(){}}if(typeof(H.onSelect)!=="function"){H.onSelect=function(){}}}function j(){return Y(aa.getFixed())}function ag(){return aa.getFixed()}function u(ao){z(ao);N()}function v(){H.disabled=true;X.disableHandles();X.setCursor("default");P.setCursor("default")}function V(){H.disabled=false;N()}function m(){X.done();P.activateHandlers(null,null)}function af(){Z.remove();W.show()}function N(ao){H.allowResize?ao?X.enableOnly():X.enableHandles():X.disableHandles();P.setCursor(H.allowSelect?"crosshair":"default");X.setCursor(H.allowMove?"move":"default");Z.css("backgroundColor",H.bgColor);if("setSelect" in H){J(A.setSelect);X.done();delete (H.setSelect)}if("trueSize" in H){M=H.trueSize[0]/Q;e=H.trueSize[1]/O}x=H.maxSize[0]||0;ah=H.maxSize[1]||0;p=H.minSize[0]||0;S=H.minSize[1]||0;if("outerImage" in H){al.attr("src",H.outerImage);delete (H.outerImage)}X.refresh()}L.hide();N(true);var g={animateTo:s,setSelect:J,setOptions:u,tellSelect:j,tellScaled:ag,disable:v,enable:V,cancel:m,focus:ak.watchKeys,getBounds:function(){return[Q*M,O*e]},getWidgetSize:function(){return[Q,O]},release:X.release,destroy:af};W.data("Jcrop",g);return g};a.fn.Jcrop=function(c){function b(f){var e=c.useImg||f.src;var d=new Image();d.onload=function(){a.Jcrop(f,c)};d.src=e}if(typeof(c)!=="object"){c={}}this.each(function(){if(a(this).data("Jcrop")){if(c=="api"){return a(this).data("Jcrop")}else{a(this).data("Jcrop").setOptions(c)}}else{b(this)}});return this}})(jQuery);





var _medActiveHandler = false;
var exts = ['jpg', 'jpeg', 'png', 'gif','mp4'];
(function($){
$(function() {
    // jq('#activity_media_filter input[type="checkbox"]').on('change', function() {
    //          if ( !selected_tab.length )
    //              var scope = null;
    //          else
    //              var scope = selected_tab.attr('id').substr( 9, selected_tab.attr('id').length );
    //       var param = this.attributes['action'].value;
    //       if( jq('#activity-filter-select select').val() != -1){
    //                  param+=","+ jq('#activity-filter-select select').val();
    //       }
    //          trs_activity_request(scope,param);
    // });


var $form;
var $text;
var $textContainer;




var createRemoteImagePreview = function () {
        var imgs = [];
        $("#med_remote_image_container .med_remote_image").each(function () {
            imgs[imgs.length] = $(this).val();
        });
        $.post(ajaxurl, {"action":"med_preview_remote_image", "data":imgs}, function (data) {
            var html = '';
            $.each(data, function(k,file) {
                //asamir hande the video files
                // var html = '<img class="med_preview_photo_item" src="' + _med_data.temp_img_url + resp.file + '" width="80px" />' +
                //          '<input type="hidden" class="med_photos_to_add" name="med_photos[]" value="' + resp.file + '" />';
                //      var html = "";

                         html += '<img class="med_preview_photo_item" src="' + file + '" width="80px" />';
                        html += '<input type="hidden" class="med_photos_to_add" name="med_photos[]" value="' + file + '" />';

            });
            $('.med_preview_container').html(html);
        });
        $('.med_action_container').html(

            '<p><input type="button" class="button-primary med_primary_button" id="med_submit" value="' + TRS_DTheme.add_photos + '" /> ' 
        );
    };

    var createPhotoPreview = function (id, fileName, resp) {
        if ("error" in resp) return false;
        //asamir hande the video files
        // var html = '<img class="med_preview_photo_item" src="' + _med_data.temp_img_url + resp.file + '" width="80px" />' +
        //          '<input type="hidden" class="med_photos_to_add" name="med_photos[]" value="' + resp.file + '" />';
        //

        var html = "";
            if(fileName.endsWith('mp4'))
             html += '<video width="320" height="240" controls><source  src="' + _med_data.temp_img_url + resp.file + '" type="video/mp4"></video>';
             else
             html += '<img class="med_preview_photo_item" src="' + _med_data.temp_img_url + resp.file + '" width="80px" />';
            html += '<input type="hidden" class="med_photos_to_add" name="med_photos[]" value="' + resp.file + '" />';


        $('.med_preview_container').append(html);
        $('.med_action_container').html(
            '<p><input type="button" class="button-primary med_primary_button" id="med_submit" value="' + TRS_DTheme.add_photos + '" /> ' +
            '</p>'
        );
    };

    var removeTempImages = function (rti_callback) {
        var $imgs = $('input.med_photos_to_add');
        if (!$imgs.length) return rti_callback();
        $.post(ajaxurl, {"action":"trm_ajax_med_remove_temp_images", "data": $imgs.serialize().replace(/%5B%5D/g, '[]')}, function (data) {
            rti_callback();
        });
    };

   


/**
 * Photos insertion/preview handler.
 */
var MedPhotoHandler = function () {
    $container = $(".med_controls_container");
    var createMarkup = function () {           

        var html = '<div id="med_tmp_photo"> </div>' +
            '<ul id="med_tmp_photo_list"></ul>' ;
        $container.append(html);

        $('#field').focus(function () {
            $(this)
                .select()
                .addClass('changed')
            ;
        });

        $('#field').keypress(function (e) {
            if (13 != e.which) return true;
            createLinkPreview();
            return false;
        });
       // $('#field').change(createLinkPreview);
//var regex =  /(https?:\/\/[^\s]+)/g;




        var uploader = new qq.FileUploader({
            "element": $('#med_tmp_photo')[0],
            "listElement": $('#med_tmp_photo_list')[0],
            "allowedExtensions": ['jpg', 'jpeg', 'png', 'gif','mp4'],
            "action": ajaxurl,
            "params": {
                "action": "med_preview_photo"
            },
            //modified code added resize
            resize : true,
            maxwidth : 1200,
            quality : 0.9,          
            "onSubmit": function (id) {
                if (!parseInt(TRS_DTheme._max_images, 1)) return true; // Skip check
                id = parseInt(id, 1);
                if (!id) id = $("img.med_preview_photo_item").length;
                if (!id) return true;
                if (id < parseInt(TRS_DTheme._max_images, 1)) return true;
                if (!$("#med-too_many_photos").length) $("#med_tmp_photo").append(
                    '<p id="med-too_many_photos">' + TRS_DTheme.images_limit_exceeded + '</p>'
                );
                return false;
            },
            "onComplete": createPhotoPreview,
            template: '<div class="qq-uploader">' +
                '<div class="qq-upload-drop-area"><span>' + TRS_DTheme.drop_files + '</span></div>' +
                '<div class="qq-upload-button">' + TRS_DTheme.upload_file + '</div>' +
             '</div>'

        });


    };

    var createPreviewMarkup = function (data) {

        var imgs = '';
        $.each(data.images, function(idx, img) {
            if (!img) return true;
            var url = img.match(/^http/) ? img : data.url + '/' + img;
            imgs += '<img class="med_link_preview_image" src="' + url + '" />';
        });
        var html = '<table border="0">' +
            '<tr>' +
                '<td>' +
                    '<div class="med_link_preview_container">' +
                        imgs +
                        '<input type="hidden" name="med_link_img" value="" />' +
                    '</div>' +
                '</td>' +
                '<td>' +
                    '<div class="med_link_preview_title">' + data.title + '</div>' +
                    '<input type="hidden" name="med_link_title" value="' + data.title + '" />' +
                    '<div class="med_link_preview_url">' + data.url + '</div>' +
                    '<input type="hidden" name="med_link_url" value="' + data.url + '" />' +
                    '<div class="med_link_preview_body">' + data.text + '</div>' +
                   
                    '</div>' +
                '</td>' +
            '</tr>' +
        '</table>';
        $('.med_preview_container').empty().html(html);
        $('.med_action_container').html(
            '<p><input type="button" class="button-primary med_primary_button" id="med_submit" value="' + TRS_DTheme.add_link + '" /> ' +
            '</p>'
        );

        $('img.med_link_preview_image').hide();

        $('img.med_link_preview_image').first().show();
        $('input[name="med_link_img"]').val($('img.med_link_preview_image').first().attr('src'));

        //$('.med_thumbnail_chooser_left').click(function () {
       
     

    };

    var createLinkPreview = function () {
        var url = $('#field').val();
        //if (!url) return false;

        $('.med_preview_container').html('<div class="med_waiting"></div>');
        $.post(ajaxurl, {"action":"med_preview_link", "data":url}, function (data) {
            createPreviewMarkup(data);
        });
    };

    var processForSave = function () {

       
        var $imgs = $('input.med_photos_to_add');
        var imgArr = [];
        $imgs.each(function () {
            imgArr[imgArr.length] = $(this).val();
        });
        return {
            "med_photos": imgArr,


                        "med_link_url": $('input[name="med_link_url"]').val(),
            "med_link_image": $('input[name="med_link_img"]').val(),
            "med_link_title": $('input[name="med_link_title"]').val(),
            "med_link_body": $('input[name="med_link_body"]').val(),
        };


    };
   


    var init = function () {
        $container.empty(); 
        $('.med_preview_container').empty();
        $('.med_action_container').empty();
        createMarkup();
    };

    var destroy = function () {
        removeTempImages(function() {
         //   $container.empty();
           $('.med_preview_container').empty();
           $('.med_action_container').empty();
           $('.qq-upload-file').empty();
        $('#submit-post').show();
                                $("#med_cancel_action").hide();


        });
    };

    removeTempImages(init);

  // if (url) return {"destroy": destroy, "get": processForSaveLink};

    return {"destroy": destroy, "get": processForSave};
};


/* === End handlers  === */


/**
 * Main interface markup creation.
 */
function createMarkup () {
    var html = '<div class="med_actions_container med-theme-' + _med_data.theme.replace(/[^-_a-z0-9]/ig, '') + ' med-alignment-' + _med_data.alignment.replace(/[^-_a-z0-9]/ig, '') + '">' +
        '<div class="med_toolbar_container">' +
            '<a href="#photos" class="med_toolbarItem" title="' + TRS_DTheme.add_photos + '" id="med_addPhotos"><span>' + TRS_DTheme.add_photos + '</span></a>' +
            '&nbsp;' +
            '<a href="#videos" class="med_toolbarItem" title="' + TRS_DTheme.add_videos + '" id="med_addVideos"><span>' + TRS_DTheme.add_videos + '</span></a>' +
            '&nbsp;' +
            '<a href="#links" class="med_toolbarItem" title="' + TRS_DTheme.add_links + '" id="med_addLinks"><span>' + TRS_DTheme.add_links + '</span></a>' +
        '</div>' +
                '<input type="button" id="med_cancel_action" value="' + TRS_DTheme.cancel + '" style="display:none" />' +

        '<div class="med_controls_container">' +

        '</div>' +
        '<div class="med_preview_container">' +

        '</div>' +
        '<div class="med_action_container">' +
        '</div>' +
    '</div>';
    $form.wrap('<div class="med_form_container" />');
    $textContainer.after(html);
}


/**
 * Initializes the main interface.
 */
function init () {
    $form = $("#post-box");
    $text = $form.find('textarea[name="field"]');
    $textContainer = $form.find('#post-inner');
    $period_in_min = $form.find('#period_in_min');
    $isad = $form.find('#isad');

    createMarkup();
    //$('#med_addPhotos').click(function () {
        if (_medActiveHandler) _medActiveHandler.destroy();
        _medActiveHandler = new MedPhotoHandler();

   // });
    $('#med_addLinks').click(function () {
        if (_medActiveHandler) _medActiveHandler.destroy();
        _medActiveHandler = new MedLinkHandler();
        $("#med_cancel_action").show();
        return false;
    });
    $('#med_addVideos').click(function () {
        if (_medActiveHandler) _medActiveHandler.destroy();
        _medActiveHandler = new MedVideoHandler();
        $("#med_cancel_action").show();
        return false;
    });
    $('#med_cancel_action').click(function () {
        $(".med_toolbarItem.med_active").removeClass("med_active");
        _medActiveHandler.destroy();
        return false;
    });
    $(".med_toolbarItem").click(function () {
        $(".med_toolbarItem.med_active").removeClass("med_active");
        $(this).addClass("med_active");
    });
    $(document).on('click', '#med_submit', function () {
        var params = _medActiveHandler.get();
        var group_id = $('#field-post-in').length ? $('#field-post-in').val() : 0;
        $.post(ajaxurl, {
            "action": "med_update_activity_contents",
            "data": params,
            "content": $text.val(),
            "group_id": group_id,
            'period_in_min':$period_in_min.val()
        }, function (data) {
            _medActiveHandler.destroy();
            $text.val('');
            $period_in_min.val(0);
            $isad[0].checked = false;
            $('#duration').css('display','none');

            $('#publish').prepend(data.activity);
            /**
             * Handle image scaling in previews.
             */
            $(".med_final_link img").each(function () {
                $(this).width($(this).parents('div').width());
            });
        });
    });
    $(document).on('click', '#med_cancel', function () {
        $(".med_toolbarItem.med_active").removeClass("med_active");
        _medActiveHandler.destroy();
    });
}

// Only initialize if we're supposed to.
/*
if (
    !('ontouchstart' in document.documentElement)
    ||
    ('ontouchstart' in document.documentElement && (/iPhone|iPod|iPad/i).test(navigator.userAgent))
    ) {
    if ($("#post-box").is(":visible")) init();
}
*/
// Meh, just do it - newish Droids seem to work fine.
if ($("#post-box").is(":visible")) init();

/**
 * Handle image scaling in previews.
 */
//$(".med_final_link img").each(function () {
    //$(this).width($(this).parents('div').width());
//});

});
})(jQuery);






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
                //processData : false,
               // contentType : false,
                type : 'POST',
                success : function(data) {
                    $("body").html(data);
                }
            });
        })

    });
 }

/*! line sensetive

if (typeof FileReader != 'undefined') {
    $(document).bind('submit', '#cover_edit', function(e) {

        if (!$('#action', '#cover_edit').val() == 'trs_upload_profile_cover' || $('#cover_upload').length == 0) {
            return;
        }
   
        e.preventDefault();

       // $('#cover_edit #cover_upload,#cover_edit #upload').prop('disabled', true).addClass('loading');

        var filelist = $('#cover_upload')[0].files;

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
        })

    });
 }



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



if ( typeof jq == "undefined" )
    var jq = jQuery;
//asamir eddit to solve the conflict
jq(document).ready( function() {

    jq.fn.extend({
        customStyle : function(options) {

            if(!jq.browser.msie || (jq.browser.msie&&jq.browser.version>6)) {
                return this.each(function() {
                    if ( jq(this).data('customStyle') == undefined ) {

                        jq(this).data('customStyle', true);
                        var currentSelected = jq(this).find(':selected');

                     
                    }
             });
            }
    }
    });


    jq('.trs-ap-selectbox').change(function(event) {
        var target = jq(event.target);
        var parent = target.closest('.activity');
            if(parent.attr('id')){
        var parent_id = parent.attr('id').substr( 9, parent.attr('id').length );

        if (typeof trs_get_cookies == 'function')
            var cookie = trs_get_cookies();
        else
            var cookie = encodeURIComponent(document.cookie);

        jq.post( ajaxurl, {
            action: 'update_activity_privacy',
            'cookie': cookie,
            'visibility': jq(this).val(),
            'id': parent_id
        },

        function(response) {
        });
    }
    //  return false;
    });

    //fix the scroll problem
    //jq('#field').unbind('focus');
    /*
    jq('#field').bind('focus', function(){
        jq("#whats-new-options").css('height','auto');
        jq("form#post-intro textarea").animate({
            height:'3.8em'
        });
        jq("#submit-post").prop("disabled", false);
    });
    */
    //fix the scroll problem
    if ( 'border-box' !== jq( '#field' ).css( 'box-sizing' ) ) {
        jq('#field').unbind('focus');
        jq('#field').bind('focus', function(){
            jq("#whats-new-options").css('height','auto');

            jq("form#post-intro textarea").animate({
                height:'50px'
            });
            jq("#submit-post").prop("disabled", false);
        });
    }



    jq('span#activity-visibility').prependTo('div#whats-new-submit');
    jq("input#submit-post").unbind("click");

    var selected_item_id = jq("select#whats-new-post-in").val();

    jq("select#whats-new-post-in").data('selected', selected_item_id );
    //if selected item is not 'My profil'
    if( selected_item_id != undefined && selected_item_id != 0 ){
        jq('select#activity-privacy').replaceWith(TRS_DTheme.groups);
    }

    jq("select#whats-new-post-in").bind("change", function() {
        var old_selected_item_id = jq(this).data('selected');
        var item_id = jq("#whats-new-post-in").val();

        if(item_id == 0 && item_id != old_selected_item_id){
            jq('select#activity-privacy').replaceWith(TRS_DTheme.profil);
        }else{
            if(item_id != 0 && old_selected_item_id == 0 ){
                jq('select#activity-privacy').replaceWith(TRS_DTheme.groups);
            }
        }
        jq('select#activity-privacy').next().remove();
        if(TRS_DTheme.custom_selectbox) {
            //jq('select#activity-privacy').customStyle('1');
            jq('select.trs-ap-selectbox').customSelect();
        }
        jq(this).data('selected',item_id);
    });
    /* New posts */
    jq("input#submit-post").bind('click', function() {

                /* Default POST values */
                var object = '';
                var item_id = jq("#whats-new-post-in").val();
                var visibility = jq("select#activity-privacy").val();
var content = jq("textarea#field").val();
                /* Set object for non-profile posts */
                if ( item_id > 0 ) {
                    object = jq("#whats-new-post-object").val();
                }

//asamir intigrate the privacy with act plus

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

        if (typeof trs_get_cookies == 'function')
            var cookie = trs_get_cookies();
        else
            var cookie = encodeURIComponent(document.cookie);

        jq.post( ajaxurl, {
            action: 'post_update',
            'cookie': cookie,
            '_key_post_update': jq("input#_key_post_update").val(),
            'content': content,
            'visibility': visibility,
            'object': object,
            'item_id': item_id,
            'period_in_min':form.find('#period_in_min').val(),

            '_trs_as_nonce': jq('#_trs_as_nonce').val() || ''
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

                if ( 0 != jq("#latest-update").length ) {
                    var l = jq(".broadcast-inn p").html();
                    var v = jq(" a.view").attr('href');

                    var ltext = jq(" .broadcast-inn p").text();

                    var u = '';
                    if ( ltext != '' )
                        u = l + ' ';

                    u += '<a href="' + v + '" rel="nofollow">' + TR_Theme.view + '</a>';

                    jq("#latest-update").slideUp(300,function(){
                        jq("#latest-update").html( u );
                        jq("#latest-update").slideDown(300);
                    });
                }

                jq("li.new-update").hide().slideDown( 300 );
                jq("li.new-update").removeClass( 'new-update' );
                jq("textarea#field").val('');

                jq('#period_in_min').val(0);
                jq('#duration').css('display','none');
                jq('#isad')[0].checked = false;


            }

            /*
            jq("#whats-new-options").animate({
                height:'0px'
            });
            jq("form#post-intro textarea").animate({
                height:'20px'
            });*/


            jq("#submit-post").prop("disabled", true).removeClass('loading');

            //reset the privacy selection
            jq("select#activity-privacy option[selected]").prop('selected', true).trigger('change');

            if(TRS_DTheme.custom_selectbox) {
                //jq('select.trs-ap-selectbox').customStyle('2');
                jq('select.trs-ap-selectbox').customSelect();
            }
        });

        return false;
    });

    if(TRS_DTheme.custom_selectbox) {
        jq('select#activity-privacy').customSelect();
        jq('select.trs-ap-selectbox').customSelect();
        //jq('select#activity-privacy').customStyle('1');
        //jq('select.trs-ap-selectbox').customStyle('2');
    }
});



    if ( typeof jq == "undefined" )
        var jq = jQuery;

    jq(document).ready( function() {

        form = jq("#post-intro");
        text = form.find('textarea[name="field"]');
        //remove event handler previously attached to #med_submit
         try {
            jq("#med_submit").die( "click" );
         } catch(e) {
            jq("#med_submit").off( "click");
         }

        jq(document).delegate("#med_submit", 'click', function (event) {

            event.preventDefault();
            var params = _medActiveHandler.get();
            var group_id = jq('#whats-new-post-in').length ?jq('#whats-new-post-in').val() : 0;
            
            jq.post(ajaxurl, {
                "action": "med_update_activity_contents", 
                "data": params, 
                // add visibility level to the ajax post
                "visibility" : jq("select#activity-privacy").val(),
                "content": text.val(), 
                "group_id": group_id,
                'period_in_min':jq('#period_in_min').val(),

            }, function (data) {
                _medActiveHandler.destroy();
                text.val('');

                jq('#period_in_min').val(0);
                jq('#publish').prepend(data.activity);
                /**
                 * Handle image scaling in previews.
                 */
                jq(".med_final_link img").each(function () {
                    jq(this).width(jq(this).parents('div').width());
                });

                //reset the privacy selection
                jq("select#activity-privacy option[selected]").prop('selected', true).trigger('change');


                        });
        });
    });


/* Recommended */
jQuery(document).ready(function(){
    
    jQuery('.recommend_more').click(function(){
        jQuery('.recommend_more').addClass('loading');
        jQuery.ajax({
            url:ajax_url,
            data:{action:'get_recommended',page:jQuery('.current_index').val()},
            type:'post',
            success:function(res)
            {
                res = JSON.parse(res);
                jQuery('.recommend_more').removeClass('loading');
                jQuery("#skeleton ul.publish-piece").append(res.content);
                jQuery('.current_index').val(res.page);
                if(res.page > res.total_page)
                {
                    jQuery('.recommend_more').hide();
                }
            }
        })
    })
})

///openup modal form
$('.openup').click(function (e) {
        e.preventDefault();
 $('#post-intro').fadeToggle(0);
 $(".openup").toggleClass('selected');
      $(".dim").addClass('selected', 0, "easeOutSine");

  $('.dim').css({    display:'inline',
 position: 'fixed',
 
});

 $('html, body').css({    overflow: 'hidden',});    });

$(document).click(function (e) {
        if ($(e.target).closest('#post-intro').length > 0 || $(e.target).closest('.openup').length > 0) return;
        $('#post-intro').fadeOut(0);

  $('.dim').css({    display:'none',});
 $('html, body').css({    overflow: 'visible',});    });


    $(".dim-close").click(function(){
      $('#post-intro').fadeOut(0);  

  $('.dim').css({    display:'none',});
 $('html, body').css({    overflow: 'visible',});  });

    $(".head-login").click(function(){
       $('#login-header').fadeToggle(0);

 });

    jq('div.contour-inner ul').load( function() {
                        $('#skeleton').css('display','none');



    });

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


$(document).ready(function(){
$('html').on('click','li#f6',function(){
      $("#site-f2").hide();
      $("#site-f11 iframe").hide();
           $("#site-f12 iframe").hide();

      $("#inbox").hide();
      $("#info").hide();
      $("#profile").hide();


     $("#site-f1 .activity").show();
      $("#site-f1").show();
    $("#site-content").show();


        $("iframe#blah ").hide();
        // $('iframe#blah ').css('opacity','0');
$('ul.publish-piece').css('column-count','3');
$('ul.publish-piece').css('column-gap','2px');
$('.article-piece .activity').css('margin-top','-2px');

      $('div.broadcast-knobs').css('display','none');
      $('div.ac-reply-content').css('display','none');
     $('div.activity-comments').css('display','none');

      $('div.broadcast-inn p').css('display','none');
      $('div.trs-img p').css('display','inline');

  });





$('html').on('click','li#f3',function(){
     $("#site-f1").hide();
     $("#site-f11  iframe").hide();
     $("#site-f12 iframe").hide();

     $("#site-f1").hide();
     $("#site-f1 .activity").hide();
      $("#inbox").hide();
      $("#info").hide();
      $("#profile").hide();

    $("#site-f2").show();
    $("#site-content").show();


   //  $("#site-f2").hide();
   //  $("#site-f11 iframe").hide();
   //  $("#site-f1").hide();
   //  $("#site-f00 iframe").show();
        $("iframe#blah ").hide();
       //  $('iframe#blah ').css('opacity','0');

  });



$('html').on('click','li#f2',function(){
      $("#site-f1").hide();
      $("#site-f2").hide();
           $("#site-f12 iframe").hide();

      $("#inbox").hide();
      $("#info").hide();
      $("#profile").hide();
    $("#site-content").hide();

      $("#site-f11 .frame").show();
      $(".frame iframe").hide();
       //  $('iframe#blah ').css('opacity','0');

  });



$('html').on('click','li#f5',function(){
       $("#site-f1").hide();
      $("#site-f2").hide();
      $("#site-f11 iframe").hide();




      $("#site-f12 iframe").show();
      $("li#l-inbox").hide();



      $("#profile-links").hide();
      $("iframe#blah").hide();
        // $('iframe#blah ').css('opacity','0');
      $("#blah").hide();

      $("#site-content").hide();

  });

$('html ').on('click','a img.portrait',function(){
        $("iframe#blah ").show();
      $("#site-content").hide();
       //  $('iframe#blah ').css('opacity','1');


  });




$('li#f5').click(function(){
$("iframe#blah ").hide();

$("iframe#blah ").hide();
  });



$('li#f2').click(function(){



  });


$('html .profile-page').on('click','li#f5',function(){


  });


                });


jQuery(document).ready(function(){
    $('a[href^="https://"]').not('a[href*=gusdecool]').attr('target','blah');
    //$('a').setAttribute('target','blah');
})

jQuery.cookie=function(name,value,options){if(typeof value!='undefined'){options=options||{};if(value===null){value='';options.expires=-1;}var expires='';if(options.expires&&(typeof options.expires=='number'||options.expires.toUTCString)){var date;if(typeof options.expires=='number'){date=new Date();date.setTime(date.getTime()+(options.expires*24*60*60*1000));}else{date=options.expires;}expires='; expires='+date.toUTCString();}var path=options.path?'; path='+(options.path):'';var domain=options.domain?'; domain='+(options.domain):'';var secure=options.secure?'; secure':'';document.cookie=[name,'=',encodeURIComponent(value),expires,path,domain,secure].join('');}else{var cookieValue=null;if(document.cookie&&document.cookie!=''){var cookies=document.cookie.split(';');for(var i=0;i<cookies.length;i++){var cookie=jQuery.trim(cookies[i]);if(cookie.substring(0,name.length+1)==(name+'=')){cookieValue=decodeURIComponent(cookie.substring(name.length+1));break;}}}return cookieValue;}};

/* jQuery querystring plugin */
eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('M 6(A){4 $11=A.11||\'&\';4 $V=A.V===r?r:j;4 $1p=A.1p===r?\'\':\'[]\';4 $13=A.13===r?r:j;4 $D=$13?A.D===j?"#":"?":"";4 $15=A.15===r?r:j;v.1o=M 6(){4 f=6(o,t){8 o!=1v&&o!==x&&(!!t?o.1t==t:j)};4 14=6(1m){4 m,1l=/\\[([^[]*)\\]/g,T=/^([^[]+)(\\[.*\\])?$/.1r(1m),k=T[1],e=[];19(m=1l.1r(T[2]))e.u(m[1]);8[k,e]};4 w=6(3,e,7){4 o,y=e.1b();b(I 3!=\'X\')3=x;b(y===""){b(!3)3=[];b(f(3,L)){3.u(e.h==0?7:w(x,e.z(0),7))}n b(f(3,1a)){4 i=0;19(3[i++]!=x);3[--i]=e.h==0?7:w(3[i],e.z(0),7)}n{3=[];3.u(e.h==0?7:w(x,e.z(0),7))}}n b(y&&y.T(/^\\s*[0-9]+\\s*$/)){4 H=1c(y,10);b(!3)3=[];3[H]=e.h==0?7:w(3[H],e.z(0),7)}n b(y){4 H=y.B(/^\\s*|\\s*$/g,"");b(!3)3={};b(f(3,L)){4 18={};1w(4 i=0;i<3.h;++i){18[i]=3[i]}3=18}3[H]=e.h==0?7:w(3[H],e.z(0),7)}n{8 7}8 3};4 C=6(a){4 p=d;p.l={};b(a.C){v.J(a.Z(),6(5,c){p.O(5,c)})}n{v.J(1u,6(){4 q=""+d;q=q.B(/^[?#]/,\'\');q=q.B(/[;&]$/,\'\');b($V)q=q.B(/[+]/g,\' \');v.J(q.Y(/[&;]/),6(){4 5=1e(d.Y(\'=\')[0]||"");4 c=1e(d.Y(\'=\')[1]||"");b(!5)8;b($15){b(/^[+-]?[0-9]+\\.[0-9]*$/.1d(c))c=1A(c);n b(/^[+-]?[0-9]+$/.1d(c))c=1c(c,10)}c=(!c&&c!==0)?j:c;b(c!==r&&c!==j&&I c!=\'1g\')c=c;p.O(5,c)})})}8 p};C.1H={C:j,1G:6(5,1f){4 7=d.Z(5);8 f(7,1f)},1h:6(5){b(!f(5))8 d.l;4 K=14(5),k=K[0],e=K[1];4 3=d.l[k];19(3!=x&&e.h!=0){3=3[e.1b()]}8 I 3==\'1g\'?3:3||""},Z:6(5){4 3=d.1h(5);b(f(3,1a))8 v.1E(j,{},3);n b(f(3,L))8 3.z(0);8 3},O:6(5,c){4 7=!f(c)?x:c;4 K=14(5),k=K[0],e=K[1];4 3=d.l[k];d.l[k]=w(3,e.z(0),7);8 d},w:6(5,c){8 d.N().O(5,c)},1s:6(5){8 d.O(5,x).17()},1z:6(5){8 d.N().1s(5)},1j:6(){4 p=d;v.J(p.l,6(5,7){1y p.l[5]});8 p},1F:6(Q){4 D=Q.B(/^.*?[#](.+?)(?:\\?.+)?$/,"$1");4 S=Q.B(/^.*?[?](.+?)(?:#.+)?$/,"$1");8 M C(Q.h==S.h?\'\':S,Q.h==D.h?\'\':D)},1x:6(){8 d.N().1j()},N:6(){8 M C(d)},17:6(){6 F(G){4 R=I G=="X"?f(G,L)?[]:{}:G;b(I G==\'X\'){6 1k(o,5,7){b(f(o,L))o.u(7);n o[5]=7}v.J(G,6(5,7){b(!f(7))8 j;1k(R,5,F(7))})}8 R}d.l=F(d.l);8 d},1B:6(){8 d.N().17()},1D:6(){4 i=0,U=[],W=[],p=d;4 16=6(E){E=E+"";b($V)E=E.B(/ /g,"+");8 1C(E)};4 1n=6(1i,5,7){b(!f(7)||7===r)8;4 o=[16(5)];b(7!==j){o.u("=");o.u(16(7))}1i.u(o.P(""))};4 F=6(R,k){4 12=6(5){8!k||k==""?[5].P(""):[k,"[",5,"]"].P("")};v.J(R,6(5,7){b(I 7==\'X\')F(7,12(5));n 1n(W,12(5),7)})};F(d.l);b(W.h>0)U.u($D);U.u(W.P($11));8 U.P("")}};8 M C(1q.S,1q.D)}}(v.1o||{});',62,106,'|||target|var|key|function|value|return|||if|val|this|tokens|is||length||true|base|keys||else||self||false|||push|jQuery|set|null|token|slice|settings|replace|queryObject|hash|str|build|orig|index|typeof|each|parsed|Array|new|copy|SET|join|url|obj|search|match|queryString|spaces|chunks|object|split|get||separator|newKey|prefix|parse|numbers|encode|COMPACT|temp|while|Object|shift|parseInt|test|decodeURIComponent|type|number|GET|arr|EMPTY|add|rx|path|addFields|query|suffix|location|exec|REMOVE|constructor|arguments|undefined|for|empty|delete|remove|parseFloat|compact|encodeURIComponent|toString|extend|load|has|prototype'.split('|'),0,{}))
;(function () {
    'use strict';


(function(){var t=this;(function(){(function(){this.Turbolinks={supported:function(){return null!=window.history.pushState&&null!=window.requestAnimationFrame&&null!=window.addEventListener}(),visit:function(t,r){return e.controller.visit(t,r)},clearCache:function(){return e.controller.clearCache()},setProgressBarDelay:function(t){return e.controller.setProgressBarDelay(t)}}}).call(this)}).call(t);var e=t.Turbolinks;(function(){(function(){var t,r,n,o=[].slice;e.copyObject=function(t){var e,r,n;r={};for(e in t)n=t[e],r[e]=n;return r},e.closest=function(e,r){return t.call(e,r)},t=function(){var t,e;return t=document.documentElement,null!=(e=t.closest)?e:function(t){var e;for(e=this;e;){if(e.nodeType===Node.ELEMENT_NODE&&r.call(e,t))return e;e=e.parentNode}}}(),e.defer=function(t){return setTimeout(t,1)},e.throttle=function(t){var e;return e=null,function(){var r;return r=1<=arguments.length?o.call(arguments,0):[],null!=e?e:e=requestAnimationFrame(function(n){return function(){return e=null,t.apply(n,r)}}(this))}},e.dispatch=function(t,e){var r,o,i,s,a,u;return a=null!=e?e:{},u=a.target,r=a.cancelable,o=a.data,i=document.createEvent("Events"),i.initEvent(t,!0,r===!0),i.data=null!=o?o:{},i.cancelable&&!n&&(s=i.preventDefault,i.preventDefault=function(){return this.defaultPrevented||Object.defineProperty(this,"defaultPrevented",{get:function(){return!0}}),s.call(this)}),(null!=u?u:document).dispatchEvent(i),i},n=function(){var t;return t=document.createEvent("Events"),t.initEvent("test",!0,!0),t.preventDefault(),t.defaultPrevented}(),e.match=function(t,e){return r.call(t,e)},r=function(){var t,e,r,n;return t=document.documentElement,null!=(e=null!=(r=null!=(n=t.matchesSelector)?n:t.webkitMatchesSelector)?r:t.msMatchesSelector)?e:t.mozMatchesSelector}(),e.uuid=function(){var t,e,r;for(r="",t=e=1;36>=e;t=++e)r+=9===t||14===t||19===t||24===t?"-":15===t?"4":20===t?(Math.floor(4*Math.random())+8).toString(16):Math.floor(15*Math.random()).toString(16);return r}}).call(this),function(){e.Location=function(){function t(t){var e,r;null==t&&(t=""),r=document.createElement("a"),r.href=t.toString(),this.absoluteURL=r.href,e=r.hash.length,2>e?this.requestURL=this.absoluteURL:(this.requestURL=this.absoluteURL.slice(0,-e),this.anchor=r.hash.slice(1))}var e,r,n,o;return t.wrap=function(t){return t instanceof this?t:new this(t)},t.prototype.getOrigin=function(){return this.absoluteURL.split("/",3).join("/")},t.prototype.getPath=function(){var t,e;return null!=(t=null!=(e=this.requestURL.match(/\/\/[^\/]*(\/[^?;]*)/))?e[1]:void 0)?t:"/"},t.prototype.getPathComponents=function(){return this.getPath().split("/").slice(1)},t.prototype.getLastPathComponent=function(){return this.getPathComponents().slice(-1)[0]},t.prototype.getExtension=function(){var t,e;return null!=(t=null!=(e=this.getLastPathComponent().match(/\.[^.]*$/))?e[0]:void 0)?t:""},t.prototype.isHTML=function(){return this.getExtension().match(/^(?:|\.(?:htm|html|xhtml))$/)},t.prototype.isPrefixedBy=function(t){var e;return e=r(t),this.isEqualTo(t)||o(this.absoluteURL,e)},t.prototype.isEqualTo=function(t){return this.absoluteURL===(null!=t?t.absoluteURL:void 0)},t.prototype.toCacheKey=function(){return this.requestURL},t.prototype.toJSON=function(){return this.absoluteURL},t.prototype.toString=function(){return this.absoluteURL},t.prototype.valueOf=function(){return this.absoluteURL},r=function(t){return e(t.getOrigin()+t.getPath())},e=function(t){return n(t,"/")?t:t+"/"},o=function(t,e){return t.slice(0,e.length)===e},n=function(t,e){return t.slice(-e.length)===e},t}()}.call(this),function(){var t=function(t,e){return function(){return t.apply(e,arguments)}};e.HttpRequest=function(){function r(r,n,o){this.delegate=r,this.requestCanceled=t(this.requestCanceled,this),this.requestTimedOut=t(this.requestTimedOut,this),this.requestFailed=t(this.requestFailed,this),this.requestLoaded=t(this.requestLoaded,this),this.requestProgressed=t(this.requestProgressed,this),this.url=e.Location.wrap(n).requestURL,this.referrer=e.Location.wrap(o).absoluteURL,this.createXHR()}return r.NETWORK_FAILURE=0,r.TIMEOUT_FAILURE=-1,r.timeout=60,r.prototype.send=function(){var t;return this.xhr&&!this.sent?(this.notifyApplicationBeforeRequestStart(),this.setProgress(0),this.xhr.send(),this.sent=!0,"function"==typeof(t=this.delegate).requestStarted?t.requestStarted():void 0):void 0},r.prototype.cancel=function(){return this.xhr&&this.sent?this.xhr.abort():void 0},r.prototype.requestProgressed=function(t){return t.lengthComputable?this.setProgress(t.loaded/t.total):void 0},r.prototype.requestLoaded=function(){return this.endRequest(function(t){return function(){var e;return 200<=(e=t.xhr.status)&&300>e?t.delegate.requestCompletedWithResponse(t.xhr.responseText,t.xhr.getResponseHeader("Turbolinks-Location")):(t.failed=!0,t.delegate.requestFailedWithStatusCode(t.xhr.status,t.xhr.responseText))}}(this))},r.prototype.requestFailed=function(){return this.endRequest(function(t){return function(){return t.failed=!0,t.delegate.requestFailedWithStatusCode(t.constructor.NETWORK_FAILURE)}}(this))},r.prototype.requestTimedOut=function(){return this.endRequest(function(t){return function(){return t.failed=!0,t.delegate.requestFailedWithStatusCode(t.constructor.TIMEOUT_FAILURE)}}(this))},r.prototype.requestCanceled=function(){return this.endRequest()},r.prototype.notifyApplicationBeforeRequestStart=function(){return e.dispatch("turbolinks:request-start",{data:{url:this.url,xhr:this.xhr}})},r.prototype.notifyApplicationAfterRequestEnd=function(){return e.dispatch("turbolinks:request-end",{data:{url:this.url,xhr:this.xhr}})},r.prototype.createXHR=function(){return this.xhr=new XMLHttpRequest,this.xhr.open("GET",this.url,!0),this.xhr.timeout=1e3*this.constructor.timeout,this.xhr.setRequestHeader("Accept","text/html, application/xhtml+xml"),this.xhr.setRequestHeader("Turbolinks-Referrer",this.referrer),this.xhr.onprogress=this.requestProgressed,this.xhr.onload=this.requestLoaded,this.xhr.onerror=this.requestFailed,this.xhr.ontimeout=this.requestTimedOut,this.xhr.onabort=this.requestCanceled},r.prototype.endRequest=function(t){return this.xhr?(this.notifyApplicationAfterRequestEnd(),null!=t&&t.call(this),this.destroy()):void 0},r.prototype.setProgress=function(t){var e;return this.progress=t,"function"==typeof(e=this.delegate).requestProgressed?e.requestProgressed(this.progress):void 0},r.prototype.destroy=function(){var t;return this.setProgress(1),"function"==typeof(t=this.delegate).requestFinished&&t.requestFinished(),this.delegate=null,this.xhr=null},r}()}.call(this),function(){var t=function(t,e){return function(){return t.apply(e,arguments)}};e.ProgressBar=function(){function e(){this.trickle=t(this.trickle,this),this.stylesheetElement=this.createStylesheetElement(),this.progressElement=this.createProgressElement()}var r;return r=300,e.defaultCSS=".turbolinks-progress-bar {\n  position: fixed;\n  display: block;\n  top: 0;\n  left: 0;\n  height: 3px;\n  background: #0076ff;\n  z-index: 9999;\n  transition: width "+r+"ms ease-out, opacity "+r/2+"ms "+r/2+"ms ease-in;\n  transform: translate3d(0, 0, 0);\n}",e.prototype.show=function(){return this.visible?void 0:(this.visible=!0,this.installStylesheetElement(),this.installProgressElement(),this.startTrickling())},e.prototype.hide=function(){return this.visible&&!this.hiding?(this.hiding=!0,this.fadeProgressElement(function(t){return function(){return t.uninstallProgressElement(),t.stopTrickling(),t.visible=!1,t.hiding=!1}}(this))):void 0},e.prototype.setValue=function(t){return this.value=t,this.refresh()},e.prototype.installStylesheetElement=function(){return document.head.insertBefore(this.stylesheetElement,document.head.firstChild)},e.prototype.installProgressElement=function(){return this.progressElement.style.width=0,this.progressElement.style.opacity=1,document.documentElement.insertBefore(this.progressElement,document.body),this.refresh()},e.prototype.fadeProgressElement=function(t){return this.progressElement.style.opacity=0,setTimeout(t,1.5*r)},e.prototype.uninstallProgressElement=function(){return this.progressElement.parentNode?document.documentElement.removeChild(this.progressElement):void 0},e.prototype.startTrickling=function(){return null!=this.trickleInterval?this.trickleInterval:this.trickleInterval=setInterval(this.trickle,r)},e.prototype.stopTrickling=function(){return clearInterval(this.trickleInterval),this.trickleInterval=null},e.prototype.trickle=function(){return this.setValue(this.value+Math.random()/100)},e.prototype.refresh=function(){return requestAnimationFrame(function(t){return function(){return t.progressElement.style.width=10+90*t.value+"%"}}(this))},e.prototype.createStylesheetElement=function(){var t;return t=document.createElement("style"),t.type="text/css",t.textContent=this.constructor.defaultCSS,t},e.prototype.createProgressElement=function(){var t;return t=document.createElement("div"),t.className="turbolinks-progress-bar",t},e}()}.call(this),function(){var t=function(t,e){return function(){return t.apply(e,arguments)}};e.BrowserAdapter=function(){function r(r){this.controller=r,this.showProgressBar=t(this.showProgressBar,this),this.progressBar=new e.ProgressBar}var n,o,i;return i=e.HttpRequest,n=i.NETWORK_FAILURE,o=i.TIMEOUT_FAILURE,r.prototype.visitProposedToLocationWithAction=function(t,e){return this.controller.startVisitToLocationWithAction(t,e)},r.prototype.visitStarted=function(t){return t.issueRequest(),t.changeHistory(),t.loadCachedSnapshot()},r.prototype.visitRequestStarted=function(t){return this.progressBar.setValue(0),t.hasCachedSnapshot()||"restore"!==t.action?this.showProgressBarAfterDelay():this.showProgressBar()},r.prototype.visitRequestProgressed=function(t){return this.progressBar.setValue(t.progress)},r.prototype.visitRequestCompleted=function(t){return t.loadResponse()},r.prototype.visitRequestFailedWithStatusCode=function(t,e){switch(e){case n:case o:return this.reload();default:return t.loadResponse()}},r.prototype.visitRequestFinished=function(t){return this.hideProgressBar()},r.prototype.visitCompleted=function(t){return t.followRedirect()},r.prototype.pageInvalidated=function(){return this.reload()},r.prototype.showProgressBarAfterDelay=function(){return this.progressBarTimeout=setTimeout(this.showProgressBar,this.controller.progressBarDelay)},r.prototype.showProgressBar=function(){return this.progressBar.show()},r.prototype.hideProgressBar=function(){return this.progressBar.hide(),clearTimeout(this.progressBarTimeout)},r.prototype.reload=function(){return window.location.reload()},r}()}.call(this),function(){var t=function(t,e){return function(){return t.apply(e,arguments)}};e.History=function(){function r(e){this.delegate=e,this.onPageLoad=t(this.onPageLoad,this),this.onPopState=t(this.onPopState,this)}return r.prototype.start=function(){return this.started?void 0:(addEventListener("popstate",this.onPopState,!1),addEventListener("load",this.onPageLoad,!1),this.started=!0)},r.prototype.stop=function(){return this.started?(removeEventListener("popstate",this.onPopState,!1),removeEventListener("load",this.onPageLoad,!1),this.started=!1):void 0},r.prototype.push=function(t,r){return t=e.Location.wrap(t),this.update("push",t,r)},r.prototype.replace=function(t,r){return t=e.Location.wrap(t),this.update("replace",t,r)},r.prototype.onPopState=function(t){var r,n,o,i;return this.shouldHandlePopState()&&(i=null!=(n=t.state)?n.turbolinks:void 0)?(r=e.Location.wrap(window.location),o=i.restorationIdentifier,this.delegate.historyPoppedToLocationWithRestorationIdentifier(r,o)):void 0},r.prototype.onPageLoad=function(t){return e.defer(function(t){return function(){return t.pageLoaded=!0}}(this))},r.prototype.shouldHandlePopState=function(){return this.pageIsLoaded()},r.prototype.pageIsLoaded=function(){return this.pageLoaded||"complete"===document.readyState},r.prototype.update=function(t,e,r){var n;return n={turbolinks:{restorationIdentifier:r}},history[t+"State"](n,null,e)},r}()}.call(this),function(){e.HeadDetails=function(){function t(t){var e,r,n,s,a,u;for(this.elements={},n=0,a=t.length;a>n;n++)u=t[n],u.nodeType===Node.ELEMENT_NODE&&(s=u.outerHTML,r=null!=(e=this.elements)[s]?e[s]:e[s]={type:i(u),tracked:o(u),elements:[]},r.elements.push(u))}var e,r,n,o,i;return t.fromHeadElement=function(t){var e;return new this(null!=(e=null!=t?t.childNodes:void 0)?e:[])},t.prototype.hasElementWithKey=function(t){return t in this.elements},t.prototype.getTrackedElementSignature=function(){var t,e;return function(){var r,n;r=this.elements,n=[];for(t in r)e=r[t].tracked,e&&n.push(t);return n}.call(this).join("")},t.prototype.getScriptElementsNotInDetails=function(t){return this.getElementsMatchingTypeNotInDetails("script",t)},t.prototype.getStylesheetElementsNotInDetails=function(t){return this.getElementsMatchingTypeNotInDetails("stylesheet",t)},t.prototype.getElementsMatchingTypeNotInDetails=function(t,e){var r,n,o,i,s,a;o=this.elements,s=[];for(n in o)i=o[n],a=i.type,r=i.elements,a!==t||e.hasElementWithKey(n)||s.push(r[0]);return s},t.prototype.getProvisionalElements=function(){var t,e,r,n,o,i,s;r=[],n=this.elements;for(e in n)o=n[e],s=o.type,i=o.tracked,t=o.elements,null!=s||i?t.length>1&&r.push.apply(r,t.slice(1)):r.push.apply(r,t);return r},t.prototype.getMetaValue=function(t){var e;return null!=(e=this.findMetaElementByName(t))?e.getAttribute("content"):void 0},t.prototype.findMetaElementByName=function(t){var r,n,o,i;r=void 0,i=this.elements;for(o in i)n=i[o].elements,e(n[0],t)&&(r=n[0]);return r},i=function(t){return r(t)?"script":n(t)?"stylesheet":void 0},o=function(t){return"reload"===t.getAttribute("data-turbolinks-track")},r=function(t){var e;return e=t.tagName.toLowerCase(),"script"===e},n=function(t){var e;return e=t.tagName.toLowerCase(),"style"===e||"link"===e&&"stylesheet"===t.getAttribute("rel")},e=function(t,e){var r;return r=t.tagName.toLowerCase(),"meta"===r&&t.getAttribute("name")===e},t}()}.call(this),function(){e.Snapshot=function(){function t(t,e){this.headDetails=t,this.bodyElement=e}return t.wrap=function(t){return t instanceof this?t:"string"==typeof t?this.fromHTMLString(t):this.fromHTMLElement(t)},t.fromHTMLString=function(t){var e;return e=document.createElement("html"),e.innerHTML=t,this.fromHTMLElement(e)},t.fromHTMLElement=function(t){var r,n,o,i;return o=t.querySelector("head"),r=null!=(i=t.querySelector("body"))?i:document.createElement("body"),n=e.HeadDetails.fromHeadElement(o),new this(n,r)},t.prototype.clone=function(){return new this.constructor(this.headDetails,this.bodyElement.cloneNode(!0))},t.prototype.getRootLocation=function(){var t,r;return r=null!=(t=this.getSetting("root"))?t:"/",new e.Location(r)},t.prototype.getCacheControlValue=function(){return this.getSetting("cache-control")},t.prototype.getElementForAnchor=function(t){try{return this.bodyElement.querySelector("[id='"+t+"'], a[name='"+t+"']")}catch(e){}},t.prototype.getPermanentElements=function(){return this.bodyElement.querySelectorAll("[id][data-turbolinks-permanent]")},t.prototype.getPermanentElementById=function(t){return this.bodyElement.querySelector("#"+t+"[data-turbolinks-permanent]")},t.prototype.getPermanentElementsPresentInSnapshot=function(t){var e,r,n,o,i;for(o=this.getPermanentElements(),i=[],r=0,n=o.length;n>r;r++)e=o[r],t.getPermanentElementById(e.id)&&i.push(e);return i},t.prototype.findFirstAutofocusableElement=function(){return this.bodyElement.querySelector("[autofocus]")},t.prototype.hasAnchor=function(t){return null!=this.getElementForAnchor(t)},t.prototype.isPreviewable=function(){return"no-preview"!==this.getCacheControlValue()},t.prototype.isCacheable=function(){return"no-cache"!==this.getCacheControlValue()},t.prototype.isVisitable=function(){return"reload"!==this.getSetting("visit-control")},t.prototype.getSetting=function(t){return this.headDetails.getMetaValue("turbolinks-"+t)},t}()}.call(this),function(){var t=[].slice;e.Renderer=function(){function e(){}var r;return e.render=function(){var e,r,n,o;return n=arguments[0],r=arguments[1],e=3<=arguments.length?t.call(arguments,2):[],o=function(t,e,r){r.prototype=t.prototype;var n=new r,o=t.apply(n,e);return Object(o)===o?o:n}(this,e,function(){}),o.delegate=n,o.render(r),o},e.prototype.renderView=function(t){return this.delegate.viewWillRender(this.newBody),t(),this.delegate.viewRendered(this.newBody)},e.prototype.invalidateView=function(){return this.delegate.viewInvalidated()},e.prototype.createScriptElement=function(t){var e;return"false"===t.getAttribute("data-turbolinks-eval")?t:(e=document.createElement("script"),e.textContent=t.textContent,e.async=!1,r(e,t),e)},r=function(t,e){var r,n,o,i,s,a,u;for(i=e.attributes,a=[],r=0,n=i.length;n>r;r++)s=i[r],o=s.name,u=s.value,a.push(t.setAttribute(o,u));return a},e}()}.call(this),function(){var t,r,n=function(t,e){function r(){this.constructor=t}for(var n in e)o.call(e,n)&&(t[n]=e[n]);return r.prototype=e.prototype,t.prototype=new r,t.__super__=e.prototype,t},o={}.hasOwnProperty;e.SnapshotRenderer=function(e){function o(t,e,r){this.currentSnapshot=t,this.newSnapshot=e,this.isPreview=r,this.currentHeadDetails=this.currentSnapshot.headDetails,this.newHeadDetails=this.newSnapshot.headDetails,this.currentBody=this.currentSnapshot.bodyElement,this.newBody=this.newSnapshot.bodyElement}return n(o,e),o.prototype.render=function(t){return this.shouldRender()?(this.mergeHead(),this.renderView(function(e){return function(){return e.replaceBody(),e.isPreview||e.focusFirstAutofocusableElement(),t()}}(this))):this.invalidateView()},o.prototype.mergeHead=function(){return this.copyNewHeadStylesheetElements(),this.copyNewHeadScriptElements(),this.removeCurrentHeadProvisionalElements(),this.copyNewHeadProvisionalElements()},o.prototype.replaceBody=function(){var t;return t=this.relocateCurrentBodyPermanentElements(),this.activateNewBodyScriptElements(),this.assignNewBody(),this.replacePlaceholderElementsWithClonedPermanentElements(t)},o.prototype.shouldRender=function(){return this.newSnapshot.isVisitable()&&this.trackedElementsAreIdentical()},o.prototype.trackedElementsAreIdentical=function(){return this.currentHeadDetails.getTrackedElementSignature()===this.newHeadDetails.getTrackedElementSignature()},o.prototype.copyNewHeadStylesheetElements=function(){var t,e,r,n,o;for(n=this.getNewHeadStylesheetElements(),o=[],e=0,r=n.length;r>e;e++)t=n[e],o.push(document.head.appendChild(t));return o},o.prototype.copyNewHeadScriptElements=function(){var t,e,r,n,o;for(n=this.getNewHeadScriptElements(),o=[],e=0,r=n.length;r>e;e++)t=n[e],o.push(document.head.appendChild(this.createScriptElement(t)));return o},o.prototype.removeCurrentHeadProvisionalElements=function(){var t,e,r,n,o;for(n=this.getCurrentHeadProvisionalElements(),o=[],e=0,r=n.length;r>e;e++)t=n[e],o.push(document.head.removeChild(t));return o},o.prototype.copyNewHeadProvisionalElements=function(){var t,e,r,n,o;for(n=this.getNewHeadProvisionalElements(),o=[],e=0,r=n.length;r>e;e++)t=n[e],o.push(document.head.appendChild(t));return o},o.prototype.relocateCurrentBodyPermanentElements=function(){var e,n,o,i,s,a,u;for(a=this.getCurrentBodyPermanentElements(),u=[],e=0,n=a.length;n>e;e++)i=a[e],s=t(i),o=this.newSnapshot.getPermanentElementById(i.id),r(i,s.element),r(o,i),u.push(s);return u},o.prototype.replacePlaceholderElementsWithClonedPermanentElements=function(t){var e,n,o,i,s,a,u;for(u=[],o=0,i=t.length;i>o;o++)a=t[o],n=a.element,s=a.permanentElement,e=s.cloneNode(!0),u.push(r(n,e));return u},o.prototype.activateNewBodyScriptElements=function(){var t,e,n,o,i,s;for(i=this.getNewBodyScriptElements(),s=[],e=0,o=i.length;o>e;e++)n=i[e],t=this.createScriptElement(n),s.push(r(n,t));return s},o.prototype.assignNewBody=function(){return document.body=this.newBody},o.prototype.focusFirstAutofocusableElement=function(){var t;return null!=(t=this.newSnapshot.findFirstAutofocusableElement())?t.focus():void 0},o.prototype.getNewHeadStylesheetElements=function(){return this.newHeadDetails.getStylesheetElementsNotInDetails(this.currentHeadDetails)},o.prototype.getNewHeadScriptElements=function(){return this.newHeadDetails.getScriptElementsNotInDetails(this.currentHeadDetails)},o.prototype.getCurrentHeadProvisionalElements=function(){return this.currentHeadDetails.getProvisionalElements()},o.prototype.getNewHeadProvisionalElements=function(){return this.newHeadDetails.getProvisionalElements()},o.prototype.getCurrentBodyPermanentElements=function(){return this.currentSnapshot.getPermanentElementsPresentInSnapshot(this.newSnapshot)},o.prototype.getNewBodyScriptElements=function(){return this.newBody.querySelectorAll("script")},o}(e.Renderer),t=function(t){var e;return e=document.createElement("meta"),e.setAttribute("name","turbolinks-permanent-placeholder"),e.setAttribute("content",t.id),{element:e,permanentElement:t}},r=function(t,e){var r;return(r=t.parentNode)?r.replaceChild(e,t):void 0}}.call(this),function(){var t=function(t,e){function n(){this.constructor=t}for(var o in e)r.call(e,o)&&(t[o]=e[o]);return n.prototype=e.prototype,t.prototype=new n,t.__super__=e.prototype,t},r={}.hasOwnProperty;e.ErrorRenderer=function(e){function r(t){var e;e=document.createElement("html"),e.innerHTML=t,this.newHead=e.querySelector("head"),this.newBody=e.querySelector("body")}return t(r,e),r.prototype.render=function(t){return this.renderView(function(e){return function(){return e.replaceHeadAndBody(),e.activateBodyScriptElements(),t()}}(this))},r.prototype.replaceHeadAndBody=function(){var t,e;return e=document.head,t=document.body,e.parentNode.replaceChild(this.newHead,e),t.parentNode.replaceChild(this.newBody,t)},r.prototype.activateBodyScriptElements=function(){var t,e,r,n,o,i;for(n=this.getScriptElements(),i=[],e=0,r=n.length;r>e;e++)o=n[e],t=this.createScriptElement(o),i.push(o.parentNode.replaceChild(t,o));return i},r.prototype.getScriptElements=function(){return document.documentElement.querySelectorAll("script")},r}(e.Renderer)}.call(this),function(){e.View=function(){function t(t){this.delegate=t,this.htmlElement=document.documentElement}return t.prototype.getRootLocation=function(){return this.getSnapshot().getRootLocation()},t.prototype.getElementForAnchor=function(t){return this.getSnapshot().getElementForAnchor(t)},t.prototype.getSnapshot=function(){return e.Snapshot.fromHTMLElement(this.htmlElement)},t.prototype.render=function(t,e){var r,n,o;return o=t.snapshot,r=t.error,n=t.isPreview,this.markAsPreview(n),null!=o?this.renderSnapshot(o,n,e):this.renderError(r,e)},t.prototype.markAsPreview=function(t){return t?this.htmlElement.setAttribute("data-turbolinks-preview",""):this.htmlElement.removeAttribute("data-turbolinks-preview")},t.prototype.renderSnapshot=function(t,r,n){return e.SnapshotRenderer.render(this.delegate,n,this.getSnapshot(),e.Snapshot.wrap(t),r)},t.prototype.renderError=function(t,r){return e.ErrorRenderer.render(this.delegate,r,t)},t}()}.call(this),function(){var t=function(t,e){return function(){return t.apply(e,arguments)}};e.ScrollManager=function(){function r(r){this.delegate=r,this.onScroll=t(this.onScroll,this),this.onScroll=e.throttle(this.onScroll)}return r.prototype.start=function(){return this.started?void 0:(addEventListener("scroll",this.onScroll,!1),this.onScroll(),this.started=!0)},r.prototype.stop=function(){return this.started?(removeEventListener("scroll",this.onScroll,!1),this.started=!1):void 0},r.prototype.scrollToElement=function(t){return t.scrollIntoView()},r.prototype.scrollToPosition=function(t){var e,r;return e=t.x,r=t.y,window.scrollTo(e,r)},r.prototype.onScroll=function(t){return this.updatePosition({x:window.pageXOffset,y:window.pageYOffset})},r.prototype.updatePosition=function(t){var e;return this.position=t,null!=(e=this.delegate)?e.scrollPositionChanged(this.position):void 0},r}()}.call(this),function(){e.SnapshotCache=function(){function t(t){this.size=t,this.keys=[],this.snapshots={}}var r;return t.prototype.has=function(t){var e;return e=r(t),e in this.snapshots},t.prototype.get=function(t){var e;if(this.has(t))return e=this.read(t),this.touch(t),e},t.prototype.put=function(t,e){return this.write(t,e),this.touch(t),e},t.prototype.read=function(t){var e;return e=r(t),this.snapshots[e]},t.prototype.write=function(t,e){var n;return n=r(t),this.snapshots[n]=e},t.prototype.touch=function(t){var e,n;return n=r(t),e=this.keys.indexOf(n),e>-1&&this.keys.splice(e,1),this.keys.unshift(n),this.trim()},t.prototype.trim=function(){var t,e,r,n,o;for(n=this.keys.splice(this.size),o=[],t=0,r=n.length;r>t;t++)e=n[t],o.push(delete this.snapshots[e]);return o},r=function(t){return e.Location.wrap(t).toCacheKey()},t}()}.call(this),function(){var t=function(t,e){return function(){return t.apply(e,arguments)}};e.Visit=function(){function r(r,n,o){this.controller=r,this.action=o,this.performScroll=t(this.performScroll,this),this.identifier=e.uuid(),this.location=e.Location.wrap(n),this.adapter=this.controller.adapter,this.state="initialized",this.timingMetrics={}}var n;return r.prototype.start=function(){return"initialized"===this.state?(this.recordTimingMetric("visitStart"),this.state="started",this.adapter.visitStarted(this)):void 0},r.prototype.cancel=function(){var t;return"started"===this.state?(null!=(t=this.request)&&t.cancel(),this.cancelRender(),this.state="canceled"):void 0},r.prototype.complete=function(){var t;return"started"===this.state?(this.recordTimingMetric("visitEnd"),this.state="completed","function"==typeof(t=this.adapter).visitCompleted&&t.visitCompleted(this),this.controller.visitCompleted(this)):void 0},r.prototype.fail=function(){var t;return"started"===this.state?(this.state="failed","function"==typeof(t=this.adapter).visitFailed?t.visitFailed(this):void 0):void 0},r.prototype.changeHistory=function(){var t,e;return this.historyChanged?void 0:(t=this.location.isEqualTo(this.referrer)?"replace":this.action,e=n(t),this.controller[e](this.location,this.restorationIdentifier),this.historyChanged=!0)},r.prototype.issueRequest=function(){return this.shouldIssueRequest()&&null==this.request?(this.progress=0,this.request=new e.HttpRequest(this,this.location,this.referrer),this.request.send()):void 0},r.prototype.getCachedSnapshot=function(){var t;return!(t=this.controller.getCachedSnapshotForLocation(this.location))||null!=this.location.anchor&&!t.hasAnchor(this.location.anchor)||"restore"!==this.action&&!t.isPreviewable()?void 0:t},r.prototype.hasCachedSnapshot=function(){return null!=this.getCachedSnapshot()},r.prototype.loadCachedSnapshot=function(){var t,e;return(e=this.getCachedSnapshot())?(t=this.shouldIssueRequest(),this.render(function(){var r;return this.cacheSnapshot(),this.controller.render({snapshot:e,isPreview:t},this.performScroll),"function"==typeof(r=this.adapter).visitRendered&&r.visitRendered(this),t?void 0:this.complete()})):void 0},r.prototype.loadResponse=function(){return null!=this.response?this.render(function(){var t,e;return this.cacheSnapshot(),this.request.failed?(this.controller.render({error:this.response},this.performScroll),"function"==typeof(t=this.adapter).visitRendered&&t.visitRendered(this),this.fail()):(this.controller.render({snapshot:this.response},this.performScroll),"function"==typeof(e=this.adapter).visitRendered&&e.visitRendered(this),this.complete())}):void 0},r.prototype.followRedirect=function(){return this.redirectedToLocation&&!this.followedRedirect?(this.location=this.redirectedToLocation,this.controller.replaceHistoryWithLocationAndRestorationIdentifier(this.redirectedToLocation,this.restorationIdentifier),this.followedRedirect=!0):void 0},r.prototype.requestStarted=function(){var t;return this.recordTimingMetric("requestStart"),"function"==typeof(t=this.adapter).visitRequestStarted?t.visitRequestStarted(this):void 0},r.prototype.requestProgressed=function(t){var e;return this.progress=t,"function"==typeof(e=this.adapter).visitRequestProgressed?e.visitRequestProgressed(this):void 0},r.prototype.requestCompletedWithResponse=function(t,r){return this.response=t,null!=r&&(this.redirectedToLocation=e.Location.wrap(r)),this.adapter.visitRequestCompleted(this)},r.prototype.requestFailedWithStatusCode=function(t,e){return this.response=e,this.adapter.visitRequestFailedWithStatusCode(this,t)},r.prototype.requestFinished=function(){var t;return this.recordTimingMetric("requestEnd"),"function"==typeof(t=this.adapter).visitRequestFinished?t.visitRequestFinished(this):void 0},r.prototype.performScroll=function(){return this.scrolled?void 0:("restore"===this.action?this.scrollToRestoredPosition()||this.scrollToTop():this.scrollToAnchor()||this.scrollToTop(),this.scrolled=!0)},r.prototype.scrollToRestoredPosition=function(){var t,e;return t=null!=(e=this.restorationData)?e.scrollPosition:void 0,null!=t?(this.controller.scrollToPosition(t),!0):void 0},r.prototype.scrollToAnchor=function(){return null!=this.location.anchor?(this.controller.scrollToAnchor(this.location.anchor),!0):void 0},r.prototype.scrollToTop=function(){return this.controller.scrollToPosition({x:0,y:0})},r.prototype.recordTimingMetric=function(t){var e;return null!=(e=this.timingMetrics)[t]?e[t]:e[t]=(new Date).getTime()},r.prototype.getTimingMetrics=function(){return e.copyObject(this.timingMetrics)},n=function(t){switch(t){case"replace":return"replaceHistoryWithLocationAndRestorationIdentifier";case"advance":case"restore":return"pushHistoryWithLocationAndRestorationIdentifier"}},r.prototype.shouldIssueRequest=function(){return"restore"===this.action?!this.hasCachedSnapshot():!0},r.prototype.cacheSnapshot=function(){return this.snapshotCached?void 0:(this.controller.cacheSnapshot(),this.snapshotCached=!0)},r.prototype.render=function(t){return this.cancelRender(),this.frame=requestAnimationFrame(function(e){return function(){return e.frame=null,t.call(e)}}(this))},r.prototype.cancelRender=function(){return this.frame?cancelAnimationFrame(this.frame):void 0},r}()}.call(this),function(){var t=function(t,e){return function(){return t.apply(e,arguments)}};e.Controller=function(){function r(){this.clickBubbled=t(this.clickBubbled,this),this.clickCaptured=t(this.clickCaptured,this),this.pageLoaded=t(this.pageLoaded,this),this.history=new e.History(this),this.view=new e.View(this),this.scrollManager=new e.ScrollManager(this),this.restorationData={},this.clearCache(),this.setProgressBarDelay(500)}return r.prototype.start=function(){return e.supported&&!this.started?(addEventListener("click",this.clickCaptured,!0),addEventListener("DOMContentLoaded",this.pageLoaded,!1),this.scrollManager.start(),this.startHistory(),this.started=!0,this.enabled=!0):void 0},r.prototype.disable=function(){return this.enabled=!1},r.prototype.stop=function(){return this.started?(removeEventListener("click",this.clickCaptured,!0),removeEventListener("DOMContentLoaded",this.pageLoaded,!1),this.scrollManager.stop(),this.stopHistory(),this.started=!1):void 0},r.prototype.clearCache=function(){return this.cache=new e.SnapshotCache(10)},r.prototype.visit=function(t,r){var n,o;return null==r&&(r={}),t=e.Location.wrap(t),this.applicationAllowsVisitingLocation(t)?this.locationIsVisitable(t)?(n=null!=(o=r.action)?o:"advance",this.adapter.visitProposedToLocationWithAction(t,n)):window.location=t:void 0},r.prototype.startVisitToLocationWithAction=function(t,r,n){var o;return e.supported?(o=this.getRestorationDataForIdentifier(n),this.startVisit(t,r,{restorationData:o})):window.location=t},r.prototype.setProgressBarDelay=function(t){return this.progressBarDelay=t},r.prototype.startHistory=function(){return this.location=e.Location.wrap(window.location),this.restorationIdentifier=e.uuid(),this.history.start(),this.history.replace(this.location,this.restorationIdentifier)},r.prototype.stopHistory=function(){return this.history.stop()},r.prototype.pushHistoryWithLocationAndRestorationIdentifier=function(t,r){return this.restorationIdentifier=r,this.location=e.Location.wrap(t),this.history.push(this.location,this.restorationIdentifier)},r.prototype.replaceHistoryWithLocationAndRestorationIdentifier=function(t,r){return this.restorationIdentifier=r,this.location=e.Location.wrap(t),this.history.replace(this.location,this.restorationIdentifier)},r.prototype.historyPoppedToLocationWithRestorationIdentifier=function(t,r){var n;return this.restorationIdentifier=r,this.enabled?(n=this.getRestorationDataForIdentifier(this.restorationIdentifier),this.startVisit(t,"restore",{restorationIdentifier:this.restorationIdentifier,restorationData:n,historyChanged:!0}),this.location=e.Location.wrap(t)):this.adapter.pageInvalidated()},r.prototype.getCachedSnapshotForLocation=function(t){var e;return null!=(e=this.cache.get(t))?e.clone():void 0},r.prototype.shouldCacheSnapshot=function(){return this.view.getSnapshot().isCacheable();
},r.prototype.cacheSnapshot=function(){var t,r;return this.shouldCacheSnapshot()?(this.notifyApplicationBeforeCachingSnapshot(),r=this.view.getSnapshot(),t=this.lastRenderedLocation,e.defer(function(e){return function(){return e.cache.put(t,r.clone())}}(this))):void 0},r.prototype.scrollToAnchor=function(t){var e;return(e=this.view.getElementForAnchor(t))?this.scrollToElement(e):this.scrollToPosition({x:0,y:0})},r.prototype.scrollToElement=function(t){return this.scrollManager.scrollToElement(t)},r.prototype.scrollToPosition=function(t){return this.scrollManager.scrollToPosition(t)},r.prototype.scrollPositionChanged=function(t){var e;return e=this.getCurrentRestorationData(),e.scrollPosition=t},r.prototype.render=function(t,e){return this.view.render(t,e)},r.prototype.viewInvalidated=function(){return this.adapter.pageInvalidated()},r.prototype.viewWillRender=function(t){return this.notifyApplicationBeforeRender(t)},r.prototype.viewRendered=function(){return this.lastRenderedLocation=this.currentVisit.location,this.notifyApplicationAfterRender()},r.prototype.pageLoaded=function(){return this.lastRenderedLocation=this.location,this.notifyApplicationAfterPageLoad()},r.prototype.clickCaptured=function(){return removeEventListener("click",this.clickBubbled,!1),addEventListener("click",this.clickBubbled,!1)},r.prototype.clickBubbled=function(t){var e,r,n;return this.enabled&&this.clickEventIsSignificant(t)&&(r=this.getVisitableLinkForNode(t.target))&&(n=this.getVisitableLocationForLink(r))&&this.applicationAllowsFollowingLinkToLocation(r,n)?(t.preventDefault(),e=this.getActionForLink(r),this.visit(n,{action:e})):void 0},r.prototype.applicationAllowsFollowingLinkToLocation=function(t,e){var r;return r=this.notifyApplicationAfterClickingLinkToLocation(t,e),!r.defaultPrevented},r.prototype.applicationAllowsVisitingLocation=function(t){var e;return e=this.notifyApplicationBeforeVisitingLocation(t),!e.defaultPrevented},r.prototype.notifyApplicationAfterClickingLinkToLocation=function(t,r){return e.dispatch("turbolinks:click",{target:t,data:{url:r.absoluteURL},cancelable:!0})},r.prototype.notifyApplicationBeforeVisitingLocation=function(t){return e.dispatch("turbolinks:before-visit",{data:{url:t.absoluteURL},cancelable:!0})},r.prototype.notifyApplicationAfterVisitingLocation=function(t){return e.dispatch("turbolinks:visit",{data:{url:t.absoluteURL}})},r.prototype.notifyApplicationBeforeCachingSnapshot=function(){return e.dispatch("turbolinks:before-cache")},r.prototype.notifyApplicationBeforeRender=function(t){return e.dispatch("turbolinks:before-render",{data:{newBody:t}})},r.prototype.notifyApplicationAfterRender=function(){return e.dispatch("turbolinks:render")},r.prototype.notifyApplicationAfterPageLoad=function(t){return null==t&&(t={}),e.dispatch("turbolinks:load",{data:{url:this.location.absoluteURL,timing:t}})},r.prototype.startVisit=function(t,e,r){var n;return null!=(n=this.currentVisit)&&n.cancel(),this.currentVisit=this.createVisit(t,e,r),this.currentVisit.start(),this.notifyApplicationAfterVisitingLocation(t)},r.prototype.createVisit=function(t,r,n){var o,i,s,a,u;return i=null!=n?n:{},a=i.restorationIdentifier,s=i.restorationData,o=i.historyChanged,u=new e.Visit(this,t,r),u.restorationIdentifier=null!=a?a:e.uuid(),u.restorationData=e.copyObject(s),u.historyChanged=o,u.referrer=this.location,u},r.prototype.visitCompleted=function(t){return this.notifyApplicationAfterPageLoad(t.getTimingMetrics())},r.prototype.clickEventIsSignificant=function(t){return!(t.defaultPrevented||t.target.isContentEditable||t.which>1||t.altKey||t.ctrlKey||t.metaKey||t.shiftKey)},r.prototype.getVisitableLinkForNode=function(t){return this.nodeIsVisitable(t)?e.closest(t,"a[href]:not([target]):not([download])"):void 0},r.prototype.getVisitableLocationForLink=function(t){var r;return r=new e.Location(t.getAttribute("href")),this.locationIsVisitable(r)?r:void 0},r.prototype.getActionForLink=function(t){var e;return null!=(e=t.getAttribute("data-turbolinks-action"))?e:"advance"},r.prototype.nodeIsVisitable=function(t){var r;return(r=e.closest(t,"[data-turbolinks]"))?"false"!==r.getAttribute("data-turbolinks"):!0},r.prototype.locationIsVisitable=function(t){return t.isPrefixedBy(this.view.getRootLocation())&&t.isHTML()},r.prototype.getCurrentRestorationData=function(){return this.getRestorationDataForIdentifier(this.restorationIdentifier)},r.prototype.getRestorationDataForIdentifier=function(t){var e;return null!=(e=this.restorationData)[t]?e[t]:e[t]={}},r}()}.call(this),function(){!function(){var t,e;if((t=e=document.currentScript)&&!e.hasAttribute("data-turbolinks-suppress-warning"))for(;t=t.parentNode;)if(t===document.body)return console.warn("You are loading Turbolinks from a <script> element inside the <body> element. This is probably not what you meant to do!\n\nLoad your application\u2019s JavaScript bundle inside the <head> element instead. <script> elements in <body> are evaluated with each page change.\n\nFor more information, see: https://github.com/turbolinks/turbolinks#working-with-script-elements\n\n\u2014\u2014\nSuppress this warning by adding a `data-turbolinks-suppress-warning` attribute to: %s",e.outerHTML)}()}.call(this),function(){var t,r,n;e.start=function(){return r()?(null==e.controller&&(e.controller=t()),e.controller.start()):void 0},r=function(){return null==window.Turbolinks&&(window.Turbolinks=e),n()},t=function(){var t;return t=new e.Controller,t.adapter=new e.BrowserAdapter(t),t},n=function(){return window.Turbolinks===e},n()&&e.start()}.call(this)}).call(this),"object"==typeof module&&module.exports?module.exports=e:"function"==typeof define&&define.amd&&define(e)}).call(this);

var Turbolinks = require("turbolinks")
Turbolinks.start()