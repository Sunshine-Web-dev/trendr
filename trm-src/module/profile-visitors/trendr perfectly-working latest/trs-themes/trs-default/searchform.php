<?php do_action( 'trs_before_blog_search_form' ) ?>

<form role="search" method="get" id="searchform" action="<?php echo home_url() ?>/">
	<input type="text" value="<?php the_search_query(); ?>" name="s" id="s" />
	<input type="submit" id="searchsubmit" value="<?php _e( 'Search', 'trendr' ) ?>" />

	<?php do_action( 'trs_blog_search_form' ) ?>
</form>

<?php do_action( 'trs_after_blog_search_form' ) ?>
