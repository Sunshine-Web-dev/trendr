
<?php get_header() ?>	<div ><img id="front-title"src="../../../../../../trm-src/858483/TRENDR/a/images/front-page-top3.png"    ></div>
	<div id="skeleton"">
		<div class="dimension">

		<?php do_action( 'trs_before_blog_page' ) ?>

		<div class="page" id="static-page" role="main">

			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

				<h2 class="pagetitle"></h2>

				<div id="post" >

					<div class="entry">


					
					</div>

				</div>

	
			<?php endwhile; endif; ?>

		</div><!-- .page -->

		<?php do_action( 'trs_after_blog_page' ) ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

		   <?php if ( is_front_page() ) : ?>

	<div class="home-page">

            	<p id="login-text">
		<?php if ( trs_get_signup_allowed() ) : ?>
				<?php printf( __( ' Do you want to test drive  !?   <a href="%s" title="Create an account">Signup&nbsp;&nbsp;&nbsp;&nbsp;</a> By signing up, you agree to our Terms, Data Policy and Cookies Policy.', 'trendr' ), site_url( TRS_REGISTER_SLUG . '/' ) ) ?>
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

				<div class="agreement">

           </div>
			<br />	
			<img id="front-middle" src="../../../../../../trm-src/858483/TRENDR/a/images/5e238d27e25b55f27b1dafac4c2aaf78-v3.png" style=""  >	
		<img id="front-bottom" src="../../../../../../trm-src/858483/TRENDR/a/images/front-page-bottom15.png" style=""  >
	
		</form></div><?php endif; ?>
<div id="app">
<img id="app-badges" src="../../../../../../trm-src/858483/TRENDR/a/images/app-stores.png"style="">
	 </div>

<?php get_footer(); ?>
