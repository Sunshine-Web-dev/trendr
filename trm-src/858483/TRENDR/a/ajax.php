<?php
/***
 * AJAX Functions
 *
 * All of these functions enhance the responsiveness of the user interface in the default
 * theme by adding AJAX functionality.
 */

/***
 * This function looks scarier than it actually is. :)
 * Each object loop (activity/members/groups/blogs/forums) contains default parameters to
 * show specific information based on the page we are currently looking at.
 * The following function will take into account any cookies set in the JS and allow us
 * to override the parameters sent. That way we can change the results returned without reloading the page.
 * By using cookies we can also make sure that user settings are retained across page loads.
 */
function trendr_ajax_call( $query_string, $object ) {
	global $trs;

	if ( empty( $object ) )
		return false;

	/* Set up the cookies passed on this AJAX request. Store a local var to avoid conflicts */
	if ( !empty( $_POST['cookie'] ) )
		$_TRS_COOKIE = trm_parse_args( str_replace( '; ', '&', urldecode( $_POST['cookie'] ) ) );
	else
		$_TRS_COOKIE = &$_COOKIE;

	$qs = false;

	/***
	 * Check if any cookie values are set. If there are then override the default params passed to the
	 * template loop
	 https://css-tricks.com/snippets/javascript/get-url-and-url-parts-in-javascript/
	 
	 */


	 //asamir hanlde IE
	 if(isset($_SERVER['REDIRECT_URL'])){

		 		if(isset($_TRS_COOKIE['trs-' . $object . '-filter'.'-'.$_SERVER['REDIRECT_URL']]))
					$_TRS_COOKIE['trs-' . $object . '-filter'] = $_TRS_COOKIE['trs-' . $object . '-filter'.'-'.$_SERVER['REDIRECT_URL']];


 				if(isset($_TRS_COOKIE['trs-' . $object . '-filter-checkbox'.'-'.$_SERVER['REDIRECT_URL']]))
 					 $_TRS_COOKIE['trs-' . $object . '-filter-checkbox'] = $_TRS_COOKIE['trs-' . $object . '-filter-checkbox'.'-'.$_SERVER['REDIRECT_URL']];


				if(isset($_TRS_COOKIE['trs-' . $object . '-scope'.'-'.$_SERVER['REDIRECT_URL']]))
 					 	$_TRS_COOKIE['trs-' . $object . '-scope'] = $_TRS_COOKIE['trs-' . $object . '-scope'.'-'.$_SERVER['REDIRECT_URL']];

 	 }


	if ( !empty( $_TRS_COOKIE['trs-' . $object . '-filter'] ) && '-1' != $_TRS_COOKIE['trs-' . $object . '-filter'] ) {
		$qs[] = 'type=' . $_TRS_COOKIE['trs-' . $object . '-filter'];
		$qs[] = 'action=' . $_TRS_COOKIE['trs-' . $object . '-filter']; // Activity stream filtering on action
	}
//+asamir added to hanlde the checkbox search

if ( !empty( $_TRS_COOKIE['trs-' . $object . '-filter-checkbox'] )
		&& '-1' != $_TRS_COOKIE['trs-' . $object . '-filter-checkbox'] ) {
		$_TRS_COOKIE['trs-' . $object . '-filter-checkbox'] = str_replace("__",",",$_TRS_COOKIE['trs-' . $object . '-filter-checkbox']);


		$qs[] = 'type=' . $_TRS_COOKIE['trs-' . $object . '-filter-checkbox'];
		$qs[] = 'action=' . $_TRS_COOKIE['trs-' . $object . '-filter-checkbox']; // Activity stream filtering on action
	}

	if ( !empty( $_TRS_COOKIE['trs-' . $object . '-scope'] ) ) {
		if ( 'personal' == $_TRS_COOKIE['trs-' . $object . '-scope'] ) {
			$user_id = ( $trs->displayed_user->id ) ? $trs->displayed_user->id : $trs->loggedin_user->id;
			$qs[] = 'user_id=' . $user_id;
		}
		if ( 'all' != $_TRS_COOKIE['trs-' . $object . '-scope'] && empty( $trs->displayed_user->id ) && !$trs->is_single_item )
			$qs[] = 'scope=' . $_TRS_COOKIE['trs-' . $object . '-scope']; // Activity stream scope only on activity directory.
	}

	/* If page and search_terms have been passed via the AJAX post request, use those */
	if ( !empty( $_POST['page'] ) && '-1' != $_POST['page'] )
		$qs[] = 'page=' . $_POST['page'];

	$object_search_text = trs_get_search_default_text( $object );
 	if ( !empty( $_POST['search_terms'] ) && $object_search_text != $_POST['search_terms'] && 'false' != $_POST['search_terms'] && 'undefined' != $_POST['search_terms'] )
		$qs[] = 'search_terms=' . $_POST['search_terms'];

	/* Now pass the querystring to override default values. */
	$query_string = empty( $qs ) ? '' : join( '&', (array)$qs );

	$object_filter = '';
	if ( isset( $_TRS_COOKIE['trs-' . $object . '-filter'] ) )
		$object_filter = $_TRS_COOKIE['trs-' . $object . '-filter'];

	$object_scope = '';
	if ( isset( $_TRS_COOKIE['trs-' . $object . '-scope'] ) )
		$object_scope = $_TRS_COOKIE['trs-' . $object . '-scope'];

	$object_page = '';
	if ( isset( $_TRS_COOKIE['trs-' . $object . '-page'] ) )
		$object_page = $_TRS_COOKIE['trs-' . $object . '-page'];

	$object_search_terms = '';
	if ( isset( $_TRS_COOKIE['trs-' . $object . '-search-terms'] ) )
		$object_search_terms = $_TRS_COOKIE['trs-' . $object . '-search-terms'];

	$object_extras = '';
	if ( isset( $_TRS_COOKIE['trs-' . $object . '-extras'] ) )
		$object_extras = $_TRS_COOKIE['trs-' . $object . '-extras'];

	return apply_filters( 'trendr_ajax_call', $query_string, $object, $object_filter, $object_scope, $object_page, $object_search_terms, $object_extras );
}
add_filter( 'trs_ajax_querystring', 'trendr_ajax_call', 10, 2 );

/* This function will simply load the template loop for the current object. On an AJAX request */
function trs_dtheme_object_template_loader() {

 	/**
	 * AJAX requests happen too early to be seen by trs_update_is_directory()
	 * so we do it manually here to ensure templates load with the correct
	 * context. Without this check, templates will load the 'single' version
	 * of themselves rather than the directory version.
	 */
	if ( !trs_current_action() )
		trs_update_is_directory( true, trs_current_component() );

	// Sanitize the post object
	$object = esc_attr( $_POST['object'] );

	// Locate the object template
	locate_template( array( "$object/$object-loop.php" ), true );
}
add_action( 'trm_ajax_members_filter', 'trs_dtheme_object_template_loader' );
add_action( 'trm_ajax_groups_filter',  'trs_dtheme_object_template_loader' );
add_action( 'trm_ajax_blogs_filter',   'trs_dtheme_object_template_loader' );
add_action( 'trm_ajax_forums_filter',  'trs_dtheme_object_template_loader' );

// This function will load the activity loop template when activity is requested via AJAX
function trs_dtheme_activity_template_loader() {
	global $trs;
	$scope = '';
	if ( !empty( $_POST['scope'] ) )
		$scope = $_POST['scope'];

	// We need to calculate and return the feed URL for each scope
	switch ( $scope ) {
		case 'friends':
			$feed_url = $trs->loggedin_user->domain . trs_get_activity_slug() . '/friends/feed/';
			break;
		case 'groups':
			$feed_url = $trs->loggedin_user->domain . trs_get_activity_slug() . '/groups/feed/';
			break;
		case 'favorites':
			$feed_url = $trs->loggedin_user->domain . trs_get_activity_slug() . '/favorites/feed/';
			break;
		case 'mentions':
			$feed_url = $trs->loggedin_user->domain . trs_get_activity_slug() . '/mentions/feed/';
			trs_activity_clear_new_mentions( $trs->loggedin_user->id );
			break;
		default:
			$feed_url = home_url( trs_get_activity_root_slug() . '/feed/' );
			break;
	}

	/* Buffer the loop in the template to a var for JS to spit out. */
	ob_start();
	locate_template( array( 'activity/activity-loop.php' ), true ); 
	$result['contents'] = ob_get_contents();
	$result['feed_url'] = apply_filters( 'trs_dtheme_activity_feed_url', $feed_url, $scope );
	ob_end_clean();
	
	echo json_encode( $result );
}
add_action( 'trm_ajax_activity_widget_filter', 'trs_dtheme_activity_template_loader' );
add_action( 'trm_ajax_activity_get_older_updates', 'trs_dtheme_activity_template_loader' );

/* AJAX update posting */
function trs_dtheme_post_update() {
	global $trs,$trmdb;

	// Check the nonce
	check_admin_referer( 'post_update', '_key_post_update' );

	if ( !is_user_logged_in() ) {
		echo '-1';
		return false;
	}

	if ( empty( $_POST['content'] ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'You have not entered any content to share!', 'trendr' ) . '</p></div>';
		return false;
	}
	if($_POST['story']=="true"){
		

		$trmdb->insert( 'trm_trs_stories', array("user_id" => $trs->loggedin_user->id, "content" => $_POST['content'],"location"=>$_POST["location_address"] ,"date_recorded"=>trs_core_current_time(),"primary_link"=>trs_core_get_user_domain( $trs->loggedin_user->id ),"type"=>"post_text","categories"=>$_POST["categories"]));

	}else{
		$activity_id = 0;
		if ( empty( $_POST['object'] ) && trs_is_active( 'activity' ) ) {
			$activity_id = trs_activity_post_update( array( 'content' => $_POST['content'] ) );

		} elseif ( $_POST['object'] == 'groups' ) {
			if ( !empty( $_POST['item_id'] ) && trs_is_active( 'groups' ) )
				$activity_id = groups_post_update( array( 'content' => $_POST['content'], 'group_id' => $_POST['item_id'] ) );

		} else {
			$activity_id = apply_filters( 'trs_activity_custom_update', $_POST['object'], $_POST['item_id'], $_POST['content'] );
		}

		if ( empty( $activity_id ) ) {
			echo '-1<div id="message" class="error"><p>' . __( 'There was a problem posting your update, please try again.', 'trendr' ) . '</p></div>';
			return false;
		}
		if ( trs_has_activities ( 'include=' . $activity_id ) ) : ?>
			<?php while ( trs_activities() ) : trs_the_activity(); ?>
				<?php locate_template( array( 'activity/entry.php' ), true ) ?>
			<?php endwhile; ?>
		<?php endif;
	}
	
}
add_action( 'trm_ajax_post_update', 'trs_dtheme_post_update' );

function trs_stories_load_more(){
	function getDistanceBetweenPoints($lat1, $lon1, $lat2, $lon2) {
	    $theta = $lon1 - $lon2;
	    $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
	    $miles = acos($miles);
	    $miles = rad2deg($miles);
	    $miles = $miles * 60 * 1.1515;
	    $kilometers = $miles * 1.609344;
	    return $kilometers;
	}

	global $trmdb,$trs;
	$users = get_users( array( 'fields' => array( 'ID' ) ) );
	//print_r($users);
	$users_nearby=[];
	$trschk_location_id = xprofile_get_field_id_from_name( 'Location' );
	$my_address_meta=trs_xprofile_get_meta( get_current_user_id(),$trschk_location_id, 'data', 'location' );
	//$trschk=trs_get_option('trschk_general_settings');
	$lat1=$my_address_meta['lat'];
	$long1=$my_address_meta['long'];
	$user_index=0;
	foreach ($users as $user){
		if($user->ID != get_current_user_id()){
			$loc_xprof_meta = trs_xprofile_get_meta( $user->ID,$trschk_location_id,'data','location');
			if($loc_xprof_meta){
				$lat2=$loc_xprof_meta['lat'];
				$long2=$loc_xprof_meta['long'];
				$range=getDistanceBetweenPoints($lat1,$long1,$lat2,$long2);
				if($range<=$_POST['radius']){
					$users_nearby[$user_index]=$user->ID;
					$user_index++;
				}
				//print_r($users_nearby);
			}
		}
	}
	
	
	$user_ids=join(",",$users_nearby);
	$querystr = "
	    SELECT * FROM {$trmdb->prefix}trs_stories
	    WHERE {$trmdb->prefix}trs_stories.user_id IN ($user_ids)
	    ORDER BY {$trmdb->prefix}trs_stories.date_recorded Desc  
	 ";
	 
	$results=$trmdb->get_results($querystr, OBJECT);
	$real_result=[];
	$real_index=0;

	foreach ( $results as $result){

		$categories=$result->categories;
		//print_r($categories);
		//$trschk=trs_get_option('trschk_general_settings');
		$categories=strtolower($categories);
		$categories_str=strtolower($_POST['categories_str']);
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
		if($exist=='all' || $exist=='true'){
			$real_result[$real_index]=$result;
			$real_index++;
		}
		
	}
	$showed_count=$_POST['index']-1+5;
	$real_showed_count=0;
	if($showed_count<count($real_result)){
		$real_showed_count=$showed_count;
	}
	else{
		$real_showed_count=count($real_result);
	}
	for($i=$_POST['index']-1;$i<$real_showed_count;$i++){
			?>
			<div  id="stores_<?php echo $real_result[$i]->id;?>" class="stores_div" style="width:150px; border:1px solid blue;margin-left: 20px;float: left;margin-top: 20px;display: block;height: 400px;">
							<div id="story-contour-image">
							<a href="<?php echo $real_result[$i]->primary_link; ?>"><?php echo trs_core_fetch_portrait( array( 'item_id' => $real_result[$i]->user_id, 'type' => 'thumb', 'width' => 20, 'height' => 20, 'html' => true, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ;
							?><p style="text-align: center;"><?php 
							$user_data=get_userdata( $real_result[$i]->user_id );
							$display_name=$user_data->display_name;
							$domain=get_site_url();
							echo '@'.$display_name;
							?></p></a>
							</div>
							<div id="spot-light-content">

								<label><b>Location:</b></label>
								<p style="margin: 0px"><?php echo $real_result[$i]->location;?></p>
								<label><b>Categories:</b></label>
								<p style="margin: 0px"><?php 
									echo str_replace("_", ",", $real_result[$i]->categories);
								 ?></p>	
								<?php
									if($real_result[$i]->type=="post_media"){
										$temp_str=$real_result[$i]->content;
										$exist_url_temp=strpos($temp_str,"[med_images]");
										//echo substr($temp_str,13);
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
										<p style="margin: 0px"><?php echo $real_result[$i]->content;?></p>
											
									<?php }
								?>
							</div>
						</div>
			<?php
		}
		?>
		<input type="hidden" id="total_count" value="<?php echo count($real_result); ?> "/>
		<?php
		//print_r($real_result);
}
add_action('trm_ajax_load_more_contents', 'trs_stories_load_more');

function trs_stories_my_load_more(){
	
	global $trmdb,$trs;
	
	$user_id=get_current_user_id();

	$querystr = "
	    SELECT * FROM {$trmdb->prefix}trs_stories
	    WHERE {$trmdb->prefix}trs_stories.user_id =$user_id
	    ORDER BY {$trmdb->prefix}trs_stories.date_recorded Desc  
	 ";
	 
	$results=$trmdb->get_results($querystr, OBJECT);
	$real_result=[];
	$real_index=0;

	foreach ( $results as $result){

		$categories=$result->categories;
		//print_r($categories);
		//$trschk=trs_get_option('trschk_general_settings');
		$categories=strtolower($categories);
		$categories_str=strtolower($_POST['categories_str']);
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
		print_r($exist);
		if($exist=='all' || $exist=='true'){
			$real_result[$real_index]=$result;
			$real_index++;
		}
		
	}
	$showed_count=$_POST['index']-1+5;
	$real_showed_count=0;
	if($showed_count<count($real_result)){
		$real_showed_count=$showed_count;
	}
	else{
		$real_showed_count=count($real_result);
	}
	for($i=$_POST['index']-1;$i<$real_showed_count;$i++){
			?>
			<div  id="stores_<?php echo $real_result[$i]->id;?>" class="stores_div" style="width:150px; border:1px solid blue;margin-left: 20px;float: left;margin-top: 20px;display: block;height: 400px;">
							<div id="story-contour-image">
							<a href="<?php echo $real_result[$i]->primary_link; ?>"><?php echo trs_core_fetch_portrait( array( 'item_id' => $real_result[$i]->user_id, 'type' => 'thumb', 'width' => 20, 'height' => 20, 'html' => true, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ;
							?><p style="text-align: center;"><?php 
							$user_data=get_userdata( $real_result[$i]->user_id );
							$display_name=$user_data->display_name;
							$domain=get_site_url();
							echo '@'.$display_name;
							?></p></a>
							</div>
							<div id="spot-light-content">

								<label><b>Location:</b></label>
								<p style="margin: 0px"><?php echo $real_result[$i]->location;?></p>
								<label><b>Categories:</b></label>
								<p style="margin: 0px"><?php 
									echo str_replace("_", ",", $real_result[$i]->categories);
								 ?></p>	
								<?php
									if($real_result[$i]->type=="post_media"){
										$temp_str=$real_result[$i]->content;
										$exist_url_temp=strpos($temp_str,"[med_images]");
										//echo substr($temp_str,13);
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
										<p style="margin: 0px"><?php echo $real_result[$i]->content;?></p>
											
									<?php }
								?>
							</div>
						</div>
			<?php
		}
		?>
		<input type="hidden" id="total_count" value="<?php echo count($real_result); ?> "/>
		<?php
		//print_r($real_result);
}
add_action('trm_ajax_my_load_more_contents', 'trs_stories_my_load_more');

function trs_search_filter(){
	function getDistanceBetweenPoints($lat1, $lon1, $lat2, $lon2) {
	    $theta = $lon1 - $lon2;
	    $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
	    $miles = acos($miles);
	    $miles = rad2deg($miles);
	    $miles = $miles * 60 * 1.1515;
	    $kilometers = $miles * 1.609344;
	    return $kilometers;
	}
	trs_xprofile_update_meta( get_current_user_id(),'3', 'data', 'categories',$_POST['categories_str']);
	trs_xprofile_update_meta( get_current_user_id(),'4', 'data', 'radius',$_POST['radius']);
	global $trmdb,$trs;
	$users = get_users( array( 'fields' => array( 'ID' ) ) );
	//print_r($users);
	$users_nearby=[];
	$trschk_location_id = xprofile_get_field_id_from_name( 'Location' );
	$my_address_meta=trs_xprofile_get_meta( get_current_user_id(),$trschk_location_id, 'data', 'location' );
	//$trschk=trs_get_option('trschk_general_settings');
	$lat1=$my_address_meta['lat'];
	$long1=$my_address_meta['long'];
	$user_index=0;
	foreach ($users as $user){
		if($user->ID != get_current_user_id()){
			$loc_xprof_meta = trs_xprofile_get_meta( $user->ID,$trschk_location_id,'data','location');
			if($loc_xprof_meta){
				$lat2=$loc_xprof_meta['lat'];
				$long2=$loc_xprof_meta['long'];
				$range=getDistanceBetweenPoints($lat1,$long1,$lat2,$long2);
				if($range<=$_POST['radius']){
					$users_nearby[$user_index]=$user->ID;
					$user_index++;
				}
				//print_r($users_nearby);
			}
		}
	}
	
	
	$user_ids=join(",",$users_nearby);
	$querystr = "
	    SELECT * FROM {$trmdb->prefix}trs_stories
	    WHERE {$trmdb->prefix}trs_stories.user_id IN ($user_ids)
	    ORDER BY {$trmdb->prefix}trs_stories.date_recorded Desc  
	 ";
	 
	$results=$trmdb->get_results($querystr, OBJECT);
	$real_result=[];
	$real_index=0;
	foreach ( $results as $result){

		$categories=$result->categories;
		//print_r($categories);
		//$categories_str=$_POST['categories_str'];
		$categories=strtolower($categories);
		$categories_str=strtolower($_POST['categories_str']);
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
		if($exist=='all' || $exist=='true'){
			$real_result[$real_index]=$result;
			$real_index++;
		}
	}
	?>
	<div class="add_new_stories stores_div"  style="padding-top: 50px;width:150px; border:1px solid blue;margin-left: 20px;float: left;margin-top: 20px;display: block;text-align: center;height: 340px;">
		
		<a href="#" data-theme="e" data-role="button" class="add_new_spotlight" original-title="Add New Story">Add New Story</a>
		<script>
			jq('.add_new_spotlight').click(function (e) {
	   // alert("ok");
			        e.preventDefault();
			 jq('#post-intro').fadeToggle(200);
			 jq(".add_new_spotlight").toggleClass('selected');
			 jq(".dim").addClass('selected', 100, "easeOutSine");
			jq('#category').css("display","block");
			jq('#spotlight').prop("checked",true);
			jq('#post-store').css("display","block");
			 jq('.dim').css({    display:'inline', position: 'fixed'});

			 jq('html, body').css({    overflow: 'hidden'});    
			});
		</script>
	</div>
	<?php
	if(count($real_result)<9){
		$showed_count=count($real_result);
	}else{
		$showed_count=9;
	}
	for($i=0;$i<$showed_count;$i++){
			?>
			<div  id="stores_<?php echo $real_result[$i]->id;?>" class="stores_div" style="width:150px; border:1px solid blue;margin-left: 20px;float: left;margin-top: 20px;display: block;height: 400px;">
							<div id="story-contour-image">
							<a href="<?php echo $real_result[$i]->primary_link; ?>"><?php echo trs_core_fetch_portrait( array( 'item_id' => $real_result[$i]->user_id, 'type' => 'thumb', 'width' => 20, 'height' => 20, 'html' => true, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ;
							?><p style="text-align: center;"><?php 
							$user_data=get_userdata( $real_result[$i]->user_id );
							$display_name=$user_data->display_name;
							echo '@'.$display_name;
							?></p></a>
							</div>
							<div id="spot-light-content">

								<label><b>Location:</b></label>
								<p style="margin: 0px"><?php echo $real_result[$i]->location;?></p>
								<label><b>Categories:</b></label>
								<p style="margin: 0px"><?php 
									echo str_replace("_", ",", $real_result[$i]->categories);
								 ?></p>	
								<?php
									if($real_result[$i]->type=="post_media"){
										$temp_str=$real_result[$i]->content;
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
										<p style="margin: 0px"><?php echo $real_result[$i]->content;?></p>
											
									<?php }
								?>
							</div>
						</div>
			<?php
		}
		?>
		<input type="hidden" id="total_count" value="<?php echo count($real_result); ?> "/>
		<?php
		//print_r($real_result);
}
add_action('trm_ajax_search_filter', 'trs_search_filter');

function trs_my_search_filter(){
	
	
	
	global $trmdb,$trs;
	
	trs_xprofile_update_meta( get_current_user_id(),'3', 'data', 'categories',$_POST['categories_str']);
	
	$user_id=get_current_user_id();
	$querystr = "
	    SELECT * FROM {$trmdb->prefix}trs_stories
	    WHERE {$trmdb->prefix}trs_stories.user_id = $user_id
	    ORDER BY {$trmdb->prefix}trs_stories.date_recorded Desc  
	 ";
	 
	$results=$trmdb->get_results($querystr, OBJECT);
	$real_result=[];
	$real_index=0;
	foreach ( $results as $result){

		$categories=$result->categories;
		//print_r($categories);
		//$categories_str=$_POST['categories_str'];
		$categories=strtolower($categories);
		$categories_str=strtolower($_POST['categories_str']);
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
		if($exist=='all' || $exist=='true'){
			$real_result[$real_index]=$result;
			$real_index++;
		}
	}
	?>
	<div class="add_new_stories stores_div"  style="padding-top: 50px;width:150px; border:1px solid blue;margin-left: 20px;float: left;margin-top: 20px;display: block;text-align: center;height: 340px;">
		
		<a href="#" data-theme="e" data-role="button" class="add_new_spotlight" original-title="Add New Story">Add New Story</a>
		<script>
			jq('.add_new_spotlight').click(function (e) {
	   // alert("ok");
			        e.preventDefault();
			 jq('#post-intro').fadeToggle(200);
			 jq(".add_new_spotlight").toggleClass('selected');
			 jq(".dim").addClass('selected', 100, "easeOutSine");
			jq('#category').css("display","block");
			jq('#spotlight').prop("checked",true);
			jq('#post-store').css("display","block");
			 jq('.dim').css({    display:'inline', position: 'fixed'});

			 jq('html, body').css({    overflow: 'hidden'});    
			});
		</script>
	</div>
	<?php
	if(count($real_result)<9){
		$showed_count=count($real_result);
	}else{
		$showed_count=9;
	}
	for($i=0;$i<$showed_count;$i++){
			?>
			<div  id="stores_<?php echo $real_result[$i]->id;?>" class="stores_div" style="width:150px; border:1px solid blue;margin-left: 20px;float: left;margin-top: 20px;display: block;height: 400px;">
							<div id="story-contour-image">
							<a href="<?php echo $real_result[$i]->primary_link; ?>"><?php echo trs_core_fetch_portrait( array( 'item_id' => $real_result[$i]->user_id, 'type' => 'thumb', 'width' => 20, 'height' => 20, 'html' => true, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ;
							?><p style="text-align: center;"><?php 
							$user_data=get_userdata( $real_result[$i]->user_id );
							$display_name=$user_data->display_name;
							echo '@'.$display_name;
							?></p></a>
							</div>
							<div id="spot-light-content">

								<label><b>Location:</b></label>
								<p style="margin: 0px"><?php echo $real_result[$i]->location;?></p>
								<label><b>Categories:</b></label>
								<p style="margin: 0px"><?php 
									echo str_replace("_", ",", $real_result[$i]->categories);
								 ?></p>	
								<?php
									if($real_result[$i]->type=="post_media"){
										$temp_str=$real_result[$i]->content;
										$exist_url_temp=strpos($temp_str,"[med_images]");
										//echo substr($temp_str,13);
										$exist_url=explode("[/med_images]",substr($temp_str,13));
										$domain=get_site_url();
										//echo $exist_url[0];
										//echo $exist_url[1];
										?>
										<label><b>Content:</b></label>
										<p style="margin: 0px"><?php echo $exist_url[1];?></p>
										<img style="width:150px;" src="<?php echo $domain;?>/trm-src/uploads/med/<?php echo $exist_url[0];?>"/>
										<?php
										
									}else{ ?>
										<label><b>Content:</b></label>
										<p style="margin: 0px"><?php echo $real_result[$i]->content;?></p>
											
									<?php }
								?>
							</div>
						</div>
			<?php
		}
		?>
		<input type="hidden" id="total_count" value="<?php echo count($real_result); ?> "/>
		<?php
		//print_r($real_result);
}
add_action('trm_ajax_my_search_filter', 'trs_my_search_filter');
/* AJAX activity comment posting */
function trs_dtheme_new_activity_comment() {
	global $trs;

	// Check the nonce
	check_admin_referer( 'new_activity_comment', '_key_new_activity_comment' );

	if ( !is_user_logged_in() ) {
		echo '-1';
		return false;
	}

	if ( empty( $_POST['content'] ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'Please do not leave the comment area blank.', 'trendr' ) . '</p></div>';
		return false;
	}

	if ( empty( $_POST['form_id'] ) || empty( $_POST['comment_id'] ) || !is_numeric( $_POST['form_id'] ) || !is_numeric( $_POST['comment_id'] ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'There was an error posting that reply, please try again.', 'trendr' ) . '</p></div>';
		return false;
	}

	$comment_id = trs_activity_new_comment( array(
		'activity_id' => $_POST['form_id'],
		'content'     => $_POST['content'],
		'parent_id'   => $_POST['comment_id']
	) );

	if ( !$comment_id ) {
		echo '-1<div id="message" class="error"><p>' . __( 'There was an error posting that reply, please try again.', 'trendr' ) . '</p></div>';
		return false;
	}

	global $activities_template;

	// Load the new activity item into the $activities_template global
	trs_has_activities( 'display_comments=stream&include=' . $comment_id );

	// Swap the current comment with the activity item we just loaded
	$activities_template->activity->id              = $activities_template->activities[0]->item_id;
	$activities_template->activity->current_comment = $activities_template->activities[0];

	$template = locate_template( 'activity/comment.php', false, false );

	// Backward compatibility. In older versions of TRS, the markup was
	// generated in the PHP instead of a template. This ensures that
	// older themes (which are not children of trs-default and won't
	// have the new template) will still work.
	if ( empty( $template ) )
		$template = TRS_PLUGIN_DIR . '/trs-themes/trs-default/activity/comment.php';

	load_template( $template, false );

	unset( $activities_template );
}
add_action( 'trm_ajax_new_activity_comment', 'trs_dtheme_new_activity_comment' );

/* AJAX delete an activity */
function trs_dtheme_delete_activity() {
	global $trs;

	// Check the nonce
	check_admin_referer( 'trs_activity_delete_link' );

	if ( !is_user_logged_in() ) {
		echo '-1';
		return false;
	}

	if ( empty( $_POST['id'] ) || !is_numeric( $_POST['id'] ) ) {
		echo '-1';
		return false;
	}

	$activity = new TRS_Activity_Activity( (int) $_POST['id'] );

	// Check access
	if ( empty( $activity->user_id ) || !trs_activity_user_can_delete( $activity ) ) {
		echo '-1';
		return false;
	}

	// Call the action before the delete so plugins can still fetch information about it
	do_action( 'trs_activity_before_action_delete_activity', $activity->id, $activity->user_id );

	if ( !trs_activity_delete( array( 'id' => $activity->id, 'user_id' => $activity->user_id ) ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'There was a problem when deleting. Please try again.', 'trendr' ) . '</p></div>';
		return false;
	}

	do_action( 'trs_activity_action_delete_activity', $activity->id, $activity->user_id );

	return true;
}
add_action( 'trm_ajax_delete_activity', 'trs_dtheme_delete_activity' );

/* AJAX delete an activity comment */
function trs_dtheme_delete_activity_comment() {
	global $trs;

	/* Check the nonce */
	check_admin_referer( 'trs_activity_delete_link' );

	if ( !is_user_logged_in() ) {
		echo '-1';
		return false;
	}

	$comment = new TRS_Activity_Activity( $_POST['id'] );

	/* Check access */
	if ( !is_super_admin() && $comment->user_id != $trs->loggedin_user->id )
		return false;

	if ( empty( $_POST['id'] ) || !is_numeric( $_POST['id'] ) )
		return false;

	/* Call the action before the delete so plugins can still fetch information about it */
	do_action( 'trs_activity_before_action_delete_activity', $_POST['id'], $comment->user_id );

	if ( !trs_activity_delete_comment( $comment->item_id, $comment->id ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'There was a problem when deleting. Please try again.', 'trendr' ) . '</p></div>';
		return false;
	}

	do_action( 'trs_activity_action_delete_activity', $_POST['id'], $comment->user_id );

	return true;
}
add_action( 'trm_ajax_delete_activity_comment', 'trs_dtheme_delete_activity_comment' );

/* AJAX mark an activity as a favorite */
function trs_dtheme_mark_activity_favorite() {
	global $trs;

	trs_activity_add_user_favorite( $_POST['id'] );
	_e( 'unwatch', 'trendr' );
}
add_action( 'trm_ajax_activity_mark_fav', 'trs_dtheme_mark_activity_favorite' );

/* AJAX mark an activity as not a favorite */
function trs_dtheme_unmark_activity_favorite() {
	global $trs;

	trs_activity_remove_user_favorite( $_POST['id'] );
	_e( 'watch later', 'trendr' );
}
add_action( 'trm_ajax_activity_mark_unfav', 'trs_dtheme_unmark_activity_favorite' );

/**
 * AJAX handler for Read More link on long activity items
 *
 * @package BuddyPress
 * @since 1.5
 */
function trs_dtheme_get_single_activity_content() {
	$activity_array = trs_activity_get_specific( array(
		'activity_ids'     => $_POST['activity_id'],
		'display_comments' => 'stream'
	) );

	$activity = !empty( $activity_array['activities'][0] ) ? $activity_array['activities'][0] : false;

	if ( !$activity )
		exit(); // todo: error?

	do_action_ref_array( 'trs_dtheme_get_single_activity_content', array( &$activity ) );

	// Activity content retrieved through AJAX should run through normal filters, but not be truncated
	remove_filter( 'trs_get_activity_content_body', 'trs_activity_truncate_entry', 5 );
	$content = apply_filters( 'trs_get_activity_content_body', $activity->content );

	echo $content;
	exit();
}
add_action( 'trm_ajax_get_single_activity_content', 'trs_dtheme_get_single_activity_content' );

/* AJAX invite a friend to a group functionality */
function trs_dtheme_ajax_invite_user() {
	global $trs;

	check_ajax_referer( 'groups_invite_uninvite_user' );

	if ( !$_POST['friend_id'] || !$_POST['friend_action'] || !$_POST['group_id'] )
		return false;

	if ( !trs_groups_user_can_send_invites( $_POST['group_id'] ) )
		return false;

	if ( !friends_check_friendship( $trs->loggedin_user->id, $_POST['friend_id'] ) )
		return false;

	if ( 'invite' == $_POST['friend_action'] ) {

		if ( !groups_invite_user( array( 'user_id' => $_POST['friend_id'], 'group_id' => $_POST['group_id'] ) ) )
			return false;

		$user = new TRS_Core_User( $_POST['friend_id'] );

		echo '<li id="uid-' . $user->id . '">';
		echo $user->portrait_thumb;
		echo '<h4>' . $user->user_link . '</h4>';
		echo '<span class="activity">' . esc_attr( $user->last_active ) . '</span>';
		echo '<div class="action">
				<a class="button remove" href="' . trm_nonce_url( $trs->loggedin_user->domain . trs_get_groups_slug() . '/' . $_POST['group_id'] . '/invites/remove/' . $user->id, 'groups_invite_uninvite_user' ) . '" id="uid-' . esc_attr( $user->id ) . '">' . __( 'Remove Invite', 'trendr' ) . '</a>
			  </div>';
		echo '</li>';

	} else if ( 'uninvite' == $_POST['friend_action'] ) {

		if ( !groups_uninvite_user( $_POST['friend_id'], $_POST['group_id'] ) )
			return false;

		return true;

	} else {
		return false;
	}
}
add_action( 'trm_ajax_groups_invite_user', 'trs_dtheme_ajax_invite_user' );

/* AJAX add/remove a user as a friend when clicking the button */
function trs_dtheme_ajax_addremove_friend() {
	global $trs;

	if ( 'is_friend' == TRS_Friends_Friendship::check_is_friend( $trs->loggedin_user->id, $_POST['fid'] ) ) {

		check_ajax_referer('friends_remove_friend');

		if ( !friends_remove_friend( $trs->loggedin_user->id, $_POST['fid'] ) ) {
			echo __("Friendship could not be canceled.", 'trendr');
		} else {
			echo '<a id="friend-' . $_POST['fid'] . '" class="add" rel="add" title="' . __( 'Add Friend', 'trendr' ) . '" href="' . trm_nonce_url( $trs->loggedin_user->domain . trs_get_friends_slug() . '/add-friend/' . $_POST['fid'], 'friends_add_friend' ) . '">' . __( 'Add Friend', 'trendr' ) . '</a>';
		}

	} else if ( 'not_friends' == TRS_Friends_Friendship::check_is_friend( $trs->loggedin_user->id, $_POST['fid'] ) ) {

		check_ajax_referer('friends_add_friend');

		if ( !friends_add_friend( $trs->loggedin_user->id, $_POST['fid'] ) ) {
			echo __("Friendship could not be requested.", 'trendr');
		} else {
			echo '<a href="' . $trs->loggedin_user->domain . trs_get_friends_slug() . '/requests" class="requested">' . __( 'Friendship Requested', 'trendr' ) . '</a>';
		}
	} else {
		echo __( 'Request Pending', 'trendr' );
	}

	return false;
}
add_action( 'trm_ajax_addremove_friend', 'trs_dtheme_ajax_addremove_friend' );

/* AJAX accept a user as a friend when clicking the "accept" button */
function trs_dtheme_ajax_accept_friendship() {
	check_admin_referer( 'friends_accept_friendship' );

	if ( !friends_accept_friendship( $_POST['id'] ) )
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem accepting that request. Please try again.', 'trendr' ) . '</p></div>';

	return true;
}
add_action( 'trm_ajax_accept_friendship', 'trs_dtheme_ajax_accept_friendship' );

/* AJAX reject a user as a friend when clicking the "reject" button */
function trs_dtheme_ajax_reject_friendship() {
	check_admin_referer( 'friends_reject_friendship' );

	if ( !friends_reject_friendship( $_POST['id'] ) )
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem rejecting that request. Please try again.', 'trendr' ) . '</p></div>';

	return true;
}
add_action( 'trm_ajax_reject_friendship', 'trs_dtheme_ajax_reject_friendship' );

/* AJAX join or leave a group when clicking the "join/leave" button */
function trs_dtheme_ajax_joinleave_group() {
	global $trs;

	if ( groups_is_user_banned( $trs->loggedin_user->id, $_POST['gid'] ) )
		return false;

	if ( !$group = new TRS_Groups_Group( $_POST['gid'], false, false ) )
		return false;

	if ( !groups_is_user_member( $trs->loggedin_user->id, $group->id ) ) {

		if ( 'public' == $group->status ) {

			check_ajax_referer( 'groups_join_group' );

			if ( !groups_join_group( $group->id ) ) {
				_e( 'Error joining group', 'trendr' );
			} else {
				echo '<a id="group-' . esc_attr( $group->id ) . '" class="leave-group" rel="leave" title="' . __( 'Leave Group', 'trendr' ) . '" href="' . trm_nonce_url( trs_get_group_permalink( $group ) . 'leave-group', 'groups_leave_group' ) . '">' . __( 'Leave Group', 'trendr' ) . '</a>';
			}

		} else if ( 'private' == $group->status ) {

			check_ajax_referer( 'groups_request_membership' );

			if ( !groups_send_membership_request( $trs->loggedin_user->id, $group->id ) ) {
				_e( 'Error requesting membership', 'trendr' );
			} else {
				echo '<a id="group-' . esc_attr( $group->id ) . '" class="membership-requested" rel="membership-requested" title="' . __( 'Membership Requested', 'trendr' ) . '" href="' . trs_get_group_permalink( $group ) . '">' . __( 'Membership Requested', 'trendr' ) . '</a>';
			}
		}

	} else {

		check_ajax_referer( 'groups_leave_group' );

		if ( !groups_leave_group( $group->id ) ) {
			_e( 'Error leaving group', 'trendr' );
		} else {
			if ( 'public' == $group->status ) {
				echo '<a id="group-' . esc_attr( $group->id ) . '" class="join-group" rel="join" title="' . __( 'Join Group', 'trendr' ) . '" href="' . trm_nonce_url( trs_get_group_permalink( $group ) . 'join', 'groups_join_group' ) . '">' . __( 'Join Group', 'trendr' ) . '</a>';
			} else if ( 'private' == $group->status ) {
				echo '<a id="group-' . esc_attr( $group->id ) . '" class="request-membership" rel="join" title="' . __( 'Request Membership', 'trendr' ) . '" href="' . trm_nonce_url( trs_get_group_permalink( $group ) . 'request-membership', 'groups_send_membership_request' ) . '">' . __( 'Request Membership', 'trendr' ) . '</a>';
			}
		}
	}
}
add_action( 'trm_ajax_joinleave_group', 'trs_dtheme_ajax_joinleave_group' );

/* AJAX close and keep closed site wide notices from an admin in the sidebar */
function trs_dtheme_ajax_close_notice() {
	global $userdata;

	if ( !isset( $_POST['notice_id'] ) ) {
		echo "-1<div id='message' class='error'><p>" . __('There was a problem closing the notice.', 'trendr') . '</p></div>';
	} else {
		$notice_ids = trs_get_user_meta( $userdata->ID, 'closed_notices', true );

		$notice_ids[] = (int) $_POST['notice_id'];

		trs_update_user_meta( $userdata->ID, 'closed_notices', $notice_ids );
	}
}
add_action( 'trm_ajax_messages_close_notice', 'trs_dtheme_ajax_close_notice' );

/* AJAX send a private message reply to a thread */
function trs_dtheme_ajax_messages_send_reply() {
	global $trs;

	check_ajax_referer( 'messages_send_message' );

	$result = messages_new_message( array( 'thread_id' => $_REQUEST['thread_id'], 'content' => $_REQUEST['content'] ) );

	if ( $result ) { ?>
		<div class="message-box new-message">
			<div class="message-metadata">
				<?php do_action( 'trs_before_message_meta' ) ?>
				<?php echo trs_loggedin_user_portrait( 'type=thumb&width=30&height=30' ); ?>

				<strong><a href="<?php echo $trs->loggedin_user->domain ?>"><?php echo $trs->loggedin_user->fullname ?></a> <span class="activity"><?php printf( __( 'Sent %s', 'trendr' ), trs_core_time_since( trs_core_current_time() ) ) ?></span></strong>

				<?php do_action( 'trs_after_message_meta' ) ?>
			</div>

			<?php do_action( 'trs_before_message_content' ) ?>

			<div class="message-content">
				<?php echo stripslashes( apply_filters( 'trs_get_the_thread_message_content', $_REQUEST['content'] ) ) ?>
			</div>

			<?php do_action( 'trs_after_message_content' ) ?>

			<div class="clear"></div>
		</div>
	<?php
	} else {
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem sending that reply. Please try again.', 'trendr' ) . '</p></div>';
	}
}
add_action( 'trm_ajax_messages_send_reply', 'trs_dtheme_ajax_messages_send_reply' );

/* AJAX mark a private message as unread in your inbox */
function trs_dtheme_ajax_message_markunread() {
	global $trs;

	if ( !isset($_POST['thread_ids']) ) {
		echo "-1<div id='message' class='error'><p>" . __('There was a problem marking messages as unread.', 'trendr' ) . '</p></div>';
	} else {
		$thread_ids = explode( ',', $_POST['thread_ids'] );

		for ( $i = 0, $count = count( $thread_ids ); $i < $count; ++$i ) {
			TRS_Messages_Thread::mark_as_unread($thread_ids[$i]);
		}
	}
}
add_action( 'trm_ajax_messages_markunread', 'trs_dtheme_ajax_message_markunread' );

/* AJAX mark a private message as read in your inbox */
function trs_dtheme_ajax_message_markread() {
	global $trs;

	if ( !isset($_POST['thread_ids']) ) {
		echo "-1<div id='message' class='error'><p>" . __('There was a problem marking messages as read.', 'trendr' ) . '</p></div>';
	} else {
		$thread_ids = explode( ',', $_POST['thread_ids'] );

		for ( $i = 0, $count = count( $thread_ids ); $i < $count; ++$i ) {
			TRS_Messages_Thread::mark_as_read($thread_ids[$i]);
		}
	}
}
add_action( 'trm_ajax_messages_markread', 'trs_dtheme_ajax_message_markread' );

/* AJAX delete a private message or array of messages in your inbox */
function trs_dtheme_ajax_messages_delete() {
	global $trs;

	if ( !isset($_POST['thread_ids']) ) {
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem deleting messages.', 'trendr' ) . '</p></div>';
	} else {
		$thread_ids = explode( ',', $_POST['thread_ids'] );

		for ( $i = 0, $count = count( $thread_ids ); $i < $count; ++$i )
			TRS_Messages_Thread::delete($thread_ids[$i]);

		_e( 'Messages deleted.', 'trendr' );
	}
}
add_action( 'trm_ajax_messages_delete', 'trs_dtheme_ajax_messages_delete' );

/**
 * trs_dtheme_ajax_messages_autocomplete_results()
 *
 * AJAX handler for autocomplete. Displays friends only, unless TRS_MESSAGES_AUTOCOMPLETE_ALL is defined
 *
 * @global object object $trs Global BuddyPress settings object
 * @return none
 */
function trs_dtheme_ajax_messages_autocomplete_results() {
	global $trs;

	// Include everyone in the autocomplete, or just friends?
	if ( $trs->messages->slug == $trs->current_component )
		$autocomplete_all = $trs->messages->autocomplete_all;

	$friends  = false;
	$pag_page = 1;

	$limit = $_GET['limit'] ? $_GET['limit'] : apply_filters( 'trs_autocomplete_max_results', 10 );

	// Get the user ids based on the search terms
	if ( !empty( $autocomplete_all ) ) {
		$users = TRS_Core_User::search_users( $_GET['q'], $limit, $pag_page );

		if ( !empty( $users['users'] ) ) {
			// Build an array with the correct format
			$user_ids = array();
			foreach( $users['users'] as $user ) {
				if ( $user->id != $trs->loggedin_user->id )
					$user_ids[] = $user->id;
			}

			$user_ids = apply_filters( 'trs_core_autocomplete_ids', $user_ids, $_GET['q'], $limit );
		}
	} else {
		if ( trs_is_active( 'friends' ) ) {
			$users = friends_search_friends( $_GET['q'], $trs->loggedin_user->id, $limit, 1 );

			// Keeping the trs_friends_autocomplete_list filter for backward compatibility
			$users = apply_filters( 'trs_friends_autocomplete_list', $users, $_GET['q'], $limit );

			if ( !empty( $users['friends'] ) )
				$user_ids = apply_filters( 'trs_friends_autocomplete_ids', $users['friends'], $_GET['q'], $limit );
		}
	}

	if ( !empty( $user_ids ) ) {
		foreach ( $user_ids as $user_id ) {
			$ud = get_userdata( $user_id );
			if ( !$ud )
				continue;

			if ( trs_is_username_compatibility_mode() )
				$username = $ud->user_login;
			else
				$username = $ud->user_nicename;

			echo '<span id="link-' . $username . '" href="' . trs_core_get_user_domain( $user_id ) . '"></span>' . trs_core_fetch_portrait( array( 'item_id' => $user_id, 'type' => 'thumb', 'width' => 15, 'height' => 15 ) ) . ' &nbsp;' . trs_core_get_user_displayname( $user_id ) . ' (' . $username . ')
			';
		}
	}
}
add_action( 'trm_ajax_messages_autocomplete_results', 'trs_dtheme_ajax_messages_autocomplete_results' );

?>
