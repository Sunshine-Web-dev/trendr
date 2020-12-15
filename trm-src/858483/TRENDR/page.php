	
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

		<?php do_action( 'trs_after_blog_page' ) ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

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



			<input type="submit" name="submit" id="submit-login" value="<?php _e('Login'); ?>" tabindex="100" />
			<input type="hidden" name="testcookie" value="1" >
		</form>
			<p></p>
            
			<br />		
	  
		</form></div><?php endif; ?>


<?php get_footer(); ?>
