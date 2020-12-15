<?php

/**
 * trendr - Activity Stream (Single Item)
 *
 * This template is used by activity-loop.php and AJAX functions to show
 * each activity.
 *
		<?php if ( 'activity_comment' == trs_get_activity_type() ) : ?>

			<div class="activity-inreplyto">
				<strong><?php _e( 'In reply to: ', 'trendr' ); ?></strong><?php trs_activity_parent_content(); ?> <a href="<?php trs_activity_thread_permalink(); ?>" class="view" title="<?php _e( 'View Thread / Permalink', 'trendr' ); ?>"><?php _e( 'View', 'trendr' ); ?></a>
			</div>

		<?php endif; ?>
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php do_action( 'trs_before_activity_entry' ); ?>

<li class="<?php trs_activity_css_class(); ?>" id="activity-<?php trs_activity_id(); ?>">

<script type="text/javascript">
       jQuery(document).ready(function(a){var b=/\[med_video\]https?:\/\/(?:www\.)?youtu(?:be\.com|\.be)\/(?:watch\?v=|v\/)?([A-Za-z0-9_\-]+)([a-zA-Z&=;_+0-9*#\-]*?)\[\/med_video\]/;
		var c='<div data-address="$1" class="youtube" style="background: url(https://i4.ytimg.com/vi/$1/hqdefault.jpg)"><span></span></div>';

		a(".broadcast-inn").each(function(){var d=a(this);d.html(d.html().replace(b,c))});a(".broadcast-inn").delegate("","",function(){var b=a(this);b.replaceWith(d.replace(/\$1/g,b.attr("")))})})
//$(document).ready(function(){
//setTimeout("imoc_init()",10);
//});

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

   </script>
	<div class="broadcast-field">

		<div class="broadcast-top">


		</div>


		<?php if ( trs_activity_has_content() ) : ?>

<?php $activity_id = trs_get_activity_id();?><?php $activity_blog_id = trs_activity_get_meta( $activities_template->activity, $activity_id); ?>
			<div class="broadcast-inn" href="<?php echo esc_url(trs_activity_get_permalink($activity_blog_id.$activity_id) ); ?>">
				<?php trs_activity_content_body(); ?>
	</div>

		<?php endif; ?>
			<div class="broadcast-knobs">

		<?php do_action( 'trs_activity_entry_content' ); ?><div  class="dropdown">   <a class="dropdown-link" href="#"></a>
			
  <div class="confirm" style="display: none;">
	<?php do_action( 'trs_activity_entry_content1' ); ?>

			<?php if ( trs_activity_user_can_delete() ) trs_activity_delete_link(); ?>

  </div>


  </div>
<a href="<?php trs_activity_user_link(); ?>"><?php trs_activity_portrait( 'type=full&width=27px&height=27px' ) ?>		</a>

<div class="post">	<?php trs_activity_action(); ?>	</div>		
		<?php if ( is_user_logged_in() ) : ?>

			<div class="broadcast-controls">
</div>
		
<?php
  if(isset($GLOBALS['featured_post'])){
	 if ( trs_get_activity_id() == $GLOBALS['featured_post']) { ?>
				<a href="#" class="button trs-primary-action fp_Recommend" id="promote-activity-<?php echo trs_get_activity_id(); ?>" >
						Recommend
				</a>

				<?php
			}
		}

				if ( trs_activity_can_comment() ) : ?>

					<a href="<?php trs_get_activity_comment_link(); ?>" class="button acomment-reply main" id="acomment-comment-<?php trs_activity_id(); ?>"><?php printf( __( 'comment <span>%s</span>', 'trendr' ), trs_activity_get_comment_count() ); ?></a>

				<?php endif; ?>

				<?php if ( trs_activity_can_favorite() ) : ?>

					<?php if ( !trs_get_activity_is_favorite() ) : ?>

						<a href="<?php trs_activity_favorite_link(); ?>" class="button fav trs-secondary-action" title="<?php esc_attr_e( 'Watch Later.', 'trendr' ); ?>"><?php _e( 'watch later', 'trendr' ) ?></a>

					<?php else : ?>

						<a href="<?php trs_activity_unfavorite_link(); ?>" class="button unfav trs-secondary-action" title="<?php esc_attr_e( 'Remove Watch.', 'trendr' ); ?>"><?php _e( 'unwatch', 'trendr' ) ?></a>

					<?php endif; ?>

				<?php endif; ?>


				<?php do_action( 'trs_activity_entry_meta' ); ?>
			</div>

		<?php endif; ?>

	</div>

	<?php do_action( 'trs_before_activity_entry_comments' ); ?>

	<?php if ( ( is_user_logged_in() && trs_activity_can_comment() ) || trs_activity_get_comment_count() ) : ?>

		<div class="activity-comments">

				<?php trs_activity_comments(); ?>

			<?php if ( is_user_logged_in() ) : ?>

				<form action="<?php trs_activity_comment_form_action(); ?>" method="post" id="ac-form-<?php trs_activity_id(); ?>" class="ac-form"<?php trs_activity_comment_form_nojs_display(); ?>>
					<div class="ac-reply-portrait"></div>
					<div class="ac-reply-content">
						<div class="ac-textarea">
							<textarea id="ac-input-<?php trs_activity_id(); ?>" class="ac-input" name="ac_input_<?php trs_activity_id(); ?>"></textarea>
						</div>
						<input id="ac-submit" type="submit" name="ac_form_submit" value="<?php _e( 'Post', 'trendr' ); ?>" /> &nbsp; <div class="ac-message"><?php _e( 'Press esc to cancel.', 'trendr' ); ?></div>
						<input type="hidden" name="comment_form_id" value="<?php trs_activity_id(); ?>" />
					</div>

					<?php do_action( 'trs_activity_entry_comments' ); ?>

					<?php trm_nonce_field( 'new_activity_comment', '_key_new_activity_comment' ); ?>

				</form>

			<?php endif; ?>

		</div>

	<?php endif; ?>

	<?php do_action( 'trs_after_activity_entry_comments' ); ?>

</li>

<?php do_action( 'trs_after_activity_entry' ); ?>
