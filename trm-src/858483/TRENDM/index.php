
<?php get_header() ?>

	<link rel="stylesheet" href="/sms/css/intlTelInput.css">
	<script src="/sms/js/intlTelInput.js"></script>
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
				<?php printf( __( '<a href="%s" title="Create an account">Signup.</a> By signing up, you agree to our Terms, Data Policy and Cookies Policy.', 'trendr' ), site_url( TRS_REGISTER_SLUG . '/' ) ) ?>
			<?php endif; ?>
		</p>

		<form name="login-form" id="login-form" class="standard-form" action="<?php echo site_url( 'enter.php', 'login_post' ) ?>" method="post">
			<input type="text" placeholder="Username or Phone Number" id="signup_phone" name="log" value="<?php echo isset( $_REQUEST['s'] ) ? esc_attr( $_REQUEST['s'] ) : ''; ?>" tabindex="100" />

			<input placeholder="Password" type="password" name="pwd" id="user-pass" class="input" value="" tabindex="100" >
			<input type="submit" name="submit" id="submit-login" value="<?php _e('Login'); ?>" tabindex="100" />
			<input type="hidden" name="testcookie" value="1" >
			<input type="hidden" value="1" name="area" id="area"/>
		</form>
			<p></p>

				<div class="agreement">

           </div>
			<br />	


		</form></div><?php endif; ?>
<div id="app">
	 </div>
<script type="text/javascript">
	var input = document.querySelector("#signup_phone");
	input.setAttribute("style", "width: "+document.getElementById("user-pass").offsetWidth+"px");
	window.intlTelInput(input, {			  
	   utilsScript: "/sms/js/utils.js",
	});

    $(document).on('click', '.flag-container .country-list li', function(){
        var area = $(this).attr('data-dial-code');
        $('#area').val(area);
    });
	document.getElementById("country-listbox").setAttribute("style", "color: #000");

</script>
<?php get_footer(); ?>
