/**
* Template Name: All Stories Page
*
* @package WordPress
* @Writed By Yang
*/	
<?php get_header() ?>

	<div id="skeleton"">
		<div class="dimension">

		<?php do_action( 'trs_before_blog_page' ) ?>
		
		<div class="page" id="static-page" role="main">

			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

				<h2 class="pagetitle"><?php the_title(); ?></h2>

				<div id="post-<?php the_ID(); ?>" >

					<div class="entry">

						<?php the_content( __( '<p class="serif">Read the rest of this page &rarr;</p>', 'trendr' ) ); ?>

					
					</div>

				</div>


			<?php endwhile; endif; ?>

		</div><!-- .page -->
		<?php
			function getDistanceBetweenPoints($lat1, $lon1, $lat2, $lon2) {
			    $theta = $lon1 - $lon2;
			    $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
			    $miles = acos($miles);
			    $miles = rad2deg($miles);
			    $miles = $miles * 60 * 1.1515;
			    $kilometers = $miles * 1.609344;
			    return $kilometers;
			}

			

			//$users = get_users( array( 'fields' => array( 'ID' ) ) );
			//$users_nearby=[];
			$trschk_location_id = xprofile_get_field_id_from_name( 'Location' );
			$my_address_meta=trs_xprofile_get_meta( get_current_user_id(),$trschk_location_id, 'data', 'location' );
			$trschk=trs_get_option('trschk_general_settings');
			$my_radius=trs_xprofile_get_meta( get_current_user_id(),'4', 'data', 'radius' );
			if($my_radius==" " || $my_radius=="" || empty($my_radius) || !isset($my_radius)){
				$my_radius=$trschk['range'];
			}

			$lat1=$my_address_meta['lat'];
			$long1=$my_address_meta['long'];
			//$user_index=0;
			//$location_query=$trmdb->prepare( "SELECT user_id,meta_value FROM " . $trs->profile->table_name_meta . " WHERE object_id = %d AND object_type = %s AND meta_key = %s", 2, 'data','location' );
			
			//$all_location_metas=$trmdb->get_results($location_query,OBJECT);
			/*foreach ($all_location_metas as $user){
				
				if($user->user_id != get_current_user_id()){
					$loc_xprof_meta=unserialize($user->meta_value);
					if(isset($loc_xprof_meta['address'])){
						$lat2=$loc_xprof_meta['lat'];
						$long2=$loc_xprof_meta['long'];
						
						$range=getDistanceBetweenPoints($lat1,$long1,$lat2,$long2);
						if($range<=$my_radius){
							$users_nearby[$user_index]=$user->user_id;
							$user_index++;
						}
					}
				}
			}*/
			
			//$user_ids=join(",",$users_nearby);
			/*$querystr = "
			    SELECT * FROM {$trmdb->prefix}trs_stories
			    WHERE {$trmdb->prefix}trs_stories.user_id IN ($user_ids)
			    ORDER BY {$trmdb->prefix}trs_stories.date_recorded Desc  
			 ";*/
			 $querystr = "
			    SELECT * FROM {$trmdb->prefix}trs_stories
			    ORDER BY {$trmdb->prefix}trs_stories.date_recorded Desc  
			 ";
			 
			 $temp_results=$trmdb->get_results($querystr, OBJECT);
			 $results=[];
 			 $user_index=0;
			 foreach ($temp_results as $temp){
			 	if(get_current_user_id() != $temp->user_id){


				 	$location=json_decode($temp->location);
				 	//print_r($location->address);
				 	if(isset($location->lat) && isset($location->long)){
				 		$range=getDistanceBetweenPoints($lat1,$long1,$location->lat,$location->long);
						if($range<=$my_radius){
							$results[$user_index]=$temp;
							$user_index++;
						}
				 	}
				 }
			 }
	
			?>	
			<h1 style="padding-left: 20px;">All Stories</h1>
			<a href="/my-stories" style="float: right;margin-top: -50px;margin-right: 40px;">My Stories</a>
			<h2 style="padding-left: 20px;color:#ff0000;">Filter Options</h2>
			<div class="filters_options" id="filter_option" style="padding-left:30px;">
				<lable>Categories:</lable>
				<input id="activate_tagator3" width="90%" type="text" class="tagator" placeholder="Please select your category for stories..." value="<?php 
					$cate_str=trs_xprofile_get_meta( get_current_user_id(),3, 'data', 'categories' );
					$cates=str_replace('_',',',$cate_str);
					
					
				?>" style="width:350px !important;" data-tagator-show-all-options-on-focus="true" data-tagator-autocomplete="['Resturant', 'Clubs', 'Bar']">
				<label style="margin-left: 30px;">Radius(Km):&nbsp;</label>
				<input type="number" id="radius" step="0.01" min="0.01" value="<?php echo $my_radius;?>" />
				<button id="submit_filter">Search Stories</button>
			</div>
			<div class="stories" id="stories" style="width:875px;height:auto;margin-bottom: 20px;display: inline-block;">
				
				<div class="add_new_stories stores_div"  style="padding-top: 50px;width:150px; border:1px solid blue;margin-left: 20px;float: left;margin-top: 20px;display: block;text-align: center;height: 320px;">
					
					<a href="#" data-theme="e" data-role="button" class="add_new_spotlight" original-title="Add New Story">Add New Story</a>
				</div>
				
				<?php
					$count=0;
					foreach ( $results as $result){

						$categories=$result->categories;
						//print_r($categories);
						$categories=strtolower($categories);

						$categories_str=strtolower(trs_xprofile_get_meta( get_current_user_id(),'3', 'data', 'categories' ));
						$exist='';
						if($categories_str!=""){
							$placeTypes=explode("_",$categories_str);
						}else{
							$exist="all";
						}
						if(count($placeTypes)==0){
							$exist="all";
							
						}else{
							foreach ($placeTypes as $type){
								$strpos=strpos($categories,$type);

								if($strpos === false){
									$exist="false";
								}
								else{
									$exist='true';		
									break;
								}

							}
						}
						?>
						
						<?php 
						if($count<9){

						if($exist=="all" || $exist=="true"){ ?>
						<div  id="stores_<?php echo $result->id;?>" class="stores_div" style="width:150px; border:1px solid blue;margin-left: 20px;float: left;margin-top: 20px;display: block;height: 400px;">
							<div id="story-contour-image">
							<a href="<?php echo $result->primary_link; ?>"><?php echo trs_core_fetch_portrait( array( 'item_id' => $result->user_id, 'type' => 'thumb', 'width' => 20, 'height' => 20, 'html' => true, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ;
							?><p style="text-align: center;"><?php 
							$user_data=get_userdata( $result->user_id );
							$display_name=$user_data->display_name;
							echo '@'.$display_name;
							?></p></a>
							</div>
							<div id="spot-light-content">

								<label><b>Location:</b></label>
								<p style="margin: 0px"><?php 
									$real_location=json_decode($result->location);
								echo $real_location->address;?></p>
								<label><b>Categories:</b></label>
								<p style="margin: 0px"><?php 
									echo str_replace("_", ",", $result->categories);
								 ?></p>	
								<?php
									if($result->type=="post_media"){
										$temp_str=$result->content;
										$exist_url_temp=strpos($temp_str,"[med_images]");
										//echo substr($temp_str,13);
										$domain=get_site_url();
										$exist_url=explode("[/med_images]",substr($temp_str,13));
										
										//echo $exist_url[0];
										//echo $exist_url[1];
										?>
										<label><b>Content:</b></label>
										<p style="margin: 0px"><?php echo $exist_url[1];?></p>
										<img style="width:150px;" src="<?php echo $domain;?>/trm-src/uploads/med/<?php echo $exist_url[0];?>"/>
										<?php
										
									}else{ ?>
										<label><b>Content:</b></label>
										<p style="margin: 0px"><?php echo $result->content;?></p>
											
									<?php }
								?>
							</div>
							

						</div>
						<?php
							$count++;
							}
						}
					}
				?>
		<?php do_action( 'trs_after_blog_page' ) ?>
		
		</div><!-- .dimension -->
		<?php if($count>=9){
		?>
		<div style="margin: 0 auto;text-align: center;">
			<button id='load_more' style="margin-top:50px;" >Load More</button>
		</div>
		<?php
		}
		?>
	</div><!-- #skeleton -->
<style>
#story-contour-image img{
	position: relative;
	background: #fff;
	margin-left:33px;
	border-radius: 49%;
	width: 70px;
	height: 70px;
	border: 2px solid #e4e4e4;
	padding: 4px;
}
</style>
<script>
	/* Load More */
    jq("button#load_more").bind('click', function() {
        var div_list = jq("#stories").find(".stores_div").map(function() {
                return jq(this).html();
            });
        var cat=jq("#activate_tagator3").val();
    	var rad=jq("#radius").val();
    	
    	var categories=[];
        var test_list = jq("#tagator_activate_tagator3 .tagator_tags").find(".tagator_tag").map(function() {
            return jq(this).html();
        });

        for (i=0;i<test_list.length;i++){
            var inx=test_list[i].indexOf("<");
            categories[i]=test_list[i].substr(0,inx);
        }
        if(categories.length>0){
        	categories_str=categories.join("_");
        }else{
        	categories_str="";
        }
        jq("button#load_more").css("display","inline");
        jq.post(ajaxurl, {
                action: "load_more_contents", 
                'index' : div_list.length,
                'radius':rad,
    			'categories_str':categories_str
            }, function (response) {
              jq("#stories").append(response);
              var div_list1 = jq("#stories").find(".stores_div").map(function() {
                return jq(this).html();
            	});
              if(div_list1.length-1==jq("#total_count").val()){
              	jq("button#load_more").css("display","none");
              }
           });
        });
    jq("button#submit_filter").bind('click',function(){
    	var cat=jq("#activate_tagator3").val();
    	var rad=jq("#radius").val();
    	
    	var categories=[];
        var test_list = $("#tagator_activate_tagator3 .tagator_tags").find(".tagator_tag").map(function() {
            return jq(this).html();
        });

        for (i=0;i<test_list.length;i++){
            var inx=test_list[i].indexOf("<");
            categories[i]=test_list[i].substr(0,inx);
        }
        categories_str=categories.join("_");
        jq("button#load_more").css("display","inline");

    	jq.post(ajaxurl,{
    		action:"search_filter",
    		'radius':rad,
    		'categories_str':categories_str
    	},function(response){
    		jq("#stories").html("");
    		jq("#stories").append(response);
    		var div_list1 = jq("#stories").find(".stores_div").map(function() {
                return jq(this).html();
            	});
              if(div_list1.length-1==jq("#total_count").val()){
              	jq("button#load_more").css("display","none");
              }
    	})
    });
</script>
		   


<?php get_footer( 'trendr' ); ?>

