

/////////////////////////////////////////////////////////////////////////// 
// TRENDR CORE 
///////////////////////////////////////////////////////////////////////////
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

                target.parent().hide();
            }, 'json' );

            return false;
        }
    });



/**
 * With version 2.0+, we simply simulate the click and the themes do the loading for us :)
 */
jQuery( document ).ready( function ($) {
    // We'll use this variable to make sure we don't send the request again and again.
    var $window = $( window );

    // Check the window scroll event.
  //  $window.scroll( function () {
      $(window).on("scroll ",function(){
        // Find the visible "load more" button.
        // since TRS does not remove the "load more" button, we need to find the last one that is visible.
        var $load_more_btn = $( '.infinite:visible' );
        // If there is no visible "load more" button, we've reached the last page of the activity stream.
        // If data attribute is set, we already triggered request for ths specific button.
        if ( ! $load_more_btn.get( 0 ) || $load_more_btn.data( 'autoloaded' ) ) {
            return;
        }

        // Find the offset of the button.
        var pos = $load_more_btn.offset();
        var offset = pos.top - 50;// 50 px before we reach the button.

        // If the window height+scrollTop is greater than the top offset of the "load more" button,
        // we have scrolled to the button's position. Let us load more activity.
        if ($window.scrollTop() + $window.height() > offset) {
            $load_more_btn.data( 'autoloaded', 1 );
            $load_more_btn.find( 'a' ).trigger( 'click' );
        }

    });
});// end of dom ready.


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
                    comment_li.fadeOut(200);

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
        jq(target).fadeOut( 100, function() {
            jq(this).html(response);
            jq(this).fadeIn(100);
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
                                jq.cookie( 'trs-activity-filter',filter, {path: location.pathname.replace(/\/$/, "")} );
            jq.cookie( 'trs-activity-filter-checkbox',(checkboxs), {path: location.pathname.replace(/\/$/, "") } );
                jq.cookie( 'trs-activity-scope', scope, {path: location.pathname.replace(/\/$/, "")} );


              //  jq.cookie( 'trs-activity-filter',filter, {path: "/"} );
          //  jq.cookie( 'trs-activity-filter-checkbox',(checkboxs), {path:   "/"} );
               // jq.cookie( 'trs-activity-scope', scope, {path:  "/"} );

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

        jq('div.activity').fadeOut( 100, function() {
            jq(this).html(response.contents);
            jq(this).fadeIn(100);

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




$(document).click(function (e) {
        if ($(e.target).closest('#login-header').length > 0 || $(e.target).closest('.head-login').length > 0) return;
       $('#login-header').fadeOut(200);

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

/* ScrollTo plugin - just inline and minified */
//;(function(d){var k=d.scrollTo=function(a,i,e){d(window).scrollTo(a,i,e)};k.defaults={axis:'xy',duration:parseFloat(d.fn.jquery)>=1.3?0:1};k.window=function(a){return d(window)._scrollable()};d.fn._scrollable=function(){return this.map(function(){var a=this,i=!a.nodeName||d.inArray(a.nodeName.toLowerCase(),['iframe','#document','html','body'])!=-1;if(!i)return a;var e=(a.contentWindow||a).document||a.ownerDocument||a;return d.browser.safari||e.compatMode=='BackCompat'?e.body:e.documentElement})};d.fn.scrollTo=function(n,j,b){if(typeof j=='object'){b=j;j=0}if(typeof b=='function')b={onAfter:b};if(n=='max')n=9e9;b=d.extend({},k.defaults,b);j=j||b.speed||b.duration;b.queue=b.queue&&b.axis.length>1;if(b.queue)j/=2;b.offset=p(b.offset);b.over=p(b.over);return this._scrollable().each(function(){var q=this,r=d(q),f=n,s,g={},u=r.is('html,body');switch(typeof f){case'number':case'string':if(/^([+-]=)?\d+(\.\d+)?(px|%)?$/.test(f)){f=p(f);break}f=d(f,this);case'object':if(f.is||f.style)s=(f=d(f)).offset()}d.each(b.axis.split(''),function(a,i){var e=i=='x'?'Left':'Top',h=e.toLowerCase(),c='scroll'+e,l=q[c],m=k.max(q,i);if(s){g[c]=s[h]+(u?0:l-r.offset()[h]);if(b.margin){g[c]-=parseInt(f.css('margin'+e))||0;g[c]-=parseInt(f.css('border'+e+'Width'))||0}g[c]+=b.offset[h]||0;if(b.over[h])g[c]+=f[i=='x'?'width':'height']()*b.over[h]}else{var o=f[h];g[c]=o.slice&&o.slice(-1)=='%'?parseFloat(o)/100*m:o}if(/^\d+$/.test(g[c]))g[c]=g[c]<=0?0:Math.min(g[c],m);if(!a&&b.queue){if(l!=g[c])t(b.onAfterFirst);delete g[c]}});t(b.onAfter);function t(a){r.animate(g,j,b.easing,a&&function(){a.call(this,n,b)})}}).end()};k.max=function(a,i){var e=i=='x'?'Width':'Height',h='scroll'+e;if(!d(a).is('html,body'))return a[h]-d(a)[e.toLowerCase()]();var c='client'+e,l=a.ownerDocument.documentElement,m=a.ownerDocument.body;return Math.max(l[h],m[h])-Math.min(l[c],m[c])};function p(a){return typeof a=='object'?a:{top:a,left:a}}})(jQuery);

/* jQuery Easing Plugin, v1.3 - http://gsgd.co.uk/sandbox/jquery/easing/ */
//jQuery.easing.jswing=jQuery.easing.swing;jQuery.extend(jQuery.easing,{def:"easeOutQuad",swing:function(e,f,a,h,g){return jQuery.easing[jQuery.easing.def](e,f,a,h,g)},easeInQuad:function(e,f,a,h,g){return h*(f/=g)*f+a},easeOutQuad:function(e,f,a,h,g){return -h*(f/=g)*(f-2)+a},easeInOutQuad:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f+a}return -h/2*((--f)*(f-2)-1)+a},easeInCubic:function(e,f,a,h,g){return h*(f/=g)*f*f+a},easeOutCubic:function(e,f,a,h,g){return h*((f=f/g-1)*f*f+1)+a},easeInOutCubic:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f+a}return h/2*((f-=2)*f*f+2)+a},easeInQuart:function(e,f,a,h,g){return h*(f/=g)*f*f*f+a},easeOutQuart:function(e,f,a,h,g){return -h*((f=f/g-1)*f*f*f-1)+a},easeInOutQuart:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f+a}return -h/2*((f-=2)*f*f*f-2)+a},easeInQuint:function(e,f,a,h,g){return h*(f/=g)*f*f*f*f+a},easeOutQuint:function(e,f,a,h,g){return h*((f=f/g-1)*f*f*f*f+1)+a},easeInOutQuint:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f*f+a}return h/2*((f-=2)*f*f*f*f+2)+a},easeInSine:function(e,f,a,h,g){return -h*Math.cos(f/g*(Math.PI/2))+h+a},easeOutSine:function(e,f,a,h,g){return h*Math.sin(f/g*(Math.PI/2))+a},easeInOutSine:function(e,f,a,h,g){return -h/2*(Math.cos(Math.PI*f/g)-1)+a},easeInExpo:function(e,f,a,h,g){return(f==0)?a:h*Math.pow(2,10*(f/g-1))+a},easeOutExpo:function(e,f,a,h,g){return(f==g)?a+h:h*(-Math.pow(2,-10*f/g)+1)+a},easeInOutExpo:function(e,f,a,h,g){if(f==0){return a}if(f==g){return a+h}if((f/=g/2)<1){return h/2*Math.pow(2,10*(f-1))+a}return h/2*(-Math.pow(2,-10*--f)+2)+a},easeInCirc:function(e,f,a,h,g){return -h*(Math.sqrt(1-(f/=g)*f)-1)+a},easeOutCirc:function(e,f,a,h,g){return h*Math.sqrt(1-(f=f/g-1)*f)+a},easeInOutCirc:function(e,f,a,h,g){if((f/=g/2)<1){return -h/2*(Math.sqrt(1-f*f)-1)+a}return h/2*(Math.sqrt(1-(f-=2)*f)+1)+a},easeInElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return -(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e},easeOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return g*Math.pow(2,-10*h)*Math.sin((h*k-i)*(2*Math.PI)/j)+l+e},easeInOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k/2)==2){return e+l}if(!j){j=k*(0.3*1.5)}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}if(h<1){return -0.5*(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e}return g*Math.pow(2,-10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j)*0.5+l+e},easeInBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*(f/=h)*f*((g+1)*f-g)+a},easeOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*((f=f/h-1)*f*((g+1)*f+g)+1)+a},easeInOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}if((f/=h/2)<1){return i/2*(f*f*(((g*=(1.525))+1)*f-g))+a}return i/2*((f-=2)*f*(((g*=(1.525))+1)*f+g)+2)+a},easeInBounce:function(e,f,a,h,g){return h-jQuery.easing.easeOutBounce(e,g-f,0,h,g)+a},easeOutBounce:function(e,f,a,h,g){if((f/=g)<(1/2.75)){return h*(7.5625*f*f)+a}else{if(f<(2/2.75)){return h*(7.5625*(f-=(1.5/2.75))*f+0.75)+a}else{if(f<(2.5/2.75)){return h*(7.5625*(f-=(2.25/2.75))*f+0.9375)+a}else{return h*(7.5625*(f-=(2.625/2.75))*f+0.984375)+a}}}},easeInOutBounce:function(e,f,a,h,g){if(f<g/2){return jQuery.easing.easeInBounce(e,f*2,0,h,g)*0.5+a}return jQuery.easing.easeOutBounce(e,f*2-g,0,h,g)*0.5+h*0.5+a}});

/* jQuery Cookie plugin */
jQuery.cookie=function(name,value,options){if(typeof value!='undefined'){options=options||{};if(value===null){value='';options.expires=-1;}var expires='';if(options.expires&&(typeof options.expires=='number'||options.expires.toUTCString)){var date;if(typeof options.expires=='number'){date=new Date();date.setTime(date.getTime()+(options.expires*24*60*60*1000));}else{date=options.expires;}expires='; expires='+date.toUTCString();}var path=options.path?'; path='+(options.path):'';var domain=options.domain?'; domain='+(options.domain):'';var secure=options.secure?'; secure':'';document.cookie=[name,'=',encodeURIComponent(value),expires,path,domain,secure].join('');}else{var cookieValue=null;if(document.cookie&&document.cookie!=''){var cookies=document.cookie.split(';');for(var i=0;i<cookies.length;i++){var cookie=jQuery.trim(cookies[i]);if(cookie.substring(0,name.length+1)==(name+'=')){cookieValue=decodeURIComponent(cookie.substring(name.length+1));break;}}}return cookieValue;}};

/* jQuery querystring plugin */
eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('M 6(A){4 $11=A.11||\'&\';4 $V=A.V===r?r:j;4 $1p=A.1p===r?\'\':\'[]\';4 $13=A.13===r?r:j;4 $D=$13?A.D===j?"#":"?":"";4 $15=A.15===r?r:j;v.1o=M 6(){4 f=6(o,t){8 o!=1v&&o!==x&&(!!t?o.1t==t:j)};4 14=6(1m){4 m,1l=/\\[([^[]*)\\]/g,T=/^([^[]+)(\\[.*\\])?$/.1r(1m),k=T[1],e=[];19(m=1l.1r(T[2]))e.u(m[1]);8[k,e]};4 w=6(3,e,7){4 o,y=e.1b();b(I 3!=\'X\')3=x;b(y===""){b(!3)3=[];b(f(3,L)){3.u(e.h==0?7:w(x,e.z(0),7))}n b(f(3,1a)){4 i=0;19(3[i++]!=x);3[--i]=e.h==0?7:w(3[i],e.z(0),7)}n{3=[];3.u(e.h==0?7:w(x,e.z(0),7))}}n b(y&&y.T(/^\\s*[0-9]+\\s*$/)){4 H=1c(y,10);b(!3)3=[];3[H]=e.h==0?7:w(3[H],e.z(0),7)}n b(y){4 H=y.B(/^\\s*|\\s*$/g,"");b(!3)3={};b(f(3,L)){4 18={};1w(4 i=0;i<3.h;++i){18[i]=3[i]}3=18}3[H]=e.h==0?7:w(3[H],e.z(0),7)}n{8 7}8 3};4 C=6(a){4 p=d;p.l={};b(a.C){v.J(a.Z(),6(5,c){p.O(5,c)})}n{v.J(1u,6(){4 q=""+d;q=q.B(/^[?#]/,\'\');q=q.B(/[;&]$/,\'\');b($V)q=q.B(/[+]/g,\' \');v.J(q.Y(/[&;]/),6(){4 5=1e(d.Y(\'=\')[0]||"");4 c=1e(d.Y(\'=\')[1]||"");b(!5)8;b($15){b(/^[+-]?[0-9]+\\.[0-9]*$/.1d(c))c=1A(c);n b(/^[+-]?[0-9]+$/.1d(c))c=1c(c,10)}c=(!c&&c!==0)?j:c;b(c!==r&&c!==j&&I c!=\'1g\')c=c;p.O(5,c)})})}8 p};C.1H={C:j,1G:6(5,1f){4 7=d.Z(5);8 f(7,1f)},1h:6(5){b(!f(5))8 d.l;4 K=14(5),k=K[0],e=K[1];4 3=d.l[k];19(3!=x&&e.h!=0){3=3[e.1b()]}8 I 3==\'1g\'?3:3||""},Z:6(5){4 3=d.1h(5);b(f(3,1a))8 v.1E(j,{},3);n b(f(3,L))8 3.z(0);8 3},O:6(5,c){4 7=!f(c)?x:c;4 K=14(5),k=K[0],e=K[1];4 3=d.l[k];d.l[k]=w(3,e.z(0),7);8 d},w:6(5,c){8 d.N().O(5,c)},1s:6(5){8 d.O(5,x).17()},1z:6(5){8 d.N().1s(5)},1j:6(){4 p=d;v.J(p.l,6(5,7){1y p.l[5]});8 p},1F:6(Q){4 D=Q.B(/^.*?[#](.+?)(?:\\?.+)?$/,"$1");4 S=Q.B(/^.*?[?](.+?)(?:#.+)?$/,"$1");8 M C(Q.h==S.h?\'\':S,Q.h==D.h?\'\':D)},1x:6(){8 d.N().1j()},N:6(){8 M C(d)},17:6(){6 F(G){4 R=I G=="X"?f(G,L)?[]:{}:G;b(I G==\'X\'){6 1k(o,5,7){b(f(o,L))o.u(7);n o[5]=7}v.J(G,6(5,7){b(!f(7))8 j;1k(R,5,F(7))})}8 R}d.l=F(d.l);8 d},1B:6(){8 d.N().17()},1D:6(){4 i=0,U=[],W=[],p=d;4 16=6(E){E=E+"";b($V)E=E.B(/ /g,"+");8 1C(E)};4 1n=6(1i,5,7){b(!f(7)||7===r)8;4 o=[16(5)];b(7!==j){o.u("=");o.u(16(7))}1i.u(o.P(""))};4 F=6(R,k){4 12=6(5){8!k||k==""?[5].P(""):[k,"[",5,"]"].P("")};v.J(R,6(5,7){b(I 7==\'X\')F(7,12(5));n 1n(W,12(5),7)})};F(d.l);b(W.h>0)U.u($D);U.u(W.P($11));8 U.P("")}};8 M C(1q.S,1q.D)}}(v.1o||{});',62,106,'|||target|var|key|function|value|return|||if|val|this|tokens|is||length||true|base|keys||else||self||false|||push|jQuery|set|null|token|slice|settings|replace|queryObject|hash|str|build|orig|index|typeof|each|parsed|Array|new|copy|SET|join|url|obj|search|match|queryString|spaces|chunks|object|split|get||separator|newKey|prefix|parse|numbers|encode|COMPACT|temp|while|Object|shift|parseInt|test|decodeURIComponent|type|number|GET|arr|EMPTY|add|rx|path|addFields|query|suffix|location|exec|REMOVE|constructor|arguments|undefined|for|empty|delete|remove|parseFloat|compact|encodeURIComponent|toString|extend|load|has|prototype'.split('|'),0,{}))




/////////////////////////////////////////////////////////////////////////// 
// MEDIA MODULE  1. Core  2.File Upload 3.Fix Rotation
///////////////////////////////////////////////////////////////////////////



var _medActiveHandler = false;
var exts = ['jpg', 'jpeg', 'png', 'gif','mp4', 'mov', 'MOV'];
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
    var html = '<div class="med_actions_container med-theme-' + 
        '<div class="med_toolbar_container">' +
            '<a href="#photos" class="med_toolbarItem" title="' + TRS_DTheme.add_photos + '" id="med_addPhotos"><span>' + TRS_DTheme.add_photos + '</span></a>' +
            '&nbsp;' +
    


        '<div class="med_controls_container">' +

        '</div>' +
        '<div class="med_preview_container">' +

        '</div>' +
        '<div class="med_action_container">' +
        '</div>' +
    '</div>';
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
            $('.dim').css({    display:'none'});

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


/**
 * http://github.com/valums/file-uploader
 * 
 * Multiple file upload component with progress-bar, drag-and-drop. Â© 2010
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

 /* Media button openner */

$('.openup').click(function (e) {
        e.preventDefault();
 $('#post-intro').fadeToggle(200);
 $(".openup").toggleClass('selected');
      $(".dim").addClass('selected', 100, "easeOutSine");
    $('#category').css("display","none");
    $('#spotlight').prop("checked",false);
    $('#post-store').css("display","none");
  $('.dim').css({    display:'inline', position: 'fixed'});

 $('html, body').css({    overflow: 'hidden'});    
});

$('.add_new_spotlight').click(function (e) {
   // alert("ok");
        e.preventDefault();
 $('#post-intro').fadeToggle(200);
 $(".add_new_spotlight").toggleClass('selected');
 $(".dim").addClass('selected', 100, "easeOutSine");
$('#category').css("display","block");
$('#spotlight').prop("checked",true);
$('#post-store').css("display","block");
 $('.dim').css({    display:'inline', position: 'fixed'});

 $('html, body').css({    overflow: 'hidden'});    
});
$(document).click(function (e) {
        if ($(e.target).closest('#post-intro').length > 0 || $(e.target).closest('.openup').length > 0) return;
        if ($(e.target).closest('#post-intro').length > 0 || $(e.target).closest('.add_new_spotlight').length > 0) return;
        $('#post-intro').fadeOut(200);

$('.dim').css({    display:'none',});
$('html, body').css({    overflow: 'visible',});    });


    $(".dim-close").click(function(){
      $('#post-intro').fadeOut(280);  

  $('.dim').css({    display:'none',});
 $('html, body').css({    overflow: 'visible',});  });

    $(".head-login").click(function(){
       $('#login-header').fadeToggle(200);

 });

/////////////////////////////////////////////////////////////////////////// 
// MAGNIFIC POPUP JQUERY
///////////////////////////////////////////////////////////////////////////
!function(e){var t,i,n,o,a,r,s,c="Close",l="BeforeClose",p="MarkupParse",u="Open",d="Change",f="mfp",m="."+f,g="mfp-ready",h="mfp-removing",v="mfp-prevent-close",y=function(){},C=!!window.jQuery,w=e(window),b=function(e,i){t.ev.on(f+e+m,i)},x=function(t,i,n,o){var a=document.createElement("div");return a.className="mfp-"+t,n&&(a.innerHTML=n),o?i&&i.appendChild(a):(a=e(a),i&&a.appendTo(i)),a},I=function(i,n){t.ev.triggerHandler(f+i,n),t.st.callbacks&&(i=i.charAt(0).toLowerCase()+i.slice(1),t.st.callbacks[i]&&t.st.callbacks[i].apply(t,e.isArray(n)?n:[n]))},P=function(i){return i===s&&t.currTemplate.closeBtn||(t.currTemplate.closeBtn=e(t.st.closeMarkup.replace("%title%",t.st.tClose)),s=i),t.currTemplate.closeBtn},k=function(){e.magnificPopup.instance||((t=new y).init(),e.magnificPopup.instance=t)};y.prototype={constructor:y,init:function(){var i=navigator.appVersion;t.isIE7=-1!==i.indexOf("MSIE 7."),t.isIE8=-1!==i.indexOf("MSIE 8."),t.isLowIE=t.isIE7||t.isIE8,t.isAndroid=/android/gi.test(i),t.isIOS=/iphone|ipad|ipod/gi.test(i),t.supportsTransition=function(){var e=document.createElement("p").style,t=["ms","O","Moz","Webkit"];if(void 0!==e.transition)return!0;for(;t.length;)if(t.pop()+"Transition"in e)return!0;return!1}(),t.probablyMobile=t.isAndroid||t.isIOS||/(Opera Mini)|Kindle|webOS|BlackBerry|(Opera Mobi)|(Windows Phone)|IEMobile/i.test(navigator.userAgent),o=e(document),t.popupsCache={}},open:function(i){var a;if(n||(n=e(document.body)),!1===i.isObj){t.items=i.items.toArray(),t.index=0;var s,c=i.items;for(a=0;c.length>a;a++)if((s=c[a]).parsed&&(s=s.el[0]),s===i.el[0]){t.index=a;break}}else t.items=e.isArray(i.items)?i.items:[i.items],t.index=i.index||0;if(!t.isOpen){t.types=[],r="",t.ev=i.mainEl&&i.mainEl.length?i.mainEl.eq(0):o,i.key?(t.popupsCache[i.key]||(t.popupsCache[i.key]={}),t.currTemplate=t.popupsCache[i.key]):t.currTemplate={},t.st=e.extend(!0,{},e.magnificPopup.defaults,i),t.fixedContentPos="auto"===t.st.fixedContentPos?!t.probablyMobile:t.st.fixedContentPos,t.st.modal&&(t.st.closeOnContentClick=!1,t.st.closeOnBgClick=!1,t.st.showCloseBtn=!1,t.st.enableEscapeKey=!1),t.bgOverlay||(t.bgOverlay=x("bg").on("click"+m,function(){t.close()}),t.wrap=x("wrap").attr("tabindex",-1).on("click"+m,function(e){t._checkIfClose(e.target)&&t.close()}),t.container=x("container",t.wrap)),t.contentContainer=x("skeleton"),t.st.preloader&&(t.preloader=x("preloader",t.container,t.st.tLoading));var l=e.magnificPopup.modules;for(a=0;l.length>a;a++){var d=l[a];d=d.charAt(0).toUpperCase()+d.slice(1),t["init"+d].call(t)}I("BeforeOpen"),t.st.showCloseBtn&&(t.st.closeBtnInside?(b(p,function(e,t,i,n){i.close_replaceWith=P(n.type)}),r+=" mfp-close-btn-in"):t.wrap.append(P())),t.st.alignTop&&(r+=" mfp-align-top"),t.fixedContentPos?t.wrap.css({overflow:t.st.overflowY,overflowX:"hidden",overflowY:t.st.overflowY}):t.wrap.css({top:w.scrollTop(),position:"absolute"}),(!1===t.st.fixedBgPos||"auto"===t.st.fixedBgPos&&!t.fixedContentPos)&&t.bgOverlay.css({height:o.height(),position:"absolute"}),t.st.enableEscapeKey&&o.on("keyup"+m,function(e){27===e.keyCode&&t.close()}),w.on("resize"+m,function(){t.updateSize()}),t.st.closeOnContentClick||(r+=" mfp-auto-cursor"),r&&t.wrap.addClass(r);var f=t.wH=w.height(),h={};if(t.fixedContentPos&&t._hasScrollBar(f)){var v=t._getScrollbarSize();v&&(h.marginRight=v)}t.fixedContentPos&&(t.isIE7?e("body, html").css("overflow","hidden"):h.overflow="hidden");var y=t.st.mainClass;return t.isIE7&&(y+=" mfp-ie7"),y&&t._addClassToMFP(y),t.updateItemHTML(),I("BuildControls"),e("html").css(h),t.bgOverlay.add(t.wrap).prependTo(t.st.prependTo||n),t._lastFocusedEl=document.activeElement,setTimeout(function(){t.content?(t._addClassToMFP(g),t._setFocus()):t.bgOverlay.addClass(g),o.on("focusin"+m,t._onFocusIn)},16),t.isOpen=!0,t.updateSize(f),I(u),i}t.updateItemHTML()},close:function(){t.isOpen&&(I(l),t.isOpen=!1,t.st.removalDelay&&!t.isLowIE&&t.supportsTransition?(t._addClassToMFP(h),setTimeout(function(){t._close()},t.st.removalDelay)):t._close())},_close:function(){$("html").removeClass("magnific"),I(c);var i=h+" "+g+" ";if(t.bgOverlay.detach(),t.wrap.detach(),t.container.empty(),t.st.mainClass&&(i+=t.st.mainClass+" "),t._removeClassFromMFP(i),t.fixedContentPos){var n={marginRight:""};t.isIE7?e("body, html").css("overflow",""):n.overflow="",e("html").css(n)}o.off("keyup.mfp focusin"+m),t.ev.off(m),t.wrap.attr("class","mfp-wrap").removeAttr("style"),t.bgOverlay.attr("class","mfp-bg"),t.container.attr("class","mfp-container"),!t.st.showCloseBtn||t.st.closeBtnInside&&!0!==t.currTemplate[t.currItem.type]||t.currTemplate.closeBtn&&t.currTemplate.closeBtn.detach(),t._lastFocusedEl&&e(t._lastFocusedEl).focus(),t.currItem=null,t.content=null,t.currTemplate=null,t.prevHeight=0,I("AfterClose")},updateSize:function(e){if(t.isIOS){var i=document.documentElement.clientWidth/window.innerWidth,n=window.innerHeight*i;t.wrap.css("height",n),t.wH=n}else t.wH=e||w.height();t.fixedContentPos||t.wrap.css("height",t.wH),I("Resize")},updateItemHTML:function(){var i=t.items[t.index];t.contentContainer.detach(),t.content&&t.content.detach(),i.parsed||(i=t.parseEl(t.index));var n=i.type;if(I("BeforeChange",[t.currItem?t.currItem.type:"",n]),t.currItem=i,!t.currTemplate[n]){var o=!!t.st[n]&&t.st[n].markup;I("FirstMarkupParse",o),t.currTemplate[n]=!o||e(o)}a&&a!==i.type&&t.container.removeClass("mfp-"+a+"-holder");var r=t["get"+n.charAt(0).toUpperCase()+n.slice(1)](i,t.currTemplate[n]);t.appendContent(r,n),i.preloaded=!0,I(d,i),a=i.type,t.container.prepend(t.contentContainer),I("AfterChange")},appendContent:function(e,i){t.content=e,e?t.st.showCloseBtn&&t.st.closeBtnInside&&!0===t.currTemplate[i]?t.content.find(".mfp-close").length||t.content.append(P()):t.content=e:t.content="",I("BeforeAppend"),t.container.addClass("mfp-"+i+"-holder"),t.contentContainer.append(t.content)},parseEl:function(i){var n,o=t.items[i];if(o.tagName?o={el:e(o)}:(n=o.type,o={data:o,src:o.src}),o.el){for(var a=t.types,r=0;a.length>r;r++)if(o.el.hasClass("mfp-"+a[r])){n=a[r];break}o.src=o.el.attr("data-mfp-src"),o.src||(o.src=o.el.attr("href"))}return o.type=n||t.st.type||"inline",o.index=i,o.parsed=!0,t.items[i]=o,I("ElementParse",o),t.items[i]},addGroup:function(e,i){var n=function(n){n.mfpEl=this,t._openClick(n,e,i)};i||(i={});var o="click.magnificPopup";i.mainEl=e,i.items?(i.isObj=!0,e.off(o).on(o,n)):(i.isObj=!1,i.delegate?e.off(o).on(o,i.delegate,n):(i.items=e,e.off(o).on(o,n)))},_openClick:function(i,n,o){if((void 0!==o.midClick?o.midClick:e.magnificPopup.defaults.midClick)||2!==i.which&&!i.ctrlKey&&!i.metaKey){var a=void 0!==o.disableOn?o.disableOn:e.magnificPopup.defaults.disableOn;if(a)if(e.isFunction(a)){if(!a.call(t))return!0}else if(a>w.width())return!0;i.type&&(i.preventDefault(),t.isOpen&&i.stopPropagation()),o.el=e(i.mfpEl),o.delegate&&(o.items=n.find(o.delegate)),t.open(o)}},updateStatus:function(e,n){if(t.preloader){i!==e&&t.container.removeClass("mfp-s-"+i),n||"loading"!==e||(n=t.st.tLoading);var o={status:e,text:n};I("UpdateStatus",o),e=o.status,n=o.text,t.preloader.html(n),t.preloader.find("a").on("click",function(e){e.stopImmediatePropagation()}),t.container.addClass("mfp-s-"+e),i=e}},_checkIfClose:function(i){if(!e(i).hasClass(v)){var n=t.st.closeOnContentClick,o=t.st.closeOnBgClick;if(n&&o)return!0;if(!t.content||e(i).hasClass("mfp-close")||t.preloader&&i===t.preloader[0])return!0;if(i===t.content[0]||e.contains(t.content[0],i)){if(n)return!0}else if(o&&e.contains(document,i))return!0;return!1}},_addClassToMFP:function(e){t.bgOverlay.addClass(e),t.wrap.addClass(e)},_removeClassFromMFP:function(e){this.bgOverlay.removeClass(e),t.wrap.removeClass(e)},_hasScrollBar:function(e){return(t.isIE7?o.height():document.body.scrollHeight)>(e||w.height())},_setFocus:function(){(t.st.focus?t.content.find(t.st.focus).eq(0):t.wrap).focus()},_onFocusIn:function(i){return i.target===t.wrap[0]||e.contains(t.wrap[0],i.target)?void 0:(t._setFocus(),!1)},_parseMarkup:function(t,i,n){var o;n.data&&(i=e.extend(n.data,i)),I(p,[t,i,n]),e.each(i,function(e,i){if(void 0===i||!1===i)return!0;if((o=e.split("_")).length>1){var n=t.find(m+"-"+o[0]);if(n.length>0){var a=o[1];"replaceWith"===a?n[0]!==i[0]&&n.replaceWith(i):"img"===a?n.is("img")?n.attr("src",i):n.replaceWith('<img src="'+i+'" class="'+n.attr("class")+'" />'):n.attr(o[1],i)}}else t.find(m+"-"+e).html(i)})},_getScrollbarSize:function(){if(void 0===t.scrollbarSize){var e=document.createElement("div");e.id="mfp-sbm",e.style.cssText="width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;",document.body.appendChild(e),t.scrollbarSize=e.offsetWidth-e.clientWidth,document.body.removeChild(e)}return t.scrollbarSize}},e.magnificPopup={instance:null,proto:y.prototype,modules:[],open:function(t,i){return k(),(t=t?e.extend(!0,{},t):{}).isObj=!0,t.index=i||0,this.instance.open(t)},close:function(){return e.magnificPopup.instance&&e.magnificPopup.instance.close()},registerModule:function(t,i){i.options&&(e.magnificPopup.defaults[t]=i.options),e.extend(this.proto,i.proto),this.modules.push(t)},defaults:{disableOn:0,key:null,midClick:!1,mainClass:"",preloader:!0,focus:"",closeOnContentClick:!1,closeOnBgClick:!0,closeBtnInside:!0,showCloseBtn:!0,enableEscapeKey:!0,modal:!1,alignTop:!1,removalDelay:0,prependTo:null,fixedContentPos:"auto",fixedBgPos:"auto",overflowY:"auto",closeMarkup:'<button title="%title%" type="button" class="mfp-close">&times;</button>',tClose:"Close (Esc)",tLoading:"Loading..."}},e.fn.magnificPopup=function(i){k();var n=e(this);if("string"==typeof i)if("open"===i){var o,a=C?n.data("magnificPopup"):n[0].magnificPopup,r=parseInt(arguments[1],10)||0;a.items?o=a.items[r]:(o=n,a.delegate&&(o=o.find(a.delegate)),o=o.eq(r)),t._openClick({mfpEl:o},n,a)}else t.isOpen&&t[i].apply(t,Array.prototype.slice.call(arguments,1));else i=e.extend(!0,{},i),C?n.data("magnificPopup",i):n[0].magnificPopup=i,t.addGroup(n,i);return n};var _,T,E,S="inline",M=function(){E&&(T.after(E.addClass(_)).detach(),E=null)};e.magnificPopup.registerModule(S,{options:{hiddenClass:"hide",markup:"",tNotFound:"Content not found"},proto:{initInline:function(){t.types.push(S),b(c+"."+S,function(){M()})},getInline:function(i,n){if(M(),i.src){var o=t.st.inline,a=e(i.src);if(a.length){var r=a[0].parentNode;r&&r.tagName&&(T||(_=o.hiddenClass,T=x(_),_="mfp-"+_),E=a.after(T).detach().removeClass(_)),t.updateStatus("ready")}else t.updateStatus("error",o.tNotFound),a=e("<div>");return i.inlineElement=a,a}return t.updateStatus("ready"),t._parseMarkup(n,{},i),n}}});var O,z="ajax",B=function(){O&&n.removeClass(O)},H=function(){B(),t.req&&t.req.abort()};e.magnificPopup.registerModule(z,{options:{settings:null,cursor:"mfp-ajax-cur",tError:'<a href="%url%">The content</a> could not be loaded.'},proto:{initAjax:function(){t.types.push(z),O=t.st.ajax.cursor,b(c+"."+z,H),b("BeforeChange."+z,H)},getAjax:function(i){O&&n.addClass(O),t.updateStatus("loading");var o=e.extend({url:i.src,success:function(n,o,a){var r=document.createElement("div");r.innerHTML=n;var s=r.getElementsByTagName("script");for(index=s.length-1;index>=0;index--)s[index].parentNode.removeChild(s[index]);var c={data:r.innerHTML,xhr:a};I("ParseAjax",c),t.appendContent(e(c.data),z),i.finished=!0,B(),t._setFocus(),setTimeout(function(){t.wrap.addClass(g)},16),t.updateStatus("ready"),I("AjaxContentAdded")},error:function(){B(),i.finished=i.loadError=!0,t.updateStatus("error",t.st.ajax.tError.replace("%url%",i.src))}},t.st.ajax.settings);return t.req=e.ajax(o),""}}});var L,F=function(i){if(i.data&&void 0!==i.data.title)return i.data.title;var n=t.st.image.titleSrc;if(n){if(e.isFunction(n))return n.call(t,i);if(i.el)return i.el.attr(n)||""}return""};e.magnificPopup.registerModule("image",{options:{markup:'<div class="mfp-figure"><div class="mfp-close"></div><figure><div class="mfp-img"></div><figcaption><div class="mfp-bottom-bar"><div class="mfp-title"></div><div class="mfp-counter"></div></div></figcaption></figure></div>',cursor:"mfp-zoom-out-cur",titleSrc:"title",verticalFit:!0,tError:'<a href="%url%">The image</a> could not be loaded.'},proto:{initImage:function(){var e=t.st.image,i=".image";t.types.push("image"),b(u+i,function(){"image"===t.currItem.type&&e.cursor&&n.addClass(e.cursor)}),b(c+i,function(){e.cursor&&n.removeClass(e.cursor),w.off("resize"+m)}),b("Resize"+i,t.resizeImage),t.isLowIE&&b("AfterChange",t.resizeImage)},resizeImage:function(){var e=t.currItem;if(e&&e.img&&t.st.image.verticalFit){var i=0;t.isLowIE&&(i=parseInt(e.img.css("padding-top"),10)+parseInt(e.img.css("padding-bottom"),10)),e.img.css("max-height",t.wH-i)}},_onImageHasSize:function(e){e.img&&(e.hasSize=!0,L&&clearInterval(L),e.isCheckingImgSize=!1,I("ImageHasSize",e),e.imgHidden&&(t.content&&t.content.removeClass("mfp-loading"),e.imgHidden=!1))},findImageSize:function(e){var i=0,n=e.img[0],o=function(a){L&&clearInterval(L),L=setInterval(function(){return n.naturalWidth>0?void t._onImageHasSize(e):(i>200&&clearInterval(L),void(3===++i?o(10):40===i?o(50):100===i&&o(500)))},a)};o(1)},getImage:function(i,n){var o=0,a=function(){i&&(i.img[0].complete?(i.img.off(".mfploader"),i===t.currItem&&(t._onImageHasSize(i),t.updateStatus("ready")),i.hasSize=!0,i.loaded=!0,I("ImageLoadComplete")):200>++o?setTimeout(a,100):r())},r=function(){i&&(i.img.off(".mfploader"),i===t.currItem&&(t._onImageHasSize(i),t.updateStatus("error",s.tError.replace("%url%",i.src))),i.hasSize=!0,i.loaded=!0,i.loadError=!0)},s=t.st.image,c=n.find(".mfp-img");if(c.length){var l=document.createElement("img");l.className="mfp-img",i.img=e(l).on("load.mfploader",a).on("error.mfploader",r),l.src=i.src,c.is("img")&&(i.img=i.img.clone()),(l=i.img[0]).naturalWidth>0?i.hasSize=!0:l.width||(i.hasSize=!1)}return t._parseMarkup(n,{title:F(i),img_replaceWith:i.img},i),t.resizeImage(),i.hasSize?(L&&clearInterval(L),i.loadError?(n.addClass("mfp-loading"),t.updateStatus("error",s.tError.replace("%url%",i.src))):(n.removeClass("mfp-loading"),t.updateStatus("ready")),n):(t.updateStatus("loading"),i.loading=!0,i.hasSize||(i.imgHidden=!0,n.addClass("mfp-loading"),t.findImageSize(i)),n)}}});var A;e.magnificPopup.registerModule("zoom",{options:{enabled:!1,easing:"ease-in-out",duration:300,opener:function(e){return e.is("img")?e:e.find("img")}},proto:{initZoom:function(){var e,i=t.st.zoom,n=".zoom";if(i.enabled&&t.supportsTransition){var o,a,r=i.duration,s=function(e){var t=e.clone().removeAttr("style").removeAttr("class").addClass("mfp-animated-image"),n="all "+i.duration/1e3+"s "+i.easing,o={position:"fixed",zIndex:9999,left:0,top:0,"-webkit-backface-visibility":"hidden"},a="transition";return o["-webkit-"+a]=o["-moz-"+a]=o["-o-"+a]=o[a]=n,t.css(o),t},p=function(){t.content.css("visibility","visible")};b("BuildControls"+n,function(){if(t._allowZoom()){if(clearTimeout(o),t.content.css("visibility","hidden"),!(e=t._getItemToZoom()))return void p();(a=s(e)).css(t._getOffset()),t.wrap.append(a),o=setTimeout(function(){a.css(t._getOffset(!0)),o=setTimeout(function(){p(),setTimeout(function(){a.remove(),e=a=null,I("ZoomAnimationEnded")},16)},r)},16)}}),b(l+n,function(){if(t._allowZoom()){if(clearTimeout(o),t.st.removalDelay=r,!e){if(!(e=t._getItemToZoom()))return;a=s(e)}a.css(t._getOffset(!0)),t.wrap.append(a),t.content.css("visibility","hidden"),setTimeout(function(){a.css(t._getOffset())},16)}}),b(c+n,function(){t._allowZoom()&&(p(),a&&a.remove(),e=null)})}},_allowZoom:function(){return"image"===t.currItem.type},_getItemToZoom:function(){return!!t.currItem.hasSize&&t.currItem.img},_getOffset:function(i){var n,o=(n=i?t.currItem.img:t.st.zoom.opener(t.currItem.el||t.currItem)).offset(),a=parseInt(n.css("padding-top"),10),r=parseInt(n.css("padding-bottom"),10);o.top-=e(window).scrollTop()-a;var s={width:n.width(),height:(C?n.innerHeight():n[0].offsetHeight)-r-a};return void 0===A&&(A=void 0!==document.createElement("p").style.MozTransform),A?s["-moz-transform"]=s.transform="translate("+o.left+"px,"+o.top+"px)":(s.left=o.left,s.top=o.top),s}}});var j="iframe",N=function(e){if(t.currTemplate[j]){var i=t.currTemplate[j].find("iframe");i.length&&(e||(i[0].src="//about:blank"),t.isIE8&&i.css("display",e?"block":"none"))}};e.magnificPopup.registerModule(j,{options:{markup:'<div class="mfp-iframe-scaler"><div class="mfp-close"></div><iframe class="mfp-iframe" src="//about:blank" frameborder="0" allowfullscreen></iframe></div>',srcAction:"iframe_src",patterns:{youtube:{index:"youtube.com",id:"v=",src:"//www.youtube.com/embed/%id%?autoplay=1"},vimeo:{index:"vimeo.com/",id:"/",src:"//player.vimeo.com/video/%id%?autoplay=1"},gmaps:{index:"//maps.google.",src:"%id%&output=embed"}}},proto:{initIframe:function(){t.types.push(j),b("BeforeChange",function(e,t,i){t!==i&&(t===j?N():i===j&&N(!0))}),b(c+"."+j,function(){N()})},getIframe:function(i,n){var o=i.src,a=t.st.iframe;e.each(a.patterns,function(){return o.indexOf(this.index)>-1?(this.id&&(o="string"==typeof this.id?o.substr(o.lastIndexOf(this.id)+this.id.length,o.length):this.id.call(this,o)),o=this.src.replace("%id%",o),!1):void 0});var r={};return a.srcAction&&(r[a.srcAction]=o),t._parseMarkup(n,r,i),t.updateStatus("ready"),n}}});var W=function(e){var i=t.items.length;return e>i-1?e-i:0>e?i+e:e},R=function(e,t,i){return e.replace(/%curr%/gi,t+1).replace(/%total%/gi,i)};e.magnificPopup.registerModule("gallery",{options:{enabled:!1,arrowMarkup:'<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>',preload:[0,2],navigateByImgClick:!0,arrows:!0,tPrev:"Previous (Left arrow key)",tNext:"Next (Right arrow key)",tCounter:"%curr% of %total%"},proto:{initGallery:function(){var i=t.st.gallery,n=".mfp-gallery",a=Boolean(e.fn.mfpFastClick);return t.direction=!0,!(!i||!i.enabled)&&(r+=" mfp-gallery",b(u+n,function(){i.navigateByImgClick&&t.wrap.on("click"+n,".mfp-img",function(){return t.items.length>1?(t.next(),!1):void 0}),o.on("keydown"+n,function(e){37===e.keyCode?t.prev():39===e.keyCode&&t.next()})}),b("UpdateStatus"+n,function(e,i){i.text&&(i.text=R(i.text,t.currItem.index,t.items.length))}),b(p+n,function(e,n,o,a){var r=t.items.length;o.counter=r>1?R(i.tCounter,a.index,r):""}),b("BuildControls"+n,function(){if(t.items.length>1&&i.arrows&&!t.arrowLeft){var n=i.arrowMarkup,o=t.arrowLeft=e(n.replace(/%title%/gi,i.tPrev).replace(/%dir%/gi,"left")).addClass(v),r=t.arrowRight=e(n.replace(/%title%/gi,i.tNext).replace(/%dir%/gi,"right")).addClass(v),s=a?"mfpFastClick":"click";o[s](function(){t.prev()}),r[s](function(){t.next()}),t.isIE7&&(x("b",o[0],!1,!0),x("a",o[0],!1,!0),x("b",r[0],!1,!0),x("a",r[0],!1,!0)),t.container.append(o.add(r))}}),b(d+n,function(){t._preloadTimeout&&clearTimeout(t._preloadTimeout),t._preloadTimeout=setTimeout(function(){t.preloadNearbyImages(),t._preloadTimeout=null},16)}),void b(c+n,function(){o.off(n),t.wrap.off("click"+n),t.arrowLeft&&a&&t.arrowLeft.add(t.arrowRight).destroyMfpFastClick(),t.arrowRight=t.arrowLeft=null}))},next:function(){t.direction=!0,t.index=W(t.index+1),t.updateItemHTML()},prev:function(){t.direction=!1,t.index=W(t.index-1),t.updateItemHTML()},goTo:function(e){t.direction=e>=t.index,t.index=e,t.updateItemHTML()},preloadNearbyImages:function(){var e,i=t.st.gallery.preload,n=Math.min(i[0],t.items.length),o=Math.min(i[1],t.items.length);for(e=1;(t.direction?o:n)>=e;e++)t._preloadItem(t.index+e);for(e=1;(t.direction?n:o)>=e;e++)t._preloadItem(t.index-e)},_preloadItem:function(i){if(i=W(i),!t.items[i].preloaded){var n=t.items[i];n.parsed||(n=t.parseEl(i)),I("LazyLoad",n),"image"===n.type&&(n.img=e('<img class="mfp-img" />').on("load.mfploader",function(){n.hasSize=!0}).on("error.mfploader",function(){n.hasSize=!0,n.loadError=!0,I("LazyLoadError",n)}).attr("src",n.src)),n.preloaded=!0}}}});var Z="retina";e.magnificPopup.registerModule(Z,{options:{replaceSrc:function(e){return e.src.replace(/\.\w+$/,function(e){return"@2x"+e})},ratio:1},proto:{initRetina:function(){if(window.devicePixelRatio>1){var e=t.st.retina,i=e.ratio;(i=isNaN(i)?i():i)>1&&(b("ImageHasSize."+Z,function(e,t){t.img.css({"max-width":t.img[0].naturalWidth/i,width:"100%"})}),b("ElementParse."+Z,function(t,n){n.src=e.replaceSrc(n,i)}))}}}}),function(){var t="ontouchstart"in window,i=function(){w.off("touchmove"+n+" touchend"+n)},n=".mfpFastClick";e.fn.mfpFastClick=function(o){return e(this).each(function(){var a,r,s,c,l,p,u,d=e(this);t&&d.on("touchstart"+n,function(e){l=!1,u=1,p=e.originalEvent?e.originalEvent.touches[0]:e.touches[0],s=p.clientX,c=p.clientY,w.on("touchmove"+n,function(e){p=e.originalEvent?e.originalEvent.touches:e.touches,u=p.length,p=p[0],(Math.abs(p.clientX-s)>10||Math.abs(p.clientY-c)>10)&&(l=!0,i())}).on("touchend"+n,function(e){i(),l||u>1||(a=!0,e.preventDefault(),clearTimeout(r),r=setTimeout(function(){a=!1},1e3),o())})});d.on("click"+n,function(){a||o()})})},e.fn.destroyMfpFastClick=function(){e(this).off("touchstart"+n+" click"+n),t&&w.off("touchmove"+n+" touchend"+n)}}(),k()}(window.jQuery||window.Zepto),jQuery(document).ready(function(e){function t(){e.magnificPopup.instance.items=[],e.magnificPopup.page=1*e.cookie("trs-activity-oldestpage"),e.magnificPopup.current_page=1*e.cookie("trs-activity-oldestpage"),e(".broadcast-inn").each(function(){e(this).addClass("magnific-view"),e(".magnific-view").magnificPopup({type:"ajax",gallery:{enabled:!0},closeOnBgClick:!1,"mfp-prevent-close":!1,callbacks:{open:function(){e(".mfp-description").append(this.currItem.el.attr("alt")),e.magnificPopup.content_count=e.magnificPopup.instance.items.length},beforeClose:function(){var e=this.container.find("video");e.length&&(e.trigger("pause"),e.get()[0].pause())},ajaxContentAdded:function(){var t=e(this.content).find(".video-flip"),i='<embed><video width="100%" height="100%" preload src="'+t.attr("data-video")+'" playsinline=1" showcontrols="0" showcontrols="false" autoplay="1"></embed>';t.replaceWith(i),e(this.content).find("video").trigger("play")},afterChange:function(){e(".mfp-description").empty().append(this.currItem.el.attr("alt")),setTimeout(function(){jQuery(document).ready(function(e){var t=/\[med_video\]https?:\/\/(?:www\.)?youtu(?:be\.com|\.be)\/(?:watch\?v=|v\/)?([A-Za-z0-9_\-]+)([a-zA-Z&=;_+0-9*#\-]*?)\[\/med_video\]/;e(".mfp-container .broadcast-inn").each(function(){var i=e(this);i.html(i.html().replace(t,'<div data-address="$1" class="youtube" style="background: url(https://i4.ytimg.com/vi/$1/hqdefault.jpg)"><span></span></div>'))}),e(".mfp-container .broadcast-inn").delegate("div.youtube","hover",function(){var t=e(this);t.replaceWith('<iframe data-address="$1"  class="youtube" src="https://www.youtube.com/embed/$1?enablejsapi=1&hd=1&autohide=1&autoplay=1&rel=0" frameborder="0" allowfullscreen></iframe>'.replace(/\$1/g,t.attr("data-address")))})})},450)}},image:{markup:'<div class="iframe-popup"><iframe class="mfp-iframe" frameborder="0" scrolling="no"" onload="resizeIframe(this)" allowtransparency="true" allowfullscreen></iframe><div class="mfp-close"></div></div>'}})})}e.magnificPopup.page=1,e.magnificPopup.current_page=1,e.magnificPopup.proto.next=function(){e.magnificPopup.instance.index==e.magnificPopup.instance.items.length-1?e.ajax({url:ajaxurl,data:{action:"activity_get_older_updates",cookie:encodeURIComponent(document.cookie),page:e.magnificPopup.page+1},type:"post",success:function(t){t=JSON.parse(t),e.magnificPopup.instance.index=e.magnificPopup.instance.items.length,$content_enable=!0,e.magnificPopup.instance.items.length==e.magnificPopup.content_count&&($content_enable=!1),e(t.contents).find(".view").each(function(t){$content_enable?e.magnificPopup.instance.items[e.magnificPopup.content_count+t]=e(this)[0]:e.magnificPopup.instance.items.push(e(this)[0])}),e.magnificPopup.instance.items.length==e.magnificPopup.content_count?(e.magnificPopup.instance.index=0,e.magnificPopup.proto.updateItemHTML()):(e.magnificPopup.page++,e.magnificPopup.instance.index=e.magnificPopup.content_count,e.magnificPopup.proto.updateItemHTML())}}):(e.magnificPopup.instance.index++,e.magnificPopup.proto.updateItemHTML())},e.magnificPopup.proto.prev=function(){e.magnificPopup.instance.index==e.magnificPopup.content_count&&e.magnificPopup.page>e.magnificPopup.current_page+1?e.ajax({url:ajaxurl,type:"post",data:{action:"activity_get_older_updates",cookie:encodeURIComponent(document.cookie),page:e.magnificPopup.page-1},success:function(t){t=JSON.parse(t);var i=[];e(t.contents).find(".view").each(function(){i.push(e(this)[0])}),e.magnificPopup.instance.items=e.magnificPopup.instance.items.slice(0,e.magnificPopup.content_count),e.magnificPopup.page--,e.magnificPopup.instance.items=e.magnificPopup.instance.items.concat(e.magnificPopup.instance.items,i),e.magnificPopup.instance.index=e.magnificPopup.instance.items.length-1,e.magnificPopup.proto.updateItemHTML()}}):e.magnificPopup.instance.index>=0&&(e.magnificPopup.instance.index--,e.magnificPopup.proto.updateItemHTML())},t(),window.imoc_init=t;try{onElementHeightChange(document.body,function(){t()})}catch(e){}e("#activity-filter-by").change(function(){window.setTimeout(t(),1e3)}),e(' ul> li > #activity_media_filter > input[type="checkbox"]').change(function(){window.setTimeout(t(),1e3)}),e(".contour-select ul li").click(function(){window.setTimeout(t(),1e3)})});
/////////////////////////////////////////////////////////////////////////// 
// MISC MODULES/JQUERY
///////////////////////////////////////////////////////////////////////////


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


/* Follow Suggest*/

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



/* Like Function */

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

/* Report */

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


/* Tipsy Function */
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
        delayIn: 640,
        delayOut: 0,
        fade: false,
        fallback: '',
        gravity: 'n',
        html: false,
        live: false,
        offset: 0,
        opacity: .9,
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





/* Cosmetcs  */

$(window).scroll(function() {
    if ($(this).scrollTop() > 380) {
                $(' #contour-image ').addClass("slide");
                $(' #header ').addClass("selected");
                $(' #figure').addClass("slide");


        //$('.user img.portrait').addClass("slideUp");
        $('#cover').addClass("slide");
        //      $('.hashtitlex').addClass("slide");


    } else {
        $(' #contour-image').removeClass("slide");
                $(' #header ').removeClass("selected");

        //$('.user  img.portrait').removeClass("slideUp");
        $('#cover').removeClass("slide");

                            //  $('.hashtitlex').removeClass("slide");
        $('#figure').removeClass("slide");


    }
});

/* AlloyCrop Function Profile Image  */

/* AlloyCrop v1.0.4
* By dntzhang
* Github: https://github.com/AlloyTeam/AlloyCrop
*/
;(function(){
      /* transformjs
   * By dntzhang
   */
      ;(function () {

          var Matrix3D = function (n11, n12, n13, n14, n21, n22, n23, n24, n31, n32, n33, n34, n41, n42, n43, n44) {
              this.elements =window.Float32Array ? new Float32Array(16) : [];
              var te = this.elements;
              te[0] = (n11 !== undefined) ? n11 : 1; te[4] = n12 || 0; te[8] = n13 || 0; te[12] = n14 || 0;
              te[1] = n21 || 0; te[5] = (n22 !== undefined) ? n22 : 1; te[9] = n23 || 0; te[13] = n24 || 0;
              te[2] = n31 || 0; te[6] = n32 || 0; te[10] = (n33 !== undefined) ? n33 : 1; te[14] = n34 || 0;
              te[3] = n41 || 0; te[7] = n42 || 0; te[11] = n43 || 0; te[15] = (n44 !== undefined) ? n44 : 1;
          };

          Matrix3D.DEG_TO_RAD = Math.PI / 180;

          Matrix3D.prototype = {
              set: function (n11, n12, n13, n14, n21, n22, n23, n24, n31, n32, n33, n34, n41, n42, n43, n44) {
                  var te = this.elements;
                  te[0] = n11; te[4] = n12; te[8] = n13; te[12] = n14;
                  te[1] = n21; te[5] = n22; te[9] = n23; te[13] = n24;
                  te[2] = n31; te[6] = n32; te[10] = n33; te[14] = n34;
                  te[3] = n41; te[7] = n42; te[11] = n43; te[15] = n44;
                  return this;
              },
              identity: function () {
                  this.set(
                      1, 0, 0, 0,
                      0, 1, 0, 0,
                      0, 0, 1, 0,
                      0, 0, 0, 1
                  );
                  return this;
              },
              multiplyMatrices: function (a, be) {

                  var ae = a.elements;
                  var te = this.elements;
                  var a11 = ae[0], a12 = ae[4], a13 = ae[8], a14 = ae[12];
                  var a21 = ae[1], a22 = ae[5], a23 = ae[9], a24 = ae[13];
                  var a31 = ae[2], a32 = ae[6], a33 = ae[10], a34 = ae[14];
                  var a41 = ae[3], a42 = ae[7], a43 = ae[11], a44 = ae[15];

                  var b11 = be[0], b12 = be[1], b13 = be[2], b14 = be[3];
                  var b21 = be[4], b22 = be[5], b23 = be[6], b24 = be[7];
                  var b31 = be[8], b32 = be[9], b33 = be[10], b34 = be[11];
                  var b41 = be[12], b42 = be[13], b43 = be[14], b44 = be[15];

                  te[0] = a11 * b11 + a12 * b21 + a13 * b31 + a14 * b41;
                  te[4] = a11 * b12 + a12 * b22 + a13 * b32 + a14 * b42;
                  te[8] = a11 * b13 + a12 * b23 + a13 * b33 + a14 * b43;
                  te[12] = a11 * b14 + a12 * b24 + a13 * b34 + a14 * b44;

                  te[1] = a21 * b11 + a22 * b21 + a23 * b31 + a24 * b41;
                  te[5] = a21 * b12 + a22 * b22 + a23 * b32 + a24 * b42;
                  te[9] = a21 * b13 + a22 * b23 + a23 * b33 + a24 * b43;
                  te[13] = a21 * b14 + a22 * b24 + a23 * b34 + a24 * b44;

                  te[2] = a31 * b11 + a32 * b21 + a33 * b31 + a34 * b41;
                  te[6] = a31 * b12 + a32 * b22 + a33 * b32 + a34 * b42;
                  te[10] = a31 * b13 + a32 * b23 + a33 * b33 + a34 * b43;
                  te[14] = a31 * b14 + a32 * b24 + a33 * b34 + a34 * b44;

                  te[3] = a41 * b11 + a42 * b21 + a43 * b31 + a44 * b41;
                  te[7] = a41 * b12 + a42 * b22 + a43 * b32 + a44 * b42;
                  te[11] = a41 * b13 + a42 * b23 + a43 * b33 + a44 * b43;
                  te[15] = a41 * b14 + a42 * b24 + a43 * b34 + a44 * b44;

                  return this;

              },
              // Ã¨Â§Â£Ã¥â€ Â³Ã¨Â§â€™Ã¥ÂºÂ¦Ã¤Â¸Âº90Ã§Å¡â€žÃ¦â€¢Â´Ã¦â€¢Â°Ã¥â‚¬ÂÃ¥Â¯Â¼Ã¨â€¡Â´Math.cosÃ¥Â¾â€”Ã¥Ë†Â°Ã¦Å¾ÂÃ¥Â°ÂÃ§Å¡â€žÃ¦â€¢Â°Ã¯Â¼Å’Ã¥â€¦Â¶Ã¥Â®Å¾Ã¦ËœÂ¯0Ã£â‚¬â€šÃ¥Â¯Â¼Ã¨â€¡Â´Ã¤Â¸ÂÃ¦Â¸Â²Ã¦Å¸â€œ
              _rounded: function(value,i){
                  i= Math.pow(10, i || 15);
                  // default
                  return Math.round(value*i)/i;
              },
              appendTransform: function (x, y, z, scaleX, scaleY, scaleZ, rotateX, rotateY, rotateZ,skewX,skewY, originX, originY, originZ) {

                  var rx = rotateX * Matrix3D.DEG_TO_RAD;
                  var cosx =this._rounded( Math.cos(rx));
                  var sinx = this._rounded(Math.sin(rx));
                  var ry = rotateY * Matrix3D.DEG_TO_RAD;
                  var cosy =this._rounded( Math.cos(ry));
                  var siny = this._rounded(Math.sin(ry));
                  var rz = rotateZ * Matrix3D.DEG_TO_RAD;
                  var cosz =this._rounded( Math.cos(rz * -1));
                  var sinz =this._rounded( Math.sin(rz * -1));

                  this.multiplyMatrices(this, [
                      1, 0, 0, x,
                      0, cosx, sinx, y,
                      0, -sinx, cosx, z,
                      0, 0, 0, 1
                  ]);

                  this.multiplyMatrices(this, [
                      cosy, 0, siny, 0,
                      0, 1, 0, 0,
                      -siny, 0, cosy, 0,
                      0, 0, 0, 1
                  ]);

                  this.multiplyMatrices(this,[
                      cosz * scaleX, sinz * scaleY, 0, 0,
                      -sinz * scaleX, cosz * scaleY, 0, 0,
                      0, 0, 1 * scaleZ, 0,
                      0, 0, 0, 1
                  ]);

                  if(skewX||skewY){
                      this.multiplyMatrices(this,[
                          this._rounded(Math.cos(skewX* Matrix3D.DEG_TO_RAD)), this._rounded( Math.sin(skewX* Matrix3D.DEG_TO_RAD)), 0, 0,
                          -1*this._rounded(Math.sin(skewY* Matrix3D.DEG_TO_RAD)), this._rounded( Math.cos(skewY* Matrix3D.DEG_TO_RAD)), 0, 0,
                          0, 0, 1, 0,
                          0, 0, 0, 1
                      ]);
                  }

                  if (originX || originY || originZ) {
                      this.elements[12] -= originX * this.elements[0] + originY * this.elements[4] + originZ * this.elements[8];
                      this.elements[13] -= originX * this.elements[1] + originY * this.elements[5] + originZ * this.elements[9];
                      this.elements[14] -= originX * this.elements[2] + originY * this.elements[6] + originZ * this.elements[10];
                  }
                  return this;
              }
          };

          function observe(target, props, callback) {
              for (var i = 0, len = props.length; i < len; i++) {
                  var prop = props[i];
                  watch(target, prop, callback);
              }
          }

          function watch(target, prop, callback) {
              Object.defineProperty(target, prop, {
                  get: function () {
                      return this["__" + prop];
                  },
                  set: function (value) {
                      if (value !== this["__" + prop]) {
                          this["__" + prop] = value;
                          callback();
                      }

                  }
              });
          }

          window.Transform = function (element) {

              observe(
                  element,
                  ["translateX", "translateY", "translateZ", "scaleX", "scaleY", "scaleZ" , "rotateX", "rotateY", "rotateZ","skewX","skewY", "originX", "originY", "originZ"],
                  function () {
                      var mtx = element.matrix3D.identity().appendTransform( element.translateX, element.translateY, element.translateZ, element.scaleX, element.scaleY, element.scaleZ, element.rotateX, element.rotateY, element.rotateZ,element.skewX,element.skewY, element.originX, element.originY, element.originZ);
                      element.style.transform = element.style.msTransform = element.style.OTransform = element.style.MozTransform = element.style.webkitTransform = "perspective("+element.perspective+"px) matrix3d(" + Array.prototype.slice.call(mtx.elements).join(",") + ")";
                  });

              observe(
                  element,
                  [ "perspective"],
                  function () {
                      element.style.transform = element.style.msTransform = element.style.OTransform = element.style.MozTransform = element.style.webkitTransform = "perspective("+element.perspective+"px) matrix3d(" + Array.prototype.slice.call(element.matrix3D.elements).join(",") + ")";
                  });

              element.matrix3D = new Matrix3D();
              element.perspective = 500;
              element.scaleX = element.scaleY = element.scaleZ = 1;
              //Ã§â€Â±Ã¤ÂºÅ½imageÃ¨â€¡ÂªÃ¥Â¸Â¦Ã¤Âºâ€ x\y\zÃ¯Â¼Å’Ã¦â€°â‚¬Ã¦Å“â€°Ã¥Å Â Ã¤Â¸Å translateÃ¥â€°ÂÃ§Â¼â‚¬
              element.translateX = element.translateY = element.translateZ = element.rotateX = element.rotateY = element.rotateZ =element.skewX=element.skewY= element.originX = element.originY = element.originZ = 0;
          }


          if (typeof module !== 'undefined' && typeof exports === 'object') {
              module.exports = Transform;
          }
      })();
      ; (function () {
          function getLen(v) {
              return Math.sqrt(v.x * v.x + v.y * v.y);
          }

          function dot(v1, v2) {
              return v1.x * v2.x + v1.y * v2.y;
          }

          function getAngle(v1, v2) {
              var mr = getLen(v1) * getLen(v2);
              if (mr === 0) return 0;
              var r = dot(v1, v2) / mr;
              if (r > 1) r = 1;
              return Math.acos(r);
          }

          function cross(v1, v2) {
              return v1.x * v2.y - v2.x * v1.y;
          }

          function getRotateAngle(v1, v2) {
              var angle = getAngle(v1, v2);
              if (cross(v1, v2) > 0) {
                  angle *= -1;
              }

              return angle * 180 / Math.PI;
          }

          var HandlerAdmin = function(el) {
              this.handlers = [];
              this.el = el;
          };

          HandlerAdmin.prototype.add = function(handler) {
              this.handlers.push(handler);
          }

          HandlerAdmin.prototype.del = function(handler) {
              if(!handler) this.handlers = [];

              for(var i=this.handlers.length; i>=0; i--) {
                  if(this.handlers[i] === handler) {
                      this.handlers.splice(i, 1);
                  }
              }
          }

          HandlerAdmin.prototype.dispatch = function() {
              for(var i=0,len=this.handlers.length; i<len; i++) {
                  var handler = this.handlers[i];
                  if(typeof handler === 'function') handler.apply(this.el, arguments);
              }
          }

          function wrapFunc(el, handler) {
              var handlerAdmin = new HandlerAdmin(el);
              handlerAdmin.add(handler);
              return handlerAdmin;
          }

          var AlloyFinger = function (el, option) {

              this.element = typeof el === 'string' ? document.querySelector(el) : el;

              this.start = this.start.bind(this);
              this.move = this.move.bind(this);
              this.end = this.end.bind(this);
              this.cancel = this.cancel.bind(this);
              this.element.addEventListener("touchstart", this.start, false);
              this.element.addEventListener("mousedown", this.start, false);
              this.element.addEventListener("touchmove", this.move, false);
              this.element.addEventListener("mousemove", this.move, false);
              this.element.addEventListener("touchend", this.end, false);
              this.element.addEventListener("mouseup", this.end, false);
              this.element.addEventListener("touchcancel", this.cancel, false);

              this.preV = { x: null, y: null };
              this.pinchStartLen = null;
              this.zoom = 1;
              this.isDoubleTap = false;

              var noop = function () { };

              this.rotate = wrapFunc(this.element, option.rotate || noop);
              this.touchStart = wrapFunc(this.element, option.touchStart || noop);
              this.click = wrapFunc(this.element, option.click || noop);
              this.multipointStart = wrapFunc(this.element, option.multipointStart || noop);
              this.multipointEnd = wrapFunc(this.element, option.multipointEnd || noop);
              this.pinch = wrapFunc(this.element, option.pinch || noop);
              this.swipe = wrapFunc(this.element, option.swipe || noop);
              this.tap = wrapFunc(this.element, option.tap || noop);
              this.doubleTap = wrapFunc(this.element, option.doubleTap || noop);
              this.longTap = wrapFunc(this.element, option.longTap || noop);
              this.singleTap = wrapFunc(this.element, option.singleTap || noop);
              this.pressMove = wrapFunc(this.element, option.pressMove || noop);
              this.touchMove = wrapFunc(this.element, option.touchMove || noop);
              this.touchEnd = wrapFunc(this.element, option.touchEnd || noop);
              this.touchCancel = wrapFunc(this.element, option.touchCancel || noop);

              this.delta = null;
              this.last = null;
              this.now = null;
              this.tapTimeout = null;
              this.singleTapTimeout = null;
              this.longTapTimeout = null;
              this.swipeTimeout = null;
              this.x1 = this.x2 = this.y1 = this.y2 = null;
              this.preTapPosition = { x: null, y: null };

              this.started = false;
          };

          AlloyFinger.prototype = {
              start: function (evt) {
                  //if (!evt.touches) return;
                  this.now = Date.now();
                  var touch = !!evt.touches;
                  this.x1 = touch ? evt.touches[0].pageX : evt.pageX;
                  this.y1 = touch ? evt.touches[0].pageY : evt.pageY;
                  this.delta = this.now - (this.last || this.now);
                  this.touchStart.dispatch(evt);
                  this.click.dispatch(evt);

                  if (this.preTapPosition.x !== null) {
                      this.isDoubleTap = (this.delta > 0 && this.delta <= 250 && Math.abs(this.preTapPosition.x - this.x1) < 30 && Math.abs(this.preTapPosition.y - this.y1) < 30);
                  }
                  this.preTapPosition.x = this.x1;
                  this.preTapPosition.y = this.y1;
                  this.last = this.now;
                  var preV = this.preV,
                      len = evt.touches ? evt.touches.length : 1;
                  if (len > 1) {
                      this._cancelLongTap();
                      this._cancelSingleTap();
                      var v = { x: evt.touches[1].pageX - this.x1, y: evt.touches[1].pageY - this.y1 };
                      preV.x = v.x;
                      preV.y = v.y;
                      this.pinchStartLen = getLen(preV);
                      this.multipointStart.dispatch(evt);
                  }
                  this.longTapTimeout = setTimeout(function () {
                      this.longTap.dispatch(evt);
                  }.bind(this), 750);
                  this.started = true;
              },
              move: function (evt) {
                  //if (!evt.touches) return;
                  if(!this.started)
                      return;
                  var preV = this.preV,
                      touch = !!evt.touches;
                      len = touch ? evt.touches.length : 1,
                      currentX = touch ? evt.touches[0].pageX : evt.pageX,
                      currentY = touch ? evt.touches[0].pageY : evt.pageY;
                  this.isDoubleTap = false;
                  if (len > 1) {
                      var v = { x: evt.touches[1].pageX - currentX, y: evt.touches[1].pageY - currentY };

                      if (preV.x !== null) {
                          if (this.pinchStartLen > 0) {
                              evt.zoom = getLen(v) / this.pinchStartLen;
                              this.pinch.dispatch(evt);
                          }

                          evt.angle = getRotateAngle(v, preV);
                          this.rotate.dispatch(evt);
                      }
                      preV.x = v.x;
                      preV.y = v.y;
                  } else {
                      if (this.x2 !== null) {
                          evt.deltaX = currentX - this.x2;
                          evt.deltaY = currentY - this.y2;
                      } else {
                          evt.deltaX = 0;
                          evt.deltaY = 0;
                      }
                      this.pressMove.dispatch(evt);
                  }

                  this.touchMove.dispatch(evt);

                  this._cancelLongTap();
                  this.x2 = currentX;
                  this.y2 = currentY;
                  if (len > 1 || !touch) {
                      evt.preventDefault();
                  }
              },
              end: function (evt) {
                  //if (!evt.changedTouches) return;
                  this._cancelLongTap();
                  var self = this,
                      len = evt.touches ? evt.touches.length : 1;
                  if (len < 2) {
                      this.multipointEnd.dispatch(evt);
                  }

                  //swipe
                  if ((this.x2 && Math.abs(this.x1 - this.x2) > 30) ||
                      (this.y2 && Math.abs(this.y1 - this.y2) > 30)) {
                      evt.direction = this._swipeDirection(this.x1, this.x2, this.y1, this.y2);
                      this.swipeTimeout = setTimeout(function () {
                          self.swipe.dispatch(evt);

                      }, 0)
                  } else {
                      this.tapTimeout = setTimeout(function () {
                          self.tap.dispatch(evt);
                          // trigger double tap immediately
                          if (self.isDoubleTap) {
                              self.doubleTap.dispatch(evt);
                              clearTimeout(self.singleTapTimeout);
                              self.isDoubleTap = false;
                          }
                      }, 0)

                      if (!self.isDoubleTap) {
                          self.singleTapTimeout = setTimeout(function () {
                              self.singleTap.dispatch(evt);
                          }, 250);
                      }
                  }

                  this.touchEnd.dispatch(evt);

                  this.preV.x = 0;
                  this.preV.y = 0;
                  this.zoom = 1;
                  this.pinchStartLen = null;
                  this.x1 = this.x2 = this.y1 = this.y2 = null;
                  this.started = false;
              },
              cancel: function (evt) {
                  clearTimeout(this.singleTapTimeout);
                  clearTimeout(this.tapTimeout);
                  clearTimeout(this.longTapTimeout);
                  clearTimeout(this.swipeTimeout);
                  this.touchCancel.dispatch(evt);
              },
              _cancelLongTap: function () {
                  clearTimeout(this.longTapTimeout);
              },
              _cancelSingleTap: function () {
                  clearTimeout(this.singleTapTimeout);
              },
              _swipeDirection: function (x1, x2, y1, y2) {
                  return Math.abs(x1 - x2) >= Math.abs(y1 - y2) ? (x1 - x2 > 0 ? 'Left' : 'Right') : (y1 - y2 > 0 ? 'Up' : 'Down')
              },

              on: function(evt, handler) {
                  if(this[evt]) {
                      this[evt].add(handler);
                  }
              },

              off: function(evt, handler) {
                  if(this[evt]) {
                      this[evt].del(handler);
                  }
              },

              destroy: function() {
                  if(this.singleTapTimeout) clearTimeout(this.singleTapTimeout);
                  if(this.tapTimeout) clearTimeout(this.tapTimeout);
                  if(this.longTapTimeout) clearTimeout(this.longTapTimeout);
                  if(this.swipeTimeout) clearTimeout(this.swipeTimeout);

                  this.element.removeEventListener("touchstart", this.start);
                  this.element.removeEventListener("mousedown", this.start);
                  this.element.removeEventListener("touchmove", this.move);
                  this.element.removeEventListener("mousemove", this.move);
                  this.element.removeEventListener("touchend", this.end);
                  this.element.removeEventListener("mouseup", this.end);
                  this.element.removeEventListener("touchcancel", this.cancel);

                  this.rotate.del();
                  this.touchStart.del();
                  this.click.del();
                  this.multipointStart.del();
                  this.multipointEnd.del();
                  this.pinch.del();
                  this.swipe.del();
                  this.tap.del();
                  this.doubleTap.del();
                  this.longTap.del();
                  this.singleTap.del();
                  this.pressMove.del();
                  this.touchMove.del();
                  this.touchEnd.del();
                  this.touchCancel.del();

                  this.preV = this.pinchStartLen = this.zoom = this.isDoubleTap = this.delta = this.last = this.now = this.tapTimeout = this.singleTapTimeout = this.longTapTimeout = this.swipeTimeout = this.x1 = this.x2 = this.y1 = this.y2 = this.preTapPosition = this.rotate = this.touchStart = this.click = this.multipointStart = this.multipointEnd = this.pinch = this.swipe = this.tap = this.doubleTap = this.longTap = this.singleTap = this.pressMove = this.touchMove = this.touchEnd = this.touchCancel = null;

                  return null;
              }
          };

          if (typeof module !== 'undefined' && typeof exports === 'object') {
              module.exports = AlloyFinger;
          } else {
              window.AlloyFinger = AlloyFinger;
          }
      })();
      var AlloyFinger = typeof require === 'function'
          ? require('alloyfinger')
          : window.AlloyFinger
      var Transform = typeof require === 'function'
          ? require('css3transform').default
          : window.Transform

      var AlloyCrop = function (option) {
          this.renderTo = document.body;
          this.canvas = document.createElement("canvas");
          this.output = option.output;
          this.width = option.width;
          this.height = option.height;
          this.canvas.width = option.width * this.output;
          this.canvas.height = option.height * this.output;
          this.circle = option.circle;
          if (option.width !== option.height && option.circle) {
              throw "can't set circle to true when width is not equal to height"
          }
          this.ctx = this.canvas.getContext("2d");
          this.croppingBox = document.createElement("div");
          this.croppingBox.style.visibility = "hidden";
          this.croppingBox.className = option.className || '';
          this.cover = document.createElement("canvas");
          this.type = option.type || "png";
          this.cover.width = window.innerWidth;
          this.cover.height = window.innerHeight;
          this.cover_ctx = this.cover.getContext("2d");
          this.img = document.createElement("img");

          if(option.image_src.substring(0,4).toLowerCase()==='http') {
              this.img.crossOrigin = 'anonymous';//resolve base64 uri bug in safari:"cross-origin image load denied by cross-origin resource sharing policy."
          }
          this.cancel = option.cancel;
          this.ok = option.ok;

          this.ok_text = option.ok_text || "ok";
          this.cancel_text = option.cancel_text || "cancel";

          this.croppingBox.appendChild(this.img);
          this.croppingBox.appendChild(this.cover);
          this.renderTo.appendChild(this.croppingBox);
          this.img.onload = this.init.bind(this);
          this.img.src = option.image_src;

          this.cancel_btn = document.createElement('a');
          this.cancel_btn.innerHTML = this.cancel_text;
          this.ok_btn = document.createElement('a');
          this.ok_btn.innerHTML = this.ok_text;

          this.croppingBox.appendChild(this.ok_btn);
          this.croppingBox.appendChild(this.cancel_btn);

          this.alloyFingerList = [];

          console.log('alloycrop init');
      };

      AlloyCrop.prototype = {
          init: function () {
              this.img_width = this.img.naturalWidth;
              this.img_height = this.img.naturalHeight;
              Transform(this.img,true);
              var scaling_x = window.innerWidth / this.img_width,
                  scaling_y = window.innerHeight / this.img_height;
              var scaling = scaling_x > scaling_y ? scaling_y : scaling_x;
              /*this.initScale = scaling;
              this.originScale = scaling;
              this.img.scaleX = this.img.scaleY = scaling;*/
              this.initScale = scaling_x;
              this.originScale = scaling_x;
              this.img.scaleX = this.img.scaleY = scaling_x;
              this.first = 1;
              var self = this;
              this.alloyFingerList.push(new AlloyFinger(this.croppingBox, {
                  multipointStart: function (evt) {
                      //reset origin x and y
                      var centerX = (evt.touches[0].pageX + evt.touches[1].pageX) / 2;
                      var centerY = (evt.touches[0].pageY + evt.touches[1].pageY) / 2;
                      var cr = self.img.getBoundingClientRect();
                      var img_centerX = cr.left + cr.width / 2;
                      var img_centerY = cr.top + cr.height / 2;
                      var offX = centerX - img_centerX;
                      var offY = centerY - img_centerY;
                      var preOriginX = self.img.originX
                      var preOriginY = self.img.originY
                      self.img.originX = offX / self.img.scaleX;
                      self.img.originY = offY / self.img.scaleY;
                      //reset translateX and translateY

                      self.img.translateX += offX - preOriginX * self.img.scaleX;
                      self.img.translateY += offY - preOriginY * self.img.scaleX;


                      if(self.first == 1){
                          self.img.scaleX = self.img.scaleY = self.initScale * 1.1;
                          ++self.first;
                      }

                      self.initScale = self.img.scaleX;

                  },
                  pinch: function (evt) {

                      var cr = self.img.getBoundingClientRect();
                      var boxOffY = (document.documentElement.clientHeight - self.height)/2;

                      var tempo = evt.zoom;
                      var dw = (cr.width * tempo - cr.width)/2;
                      var dh = (cr.height - cr.height * tempo)/2;
                      if( (self.initScale * tempo <= 1.6 ) && (self.initScale * tempo >= self.originScale) && (dw >= cr.left) && (-dw <= (cr.right - self.width) ) && (dh <= (boxOffY - cr.top) ) && (dh <= (cr.bottom-boxOffY-self.height)) ){
                          self.img.scaleX = self.img.scaleY = self.initScale * tempo;
                      }
                  },
                  pressMove: function (evt) {
                      var cr = self.img.getBoundingClientRect();
                      var boxOffY = (document.documentElement.clientHeight - self.height)/2;
                      if((boxOffY - cr.top - evt.deltaY >= 0) && (cr.bottom + evt.deltaY - boxOffY>= self.height)){
                          self.img.translateY += evt.deltaY;
                      }
                      var boxOffX = (document.documentElement.clientWidth - self.width)/2;
                      if((cr.left + evt.deltaX <= boxOffX) && (cr.right + evt.deltaX - boxOffX >= self.width)){
                          self.img.translateX += evt.deltaX;
                      }
                      evt.preventDefault();
                  }
              }));

              this.alloyFingerList.push(new AlloyFinger(this.cancel_btn, {
                  touchStart:function(){
                      self.cancel_btn.style.backgroundColor = '#ffffff';
                      self.cancel_btn.style.color = '#3B4152';
                  },
                  click:this._cancel.bind(this),
                  tap: this._cancel.bind(this)
              }));

              this.alloyFingerList.push(new AlloyFinger(this.ok_btn, {
                  touchStart:function(){
                      self.ok_btn.style.backgroundColor = '#2bcafd';
                      self.ok_btn.style.color = '#ffffff';
                  },
                  click:this._ok.bind(this),
                  tap: this._ok.bind(this)
              }));

              this.alloyFingerList.push(new AlloyFinger(document, {
                  touchEnd: function () {
                      self.cancel_btn.style.backgroundColor = '#ffffff';
                      self.ok_btn.style.backgroundColor = '#2bcafd';
                  }
              }));

              this.renderCover();
              this.setStyle();

          },
          _cancel: function () {
              var self = this;
              setTimeout(function () {
                  self.croppingBox && self._css(self.croppingBox, {
                      display: "none"
                  });
              }, 300);
              this.cancel();
          },
          _ok: function () {
              this.crop();
              var self = this;
              setTimeout(function () {
                  self.croppingBox && self._css(self.croppingBox, {
                      display: "none"
                  });
              }, 300);
              this.ok(this.canvas.toDataURL("image/" + this.type), this.canvas);
          },
          renderCover: function () {
              var ctx = this.cover_ctx,
                  w = this.cover.width,
                  h = this.cover.height,
                  cw = this.width,
                  ch = this.height;
              ctx.save();
              ctx.fillStyle = "black";
              ctx.globalAlpha = 0.7;
              ctx.fillRect(0, 0, this.cover.width, this.cover.height);
              ctx.restore();
              ctx.save();
              ctx.globalCompositeOperation = "destination-out";
              ctx.beginPath();
              if (this.circle) {
                  ctx.arc(w / 2, h / 2, cw / 2 - 4, 0, Math.PI * 2, false);
              } else {
                  ctx.rect(w / 2 - cw / 2, h / 2 - ch / 2, cw, ch)
              }
              ctx.fill();
              ctx.restore();
              ctx.save();
              ctx.beginPath();
              ctx.strokeStyle = "white";
              if (this.circle) {
                  ctx.arc(w / 2, h / 2, cw / 2 - 4, 0, Math.PI * 2, false);
              } else {
                  ctx.rect(w / 2 - cw / 2, h / 2 - ch / 2, cw, ch)
              }
              ctx.stroke();
          },
          setStyle: function () {
              this._css(this.cover, {
                  position: "fixed",
                  zIndex: "100",
                  left: "0px",
                  top: "0px"
              });

              this._css(this.croppingBox, {
                  color: "white",
                  textAlign: "center",
                  fontSize: "18px",
                  textDecoration: "none",
                  visibility: "visible"
              });

              this._css(this.img, {
                  position: "fixed",
                  zIndex: "99",
                  left: "50%",
                  // error position in meizu when set the top  50%
                  top: window.innerHeight / 2 + "px",
                  marginLeft: this.img_width / -2 + "px",
                  marginTop: this.img_height / -2 + "px"
              });


              this._css(this.ok_btn, {
                  position: "fixed",
                  zIndex: "101",
                  width: "100px",
                  right: "50px",
                  lineHeight: "40px",
                  height: "40px",
                  bottom: "120px",
                  borderRadius: "2px",
                  color: "#ffffff",
                  backgroundColor: "#2bcafd"

              });

              this._css(this.cancel_btn, {
                  position: "fixed",
                  zIndex: "101",
                  width: "100px",
                  height: "40px",
                  lineHeight: "40px",
                  left: "50px",
                  bottom: "120px",
                  borderRadius: "2px",
                  color: "#3B4152",
                  backgroundColor: "#ffffff"

              });
          },
          crop: function () {
              this.calculateRect();
              //this.ctx.drawImage(this.img, this.crop_rect[0], this.crop_rect[1], this.crop_rect[2], this.crop_rect[3], 0, 0, this.canvas.width, this.canvas.height);
              this.ctx.drawImage(this.img, this.crop_rect[0], this.crop_rect[1], this.crop_rect[2], this.crop_rect[3], 0, 0, this.crop_rect[2]*this.img.scaleX*this.output, this.crop_rect[3]*this.img.scaleY*this.output);

          },
          calculateRect: function () {
              var cr = this.img.getBoundingClientRect();
              var c_left = window.innerWidth / 2 - this.width / 2;
              var c_top = window.innerHeight / 2 - this.height / 2;
              var cover_rect = [c_left, c_top, this.width + c_left, this.height + c_top];
              var img_rect = [cr.left, cr.top, cr.width + cr.left, cr.height + cr.top];
              var intersect_rect = this.getOverlap.apply(this, cover_rect.concat(img_rect));
              var left = (intersect_rect[0] - img_rect[0]) / this.img.scaleX;
              var top = (intersect_rect[1] - img_rect[1]) / this.img.scaleY;
              var width = intersect_rect[2] / this.img.scaleX;
              var height = intersect_rect[3] / this.img.scaleY;

              if (left < 0) left = 0;
              if (top < 0) top = 0;
              if (left + width > this.img_width) width = this.img_width - left;
              if (top + height > this.img_height) height = this.img_height - top;

              this.crop_rect = [left, top, width, height];
          },
          // top left (x1,y1) and bottom right (x2,y2) coordination
          getOverlap: function (ax1, ay1, ax2, ay2, bx1, by1, bx2, by2) {
              if (ax2 < bx1 || ay2 < by1 || ax1 > bx2 || ay1 > by2) return [0, 0, 0, 0];

              var left = Math.max(ax1, bx1);
              var top = Math.max(ay1, by1);
              var right = Math.min(ax2, bx2);
              var bottom = Math.min(ay2, by2);
              return [left, top, right - left, bottom - top]
          },
          _css: function (el, obj) {
              for (var key in obj) {
                  if (obj.hasOwnProperty(key)) {
                      el.style[key] = obj[key];
                  }
              }
          },
          destroy: function () {
              this.alloyFingerList.forEach(function (alloyFinger) {
                  alloyFinger.destroy();
              });
              this.renderTo.removeChild(this.croppingBox);
              for(var key in this) {
                  delete this[key];
              }
          }
      };

      if (typeof module !== 'undefined' && typeof exports === 'object') {
          module.exports = AlloyCrop;
      }else {
          window.AlloyCrop = AlloyCrop;
      }
  })();
console.log('test');
  if (typeof FileReader != 'undefined') {
// this is corect .. it's what i have locally ... so it uploads properly
// the issue lies with the server, not with me
//please try again once more in your pc

      $('#turkey-file', '#turkey-upload-and-crop').on('change', function (e) {
          e.preventDefault();
          if ($('#turkey-action', '#turkey-upload-and-crop').val() == 'trs_portrait_upload' && $('#turkey-file', '#turkey-upload-and-crop').length == 0) {
              return;
          }

          $('#turkey-upload-and-crop #turkey-file,#turkey-upload-and-crop #turkey-upload').prop('disabled', true).addClass('loading');

          var filelist = $('#turkey-file', '#turkey-upload-and-crop')[0].files;

          if (!filelist) {
              alert('Please select an Image file first!');
              return;
          }
          var file = filelist[0],
              // turturkeykey duplicate photo fix start
              filename = file.name;

          if(filename.indexOf('image') === 0) {
              filename = Math.random().toString(36).substring(4) + '-' + filename;
          }
            // turturkeykey duplicate photo fix end
          var maxwidth = 700;
          var quality = 0.89;

          ImageResizer.scaleImageRaw( file , quality, maxwidth , function( dataUrl ) {
              var fd = new FormData();
              fd.append('_key', $('#_key', '#turkey-upload-and-crop').val());
              fd.append('_http_referer', $('[name="_http_referer"]', '#turkey-upload-and-crop').val());
              fd.append('file', dataURItoBlob(dataUrl), filename); // turturkeykey also changed to filename here
              fd.append('action', 'trs_portrait_upload');
              fd.append('turkey-upload', 'yes');
              fd.append('upload', 'Save');
              $.ajax({
                  // use the username var from the header
                  url : '/members/'+username+'/profile/change-profile-photo/',
                  data : fd,
                  processData : false,
                  contentType : false,
                  type : 'POST',
                  dataType: 'json',
                  success : function(data) {
                      var mAlloyCrop = new AlloyCrop({
                          image_src: data.image,
                          circle: true,
                          width: 275,
                          height:275,
                          output: 1.0,
                          className: 'm-clip-box',
                          ok: function (base64, canvas) {
                              $('#contour-image img').attr('src', canvas.toDataURL());
                              var fd = new FormData();
                              fd.append('_key', data._key);
                              fd.append('_http_referer', data._http_referer);
                              fd.append('image_crop', canvas.toDataURL());
                              fd.append('image_src', data.image);
                              fd.append('portrait-crop-submit', 'Crop Image');
                              fd.append('turkey-crop', 'yes');
                              fd.append('upload', 'Save');
                              $.ajax({
                                    // use the username var from the header
                                  url: '/members/'+username+'/profile/change-profile-photo/',
                                  data: fd,
                                  processData: false,
                                  contentType: false,
                                  type: 'POST',
                                    success: function (response) {
                                        $('#turkey-file', '#turkey-upload-and-crop').prop("disabled", false).val("");
                                    }
                              });
                              mAlloyCrop.destroy();
                          },
                          cancel: function () {
                              mAlloyCrop.destroy();
                              $('#turkey-file', '#turkey-upload-and-crop').prop("disabled", false).val("");

                          }
                      });
                  }
              });
          })

      });
  }


