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
		if ( TR_Theme.mention_explain )
			link.addClass('loading');

		target=target.replace("&inv=ajax", "");


		jq.get( target+"&inv=ajax",
		function(response) {
			var response = JSON.parse(response);
			if(response.res == true){

					jq(link.parent()).fadeOut(200, function() {

						link.attr('data-ref',response.lnk);

						if(link.hasClass('block')){
								link.removeClass('block');
						 	 	link.html( 'Unblock' );
						 		link.addClass('unblock');
					  }else if(link.hasClass('unblock')){
								link.removeClass('unblock');
							 	link.html( 'Block' );
								link.addClass('block');
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
