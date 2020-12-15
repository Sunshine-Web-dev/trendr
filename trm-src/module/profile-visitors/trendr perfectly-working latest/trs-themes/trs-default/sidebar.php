<?php do_action( 'trs_before_sidebar' ) ?>

<div id="sidebar" role="complementary">
	<div class="dimension">

	<?php do_action( 'trs_inside_before_sidebar' ) ?>

	<?php if ( is_user_logged_in() ) : ?>

		<?php do_action( 'trs_before_sidebar_me' ) ?>

		<div id="sidebar-me">
			<a href="<?php echo trs_loggedin_user_domain() ?>">
				<?php trs_loggedin_user_portrait( 'type=thumb&width=40&height=40' ) ?>
			</a>

			<h4><?php echo trs_core_get_userlink( trs_loggedin_user_id() ); ?></h4>
			<a class="button logout" href="<?php echo trm_logout_url( trs_get_root_domain() ) ?>"><?php _e( 'Log Out', 'trendr' ) ?></a>

			<?php do_action( 'trs_sidebar_me' ) ?>
		</div>

		<?php do_action( 'trs_after_sidebar_me' ) ?>

		<?php if ( trs_is_active( 'messages' ) ) : ?>
			<?php trs_message_get_notices(); /* Site wide notices to all users */ ?>
		<?php endif; ?>

	<?php else : ?>

		<?php do_action( 'trs_before_sidebar_login_form' ) ?>

		<?php if ( trs_get_signup_allowed() ) : ?>
		
			<p id="login-text">

				<?php printf( __( 'Please <a href="%s" title="Create an account">create an account</a> to get started.', 'trendr' ), site_url( trs_get_signup_slug() . '/' ) ) ?>

			</p>

		<?php endif; ?>

		<form name="login-form" id="sidebar-login-form" class="standard-form" action="<?php echo site_url( 'enter.php', 'login_post' ) ?>" method="post">
			<label><?php _e( 'Username', 'trendr' ) ?><br />
			<input type="text" name="log" id="sidebar-user-login" class="input" value="<?php if ( isset( $user_login) ) echo esc_attr(stripslashes($user_login)); ?>" tabindex="97" /></label>

			<label><?php _e( 'Password', 'trendr' ) ?><br />
			<input type="password" name="pwd" id="sidebar-user-pass" class="input" value="" tabindex="98" /></label>

			<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="sidebar-rememberme" value="forever" tabindex="99" /> <?php _e( 'Remember Me', 'trendr' ) ?></label></p>

			<?php do_action( 'trs_sidebar_login_form' ) ?>
			<input type="submit" name="trm-submit" id="sidebar-trm-submit" value="<?php _e( 'Log In', 'trendr' ); ?>" tabindex="100" />
			<input type="hidden" name="testcookie" value="1" />
		</form>

		<?php do_action( 'trs_after_sidebar_login_form' ) ?>

	<?php endif; ?>

	<?php /* Show forum tags on the forums directory */
	if ( trs_is_active( 'forums' ) && trs_is_forums_component() && trs_is_directory() ) : ?>
		<div id="forum-directory-tags" class="widget tags">
			<h3 class="widgettitle"><?php _e( 'Forum Topic Tags', 'trendr' ) ?></h3>
			<div id="tag-text"><?php trs_forums_tag_heat_map(); ?></div>
		</div>
	<?php endif; ?>

	<?php dynamic_sidebar( 'sidebar-1' ) ?>

	<?php do_action( 'trs_inside_after_sidebar' ) ?>

	</div><!-- .dimension -->
</div><!-- #sidebar -->

<?php do_action( 'trs_after_sidebar' ) ?>
