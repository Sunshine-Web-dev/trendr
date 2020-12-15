
<?php get_header() ?>	<img id="front-title"src="../../../../../../trm-src/858483/TRENDR/a/images/intro.png"    >

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

	<div class="front-page">

            	<p id="login-text"  >
		<?php if ( trs_get_signup_allowed() ) : ?>
				<?php printf( __( '<a href="%s" title="Create an account">Welcome to trendr! Since you came here, why not Signup.</a> By signing up, you agree to our Terms, Data Policy and Cookies Policy.', 'trendr' ), site_url( TRS_REGISTER_SLUG . '/' ) ) ?>
			<?php endif; ?>
		</p>

		<form name="login-form" id="login-form" class="standard-form" action="<?php echo site_url( 'enter.php', 'login_post' ) ?>" method="post">
			<br />
<input type="text" placeholder="Username or E-mail" id="user-login" name="log" value="<?php echo isset( $_REQUEST['s'] ) ? esc_attr( $_REQUEST['s'] ) : ''; ?>" tabindex="100" />

			<br>
			<input placeholder="Password" type="password" name="pwd" id="user-pass" class="input" value="" tabindex="100" >



			<input type="submit" name="submit" id="submit-login" value="<?php _e('Login'); ?>" tabindex="100" />
			<input type="hidden" name="testcookie" value="1" >
		</form>
			<p></p>

				<div class="agreement">

           </div>
			<br />	
			<img id="front-middle" src="" style=""  >	
			<img id="front-bottom" src="../../../../../../trm-src/858483/TRENDR/a/images/trendr-big-latestv7.png" style=""  >
	<img id="app-badges" src="../../../../../../trm-src/858483/TRENDR/a/images/app-stores-1.png"style="">

		</form></div><?php endif; ?>
<div id="app">
	 </div>

<?php get_footer(); ?>
