<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>


	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ) ?>; charset=<?php bloginfo( 'charset' ) ?>" />
		<title><?php trm_title( '|', true, 'right' ); bloginfo( 'name' ); ?></title>

 <link rel='stylesheet' type='text/css' href='../../../../../../trm-src/858483/TRENDR/a/src.css' />
  

 <link rel="shortcut icon" href="../../../../../../trm-src/858483/TRENDR/favicon6.ico" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php do_action( 'trs_head' ) ?>


        <script>
            <?php
                    if(is_user_logged_in()) {
                        $user = trm_get_current_user();
                        // this will be used to compose upload urls //
                        echo "var username = '{$user->user_login}'";
                    }
            ?>
        </script>
 
<?php if ( ! is_front_page() )  : ?>
		<?php trm_head();	?>
	</head>
<body>

    <header id="site-header" role="banner" >

		<div id="header" >
		<div class="container">
<?php if ( is_user_logged_in() ) : ?>
<a  href="/activity" rel="home"  class="site-title"><span class="char2">t</span>rendr</a>

<?php endif; ?>
<?php if ( !is_user_logged_in() ) : ?>

<a  href="<?php echo site_url() ?>/" rel="home"  class="site-title"><span class="char2">t</span>rendr</a>

<?php endif; ?>

	<div id="bar" > <h00 id="bar"> </h00><h000 id="bar"> </h000></div>


		</h0>	</div><div class="container">

<?php if ( is_user_logged_in() ) : ?>



	<div id="line" > <h1 id="line"> </h1><h2 id="line"> </h2><h3 id="line"> </h3><h4 id="line"> </h4><h5 id="line"> </h5><h6 id="line"> </h6></div>  

<?php endif; ?>

	
<?php if ( !is_user_logged_in() ) : ?>
	<div id="line" > <h3 id="line"> </h3></div>  <?php endif; ?>
					
<ul class="head-refine">





     <nav class="menu">	

			  <?php do_action( 'header_menu' ); ?>  



	

<?php if ( trs_displayed_user_id() && ( trs_is_activity_component() ) ) do_action( 'friends_list' ); ?>  

  <?php endif; ?>

	 						<div id="in" >	
                      <?php do_action( 'trs_before_header' ) ?> <?php if ( ( is_user_logged_in()  ) ) : ?>
<div class='user-info'>     <a href="<?php echo trs_loggedin_user_domain() ?>">	<?php trs_loggedin_user_portrait( 'type=full&width=17&height=17' ) ?></a> </div>
  <a href="#" data-theme="e"  title="upload" data-role="button"  class="openup"    >post</a>
<?php endif; ?>
<?php if ( trs_is_activity_component() ) : ?>

<li id="post-refine" class="last">
			<select id="post-refine-by">
<option  style="background:#aaa url(checkb.png) left top no-repeat;" value="-1"><?php _e( 'Recent', 'trendr' ); ?></option>
						
        		<?php
				do_action( 'trs_activity_filter_options' ); ?>
			</select>
		</li>  <?php endif; ?>
    
</div>

				<div class="padder">


		
		</div> <?php if ( (  trs_displayed_user_id()  ) ) : ?>
<div class="user_a"> <a href="<?php echo trs_displayed_user_domain() ?>">
 
 <?php trs_displayed_user_portrait( 'type=full&width=25&height=25' ) ?></a></div>	


<?php endif; ?><!-- .padder -->


<?php trs_adminbar_notifications_menu();?>


		</div><!-- #search-bar -->	



	<?php if ( trs_is_activity_component() ) do_action( 'trs_before_container' ); ?>  
          	  <div id="profile-bar"  >
	  			<?php do_action( 'trs_before_member_header' ); ?>

</div>
<?php if ( trs_displayed_user_id() && ( trs_is_activity_component() ) ) do_action( 'trs_profile_hashtag' ); ?>  
 <?php if ( (  trs_displayed_user_id()  ) && ( trs_is_activity_component()) ) : ?>
	
<?php if ( is_user_logged_in() ) : ?>
	<img id="front-triangle"src="../../../../../../trm-src/858483/TRENDR/a/images/triangle.png"    >
 <?php endif; ?>


<?php endif; ?><!-- .padder -->    		   <?php if ( !is_front_page() && !is_user_logged_in() ) : ?>	

    		         <span class="header-login"> Have an account?
 <a href="#" data-theme="e"  title="login" data-role="button" class="head-login">Log in</a><img id="login-triangle"src="../../../../../../trm-src/858483/TRENDR/a/images/triangle.png"    ></span>
  <div id="login-header">


<form name="login-form" id="login-form" class="standard-form" action="<?php echo site_url( 'enter.php', 'login_post' ) ?>" method="post">
			<br />
<input type="text" placeholder="Username or E-mail" id="user-login" name="log" value="<?php echo isset( $_REQUEST['s'] ) ? esc_attr( $_REQUEST['s'] ) : ''; ?>" tabindex="97" />

			<br>
			<input placeholder="Password" type="password" name="pwd" id="user-pass" class="input" value="" tabindex="98" >



			<input type="submit" name="submit" id="submit-login" value="<?php _e('Login'); ?>" tabindex="100" />
			<input type="hidden" name="testcookie" value="1" >				<div class="agreement">

           <p> 		<?php if ( trs_get_signup_allowed() ) : ?>
				<?php printf( __( ' Do you want to sign up ?   <a href="%s" title="Create an account">Signup&nbsp;&nbsp;&nbsp;&nbsp;</a> <div class="terms">By signing up, you agree to our Terms, Data Policy and Cookies Policy.</div>', 'trendr' ), site_url( TRS_REGISTER_SLUG . '/' ) ) ?>
			<?php endif; ?></p>
           </div>
		</form>
			<p></p>


			<br />	

		</form><?php endif; ?>  
	</nav> 
<div class="dim">	
  
<div class="dim-inner">	
 <form action="<?php trs_activity_post_form_action(); ?>" method="post" id="post-box" name="post-box" role="complementary">

	<?php do_action( 'trs_before_activity_post_form' ); ?>

<div id="post-intro">
 

  <a href="#" data-theme="e"  title="close" data-role="button" class="dim-close"  onload="resizeIframe(this)">X</a>
	
	<div id="isadDiv" style="    float: right; display:block;">
		promote this post<input type="checkbox" name="isad" id="isad"  >
	</div>

<link rel="stylesheet" href="../../../../../../trm-src/858483/TRENDR/a/fm.tagator.jquery.css"/>




	<div id="duration" style="display:none;    float: right;">
		<?php
			global $trs;
			$trschk=trs_get_option('trschk_general_settings');
			$placeTypes=$trschk['placetypes'];
			$trschk_location_id = xprofile_get_field_id_from_name( 'Location' );

		?>
		<input type="number" name="period_in_min" id="period_in_min" value="<?php echo $trschk['range'];?>">
	</div>

<div id="post-content" >		
<script type="text/javascript">
	$('#spotlight').change(function(){
		if($(this).prop("checked")){
			$('#category').css("display","block");
		}else{	
			$('#category').css("display","none");
		}
	});
	$('#enable_change_location').change(function(){
		if($(this).prop("checked")){
			$('.placepicker').prop("disabled",false);
		}else{	
			$('.placepicker').prop("disabled",true);	
		}
	});
	$(".placepicker").placepicker();
</script>
 <div id="post-inner" style="display: block;">	
 	<div id="spotlight-panel" style="display: block;">
 		<div id="post-store" style="width: 200px;height: 50px;display: block;padding-top: 20px;padding-left:10px; ">
 			<input type="checkbox" id="spotlight" /> <label for="scales">Post to Stories</label>
 		</div>
 		<div id="category" style="width: 90%; display: block; padding-left: 10px;">
 			<input id="activate_tagator2" width="90%" type="text" class="tagator" placeholder="Please select your category for stories..." value="" style="width:350px !important;" data-tagator-show-all-options-on-focus="true" data-tagator-autocomplete="['Resturant', 'Clubs', 'Bar']">
 			<div id="address-autocomplete" style="width: 90%;display: block;padding-left: 10px;margin-top:10px;">
	 			<input type="checkbox" id="enable_change_location" value="checked" style="margin-top:5px;" /> <label for="scales">Add  location</label><br/>
	 			<input class="placepicker form-control" id="address_location" style="width: 350px;margin-top:5px;" placeholder="Enter a location" disabled="" data-latitude-input="#latitude"
                data-longitude-input="#longitude" value="<?php echo $loc_xprof_meta['place'];?>"/>
	 			<input type="hidden" id="latitude" value="<?php echo $loc_xprof_meta['latitude'];?>"/>
	 			<input type="hidden" id="longitude" value="<?php echo $loc_xprof_meta['longitude'];?>"/>
 			</div>
 		</div>
 		
 	</div>



	<textarea  style="border:1px solid #171717;margin: auto;margin-top: 20px;margin-left: 5px;" name="field" id="field"placeholder="<?php printf( __( "What's new, %s?", 'trendr' ), trs_get_user_firstname() );?>"></textarea>

 	
			

<?php do_action( 'trs_activity_privacy_options' ); ?>
		<div id="post-controls">

</div><!-- #post-inner -->
</div><!-- #post-content -->

</div><!-- #post-intro -->
</div><!-- .dim -->	

</div><!-- .dim-inner -->	

   	</div><!-- #header -->	
    </div><!-- .container -->	
	<?php trm_nonce_field( 'post_update', '_key_post_update' ); ?>
	<?php do_action( 'trs_after_activity_post_form' ); ?>


</form><!-- #post-box -->



    </div><!-- .login-header -->	
<?php if ( trs_displayed_user_id() && ( trs_is_activity_component() ) ) do_action( 'trs_suggestions' ); ?>  




	    </header >	

		<?php do_action( 'trs_after_header' ) ?>




    </div><!-- .login-header -->	
<?php if ( trs_displayed_user_id() && ( trs_is_activity_component() ) ) do_action( 'trs_suggestions' ); ?>  





		<?php do_action( 'trs_after_header' ) ?>

	<body <?php body_class() ?>>

	<div id="site-content" class="site-content">
    <div class="container">		    	

<?php if ( trs_displayed_user_domain()) : ?>

    <div id="cover">
<?php endif; ?>

    	<?php if ( trs_displayed_user_id() && ( trs_is_activity_component() ) ) ; ?> 
<div id="contour-image">


<a href="<?php trs_displayed_user_link() ?>">

  	<?php trs_displayed_user_portrait( 'type=full' ); ?>
</a><!-- #contour-image --></div>

</div>	    <?php if (  trs_displayed_user_id() && ( trs_current_action() || trs_is_current_action( 'personal' ) ) ) do_action( 'trs_profile_views' ); ?> 
