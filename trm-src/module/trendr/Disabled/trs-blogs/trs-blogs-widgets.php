<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/***
 * The recent blogs widget is actually just the activity feed filtered on "new_blog_post".
 * Why not make some of your own widgets using a filtered activity stream?
 */

function trs_blogs_register_widgets() {
	global $trmdb, $trs;

	if ( trs_is_active( 'activity' ) && (int)$trmdb->blogid == trs_get_root_blog_id() )
		add_action('widgets_init', create_function('', 'return register_widget("TRS_Blogs_Recent_Posts_Widget");') );
}
add_action( 'trs_register_widgets', 'trs_blogs_register_widgets' );

class TRS_Blogs_Recent_Posts_Widget extends TRM_Widget {

	function trs_blogs_recent_posts_widget() {
		$this->__construct();
	}

	function __construct() {
		parent::__construct( false, $name = __( 'Recent Networkwide Posts', 'trendr' ) );
	}

	function widget($args, $instance) {
		global $trs;

		extract( $args );

		echo $before_widget;
		echo $before_title . $widget_name . $after_title;

		if ( empty( $instance['max_posts'] ) || !$instance['max_posts'] )
			$instance['max_posts'] = 10; ?>

		<?php /* Override some of the contextually set parameters for trs_has_activities() */ ?>
		<?php if ( trs_has_activities( array( 'action' => 'new_blog_post', 'max' => $instance['max_posts'], 'per_page' => $instance['max_posts'], 'user_id' => 0, 'scope' => false, 'object' => false, 'primary_id' => false ) ) ) : ?>

			<ul id="blog-post-list" class="activity-list item-list">

				<?php while ( trs_activities() ) : trs_the_activity(); ?>

					<li>
						<div class="activity-content" style="margin: 0">

							<div class="activity-header">
								<?php trs_activity_action() ?>
							</div>

							<?php if ( trs_get_activity_content_body() ) : ?>
								<div class="activity-inner">
									<?php trs_activity_content_body() ?>
								</div>
							<?php endif; ?>

						</div>
					</li>

				<?php endwhile; ?>

			</ul>

		<?php else : ?>
			<div id="message" class="info">
				<p><?php _e( 'Sorry, there were no posts found. Why not write one?', 'trendr' ) ?></p>
			</div>
		<?php endif; ?>

		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['max_posts'] = strip_tags( $new_instance['max_posts'] );

		return $instance;
	}

	function form( $instance ) {
		$instance = trm_parse_args( (array) $instance, array( 'max_posts' => 10 ) );
		$max_posts = strip_tags( $instance['max_posts'] );
		?>

		<p><label for="trs-blogs-widget-posts-max"><?php _e('Max posts to show:', 'trendr'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_posts' ); ?>" name="<?php echo $this->get_field_name( 'max_posts' ); ?>" type="text" value="<?php echo esc_attr( $max_posts ); ?>" style="width: 30%" /></label></p>
	<?php
	}
}
?>