<?php

/**
 * Template Name: trendr - Activity Directory
 *
					<li class="selected" id="activity-all"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/'; ?>" title="<?php _e( 'The public activity for everyone on this site.', 'trendr' ); ?>"><?php printf( __( 'All Members <span>%s</span><div class=nul></div>', 'trendr' ), trs_get_total_site_member_count() ); ?></a></li>
 * @package trendr
 * @sutrsackage Theme
 */
$bd = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
// Check connection
if (!$bd) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
<?php get_header( 'trendr' ); ?>

		<div id="l-a">

		

			<?php do_action( 'trs_before_directory_activity' ); ?>



			<?php do_action( 'trs_before_directory_activity_content' ); ?>

			<?php if ( is_user_logged_in() ) : ?>

				<?php locate_template( array( 'activity/post-form.php'), true ); ?>

			<?php endif; ?>
			<?php do_action( 'template_notices' ); ?>
				<div class="contour-select activity-type-tabs" role="navigation"> <div class="inner"><div class="contour-inner">
				<ul>
										<?php if ( is_user_logged_in() ) : ?>

					<?php do_action( 'trs_before_activity_type_tab_all' ); ?>


					<li   id="activity-all"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/'; ?>" title="<?php _e( 'The public activity for everyone on this site.', 'trendr' ); ?>"><?php printf( __( 'All Members <span>%s</span><div class=nul></div>', 'trendr' ), trs_get_total_site_member_count() ); ?></a></li>	


<li id="activity-following"><a href="<?php echo trs_loggedin_user_domain() . TRS_ACTIVITY_SLUG . '/' . TRS_FOLLOWING_SLUG . '/' ?>" title="<?php _e( 'The public activity for everyone you are following on this site.', 'trs-follow' ) ?>"><?php printf( __( 'Following <span>%d</span><div class=nul></div>', 'trs-follow' ), (int)$counts['following'] ) ?></a></li>


					<li class="selected" id="activity-following_recommend">
						<a href="<?php echo  trs_loggedin_user_domain() . trs_get_activity_slug() . '/ReCommendedandFollow/' ; ?>" title="<?php _e( 'my followers and recommended posts.', 'trendr' ) ?>">
							<?php printf( __( 'public <div class=nul></div>', 'trendr' ) ) ?>
						</a>
					</li>
									

					<li class="selected" id="activity-groups_recommend">
						<a href="<?php echo  trs_loggedin_user_domain() . trs_get_activity_slug() . '/ReCommendedandFollow/' ; ?>" title="<?php _e( 'my followers and recommended posts.', 'trendr' ) ?>">
							<?php printf( __( 'recommend <div class=nul></div>', 'trendr' ) ) ?>
						</a>
					</li>		
					<li id="activity-groups_friends">
						<a href="<?php echo  trs_loggedin_user_domain() . trs_get_activity_slug() . '/freindsandgroups/' ; ?>" title="<?php _e( 'my friends and groups posts.', 'trendr' ) ?>">
							<?php printf( __( 'private<div class=nul></div>', 'trendr' ) ) ?>
						</a>
					</li>




						<?php do_action( 'trs_before_activity_type_tab_friends' ) ?>

						<?php if ( trs_is_active( 'friends' ) ) : ?>

							<?php if ( trs_get_total_friend_count( trs_loggedin_user_id() ) ) : ?>

								<li id="activity-friends_featuredposts"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/' . trs_get_friends_slug() . '/'; ?>" title="<?php _e( 'The activity of my friends only.', 'trendr' ); ?>"><?php printf( __( 'My Friends <span>%s</span><div class=nul></div>', 'trendr' ), trs_get_total_friend_count( trs_loggedin_user_id() ) ); ?></a></li>

							<?php endif; ?>

						<?php endif; ?>

						<?php do_action( 'trs_before_activity_type_tab_groups' ) ?>

						<?php if ( trs_is_active( 'groups' ) ) : ?>

							<?php if ( trs_get_total_group_count_for_user( trs_loggedin_user_id() ) ) : ?>

								<li id="activity-groups"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/' . trs_get_groups_slug() . '/'; ?>" title="<?php _e( 'The activity of groups I am a member of.', 'trendr' ); ?>"><?php printf( __( 'My Groups <span>%s</span><div class=nul></div>', 'trendr' ), trs_get_total_group_count_for_user( trs_loggedin_user_id() ) ); ?></a></li>
a
							<?php endif; ?>

						<?php endif; ?>

						<?php do_action( 'trs_before_activity_type_tab_favorites' ); ?>

						
							<li id="activity-favorites"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/favorites/'; ?>" title="<?php _e( "Posts I've marked as a favorite.", 'trendr' ); ?>"><?php printf( __( ' Favorites <span>%s</span><div class=nul></div>', 'trendr' ), trs_get_total_favorite_count_for_user( trs_loggedin_user_id() ) ); ?></a></li>


						<?php do_action( 'trs_before_activity_type_tab_mentions' ); ?>

						<li id="activity-mentions"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/mentions/'; ?>"
						 title="<?php _e( "Posts I've been mentioned on", 'trendr' ); ?>"><?php _e( 'Mentions', 'trendr' ); ?><?php if ( trs_get_total_mention_count_for_user( trs_loggedin_user_id() ) ) : ?> <strong><?php printf( __( '<span>%s new</span><div class=nul></div>', 'trendr' ), trs_get_total_mention_count_for_user( trs_loggedin_user_id() ) ); ?></strong><?php endif; ?></a></li>
					<?php endif; ?>

					<?php do_action( 'trs_activity_type_tabs' ); ?>
		
		</ul>
								</ul>

			</div><!-- .contour-select -->
			</div>
            
			</div><!-- .contour-inner -->

		<div class="dimension-inn">
			<?php
			
			global $trmdb;
			$my_user_id=get_current_user_id();
			//$user_ids=join(",",$users_nearby);
			$querystr = "
			    SELECT * FROM {$trmdb->prefix}trs_stories
			    WHERE {$trmdb->prefix}trs_stories.user_id != $my_user_id 
			    ORDER BY {$trmdb->prefix}trs_stories.date_recorded Desc  
			 ";
			 $results=$trmdb->get_results($querystr, OBJECT);
			 //echo "<script>console.log('" . json_encode($results) . "');</script>";
			
			?>	
			<h1 style="padding-left: 20px;"></h1>
			<a href="/all-Stories" style="float: right;margin-top: 0px;margin-right: 40px;">All Stories</a>
			<div class="Stories" id="Stories" style="width:875px;margin-bottom: 20px;display: inline-block;">
				
				<div class="add_new_Stories" style="">
					
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
						if($count<4){
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
			</div>


			<div class="activity" role="main">

				<?php locate_template( array( 'activity/activity-loop.php' ), true ); ?>
			</div>

			</div><!-- .dimension-inn -->

			<?php do_action( 'trs_after_directory_activity_list' ); ?>

			<?php do_action( 'trs_directory_activity_content' ); ?>

			<?php do_action( 'trs_after_directory_activity_content' ); ?>

			<?php do_action( 'trs_after_directory_activity' ); ?>
		</div><!-- #l-a -->

<?php 

if ( is_user_logged_in() &&  trs_is_activity_component() ) {
?>
<div id="l-c" >
<script>





function getLocation1() {
  //x.innerHTML = "Geolocation is not supported by this browser.";
  //var x = document.getElementById("demo");
  if (navigator.geolocation) {
   navigator.geolocation.getCurrentPosition(position => {
  lata = position.coords.latitude;
  longa = position.coords.longitude;
  console.log('Lat index', lata);
  console.log('Long inxed', longa);
  jq.post(ajaxurl,{
    		action:"search_filter",
    	//	'radius':rad,
    	//	'categories_str':categories_str,ff
			'latitude':lata,
			'longitude':longa
    	});
})
  }
  else console.log("geolcation is not supported");
}


getLocation1();

var track_page = 1; //track user scroll as page number, right now page number is 1
var loading  = false; //prevents multiple loads
$(document).ready(function(){
    $(document).on('click','.show_more',function(){
    if (loading == false) {
          load_contents(track_page);
          track_page++; }
        else {  }
    });
});
var heightdown = '100';
$(window).scroll(function() { //detect page scroll
    if($(window).scrollTop() >= ($(document).height() - heightdown) - $(window).height()) {//if user scrolled to bottom of the page
      //alert($(window).height());
         //page number increment
         //load content
        if (loading == false) {
          load_contents(track_page);
          track_page++; }
        else {  }
    }
});  
//Ajax load function
function load_contents(track_page){
    if(loading == false){
        loading = true;  //set loading flag on
        $('#loading').html("Please wait..");
        $.post( '/m-activity-pages.php', {'page': track_page}, function(data){
             //set loading flag off once the content is loaded
            if(data.length == 0){
                //notify user if nothing to load
                $('#loading').html("<p>No more images</p>");
                $('.show_more').hide();
                loading = true;
                return;
            }
            else {
            $('#loading').hide(); //hide loading animation once data is received
            $('.show_more').show(); //show loading animation once data is received
            $("#list").append(data); //append data into #results element
            loading = false;
			var images = document.querySelectorAll(".someClass");
			for (let i = 0; i < images.length; i++) {
				images[i].addEventListener("click", showImageModal);
			}
            }
        })
    }
}
</script>

<div style="width:100%;float:left;">


<div id="modal01" class="w3-modal" onclick="this.style.display='none'">
	<span class="w3-button w3-hover-red w3-xlarge w3-display-topright"></span>
    <div class="w3-modal-content w3-animate-zoom">
    <img id="vid01" style="width:100%">
	</div>
</div>


<div style="width:50%; length:100%; float:left;" >
<style>
.btn{ background-color: grey; float: right;}
</style>
<?php

isset($vqueryactivity);
$vqueryactivity = mysqli_query($bd,"SELECT id,content FROM `trm_trs_activity` WHERE type='video_post' ORDER BY id DESC LIMIT 0,4");
$counter =1;
if(mysqli_num_rows($vqueryactivity)>0)
{
while ($rowvactivity1=mysqli_fetch_array($vqueryactivity))
{
	$v_name = str_replace("[med_images]", "", $rowvactivity1['content']);
	$v_name = str_replace("\n", "", $v_name);
	
	$finalvideo = substr($v_name, 0, strpos($v_name, "[/med_images]"));
	//echo "<br>".$finalvideo;
	?>
	<input type="hidden" name="muted" id="muted" value="1" >
	
	<video class="videos"  playsinline autoplay loop  muted style=" width: 100%;height: 100%;padding:10px;" preload="auto" onclick="openVid(this)">
	<source src="<?php echo site_url().'/trm-src/uploads/med/'.$finalvideo;?>" type="video/mp4"  >
	<source src="<?php echo site_url().'/trm-src/uploads/med/'.$finalvideo;?>" type="video/ogg" >
	</video>
	
	
	
	<div id="overlay">
	<button class="btn" onclick = change_all()>mute/unmute all </button>
	</div>
	
	   <br><br><br><br><br><br>
	   
	   
	   
	

	

	<?php
}
}
?>


</div>
<script>


</script>

<div style="width:50%;float:right;" id="list">
<table>
<?php


function strpos_arr($haystack, $needle) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $what) {
        if(($pos = strpos($haystack, $what))!==false) return $pos;
    }
    return false;
}
$spm = array("png","jpg","png","webp","jpeg");
isset($queryactivity);
$queryactivity = mysqli_query($bd,"SELECT id,content FROM `trm_trs_activity` WHERE type='photo_post' ORDER BY id DESC LIMIT 0,10");
$counter =1;
if(mysqli_num_rows($queryactivity)>0)
{
while ($rowactivity1=mysqli_fetch_array($queryactivity))
{
	
//echo "from: ".$rowactivity1['content'].'<br><br>';
	$i_name = str_replace("[med_images]", "", $rowactivity1['content']);
	$i_name = str_replace("\n", "", $i_name);

if (strpos_arr($i_name, $spm)) {
	$finalimage = substr($i_name, 0, strpos($i_name, "[/med_images]"));
	//echo "<br>".$i_name;
	//echo getcwd();
if(file_exists('trm-src/uploads/med/'.$finalimage)) {
echo '
<tr><td>'.$counter.'</td><td>
<img class="someClass" src="/trm-src/uploads/med/'.$finalimage.'" alt="" style="width:100%;">
</td></tr>
';
}
else {
echo "please recheck image presents in trm-src/uploads/med/ folder";
}

} else {
 echo "invalid image format";
}//invalid image
$counter++;
}//while
}//if activity mysql has rows
?>
<style>
.modal {
  position: fixed;
  width: 80%;
  height: 90%;
  left: 10%;
  top: 0%;
  background: white;
  border: 1px solid;
  display: none;
  overflow: auto;
}

.close {
  position: absolute;
  top: 0px;
  right: 0px;
  background: red;
  color: white;
  font-size: 18px;
  font-weight: bold;
  width: 32px;
  height: 32px;
  text-align: center;
  line-height: 32px;
  cursor: pointer;
}









</style>

<div id="myModal" class="modal">
  <span class="close">X</span>
  <image class="modal-content" id="img01">
</div>

<div id="Vmodal" class="modal">
	<span class="close">X</span>

</div>
<script>

function showImageModal() {
  console.log("got to show image");
  modal.style.display = "block";
  modalImg.src = this.src;
  console.log(modalImg);
}

function openVid(element) {
	if(videoup){
		return;
	}
	videoup = true;
	var a = element.cloneNode(true);
	a.controls = true;
	a.style.height = '90%';
	a.style.width = '98%';
	modalVcont.append(a);
	modalVcont.style.display = "block";
}



function change_all(){
	var set = document.getElementById('muted').value;
	var videos = document.querySelectorAll(".videos");
	if(set == 1){
		for (let i = 0; i < videos.length; i++) {
			videos[i].volume = 1;
			videos[i].muted = 0;
		}
		
		document.getElementById('muted').value = 0;
	}
	
	else {
		for (let j = 0; j < videos.length; j++) {
	
			videos[j].volume = 0;
			videos[j].muted = 1;
		}
		
		document.getElementById('muted').value = 1;
	}
}


var videoup = false;
var modal = document.getElementById('myModal');
var modalImg = document.getElementById("img01");
modalImg.style.height = '100%';
modalImg.style.width = '100%';
var captionText = document.getElementById("caption");
var modalCloseBtn = modal.querySelector(".close");



var modalVcont = document.getElementById('Vmodal');
var vCloseBtn = modalVcont.querySelector(".close");

var images = document.querySelectorAll(".someClass");
for (let i = 0; i < images.length; i++) {
  images[i].addEventListener("click", showImageModal);
}



vCloseBtn.addEventListener("click", function() {
  modalVcont.style.display = "none";
  var b = modalVcont.querySelector(".videos");
  b.pause();
  //delete b;
  b.remove();
  videoup = false;
});


modalCloseBtn.addEventListener("click", function() {
  modal.style.display = "none";
});


</script>

</table>
<div style="float:left;width:100%">
<div id="loading">
</div>
<div class="show_more_main">
<span class="show_more" title="Load more posts">SHOW MORE</span>
</div>
</div>
</div>
</div>
<!-- <iframe  src="<?php echo site_url() .'/activity/'?>"  class="frame" id="frame" ></iframe> -->
<?php $user_id = trs_displayed_user_id(); ?>
</div><!-- #l-c -->
<div id="l-b" >





<?php

//notifications page
$my_user_id=get_current_user_id();
$nqueryactivity = mysqli_query($bd,"SELECT id,component_name,component_action,item_id FROM `trm_trs_notifications` WHERE trm_trs_notifications.user_id = $my_user_id AND trm_trs_notifications.component_name = 'messages' ORDER BY id DESC LIMIT 0,10");


$nqueryactivity2 = mysqli_query($bd,"SELECT id,component_name,component_action,item_id FROM `trm_trs_notifications` WHERE trm_trs_notifications.user_id = $my_user_id AND trm_trs_notifications.component_name = 'follow' ORDER BY id DESC LIMIT 0,10");
$counter =1;






?>
<h1> Messages: </h1>
<br>
<h1> You have <?php echo mysqli_num_rows($nqueryactivity) ?> new messages <h1>
		<br>
<?php

?>

<h1> Followers: </h1>
<?php

if(mysqli_num_rows($nqueryactivity2)>0)
{
while ($nqueryactivity1=mysqli_fetch_array($nqueryactivity2))
{
	
	echo "<script>console.log('" . json_encode($nqueryactivity1) . "');</script>";
	$user_data=get_userdata( $nqueryactivity1['item_id'] );
	$display_name=$user_data->display_name;
	
	//print_r($nqueryactivity1);
	?>
	<a href="http://localhost/members/<?php echo $display_name;?>/" >
	<h1>@<?php echo $display_name;?> </a>is now following you</h1>
	
	<?php
}

}

?>


 <!-- <iframe src="<?php echo trs_loggedin_user_domain() .'profile/change-profile-photo/' ?>"  class="frame" id="frame" > </iframe> -->
</div><!-- #l-b -->
<div id="settings"></div><!-- #settings -->
<div id="info"></div><!-- #info -->
<div id="avatar"></div><!-- #avatar -->
<div id="contour-image">
<a href="<?php trs_displayed_user_link() ?>"> 	<?php trs_displayed_user_portrait( 'type=full' ); ?></a>
<!-- #contour-image --></div>
     <li id="p1"><a href="#" >destroy page </a></li>

		<!-- #scroll button -->
              
                <ul id="nav2" scrolling="no"  seamless="seamless" >  
    <!-- #l-a --><li id="f1" ><div  data-location="">Content page 1</div></li>
    <!-- #l-c --><li id="f3"><div data-location="">Content page 2</div></li>
    <!-- #dim --><li id="f0"><div data-location="">Content page 3</div></li>
    <!-- #l-b --><li id="f2"><div data-location="">Content page 4</div></li>
	<!-- #l-d --><li id="f4"><div data-location="">Content page 5</div></li>

    </ul>
<?php 
}
?>


	<?php do_action( 'trs_before_directory_activity_page' ); ?>

		
	<div id="skeleton">
		<div class="dimension">







<div id="l-d">
		
	<?php if ( is_user_logged_in() && trs_displayed_user_id() && ( trs_is_activity_component() ) ) do_action( 'trs_profile_knobs' ); ?>  
<div id="bar_bottom" ><span class="user-name">
	<?php trs_displayed_user_fullname() ?>
<span class="user-call">


</div>
	<span class="activity"><?php trs_last_activity( trs_displayed_user_id() ); ?></span>

	<?php if ( !is_super_admin() && is_user_logged_in() ) : ?>


	<div ><span class="user-call"><a  href="<?php echo trs_get_send_public_message_link() ?>" >@<?php trs_displayed_user_username(); ?></a></div>
</span>



<?php endif; ?>



<?php if ( is_super_admin() ) : ?>


	<div ><span class="user-call"><a  href="<?php echo get_admin_url() ?>" >@<?php trs_displayed_user_username(); ?></a></div>
</span>
<?php endif; ?>
	
 <form action=""  id="profile-form" class="dir-form">
			<br>
			<br>
			<br>
			<br>
 <?php
			$my_user_id=get_current_user_id();
			$querystr = "
			    SELECT * FROM {$trmdb->prefix}trs_stories
			    WHERE {$trmdb->prefix}trs_stories.user_id =$my_user_id
			    ORDER BY {$trmdb->prefix}trs_stories.date_recorded Desc  
			 ";
			 
			 $results=$trmdb->get_results($querystr, OBJECT);
			 
	
			?>	
			
			<div class="stories" id="stories" style="width:100%;height:auto;margin-bottom: 20px;display: inline-block;">
				
				<div class="add_new_stories stores_div"  style="">
					
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
						if($count<5){

						if($exist=="all" || $exist=="true"){ ?>
						<div  id="stores_<?php echo $result->id;?>" class="stores_div" style="width:30%; height:50%;display: inline-block;border:1px solid #999;padding:5px;margin-bottom: 5px">
							<div id="story-contour-image">
							<a href="<?php echo $result->primary_link; ?>"><?php echo trs_core_fetch_portrait( array( 'item_id' => $result->user_id, 'type' => 'full', 'width' => 20, 'height' => 20, 'html' => true, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ;
							?><p style="text-align: center;"><?php 
							$user_data=get_userdata( $result->user_id );
							$display_name=$user_data->display_name;
							echo '@'.$display_name;
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
		<?php if($count>=5){
		?>
		<div style="margin: 0 auto;text-align: center;">
			<button type = "button" id='load_more' style="margin-top:50px;" >Load More</button>
		</div>
		<?php
		}
		?>

<script>
	/* Load More */
    jq("button#load_more").bind('click', function() {
        var div_list = jq("#stories").find(".stores_div").map(function() {
                return jq(this).html();
            });
        var cat=jq("#activate_tagator3").val();

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
       
      //  alert(div_list.length);
        jq("button#load_more").css("display","inline");
        jq.post(ajaxurl, {
                action: "my_load_more_contents", 
                'index' : div_list.length,
    			'categories_str':categories_str
            }, function (response) {
            	console.log(response);
              jq("#stories").append(response);
			  return false;
              var div_list1 = jq("#stories").find(".stores_div").map(function() {
                return jq(this).html();
            	});
              if(div_list1.length-1==jq("#total_count").val()){
              	jq("button#load_more").css("display","none");
              }
           });
        });
    
</script>
		
	
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>


	
<script>

  $('div.dropdown').each(function() {
    var $dropdown = $(this);

    $("a.dropdown-link", $dropdown).click(function(e) {
      e.preventDefault();
      $div = $("div.confirm", $dropdown);
      $div.toggle();
      $("div.confirm").not($div).hide();
      return false;
    });

});

  $('html').click(function(){
    $("div.confirm").hide();              

  });
	
	
</script>
<?php do_action( 'trs_after_activity_loop' ); ?>

<form action="" name="publish-spiral1" id="publish-spiral1" method="post">

  <?php trm_nonce_field( 'activity_filter', '_key_activity_filter' ); ?>

</form>	
	
	
	
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	


      
</div><!-- #l-d -->
</div>

	<?php //do_action( 'trs_after_directory_members_page' ); ?>



	<?php do_action( 'trs_after_directory_activity_page' ); ?>

<?php get_footer( 'trendr' ); ?>

<script>






var videos = document.getElementsByTagName("video"),
fraction = 0.8;
function checkScroll() {

    for(var i = 0; i < videos.length; i++) {

        var video = videos[i];

        var x = video.offsetLeft, y = video.offsetTop, w = video.offsetWidth, h = video.offsetHeight, r = x + w, //right
            b = y + h, //bottom
            visibleX, visibleY, visible;

            visibleX = Math.max(0, Math.min(w, window.pageXOffset + window.innerWidth - x, r - window.pageXOffset));
            visibleY = Math.max(0, Math.min(h, window.pageYOffset + window.innerHeight - y, b - window.pageYOffset));

            visible = visibleX * visibleY / (w * h);

            if (visible > fraction) {
                video.play();
            } else {
                video.pause();
            }

    }

}

window.addEventListener('scroll', checkScroll, false);
window.addEventListener('resize', checkScroll, false);

</script>







