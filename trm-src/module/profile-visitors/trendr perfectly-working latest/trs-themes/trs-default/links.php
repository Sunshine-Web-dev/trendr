<?php
/*
Template Name: Links
*/
?>

<?php get_header() ?>

	<div id="skeleton">
		<div class="dimension">

		<?php do_action( 'trs_before_blog_links' ) ?>

		<div class="page" id="blog-latest" role="main">

			<h2 class="pagetitle"><?php _e( 'Links', 'trendr' ) ?></h2>

			<ul id="links-list">
				<?php trm_list_bookmarks(); ?>
			</ul>

		</div>

		<?php do_action( 'trs_after_blog_links' ) ?>

		</div>
	</div>

<?php get_footer(); ?>
