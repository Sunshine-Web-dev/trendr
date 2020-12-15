<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ) ?>; charset=<?php bloginfo( 'charset' ) ?>" />
		<title><?php trm_title( '|', true, 'right' ); bloginfo( 'name' ); ?></title>

		<?php do_action( 'trs_head' ) ?>

		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ) ?>" />

		<?php
			if ( is_singular() && trs_is_blog_page() && get_option( 'thread_comments' ) )
				trm_enqueue_script( 'comment-reply' );

			trm_head();
		?>
	</head>

	<body <?php body_class() ?> id="trs-default">

		<?php do_action( 'trs_before_header' ) ?>

		<div id="header">
			<div id="search-bar" role="search">
				<div class="dimension">
					<h1 id="logo" role="banner"><a href="<?php echo home_url(); ?>" title="<?php _ex( 'Home', 'Home page banner link title', 'trendr' ); ?>"><?php trs_site_name(); ?></a></h1>

						<form action="<?php echo trs_search_form_action() ?>" method="post" id="search-form">
							<label for="search-terms" class="accessibly-hidden"><?php _e( 'Search for:', 'trendr' ); ?></label>
							<input type="text" id="search-terms" name="search-terms" value="<?php echo isset( $_REQUEST['s'] ) ? esc_attr( $_REQUEST['s'] ) : ''; ?>" />

							<?php echo trs_search_form_type_select() ?>

							<input type="submit" name="search-submit" id="search-submit" value="<?php _e( 'Search', 'trendr' ) ?>" />

							<?php trm_nonce_field( 'trs_search_form' ) ?>

						</form><!-- #search-form -->

				<?php do_action( 'trs_search_login_bar' ) ?>

				</div><!-- .dimension -->
			</div><!-- #search-bar -->

			

			<?php do_action( 'trs_header' ) ?>

		</div><!-- #header -->

		<?php do_action( 'trs_after_header' ) ?>
		<?php do_action( 'trs_before_container' ) ?>

		<div id="container">
