<?php

/**
 * trendr - Users Blogs
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<div class="contour-select" id="subnav" role="navigation">
	<ul>

		<?php trs_get_options_nav(); ?>

		<li id="blogs-order-select" class="last filter">

			<label for="blogs-all"><?php _e( 'Order By:', 'trendr' ); ?></label>
			<select id="blogs-all">
				<option value="active"><?php _e( 'Last Active', 'trendr' ); ?></option>
				<option value="newest"><?php _e( 'Newest', 'trendr' ); ?></option>
				<option value="alphabetical"><?php _e( 'Alphabetical', 'trendr' ); ?></option>

				<?php do_action( 'trs_member_blog_order_options' ); ?>

			</select>
		</li>
	</ul>
</div><!-- .contour-select -->

<?php do_action( 'trs_before_member_blogs_content' ); ?>

<div class="blogs myblogs" role="main">

	<?php locate_template( array( 'blogs/blogs-loop.php' ), true ); ?>

</div><!-- .blogs.myblogs -->

<?php do_action( 'trs_after_member_blogs_content' ); ?>
