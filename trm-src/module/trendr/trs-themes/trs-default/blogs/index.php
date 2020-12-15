<?php

/**
 * trendr - Blogs Directory
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php get_header( 'trendr' ); ?>

	<?php do_action( 'trs_before_directory_blogs_page' ); ?>

	<div id="skeleton">
		<div class="dimension">

		<?php do_action( 'trs_before_directory_blogs' ); ?>

		<form action="" method="post" id="blogs-directory-form" class="dir-form">

			<h3><?php _e( 'Site Directory', 'trendr' ); ?><?php if ( is_user_logged_in() && trs_blog_signup_enabled() ) : ?> &nbsp;<a class="button" href="<?php echo trs_get_root_domain() . '/' . trs_get_blogs_root_slug() . '/create/' ?>"><?php _e( 'Create a Site', 'trendr' ); ?></a><?php endif; ?></h3>

			<?php do_action( 'trs_before_directory_blogs_content' ); ?>

			<div id="blog-dir-search" class="dir-search" role="search">

				<?php trs_directory_blogs_search_form(); ?>

			</div><!-- #blog-dir-search -->

			<div class="contour-select" role="navigation">
				<ul>
					<li class="selected" id="blogs-all"><a href="<?php trs_root_domain(); ?>/<?php trs_blogs_root_slug() ?>"><?php printf( __( 'All Sites <span>%s</span>', 'trendr' ), trs_get_total_blog_count() ); ?></a></li>

					<?php if ( is_user_logged_in() && trs_get_total_blog_count_for_user( trs_loggedin_user_id() ) ) : ?>

						<li id="blogs-personal"><a href="<?php echo trs_loggedin_user_domain() . trs_get_blogs_slug() ?>"><?php printf( __( 'My Sites <span>%s</span>', 'trendr' ), trs_get_total_blog_count_for_user( trs_loggedin_user_id() ) ); ?></a></li>

					<?php endif; ?>

					<?php do_action( 'trs_blogs_directory_blog_types' ); ?>

				</ul>
			</div><!-- .contour-select -->

			<div class="contour-select" id="subnav" role="navigation">
				<ul>

					<?php do_action( 'trs_blogs_directory_blog_sub_types' ); ?>

					<li id="blogs-order-select" class="last filter">

						<label for="blogs-order-by"><?php _e( 'Order By:', 'trendr' ); ?></label>
						<select id="blogs-order-by">
							<option value="active"><?php _e( 'Last Active', 'trendr' ); ?></option>
							<option value="newest"><?php _e( 'Newest', 'trendr' ); ?></option>
							<option value="alphabetical"><?php _e( 'Alphabetical', 'trendr' ); ?></option>

							<?php do_action( 'trs_blogs_directory_order_options' ); ?>

						</select>
					</li>
				</ul>
			</div>

			<div id="blogs-dir-list" class="blogs dir-list">

				<?php locate_template( array( 'blogs/blogs-loop.php' ), true ); ?>

			</div><!-- #blogs-dir-list -->

			<?php do_action( 'trs_directory_blogs_content' ); ?>

			<?php trm_nonce_field( 'directory_blogs', '_key-blogs-filter' ); ?>

			<?php do_action( 'trs_after_directory_blogs_content' ); ?>

		</form><!-- #blogs-directory-form -->

		<?php do_action( 'trs_after_directory_blogs' ); ?>

		</div><!-- .padder -->
	</div><!-- #skeleton -->

	<?php do_action( 'trs_after_directory_blogs_page' ); ?>

<?php get_sidebar( 'trendr' ); ?>
<?php get_footer( 'trendr' ); ?>
