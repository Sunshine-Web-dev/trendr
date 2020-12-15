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
	//	return false;
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
		jq('select#activity-privacy').replaceWith(visibility_levels.groups);
	}

	jq("select#whats-new-post-in").bind("change", function() {
		var old_selected_item_id = jq(this).data('selected');
		var item_id = jq("#whats-new-post-in").val();

		if(item_id == 0 && item_id != old_selected_item_id){
			jq('select#activity-privacy').replaceWith(visibility_levels.profil);
		}else{
			if(item_id != 0 && old_selected_item_id == 0 ){
				jq('select#activity-privacy').replaceWith(visibility_levels.groups);
			}
		}
		jq('select#activity-privacy').next().remove();
		if(visibility_levels.custom_selectbox) {
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

			if(visibility_levels.custom_selectbox) {
				//jq('select.trs-ap-selectbox').customStyle('2');
				jq('select.trs-ap-selectbox').customSelect();
			}
		});

		return false;
	});

	if(visibility_levels.custom_selectbox) {
		jq('select#activity-privacy').customSelect();
		jq('select.trs-ap-selectbox').customSelect();
		//jq('select#activity-privacy').customStyle('1');
		//jq('select.trs-ap-selectbox').customStyle('2');
	}
});
