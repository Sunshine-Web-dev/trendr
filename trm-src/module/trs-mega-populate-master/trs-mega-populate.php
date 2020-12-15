<?php

class TRS_Mega_Populate {
	function __construct() {
		require( dirname(__FILE__) . '/lorem.php' );
		$this->lorem = new BBG_Lorem_Ipsum;

		add_action( 'init', array( &$this, 'catcher' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
	}

	function catcher() {
		if ( !empty( $_GET['trsmp_submit'] ) || !empty( $_GET['amp;trsmp_submit'] ) ) {
			$this->process();
		}
	}

	function admin_menu() {
		$page = add_submenu_page(
			'tools.php',
			'TRS Mega Populate',
			'TRS Mega Populate',
			'manage_options',
			'trs-mega-populate',
			array( &$this, 'admin_markup' )
		);
	}

	function admin_markup() {
		if ( !is_super_admin() ) {
			return;
		}

		?>

		<div class="wrap">
			<h2>TRS Mega Populate</h2>

			<form action="" method="get">
				<table class="form-table">
				<tbody>

				<tr>
					<th scope="row">
						Content type
					</th>

					<td>
						<select name="trsmp_content_type">
							<option value="activity"><?php _e( 'Activity', 'buddypress' ) ?></option>
							<option value="members"><?php _e( 'Members', 'buddypress' ) ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row">
						Total number to create
					</th>

					<td>
						<input type="text" name="trsmp_number" value="1000" />
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						Number to create per pageload
					</th>

					<td>
						<input type="text" name="trsmp_per_page" value="200" />
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						Exclude activity comments (applies only to Activity)
					</th>

					<td>
						<input type="checkbox" name="trsmp_skip_acomments" value="1" />
						<p class="description">Activity comments require a large amount of processing power (as the MPTT tree must be recalculated with each new item). Excluding them from the creation process will make it the bulk creation process a lot faster.</p> 
					</td>
				</tr>
				
				

				</tbody>
				</table>

				<br /><br />
				<?php trm_nonce_field( 'trsmp' ) ?>
				<input type="submit" value="Submit" name="trsmp_submit" />
			</form>
		</div>

		<?php
	}

	function process() {
		foreach( $_GET as $gkey => $gvalue ) {
			$clean_key = str_replace( 'amp;', '', $gkey );
			$_GET[$clean_key] = $gvalue;
		}

		if ( empty( $_GET['_key'] ) || !trm_verify_nonce( $_GET['_key'], 'trsmp' ) ) {
			trm_die( 'No.' );
		}

		$url = add_query_arg( 'page', 'trs-mega-populate', admin_url( 'tools.php' ) );
		$url = add_query_arg( 'trsmp_submit', 'Submit', $url );
		
		if ( !empty( $_GET['trsmp_skip_acomments'] ) ) {
			$url = add_query_arg( 'trsmp_skip_acomments', '1', $url );
		}
		
		$total_number = $_GET['trsmp_number'];
		$url = add_query_arg( 'trsmp_number', $total_number, $url );

		$content_type = $_GET['trsmp_content_type'] ? $_GET['trsmp_content_type'] : 'activity';
		$url = add_query_arg( 'trsmp_content_type', $content_type, $url );

		$url = trm_nonce_url( $url, 'trsmp' );

		$per_page = isset( $_GET['trsmp_per_page'] ) ? (int)$_GET['trsmp_per_page'] : 500;
		$url = add_query_arg( 'trsmp_per_page', $per_page, $url );

		$start = isset( $_GET['trsmp_start'] ) ? $_GET['trsmp_start'] : 0;
		$end   = $start + $per_page <= $total_number ? $start + $per_page : $total_number;

		if ( $start >= $total_number ) {
			return;
		}

		for ( $i = $start; $i <= $end; $i++ ) {
			switch( $content_type ) {
				case 'activity' :
					$this->create_activity();
					break;
				
				case 'members' :
					$this->create_member();
					break;
			}
		}
		echo "Processed $start through $end<br /> Processing...";

		$url = add_query_arg( 'trsmp_start', $end, $url );
		//echo $url; die();
		?>

		<script type="text/javascript">
			setTimeout( 'reload();', 1000 );
			function reload() {
				window.location = '<?php echo $url ?>';
			}
		</script>

		<?php
		die();
	}

	function create_activity() {
		global $trs;

		// get a component item that actually has activity
		$component = 'settings';
		$no_ac_comps = array( 'settings', 'messages', 'members', 'forums', 'blogs' );
		while ( in_array( $component, $no_ac_comps ) ) {
			$component = array_rand( $trs->active_components );
		}

		$type = $primary_link = $action = '';
		
		$item_id = $secondary_item_id = 0;
		
		$content = $this->lorem->generate( rand( 8, 250 ) );
		$user_id = $this->get_random_user_id();
		
		$recorded_time = $this->get_random_recorded_time();

		switch( $component ) {
			case 'activity' :
				if ( !empty( $_GET['trsmp_skip_acomments'] ) ) {
					$type = 'activity_update';
				} else {
					$types = array(	'activity_update', 'activity_comment' );
					$key   = array_rand( $types );
					$type  = $types[$key];
				}
				
				if ( $type == 'activity_comment' ) {
					$item_id = $this->get_random_activity_id();
					$secondary_item_id = $this->get_random_activity_parent( $item_id );
					$action = sprintf( __( '%s posted a new activity comment', 'buddypress' ), trs_core_get_userlink( $user_id ) );
					
				} else {					
					$from_user_link   = trs_core_get_userlink( $user_id );
					$action  = sprintf( __( '%s posted an update', 'buddypress' ), $from_user_link );					
					$primary_link = trs_core_get_userlink( $user_id, false, true );
				}

				break;

			case 'groups' :
				$types = array(	'created_group', 'joined_group', 'new_forum_post', 'new_forum_topic' );
				$key   = array_rand( $types );
				$type  = $types[$key];

				$item_id = $this->get_random_group_id();
				$group   = groups_get_group( array( 'group_id' => $item_id ) );
				$group_permalink = trs_get_group_permalink( $group );
				
				switch( $type ) {
					case 'created_group' :
						$action = sprintf( __( '%1$s created the group %2$s', 'buddypress'), trs_core_get_userlink( $user_id ), '<a href="' . $group_permalink . '">' . esc_attr( $group->name ) . '</a>' );
						$primary_link = $group_permalink;
						
						break;
					
					case 'joined_group' :
						$action = sprintf( __( '%1$s joined the group %2$s', 'buddypress'), trs_core_get_userlink( $user_id ), '<a href="' . $group_permalink . '">' . esc_attr( $group->name ) . '</a>' );
						$primary_link = $group_permalink;
						
						break;
					
					case 'new_forum_topic' :
						$topic_id = $this->get_random_topic_id();
						$topic = trs_forums_get_topic_details( $topic_id );
						
						$action = sprintf( __( '%1$s started the forum topic %2$s in the group %3$s', 'buddypress'), trs_core_get_userlink( $user_id ), '<a href="' . $group_permalink . 'forum/topic/' . $topic->topic_slug .'/">' . esc_attr( $topic->topic_title ) . '</a>', '<a href="' . $group_permalink . '">' . esc_attr( $group->name ) . '</a>' );
						
						$secondary_item_id = $topic_id;
						
						break;
					
					case 'new_forum_post' :
						$topic_id = $this->get_random_topic_id();
						$topic = trs_forums_get_topic_details( $topic_id );
						
						$action = sprintf( __( '%1$s replied to the forum topic %2$s in the group %3$s', 'buddypress'), trs_core_get_userlink( $user_id ), '<a href="' . $group_permalink . 'forum/topic/' . $topic->topic_slug .'/">' . esc_attr( $topic->topic_title ) . '</a>', '<a href="' . $group_permalink . '">' . esc_attr( $group->name ) . '</a>' );
						
						$secondary_item_id = $this->get_random_post_id( $topic_id );
						
						break;
				}


				break;

			case 'friends' :
				$type = 'friendship_created';
				$secondary_item_id = $this->get_random_user_id();
				$item_id = $this->get_random_friendship_id();
				
				$initiator_link = trs_core_get_userlink( $user_id );
				$friend_link = trs_core_get_userlink( $secondary_item_id );
				
				$action = sprintf( __( '%1$s and %2$s are now friends', 'buddypress' ), $friend_link, $initiator_link );
				
				break;

			case 'xprofile' :
				$type = 'new_member';
				
				$action = sprintf( __( '%s became a registered member', 'buddypress' ), trs_core_get_userlink( $user_id ) );
				
				break;
			
			default :
				break;

		}

		$args = array(
			'action'            => $action,
			'content'           => $content,
			'user_id'           => $user_id,
			'item_id'           => $item_id,
			'secondary_item_id' => $secondary_item_id,
			'component'         => $component,
			'type'              => $type,
			'primary_link'      => $primary_link,
			'recorded_time'     => $recorded_time
		);
		
		trs_activity_add( $args );
	}
	
	function create_member() {
		// using 3 names for a better shot at uniqueness
		$name = $this->lorem->generate( 3 );
		
		$name_safe = $name_safe_unique = sanitize_user( $name );
		$append = 1;
		while ( username_exists( $name_safe_unique ) ) {
			$name_safe_unique = $name_safe . $append;
			$append++;
		}
		
		$email = $name_safe_unique . '@not.a.real.domain.local';
		
		$args = array(
			'user_login'   => $name_safe_unique,
			'user_pass'    => 'password',
			'display_name' => ucwords( $name ),
			'user_email'   => $email
		);
		
		$user_id = trm_insert_user( $args );
		
		if ( !$user_id || is_trm_error( $user_id ) )
			return;
		
		// set a dummy last_activity so they show up in directories
		trs_update_user_meta( $user_id, 'last_activity', $this->get_random_recorded_time() );
		
		// record a fullname
		xprofile_set_field_data( 1, $user_id, ucwords( $name ) );
		
		// todo: xprofile
	}
	
	function get_random_recorded_time() {
		// Pick a date, any date
		$now = time();
		$timestamp = rand( 0, $now );
		return date( 'Y-m-d H:i:s', $timestamp );
	}

	function get_random_user_id() {
		global $trmdb;

		return $trmdb->get_var( $trmdb->prepare( "SELECT ID FROM $trmdb->users ORDER BY RAND() LIMIT 1" ) );
	}
	
	/**
	 * Returns only root-level items
	 */
	function get_random_activity_id() {
		global $trmdb, $trs;
		
		return $trmdb->get_var( $trmdb->prepare( "SELECT ID FROM {$trs->activity->table_name} WHERE ( component != 'activity' OR item_id = '' OR item_id = 0 ) ORDER BY RAND() LIMIT 1" ) );
	}
	
	/**
	 * Select a random activity item from among the tree of a given root-level item
	 */
	function get_random_activity_parent( $activity_id ) {
		global $trmdb, $trs;
		
		return $trmdb->get_var( $trmdb->prepare( "SELECT ID FROM {$trs->activity->table_name} WHERE ( id = {$activity_id} OR ( type = 'new_activity_comment' AND item_id = {$activity_id} ) ) ORDER BY RAND() LIMIT 1" ) );
	}
	
	function get_random_group_id() {
		global $trmdb, $trs;

		return $trmdb->get_var( $trmdb->prepare( "SELECT id FROM {$trs->groups->table_name} ORDER BY RAND() LIMIT 1" ) );
	}
	
	function get_random_topic_id() {
		global $trmdb, $trs, $bbdb;

		do_action( 'btrsress_init' );

		return $trmdb->get_var( $trmdb->prepare( "SELECT topic_id FROM {$bbdb->topics} ORDER BY RAND() LIMIT 1" ) );
	}
	
	function get_random_post_id( $topic_id = 0 ) {
		global $trmdb, $bbdb;
		
		do_action( 'btrsress_init' );
		
		if ( $topic_id ) {
			$sql = $trmdb->prepare( "SELECT post_id FROM {$bbdb->posts} WHERE topic_id = {$topic_id} ORDER BY RAND() LIMIT 1" );
		} else {
			$sql = $trmdb->prepare( "SELECT post_id FROM {$bbdb->posts} ORDER BY RAND() LIMIT 1" );
		}
		
		return $trmdb->get_var( $sql );
	}
	
	function get_random_friendship_id() {
		global $trmdb, $trs;
		
		return $trmdb->get_var( $trmdb->prepare( "SELECT id FROM {$trs->friends->table_name} ORDER BY RAND() LIMIT 1" ) );
	}
}
new TRS_Mega_Populate;

?>