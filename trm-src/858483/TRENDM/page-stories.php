
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
			$lati = trs_xprofile_get_meta(get_current_user_id(), 5, 'data', 'latitude');
			$longi = trs_xprofile_get_meta(get_current_user_id(), 6, 'data', 'longitude');
			$users = get_users( array( 'fields' => array( 'ID' ) ) );		
			$lat1=$my_address_meta['lat'];
			$long1=$my_address_meta['long'];
		//	echo("<script>console.log('Lat: " . $lat1.'Long: '.$long1 . "');</script>");
			$user_index=0;
			
			global $trmdb;
			//$Max_dist = 1000; 
			$my_user_id=get_current_user_id();
			$user_ids=join(",",$users_nearby);
			$user_loc = round($lati) * 1000 + round($longi); 
			echo("<script>console.log('php: " . $user_loc.'Long: '.$long1 . "');</script>");

			$u1 = 35;
			$u2 = 38;
			$querystr = "
			    SELECT * FROM {$trmdb->prefix}trs_stories
			    WHERE {$trmdb->prefix}trs_stories.user_id != $my_user_id AND {$trmdb->prefix}trs_stories.Lat_Long_fk = $user_loc
			    ORDER BY {$trmdb->prefix}trs_stories.date_recorded Desc  
			 ";
			 $results=$trmdb->get_results($querystr, OBJECT);
			 $max_dist = trs_xprofile_get_meta( get_current_user_id(),4, 'data', 'radius' );
			 if($max_dist + 1 == 1){
				 $max_dist = 100;
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
					if (empty($cates)){
						$cates = "Resturant";
					}
					echo $cates;
						
				?>" style="width:350px !important;" data-tagator-show-all-options-on-focus="true" data-tagator-autocomplete="['Resturant', 'Clubs', 'Bar']">
				<label style="margin-left: 30px;">Radius(miles):&nbsp;</label>
				<input type="number" id="radius" step="1" min="0"  value="<?php echo $max_dist?>" />
				<input type="hidden" id="latitude" />
	 			<input type="hidden" id="longitude" />
				<button id="submit_filter">Search Stories</button>
				
			</div>
			<div class="stories" id="stories" style="">	
			
				<div class="add_new_stories stores_div"  style="">
					
					<a href="#" data-theme="e" data-role="button" class="add_new_spotlight" original-title="Add New Story">Add New Story</a>
				</div>
				
				<?php
					
					echo("<script>console.log('PHP: " . $lati . "');</script>");
					
					
											
					foreach ( $results as $result){
						$lat = $result->Latitude;
						//$lat = floatval($lat);
						$long = $result->Longitude;
						//$long = floatval($long);
							//echo $u1. ' ' .$u2. ' ';
							
						$u1 = $lati;
						$u2 = $longi;
						$theta = $long - $u2;
						$miles = (sin(deg2rad($lat)) * sin(deg2rad($u1))) + (cos(deg2rad($lat)) * cos(deg2rad($u1)) * cos(deg2rad($theta)));
						$miles = acos($miles);
						$miles = rad2deg($miles);
						$miles = $miles * 60 * 1.1515;
						$kilometers = $miles * 1.609344;
						
						if($miles > $max_dist){
							continue;
						}
						
						$categories=$result->categories;
						//print_r($categories);
						$categories=strtolower($categories);
						//echo("<script>console.log('PHP: " . $categories . "');</script>");
						$categories_str=strtolower(trs_xprofile_get_meta( get_current_user_id(),'3', 'data', 'categories'));
					//	echo("<script>console.log('PHP: " . $categories_str . "');</script>");
						
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
						<div  id="stores_<?php echo $result->id;?>" class="stores_div" style="width:90%; height:auto;display: inline-block;border:1px solid #999;padding:5px;margin-bottom: 5px">
							<div id="story-contour-image">
							<a href="<?php echo $result->primary_link; ?>"><?php echo trs_core_fetch_portrait( array( 'item_id' => $result->user_id, 'type' => 'full', 'width' => 20, 'height' => 20, 'html' => true, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ;
							
							
							?>
							
							
							
							<p style="text-align: center;"><?php 
								
							
							
							
						//	echo $lat;
					//		echo ' '.$long;
							
							
							
							
							$output = print_r($results,1);
						//	echo "<script>console.log('" . json_encode($result) . "');</script>";
							$user_data=get_userdata( $result->user_id );
							$display_name=$user_data->display_name;
							echo '@'.$display_name;
							echo "  ".round($miles, 2)." Miles Away";
							?></p></a>
							</div>
							<div id="spot-light-content">

								<label><b>Location:</b></label>
								<p style="margin: 0px"><?php echo $result->location;?></p>
								<label><b>Categories:</b></label>
								<p style="margin: 0px"><?php 
									echo str_replace("_", ",", $result->categories);
								 ?></p>	
								<?php
									if($result->type=="post_media"){
										$temp_str=$result->content;
										$exist_url_temp=strpos($temp_str,"[med_images]");
										//echo substr($temp_str,13);
										$exist_url=explode("[/med_images]",substr($temp_str,13));
										
										//echo $exist_url[0];
										//echo $exist_url[1];
										?>
										<label><b>Content:</b></label>
										<p style="margin: 0px"><?php echo $exist_url[1];?></p>
										<img style="width:90%;" src="<?php echo $domain;?>/trm-src/uploads/med/<?php echo $exist_url[0];?>"/>
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

</style>
<script>


function showPosition1(position) {
  //x.innerHTML = "Latitude: " + position.coords.latitude + 
  //"<br>Longitude: " + position.coords.longitude; 
  var lata = position.coords.latitude;
  var longa = position.coords.longitude;
  document.getElementById("latitude").value = lata;
  document.getElementById("longitude").value = longa;
  console.log('Lat 1', lata);
  console.log('Long 1', longa);
}
function getLocation1() {
  //x.innerHTML = "Geolocation is not supported by this browser.";
  //var x = document.getElementById("demo");
  if (navigator.geolocation) {
   navigator.geolocation.getCurrentPosition(position => {
  lata = position.coords.latitude;
  longa = position.coords.longitude;
  document.getElementById("latitude").value = lata;
  document.getElementById("longitude").value = longa;
  console.log('Lat 1', lata);
  console.log('Long 1', longa);
})
  }
	
  else { 
    console.log("geoloation error");
  }
}





	/* Load More */
    jq("button#load_more").bind('click', function() {
		
        var div_list = jq("#stories").find(".stores_div").map(function() {
                return jq(this).html();
            });
        var cat=jq("#activate_tagator3").val();
    	var rad=jq("#radius").val();
    	
    	var categories=[];
        var test_list = $("#tagator_activate_tagator3 .tagator_tags").find(".tagator_tag").map(function() {
            return $(this).html();
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
    			'categories_str':categories_str,
				'latitude':lat,
				'longitude':longi
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
		getLocation1()
		var lat = document.getElementById("latitude").value;
		var longi = document.getElementById("longitude").value
		console.log('Lat',lat);
		console.log('Long', longi);
    	var cat=jq("#activate_tagator3").val();
    	var rad=jq("#radius").val();
    	
    	var categories=[];
        var test_list = $("#tagator_activate_tagator3 .tagator_tags").find(".tagator_tag").map(function() {
            return $(this).html();
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
    		'categories_str':categories_str,
			'latitude':lat,
			'longitude':longi
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
		 
		 window.location.href=window.location.href;
		 setTimeout(window.location.reload(), 150);
		//window.location.reload();	
		//window.location.reload();	

    });
	
	
	


getLocation1();

window.onload = function() {
	
	var lat = document.getElementById("latitude").value;
	var longi = document.getElementById("longitude").value
	console.log("lat 2", lat);
	console.log("long 2", longi);
	jq.post(ajaxurl,{
    		action:"search_filter",
    	//	'radius':rad,
    	//	'categories_str':categories_str,ff
			'latitude':lat,
			'longitude':longi
    	});
}






</script>
		   


<?php get_footer( 'trendr' ); ?>

