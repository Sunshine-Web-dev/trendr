<?php

//$iwmp_options = get_option('iwmp_settings');

//if ( $iwmp_options['iwmp_single_images'] == '1' ) {

	function iwmp_add_single_script() { ?>

		<script id="iwmp_settings_single">

		// Ref: http://ajtroxell.com/use-magnific-popup-with-wordpress-now/
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
							$(res.contents).find('a.view').each(function(index){
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
									$(res.contents).find('a.view').each(function(){
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
				$(' a.view').each(function(){
					//single image popup
					//if ($(this).parents('.iwmp-gallery').length == 0) { //check that it's not part of a gallery

						$(this).addClass('iwmp-gallery'); //Add a class



						$('.iwmp-gallery').magnificPopup({
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
							      }
							    },
								image: {
											 markup: '<div class="iframe-popup">'+
				                            '<iframe class="mfp-iframe" frameborder="0" scrolling="no"" onload="resizeIframe(this)" allowtransparency="true" allowfullscreen></iframe>'+
				                            '<div class="mfp-close"></div>'+
				                          '</div>'
				                          }
							});
				//	}



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

		</script>

	<?php } //end function iwmp_add_single_script()

	add_action( 'trm_footer', 'iwmp_add_single_script' );

//} // end if for single images



	//add_action( 'trm_footer', 'iwmp_add_css' );
?>
