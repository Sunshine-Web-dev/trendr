<?php

/**
 * trendr - Activity Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - trs_dtheme_object_filter()
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php do_action( 'trs_before_activity_loop' ); ?>
  	<script> 
	jQuery(document).ready(function(a){var b=/\[trs-vid\]https?:\/\/(?:www\.)?youtu(?:be\.com|\.be)\/(?:watch\?v=|v\/)?([A-Za-z0-9_\-]+)([a-zA-Z&=;_+0-9*#\-]*?)\[\/trs-vid\]/;
		var c='<div data-address="$1" class="youtube" style="background: url(https://i4.ytimg.com/vi/$1/hqdefault.jpg)"><span></span></div>';
		var d='<iframe data-address="$1" class="youtube" src="https://www.youtube.com/embed/$1?enablejsapi=1&hd=1&autohide=1&autoplay=0" frameborder="0" allowfullscreen></iframe>';
		a(".broadcast-inn").each(function(){var d=a(this);d.html(d.html().replace(b,c))});a(".broadcast-inn").delegate("div.youtube","click",function(){var b=a(this);b.replaceWith(d.replace(/\$1/g,b.attr("data-address")))})})
$(document).ready(function(){

  $('div.dropdown').each(function() {
    var $dropdown = $(this);

    $("a.dropdown-link", $dropdown).click(function(e) {
      e.preventDefault();
      $div = $("div.confirm", $dropdown);
      $div.toggle();
      $("div.confirm").not($div).hide();
      return false;
    });

});

  $('html').click(function(){
    $("div.confirm").hide();      				

  });
     
});


	</script>   	
<?php if ( trs_has_activities( trs_ajax_querystring( 'activity' ) ) ) : ?>

	<?php /* Show pagination if JS is not enabled, since the "Load More" link will do nothing */ ?>
	<noscript>
		<div class="pagination">
			<div class="pag-count"><?php trs_activity_pagination_count(); ?></div>
			<div class="pagination-links"><?php trs_activity_pagination_links(); ?></div>
		</div>
	</noscript>

	<?php if ( empty( $_POST['page'] ) ) : ?>

		<ul id="publish" class="publish-piece article-piece">

	<?php endif; ?>
<script>
    $(".openup").click(function(){
	//$('.hashtitle-e').toggle();
	//	$('.hashtags').toggle();
	//$('.hashtitle').toggle();
	//	$('.post-title h3').toggle();
	    });
</script>	
	<?php while ( trs_activities() ) : trs_the_activity(); ?>

		<?php locate_template( array( 'activity/entry.php' ), true, false ); ?>

	<?php endwhile; ?>

	<?php if ( trs_activity_has_more_items() ) : ?>

		<li class="infinite">
			<a href="#more"><?php _e( 'Infinite load', 'trendr' ); ?></a>
		</li>

	<?php endif; ?>

	<?php if ( empty( $_POST['page'] ) ) : ?>

		</ul>

	<?php endif; ?>

<?php else : ?>

	<div id="message" class="info">
		<p><?php _e( 'Sorry, there was no activity found. Please try a different filter.', 'trendr' ); ?></p>
	</div>

<?php endif; ?>

<?php do_action( 'trs_after_activity_loop' ); ?>

<form action="" name="publish-spiral" id="publish-spiral" method="post">

	<?php trm_nonce_field( 'activity_filter', '_key_activity_filter' ); ?>

</form>