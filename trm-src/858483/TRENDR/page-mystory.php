/**
* Template Name: My Story Page
*
* @package WordPress
* @Writed By Yang
*/	
<?php get_header() ?>
	<input type="hidden" id="my_stories_page" value="my_stories"/>
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
			$my_user_id=get_current_user_id();
			$querystr = "
			    SELECT * FROM {$trmdb->prefix}trs_stories
			    WHERE {$trmdb->prefix}trs_stories.user_id =$my_user_id
			    ORDER BY {$trmdb->prefix}trs_stories.date_recorded Desc  
			 ";
			 
			 $results=$trmdb->get_results($querystr, OBJECT);
			 
	
			?>	
			<h1 style="padding-left: 20px;">My Stories</h1>
			<h2 style="padding-left: 20px;color:#ff0000;">Filter Options</h2>
			<div class="filters_options" id="filter_option" style="padding-left:30px;">
				<lable>Categories:</lable>
				<input id="activate_tagator3" width="90%" type="text" class="tagator" placeholder="Please select your category for stories..." value="<?php 
					$cate_str=trs_xprofile_get_meta( get_current_user_id(),3, 'data', 'categories' );
					$cates=str_replace('_',',',$cate_str);
					
					echo $cates;
					
				?>" style="width:350px !important;" data-tagator-show-all-options-on-focus="true" data-tagator-autocomplete="['Resturant', 'Clubs', 'Bar']">
				
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
                action: "my_load_more_contents", 
                'index' : div_list.length,
    			'categories_str':categories_str
            }, function (response) {
            	console.log(response);
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
    	
    	var categories=[];
        var test_list = $("#tagator_activate_tagator3 .tagator_tags").find(".tagator_tag").map(function() {
            return $(this).html();
        });

        for (i=0;i<test_list.length;i++){
            var inx=test_list[i].indexOf("<");
            categories[i]=test_list[i].substr(0,inx);
        }
        if(categories.length!=0){
        	categories_str=categories.join("_");
    	}else{
    		categories_str="";

    	}
        jq("button#load_more").css("display","inline");

    	jq.post(ajaxurl,{
    		action:"my_search_filter",
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

