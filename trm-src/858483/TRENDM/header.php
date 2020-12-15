<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>


	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ) ?>; charset=<?php bloginfo( 'charset' ) ?>" />
		<title><?php trm_title( '|', true, 'right' ); bloginfo( 'name' ); ?></title>

 <link rel='stylesheet' type='text/css' href='../../../../../../trm-src/858483/TRENDM/a/src-app.css' />
  

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
		
		
<style>




</style>
</head>

<body>

    <header id="site-header" role="banner" >
<iframe name="blah" id="blah" class="blah" >
</iframe>
		<div id="header" >
		<div class="container">
 <h1 class="site-title">			
<a  href="/activity" rel="home"  class="site-title">trendr</a>	




		</h0>	</div><div class="container">


					
<ul class="head-refine">





     <nav class="menu">	


  <?php endif; ?>

	 						<div id="in" >	
                      <?php do_action( 'trs_before_header' ) ?> <?php if ( ( is_user_logged_in()  ) ) : ?>
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


		
		</div> 


		</div><!-- #search-bar -->	



          	  <div id="profile-bar"  >
	  			<?php do_action( 'trs_before_member_header' ); ?>

</div>
  		   <?php if (  !is_user_logged_in() ) : ?>	

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

 <form action="<?php trs_activity_post_form_action(); ?>" method="post" id="post-box" name="post-box" role="complementary">

	<?php do_action( 'trs_before_activity_post_form' ); ?>

<div id="post-intro">
 

	
	<div id="isadDiv" style="    ">
		promote this post<input type="checkbox" name="isad" id="isad"  >
	</div>

<link rel="stylesheet" href="../../../../../../trm-src/858483/TRENDR/a/fm.tagator.jquery.css"/>


<script src="../../../../../../trm-src/858483/TRENDR/a/fm.tagator.jquery.js"></script>
<script src="../../../../../../trm-src/858483/TRENDR/a/jquery.placepicker.js"></script>

<div class="dim">	
  
<div class="dim-inner">	

	
	<div id="duration" style="display:none;    float: right;">
		<?php
			global $trs;
			$trschk=trs_get_option('trschk_general_settings');
			$placeTypes=$trschk['placetypes'];
			$trschk_location_id = xprofile_get_field_id_from_name( 'Location' );

		?>
		<input type="number" name="period_in_min" id="period_in_min" value="<?php echo $trschk['range'];?>">
	</div>

 <div id="post-inner" style="display: block;">
<script>
//getLocation();
</script> 
 	<div id="spotlight-panel" style="display: block;">
 		<div id="post-store" style="width: 200px;height: 50px;display: block;padding-top: 20px;padding-left:10px; ">
 			<input type="checkbox" id="spotlight" /> <label for="scales">Post to Spotlights</label>
 		</div>
 		<div id="category" style="width: 90%; display: block; padding-left: 10px;">
			
 			<input id="activate_tagator2" width="90%" type="text" class="tagator" placeholder="Please select your category for spotlights..." value="" style="width:350px !important;" data-tagator-show-all-options-on-focus="true" data-tagator-autocomplete="['Resturant', 'Clubs', 'Bar']">
			
			<!-- <div id="fetched Locaiton"> -->
			<!-- <input type = "text" id="Location-Box" style="width: 350px;margin-top:5px;" placeholder="" disabled=""/> -->
			<!-- </div> -->
 			<div id="address-autocomplete" style="width: 90%;display: block;padding-left: 10px;margin-top:10px;">
			<!-- <button onclick="getLocation();return false;">Add Location</button> -->
			    
	 			<input type="checkbox" id="enable_change_location" value="checked" style="margin-top:5px;" /> <label for="scales">Add  location manually</label><br/>
	 			<input class="placepicker form-control" id="address_location" style="" placeholder="Enter a location" disabled="" data-latitude-input="#latitude"
                data-longitude-input="#longitude" value="<?php echo $loc_xprof_meta['place'];?>"/>
				<button class="open-button" onclick="openForm();return false;">Add Location</button>
				<!--<select id="select" onchange="changeValue();return false;" onfocus="this.selectedIndex = -1;">
				<option value="Don't Post Location">Don't Post Location</option>
				</select> -->
	 			<input type="hidden" id="latitude" value="<?php echo $loc_xprof_meta['latitude'];?>"/>
	 			<input type="hidden" id="longitude" value="<?php echo $loc_xprof_meta['longitude'];?>"/>
 			</div>
 		</div>
 		
 	</div>

	<div class="form-popup" id="myForm">
		<form action="/action_page.php" class="form-container">
		<h1> Select Your Location </h1>
		<select id="select" data-native-menu="false" data-tagator-show-all-options-on-focus="true" style="" size = "70" onchange="changeValue();closeForm();return false;" onclick="focusMethod(); onfocus="this.selectedIndex = -1;">
				<option value="Don't Post Location">Don't Post Location</option>
		</select>
		<button type="button" style="" class="btncancel" onclick="closeForm()"><</button>
		</form>
	</div>

	<textarea  style="border:1px solid #171717;margin: auto;margin-top: 20px;margin-left: 5px;" name="field" id="field"placeholder="<?php printf( __( "What's new, %s?", 'trendr' ), trs_get_user_firstname() );?>"></textarea>

 	
			


<?php do_action( 'trs_activity_privacy_options' ); ?>
		<div id="post-controls">

</div><!-- #post-controls -->
</div><!-- #post-inner -->
</div><!-- #post-content -->
              <div id="post-submit">
				<input type="submit" name="submit-post" id="submit-post" value="<?php _e( 'Post Update', 'trendr' ); ?>" />
			</div><!-- #post-submit -->

</div><!-- #post-intro -->
</div><!-- .dim -->	

</div><!-- .dim-inner -->

   	</div><!-- #header -->	
    </div><!-- .container -->	
	<?php trm_nonce_field( 'post_update', '_key_post_update' ); ?>
	<?php do_action( 'trs_after_activity_post_form' ); ?>


</form><!-- #post-box -->



	<div id="site-content" class="site-content">


    <div id="portrait-page">		    	


    </div><!-- #potrait-upload -->
					


    <div class="container">		    	
<?php if ( is_user_logged_in() ) : ?>
  <form action="" id="turkey-upload-and-crop" class="standard-form" enctype="multipart/form-data">

            <?php trm_nonce_field( 'trs_portrait_upload' ) ?>

            <p id="turkey-portrait-upload">
                <input type="file" name="turkey-file" id="turkey-file" />
                <input type="submit" name="tukey-upload" id="tukey-upload" value="<?php _e( 'Save', 'trendr' ) ?>" />
                <input type="hidden" name="tukey-action" id="turkey-action" value="trs_portrait_upload" />
            </p>
    </form>
  <?php endif; ?>


</html>