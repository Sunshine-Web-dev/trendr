 <style>

 	body.home-page {	
		background:#272b35 url(../../../../../../trm-src/858483/TRENDB/a/images/discord.jpg); background-size:     cover;                      /* <------ */
    background-repeat:   no-repeat;
    background-position: center center;           margin-top:1308px;}
	@media only screen and (max-device-width: 780px) {
		body.home-page{	
		background:#1c4173;}	}	
    </style>	
<?php get_header() ?>
<html lang="en" manifest="CACHE.appcache">
	<div id="content">
    
		<div class="padder">
        
    		<?php do_action( 'trs_before_blog_page' ) ?>
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				<h2 class="pagetitle"><?php the_title(); ?></h2>
				<div class="post" id="post-<?php the_ID(); ?>">
						<?php the_content( array()); ?>
					</div>
			<?php endwhile; endif; ?>
				</div>
		</div><!-- .padder -->
	</div><!-- #content -->

		   <?php if ( is_front_page() ) : ?>

	<div class="home-page">
	    <div class="section" id="section1">
	    	<h2>Welcome to trendr!</h2>

            	<p id="login-text">
		<?php if ( trs_get_signup_allowed() ) : ?>
				<?php printf( __( ' Do you want to test drive  !?  Signup <a href="%s" title="Create an account">here</a>', 'trendr' ), site_url( TRS_REGISTER_SLUG . '/' ) ) ?>
			<?php endif; ?>
		</p>

		<form name="login-form" id="login-form" class="standard-form" action="<?php echo site_url( 'enter.php', 'login_post' ) ?>" method="post">
			<br />
<input type="text" placeholder="Username or E-mail" id="user-login" name="log" value="<?php echo isset( $_REQUEST['s'] ) ? esc_attr( $_REQUEST['s'] ) : ''; ?>" tabindex="97" />

			<br>
			<input placeholder="Password" type="password" name="pwd" id="user-pass" class="input" value="" tabindex="98" >



			<input type="submit" name="submit" id="submit" value="<?php _e('Login'); ?>" tabindex="100" />
			<input type="hidden" name="testcookie" value="1" >
		</form>
			<p></p>
            
			<br />		
	  
		</form></div><?php endif; ?>

                 
<?php get_footer(); ?>
</html>
