<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Output the friends component slug
 *
 * @package trendr
 * @sutrsackage Friends Template
 * @since 1.5
 *
 * @uses trs_get_friends_slug()
 */
function trs_friends_slug() {
	echo trs_get_friends_slug();
}
	/**
	 * Return the friends component slug
	 *
	 * @package trendr
	 * @sutrsackage Friends Template
	 * @since 1.5
	 */
	function trs_get_friends_slug() {
		global $trs;
		return apply_filters( 'trs_get_friends_slug', $trs->friends->slug );
	}

/**
 * Output the friends component root slug
 *
 * @package trendr
 * @sutrsackage Friends Template
 * @since 1.5
 *
 * @uses trs_get_friends_root_slug()
 */
function trs_friends_root_slug() {
	echo trs_get_friends_root_slug();
}
	/**
	 * Return the friends component root slug
	 *
	 * @package trendr
	 * @sutrsackage Friends Template
	 * @since 1.5
	 */
	function trs_get_friends_root_slug() {
		global $trs;
		return apply_filters( 'trs_get_friends_root_slug', $trs->friends->root_slug );
	}

/**
 * Displays Friends header tabs
 *
 * @package trendr
 * @todo Deprecate?
 */
function trs_friends_header_tabs() {
	global $trs; ?>

	<li<?php if ( !trs_action_variable( 0 ) || trs_is_action_variable( 'recently-active', 0 ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo $trs->displayed_user->domain . trs_get_friends_slug() ?>/my-friends/recently-active"><?php _e( 'Recently Active', 'trendr' ) ?></a></li>
	<li<?php if ( trs_is_action_variable( 'newest', 0 ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo $trs->displayed_user->domain . trs_get_friends_slug() ?>/my-friends/newest"><?php _e( 'Newest', 'trendr' ) ?></a></li>
	<li<?php if ( trs_is_action_variable( 'alphabetically', 0 ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo $trs->displayed_user->domain . trs_get_friends_slug() ?>/my-friends/alphabetically"><?php _e( 'Alphabetically', 'trendr' ) ?></a></li>

<?php
	do_action( 'friends_header_tabs' );
}

/**
 * Filters the title for the Friends component
 *
 * @package trendr
 * @todo Deprecate?
 */
function trs_friends_filter_title() {
	$current_filter = trs_action_variable( 0 );

	switch ( $current_filter ) {
		case 'recently-active': default:
			_e( 'Recently Active', 'trendr' );
			break;
		case 'newest':
			_e( 'Newest', 'trendr' );
			break;
		case 'alphabetically':
			_e( 'Alphabetically', 'trendr' );
			break;
	}
}

function trs_friends_random_friends() {
	global $trs;

	if ( !$friend_ids = trm_cache_get( 'friends_friend_ids_' . $trs->displayed_user->id, 'trs' ) ) {
		$friend_ids = TRS_Friends_Friendship::get_random_friends( $trs->displayed_user->id );
		trm_cache_set( 'friends_friend_ids_' . $trs->displayed_user->id, $friend_ids, 'trs' );
	} ?>

	<div class="info-group">
		<h4><?php trs_word_or_name( __( "My Friends", 'trendr' ), __( "%s's Friends", 'trendr' ) ) ?>  (<?php echo TRS_Friends_Friendship::total_friend_count( $trs->displayed_user->id ) ?>) <span><a href="<?php echo $trs->displayed_user->domain . trs_get_friends_slug() ?>"><?php _e('See All', 'trendr') ?></a></span></h4>

		<?php if ( $friend_ids ) { ?>

			<ul class="horiz-gallery">

			<?php for ( $i = 0, $count = count( $friend_ids ); $i < $count; ++$i ) { ?>

				<li>
					<a href="<?php echo trs_core_get_user_domain( $friend_ids[$i] ) ?>"><?php echo trs_core_fetch_portrait( array( 'item_id' => $friend_ids[$i], 'type' => 'thumb' ) ) ?></a>
					<h5><?php echo trs_core_get_userlink($friend_ids[$i]) ?></h5>
				</li>

			<?php } ?>

			</ul>

		<?php } else { ?>

			<div id="message" class="info">
				<p><?php trs_word_or_name( __( "You haven't added any friend connections yet.", 'trendr' ), __( "%s hasn't created any friend connections yet.", 'trendr' ) ) ?></p>
			</div>

		<?php } ?>
		<div class="clear"></div>
	</div>
<?php
}

/**
 * Pull up a group of random members, and display some profile data about them
 *
 * This function is no longer used by trendr core.
 *
 * @package trendr
 *
 * @param int $total_members The number of members to retrieve
 */
function trs_friends_random_members( $total_members = 5 ) {
	global $trs;

	if ( !$user_ids = trm_cache_get( 'friends_random_users', 'trs' ) ) {
		$user_ids = TRS_Core_User::get_users( 'random', $total_members );
		trm_cache_set( 'friends_random_users', $user_ids, 'trs' );
	}

	?>

	<?php if ( $user_ids['users'] ) { ?>

		<ul class="item-list" id="random-members-list">

		<?php for ( $i = 0, $count = count( $user_ids['users'] ); $i < $count; ++$i ) { ?>

			<li>
				<a href="<?php echo trs_core_get_user_domain( $user_ids['users'][$i]->id ) ?>"><?php echo trs_core_fetch_portrait( array( 'item_id' => $user_ids['users'][$i]->id, 'type' => 'thumb' ) ) ?></a>
				<h5><?php echo trs_core_get_userlink( $user_ids['users'][$i]->id ) ?></h5>

				<?php if ( trs_is_active( 'xprofile' ) ) { ?>

					<?php $random_data = xprofile_get_random_profile_data( $user_ids['users'][$i]->id, true ); ?>

					<div class="profile-data">
						<p class="field-name"><?php echo $random_data[0]->name ?></p>

						<?php echo $random_data[0]->value ?>

					</div>

				<?php } ?>

				<div class="action">

					<?php if ( trs_is_active( 'friends' ) ) { ?>

						<?php trs_add_friend_button( $user_ids['users'][$i]->id ) ?>

					<?php } ?>

				</div>
			</li>

		<?php } ?>

		</ul>

	<?php } else { ?>

		<div id="message" class="info">
			<p><?php _e( "There aren't enough site members to show a random sample just yet.", 'trendr' ) ?></p>
		</div>

	<?php } ?>
<?php
}

function trs_friend_search_form() {
	global $friends_template, $trs;

	$action = $trs->displayed_user->domain . trs_get_friends_slug() . '/my-friends/search/';
	$label = __( 'Filter Friends', 'trendr' ); ?>

		<form action="<?php echo $action ?>" id="friend-search-form" method="post">

			<label for="friend-search-box" id="friend-search-label"><?php echo $label ?></label>
			<input type="search" name="friend-search-box" id="friend-search-box" value="<?php echo $value ?>"<?php echo $disabled ?> />

			<?php trm_nonce_field( 'friends_search', '_key_friend_search' ) ?>

			<input type="hidden" name="initiator" id="initiator" value="<?php echo esc_attr( $trs->displayed_user->id ) ?>" />

		</form>

	<?php
}

function trs_member_add_friend_button() {
	global $members_template;

	if ( !isset( $members_template->member->is_friend ) || null === $members_template->member->is_friend )
		$friend_status = 'not_friends';
	else
		$friend_status = ( 0 == $members_template->member->is_friend ) ? 'pending' : 'is_friend';

	echo trs_get_add_friend_button( $members_template->member->id, $friend_status );
}
add_action( 'trs_directory_members_actions', 'trs_member_add_friend_button' );

function trs_member_total_friend_count() {
	global $members_template;

	echo trs_get_member_total_friend_count();
}
	function trs_get_member_total_friend_count() {
		global $members_template;

		if ( 1 == (int) $members_template->member->total_friend_count )
			return apply_filters( 'trs_get_member_total_friend_count', sprintf( __( '%d friend', 'trendr' ), (int) $members_template->member->total_friend_count ) );
		else
			return apply_filters( 'trs_get_member_total_friend_count', sprintf( __( '%d friends', 'trendr' ), (int) $members_template->member->total_friend_count ) );
	}

/**
 * trs_potential_friend_id( $user_id )
 *
 * Outputs the ID of the potential friend
 *
 * @uses trs_get_potential_friend_id()
 * @param <type> $user_id
 */
function trs_potential_friend_id( $user_id = 0 ) {
	echo trs_get_potential_friend_id( $user_id );
}
	/**
	 * trs_get_potential_friend_id( $user_id )
	 *
	 * Returns the ID of the potential friend
	 *
	 * @global object $trs
	 * @global object $friends_template
	 * @param int $user_id
	 * @return int ID of potential friend
	 */
	function trs_get_potential_friend_id( $user_id = 0 ) {
		global $trs, $friends_template;

		if ( empty( $user_id ) && isset( $friends_template->friendship->friend ) )
			$user_id = $friends_template->friendship->friend->id;
		else if ( empty( $user_id ) && !isset( $friends_template->friendship->friend ) )
			$user_id = $trs->displayed_user->id;

		return apply_filters( 'trs_get_potential_friend_id', (int)$user_id );
	}

/**
 * trs_is_friend( $user_id )
 *
 * Returns - 'is_friend', 'not_friends', 'pending'
 *
 * @global object $trs
 * @param int $potential_friend_id
 * @return string
 */
function trs_is_friend( $user_id = 0 ) {
	global $trs;

	if ( !is_user_logged_in() )
		return false;

	if ( empty( $user_id ) )
		$user_id = trs_get_potential_friend_id( $user_id );

	if ( $trs->loggedin_user->id == $user_id )
		return false;

	return apply_filters( 'trs_is_friend', friends_check_friendship_status( $trs->loggedin_user->id, $user_id ), $user_id );
}

function trs_add_friend_button( $potential_friend_id = 0, $friend_status = false ) {
	echo trs_get_add_friend_button( $potential_friend_id, $friend_status );
}
	function trs_get_add_friend_button( $potential_friend_id = 0, $friend_status = false ) {
		global $trs, $friends_template;

		if ( empty( $potential_friend_id ) )
			$potential_friend_id = trs_get_potential_friend_id( $potential_friend_id );

		$is_friend = trs_is_friend( $potential_friend_id );

		if ( empty( $is_friend ) )
			return false;

		switch ( $is_friend ) {
			case 'pending' :
				$button = array(
					'id'                => 'pending',
					'component'         => 'friends',
					'must_be_logged_in' => true,
					'block_self'        => true,
					'wrapper_class'     => 'friendship-button pending',
					'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
					'link_href'         => trailingslashit( $trs->loggedin_user->domain . trs_get_friends_slug() . '/requests' ),
					'link_text'         => __( 'Friendship Requested', 'trendr' ),
					'link_title'        => __( 'Friendship Requested', 'trendr' ),
					'link_class'        => 'friendship-button pending requested'
				);
				break;

			case 'is_friend' :
				$button = array(
					'id'                => 'is_friend',
					'component'         => 'friends',
					'must_be_logged_in' => true,
					'block_self'        => false,
					'wrapper_class'     => 'friendship-button is_friend',
					'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
					'link_href'         => trm_nonce_url( $trs->loggedin_user->domain . trs_get_friends_slug() . '/remove-friend/' . $potential_friend_id . '/', 'friends_remove_friend' ),
					'link_text'         => __( 'Cancel Friendship', 'trendr' ),
					'link_title'        => __( 'Cancel Friendship', 'trendr' ),
					'link_id'           => 'friend-' . $potential_friend_id,
					'link_rel'          => 'remove',
					'link_class'        => 'friendship-button is_friend remove'
				);
				break;

			default:
				$button = array(
					'id'                => 'not_friends',
					'component'         => 'friends',
					'must_be_logged_in' => true,
					'block_self'        => true,
					'wrapper_class'     => 'friendship-button not_friends',
					'wrapper_id'        => 'friendship-button-' . $potential_friend_id,
					'link_href'         => trm_nonce_url( $trs->loggedin_user->domain . trs_get_friends_slug() . '/add-friend/' . $potential_friend_id . '/', 'friends_add_friend' ),
					'link_text'         => __( 'Add Friend', 'trendr' ),
					'link_title'        => __( 'Add Friend', 'trendr' ),
					'link_id'           => 'friend-' . $potential_friend_id,
					'link_rel'          => 'add',
					'link_class'        => 'friendship-button not_friends add'
				);
				break;
		}

		// Filter and return the HTML button
		return trs_get_button( apply_filters( 'trs_get_add_friend_button', $button ) );
	}

function trs_get_friend_ids( $user_id = 0 ) {
	global $trs;

	if ( !$user_id )
		$user_id = ( $trs->displayed_user->id ) ? $trs->displayed_user->id : $trs->loggedin_user->id;

	$friend_ids = friends_get_friend_user_ids( $user_id );

	if ( empty( $friend_ids ) )
		return false;

	return implode( ',', friends_get_friend_user_ids( $user_id ) );
}
function trs_get_friendship_requests() {
	global $trs;

	return apply_filters( 'trs_get_friendship_requests', implode( ',', (array) friends_get_friendship_request_user_ids( $trs->loggedin_user->id ) ) );
}

function trs_friend_friendship_id() {
	echo trs_get_friend_friendship_id();
}
	function trs_get_friend_friendship_id() {
		global $members_template, $trs;

		if ( !$friendship_id = trm_cache_get( 'friendship_id_' . $members_template->member->id . '_' . $trs->loggedin_user->id ) ) {
			$friendship_id = friends_get_friendship_id( $members_template->member->id, $trs->loggedin_user->id );
			trm_cache_set( 'friendship_id_' . $members_template->member->id . '_' . $trs->loggedin_user->id, $friendship_id, 'trs' );
		}

		return apply_filters( 'trs_get_friend_friendship_id', $friendship_id );
	}

function trs_friend_accept_request_link() {
	echo trs_get_friend_accept_request_link();
}
	function trs_get_friend_accept_request_link() {
		global $members_template, $trs;

		if ( !$friendship_id = trm_cache_get( 'friendship_id_' . $members_template->member->id . '_' . $trs->loggedin_user->id ) ) {
			$friendship_id = friends_get_friendship_id( $members_template->member->id, $trs->loggedin_user->id );
			trm_cache_set( 'friendship_id_' . $members_template->member->id . '_' . $trs->loggedin_user->id, $friendship_id, 'trs' );
		}

		return apply_filters( 'trs_get_friend_accept_request_link', trm_nonce_url( $trs->loggedin_user->domain . trs_get_friends_slug() . '/requests/accept/' . $friendship_id, 'friends_accept_friendship' ) );
	}

function trs_friend_reject_request_link() {
	echo trs_get_friend_reject_request_link();
}
	function trs_get_friend_reject_request_link() {
		global $members_template, $trs;

		if ( !$friendship_id = trm_cache_get( 'friendship_id_' . $members_template->member->id . '_' . $trs->loggedin_user->id ) ) {
			$friendship_id = friends_get_friendship_id( $members_template->member->id, $trs->loggedin_user->id );
			trm_cache_set( 'friendship_id_' . $members_template->member->id . '_' . $trs->loggedin_user->id, $friendship_id, 'trs' );
		}

		return apply_filters( 'trs_get_friend_reject_request_link', trm_nonce_url( $trs->loggedin_user->domain . trs_get_friends_slug() . '/requests/reject/' . $friendship_id, 'friends_reject_friendship' ) );
	}

function trs_total_friend_count( $user_id = 0 ) {
	echo trs_get_total_friend_count( $user_id );
}
	function trs_get_total_friend_count( $user_id = 0 ) {
		return apply_filters( 'trs_get_total_friend_count', friends_get_total_friend_count( $user_id ) );
	}
	add_filter( 'trs_get_total_friend_count', 'trs_core_number_format' );

function trs_friend_total_requests_count( $user_id = 0 ) {
	echo trs_friend_get_total_requests_count( $user_id );
}
	function trs_friend_get_total_requests_count( $user_id = 0 ) {
		global $trs;

		if ( empty( $user_id ) )
			$user_id = $trs->loggedin_user->id;

		return apply_filters( 'trs_friend_get_total_requests_count', count( TRS_Friends_Friendship::get_friend_user_ids( $user_id, true ) ) );
	}

?>