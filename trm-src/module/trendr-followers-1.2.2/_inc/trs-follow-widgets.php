<?php
/**
 * TRS Follow Wodgets
 *
 * @package TRS-Follow
 * @sutrsackage Widgets
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Add a "Users I'm following" widget for the logged-in user
 *
 * @sutrsackage Widgets
 */
class TRS_Follow_Following_Widget extends TRM_Widget {
	/**
	 * Constructor.
	 */
	function __construct() {
		// Set up optional widget args
		$widget_ops = array(
			'classname'   => 'widget_trs_follow_following_widget widget trendr',
			'description' => __( "Show a list of member portraits that the logged-in user is following.", 'trs-follow' )
		);

		// Set up the widget
		parent::__construct(
			false,
			__( "(TRS Follow) Users I'm Following", 'trs-follow' ),
			$widget_ops
		);
	}

	/**
	 * Displays the widget.
	 */
	function widget( $args, $instance ) {
		// do not do anything if user isn't logged in
		if ( ! is_user_logged_in() )
			return;

		if ( empty( $instance['max_users'] ) ) {
			$instance['max_users'] = 16;
		}

		// logged-in user isn't following anyone, so stop!
		if ( ! $following = trs_get_following_ids( array( 'user_id' => trs_loggedin_user_id() ) ) ) {
			return false;
		}

		// show the users the logged-in user is following
		if ( trs_has_members( array(
			'include'         => $following,
			'max'             => $instance['max_users'],
			'populate_extras' => false,
		) ) ) {
			do_action( 'trs_before_following_widget' );

			echo $args['before_widget'];
			echo $args['before_title']
			   . $instance['title']
			   . $args['after_title'];
	?>

			<div class="portrait-block">
				<?php while ( trs_members() ) : trs_the_member(); ?>
					<div class="item-portrait">
						<a href="<?php trs_member_permalink() ?>" title="<?php trs_member_name() ?>"><?php trs_member_portrait() ?></a>
					</div>
				<?php endwhile; ?>
			</div>

			<?php echo $args['after_widget']; ?>

			<?php do_action( 'trs_after_following_widget' ); ?>

	<?php
		}
	}

	/**
	 * Callback to save widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']     = strip_tags( $new_instance['title'] );
		$instance['max_users'] = (int) $new_instance['max_users'];

		return $instance;
	}

	/**
	 * Widget settings form.
	 */
	function form( $instance ) {
		$instance = trm_parse_args( (array) $instance, array(
			'title'     => __( "Users I'm Following", 'trs-follow' ),
			'max_users' => 16
		) );
	?>

		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" /></p>

		<p><label for="trs-follow-widget-users-max"><?php _e('Max members to show:', 'trs-follow'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_users' ); ?>" name="<?php echo $this->get_field_name( 'max_users' ); ?>" type="text" value="<?php echo esc_attr( (int) $instance['max_users'] ); ?>" style="width: 30%" /></label></p>
		<p><small><?php _e( 'Note: This widget is only displayed if a member is logged in and if the logged-in user is following some users.', 'trs-follow' ); ?></small></p>

	<?php
	}
}
add_action( 'widgets_init', create_function( '', 'return register_widget("TRS_Follow_Following_Widget");' ) );
