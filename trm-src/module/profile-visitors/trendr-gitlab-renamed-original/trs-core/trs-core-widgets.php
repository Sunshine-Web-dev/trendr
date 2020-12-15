<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* Register widgets for the core component */
function trs_core_register_widgets() {
	add_action('widgets_init', create_function('', 'return register_widget("TRS_Core_Members_Widget");') );
	add_action('widgets_init', create_function('', 'return register_widget("TRS_Core_Whos_Online_Widget");') );
	add_action('widgets_init', create_function('', 'return register_widget("TRS_Core_Recently_Active_Widget");') );
}
add_action( 'trs_register_widgets', 'trs_core_register_widgets' );

/*** MEMBERS WIDGET *****************/

class TRS_Core_Members_Widget extends TRM_Widget {

	function trs_core_members_widget() {
		$this->__construct();
	}

	function __construct() {
		$widget_ops = array( 'description' => __( 'A dynamic list of recently active, popular, and newest members', 'trendr' ) );
		parent::__construct( false, $name = __( 'Members', 'trendr' ), $widget_ops );

		if ( is_active_widget( false, false, $this->id_base ) && !is_admin() && !is_network_admin() ) {
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
				trm_enqueue_script( 'trs_core_widget_members-js', TRS_PLUGIN_URL . '/trs-core/js/widget-members.dev.js', array( 'jquery' ), '20110723' );
			else
				trm_enqueue_script( 'trs_core_widget_members-js', TRS_PLUGIN_URL . '/trs-core/js/widget-members.js', array( 'jquery' ), '20110723' );
		}
	}

	function widget( $args, $instance ) {
		global $trs;

		extract( $args );

		if ( !$instance['member_default'] )
			$instance['member_default'] = 'active';

		echo $before_widget;
		echo $before_title
		   . $instance['title']
		   . $after_title; ?>

		<?php if ( trs_has_members( 'user_id=0&type=' . $instance['member_default'] . '&max=' . $instance['max_members'] . '&populate_extras=0' ) ) : ?>
			<div class="item-options" id="members-list-options">
				<a href="<?php echo site_url( trs_get_members_root_slug() ); ?>" id="newest-members" <?php if ( $instance['member_default'] == 'newest' ) : ?>class="selected"<?php endif; ?>><?php _e( 'Newest', 'trendr' ) ?></a>
				|  <a href="<?php echo site_url( trs_get_members_root_slug() ); ?>" id="recently-active-members" <?php if ( $instance['member_default'] == 'active' ) : ?>class="selected"<?php endif; ?>><?php _e( 'Active', 'trendr' ) ?></a>

				<?php if ( trs_is_active( 'friends' ) ) : ?>

					| <a href="<?php echo site_url( trs_get_members_root_slug() ); ?>" id="popular-members" <?php if ( $instance['member_default'] == 'popular' ) : ?>class="selected"<?php endif; ?>><?php _e( 'Popular', 'trendr' ) ?></a>

				<?php endif; ?>
			</div>

			<ul id="members-list" class="article-piece">
				<?php while ( trs_members() ) : trs_the_member(); ?>
					<li class="vcard">
						<div class="item-portrait">
							<a href="<?php trs_member_permalink() ?>" title="<?php trs_member_name() ?>"><?php trs_member_portrait() ?></a>
						</div>

						<div class="item">
							<div class="item-title fn"><a href="<?php trs_member_permalink() ?>" title="<?php trs_member_name() ?>"><?php trs_member_name() ?></a></div>
							<div class="item-meta">
								<span class="activity">
								<?php
									if ( 'newest' == $instance['member_default'] )
										trs_member_registered();
									if ( 'active' == $instance['member_default'] )
										trs_member_last_active();
									if ( 'popular' == $instance['member_default'] )
										trs_member_total_friend_count();
								?>
								</span>
							</div>
						</div>
					</li>

				<?php endwhile; ?>
			</ul>
			<?php trm_nonce_field( 'trs_core_widget_members', '_key-members' ); ?>
			<input type="hidden" name="members_widget_max" id="members_widget_max" value="<?php echo esc_attr( $instance['max_members'] ); ?>" />

		<?php else: ?>

			<div class="widget-error">
				<?php _e('No one has signed up yet!', 'trendr') ?>
			</div>

		<?php endif; ?>

		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['max_members'] = strip_tags( $new_instance['max_members'] );
		$instance['member_default'] = strip_tags( $new_instance['member_default'] );

		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'title' => __( 'Members', 'trendr' ),
			'max_members' => 5,
			'member_default' => 'active'
		);
		$instance = trm_parse_args( (array) $instance, $defaults );

		$title = strip_tags( $instance['title'] );
		$max_members = strip_tags( $instance['max_members'] );
		$member_default = strip_tags( $instance['member_default'] );
		?>

		<p><label for="trs-core-widget-title"><?php _e('Title:', 'trendr'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%" /></label></p>

		<p><label for="trs-core-widget-members-max"><?php _e('Max members to show:', 'trendr'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_members' ); ?>" name="<?php echo $this->get_field_name( 'max_members' ); ?>" type="text" value="<?php echo esc_attr( $max_members ); ?>" style="width: 30%" /></label></p>

		<p>
			<label for="trs-core-widget-groups-default"><?php _e('Default members to show:', 'trendr'); ?>
			<select name="<?php echo $this->get_field_name( 'member_default' ) ?>">
				<option value="newest" <?php if ( $member_default == 'newest' ) : ?>selected="selected"<?php endif; ?>><?php _e( 'Newest', 'trendr' ) ?></option>
				<option value="active" <?php if ( $member_default == 'active' ) : ?>selected="selected"<?php endif; ?>><?php _e( 'Active', 'trendr' ) ?></option>
				<option value="popular"  <?php if ( $member_default == 'popular' ) : ?>selected="selected"<?php endif; ?>><?php _e( 'Popular', 'trendr' ) ?></option>
			</select>
			</label>
		</p>

	<?php
	}
}

/*** WHO'S ONLINE WIDGET *****************/

class TRS_Core_Whos_Online_Widget extends TRM_Widget {

	function trs_core_whos_online_widget() {
		$this->__construct();
	}

	function __construct() {
		$widget_ops = array( 'description' => __( 'Avatars of users who are currently online', 'trendr' ) );
		parent::__construct( false, $name = __( "Who's Online Avatars", 'trendr' ), $widget_ops );
	}

	function widget($args, $instance) {
		global $trs;

	    extract( $args );

		echo $before_widget;
		echo $before_title
		   . $instance['title']
		   . $after_title; ?>

		<?php if ( trs_has_members( 'user_id=0&type=online&per_page=' . $instance['max_members'] . '&max=' . $instance['max_members'] . '&populate_extras=0' ) ) : ?>
			<div class="portrait-block">
				<?php while ( trs_members() ) : trs_the_member(); ?>
					<div class="item-portrait">
						<a href="<?php trs_member_permalink() ?>" title="<?php trs_member_name() ?>"><?php trs_member_portrait() ?></a>
					</div>
				<?php endwhile; ?>
			</div>
		<?php else: ?>

			<div class="widget-error">
				<?php _e( 'There are no users currently online', 'trendr' ) ?>
			</div>

		<?php endif; ?>

		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['max_members'] = strip_tags( $new_instance['max_members'] );

		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'title' => __( "Who's Online", 'trendr' ),
			'max_members' => 15
		);
		$instance = trm_parse_args( (array) $instance, $defaults );

		$title = strip_tags( $instance['title'] );
		$max_members = strip_tags( $instance['max_members'] );
		?>

		<p><label for="trs-core-widget-title"><?php _e('Title:', 'trendr'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%" /></label></p>

		<p><label for="trs-core-widget-members-max"><?php _e('Max Members to show:', 'trendr'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_members' ); ?>" name="<?php echo $this->get_field_name( 'max_members' ); ?>" type="text" value="<?php echo esc_attr( $max_members ); ?>" style="width: 30%" /></label></p>
	<?php
	}
}

/*** RECENTLY ACTIVE WIDGET *****************/

class TRS_Core_Recently_Active_Widget extends TRM_Widget {

	function trs_core_recently_active_widget() {
		$this->__construct();
	}

	function __construct() {
		$widget_ops = array( 'description' => __( 'Avatars of recently active members', 'trendr' ) );
		parent::__construct( false, $name = __( 'Recently Active Member Avatars', 'trendr' ), $widget_ops );
	}

	function widget($args, $instance) {
		global $trs;

		extract( $args );

		echo $before_widget;
		echo $before_title
		   . $instance['title']
		   . $after_title; ?>

		<?php if ( trs_has_members( 'user_id=0&type=active&per_page=' . $instance['max_members'] . '&max=' . $instance['max_members'] . '&populate_extras=0' ) ) : ?>
			<div class="portrait-block">
				<?php while ( trs_members() ) : trs_the_member(); ?>
					<div class="item-portrait">
						<a href="<?php trs_member_permalink() ?>" title="<?php trs_member_name() ?>"><?php trs_member_portrait() ?></a>
					</div>
				<?php endwhile; ?>
			</div>
		<?php else: ?>

			<div class="widget-error">
				<?php _e( 'There are no recently active members', 'trendr' ) ?>
			</div>

		<?php endif; ?>

		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['max_members'] = strip_tags( $new_instance['max_members'] );

		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'title' => 'Recently Active Members',
			'max_members' => 15
		);
		$instance = trm_parse_args( (array) $instance, $defaults );

		$title = strip_tags( $instance['title'] );
		$max_members = strip_tags( $instance['max_members'] );
		?>

		<p><label for="trs-core-widget-members-title"><?php _e('Title:', 'trendr'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%" /></label></p>

		<p><label for="trs-core-widget-members-max"><?php _e('Max Members to show:', 'trendr'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_members' ); ?>" name="<?php echo $this->get_field_name( 'max_members' ); ?>" type="text" value="<?php echo esc_attr( $max_members ); ?>" style="width: 30%" /></label></p>
	<?php
	}
}

/** Widget AJAX ******************/

function trs_core_ajax_widget_members() {
	global $trs;

	check_ajax_referer( 'trs_core_widget_members' );

	switch ( $_POST['filter'] ) {
		case 'newest-members':
			$type = 'newest';
			break;

		case 'recently-active-members':
			$type = 'active';
			break;

		case 'popular-members':
			if ( trs_is_active( 'friends' ) )
				$type = 'popular';
			else
				$type = 'active';

			break;
	}

	if ( trs_has_members( 'user_id=0&type=' . $type . '&per_page=' . $_POST['max-members'] . '&max=' . $_POST['max-members'] . '&populate_extras=0' ) ) : ?>
		<?php echo '0[[SPLIT]]'; // return valid result. TODO: remove this. ?>
		<div class="portrait-block">
			<?php while ( trs_members() ) : trs_the_member(); ?>
				<li class="vcard">
					<div class="item-portrait">
						<a href="<?php trs_member_permalink() ?>"><?php trs_member_portrait() ?></a>
					</div>

					<div class="item">
						<div class="item-title fn"><a href="<?php trs_member_permalink() ?>" title="<?php trs_member_name() ?>"><?php trs_member_name() ?></a></div>
						<?php if ( 'active' == $type ) : ?>
							<div class="item-meta"><span class="activity"><?php trs_member_last_active() ?></span></div>
						<?php elseif ( 'newest' == $type ) : ?>
							<div class="item-meta"><span class="activity"><?php trs_member_registered() ?></span></div>
						<?php elseif ( trs_is_active( 'friends' ) ) : ?>
							<div class="item-meta"><span class="activity"><?php trs_member_total_friend_count() ?></span></div>
						<?php endif; ?>
					</div>
				</li>

			<?php endwhile; ?>
		</div>

	<?php else: ?>
		<?php echo "-1[[SPLIT]]<li>"; ?>
		<?php _e( 'There were no members found, please try another filter.', 'trendr' ) ?>
		<?php echo "</li>"; ?>
	<?php endif;
}
add_action( 'trm_ajax_widget_members', 'trs_core_ajax_widget_members' );

?>