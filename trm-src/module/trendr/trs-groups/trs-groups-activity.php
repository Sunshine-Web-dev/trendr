<?php
/**
 * trendr Groups Activity & Notification Functions
 *
 * These functions handle the recording, deleting and formatting of activity and
 * notifications for the user and for this specific component.
 *
 * @package trendr
 * @sutrsackage Groups
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function groups_register_activity_actions() {
	global $trs;

	if ( !trs_is_active( 'activity' ) )
		return false;

	trs_activity_set_action( $trs->groups->id, 'created_group',   __( 'Created a group',       'trendr' ) );
	trs_activity_set_action( $trs->groups->id, 'joined_group',    __( 'Joined a group',        'trendr' ) );
	trs_activity_set_action( $trs->groups->id, 'new_forum_topic', __( 'New group forum topic', 'trendr' ) );
	trs_activity_set_action( $trs->groups->id, 'new_forum_post',  __( 'New group forum post',  'trendr' ) );

	do_action( 'groups_register_activity_actions' );
}
add_action( 'trs_register_activity_actions', 'groups_register_activity_actions' );

function groups_record_activity( $args = '' ) {
	global $trs;

	if ( !trs_is_active( 'activity' ) )
		return false;

	// If the group is not public, hide the activity sitewide.
	if ( isset( $trs->groups->current_group->status ) && 'public' == $trs->groups->current_group->status )
		$hide_sitewide = false;
	else
		$hide_sitewide = true;

	$defaults = array (
		'id'                => false,
		'user_id'           => $trs->loggedin_user->id,
		'action'            => '',
		'content'           => '',
		'primary_link'      => '',
		'component'         => $trs->groups->id,
		'type'              => false,
		'item_id'           => false,
		'secondary_item_id' => false,
		'recorded_time'     => trs_core_current_time(),
		'hide_sitewide'     => $hide_sitewide
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r );

	return trs_activity_add( array( 'id' => $id, 'user_id' => $user_id, 'action' => $action, 'content' => $content, 'primary_link' => $primary_link, 'component' => $component, 'type' => $type, 'item_id' => $item_id, 'secondary_item_id' => $secondary_item_id, 'recorded_time' => $recorded_time, 'hide_sitewide' => $hide_sitewide ) );
}

function groups_update_last_activity( $group_id = 0 ) {
	global $trs;

	if ( !$group_id )
		$group_id = $trs->groups->current_group->id;

	if ( !$group_id )
		return false;

	groups_update_groupmeta( $group_id, 'last_activity', trs_core_current_time() );
}
add_action( 'groups_leave_group', 'groups_update_last_activity' );
add_action( 'groups_created_group', 'groups_update_last_activity' );
add_action( 'groups_new_forum_topic', 'groups_update_last_activity' );
add_action( 'groups_new_forum_topic_post', 'groups_update_last_activity' );

function groups_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
	global $trs;

	switch ( $action ) {
		case 'new_membership_request':
			$group_id = $secondary_item_id;
			$requesting_user_id = $item_id;

			$group = new TRS_Groups_Group( $group_id );
			$group_link = trs_get_group_permalink( $group );

			// Set up the string and the filter
			// Because different values are passed to the filters, we'll return the
			// values inline
			if ( (int)$total_items > 1 ) {
				$text = sprintf( __( '%1$d new membership requests for the group "%2$s"', 'trendr' ), (int)$total_items, $group->name );
				$filter = 'trs_groups_multiple_new_membership_requests_notification';
				$notification_link = $group_link . 'admin/membership-requests/?n=1';

				if ( 'string' == $format ) {
					return apply_filters( $filter, '<a href="' . $notification_link . '" title="' . __( 'Group Membership Requests', 'trendr' ) . '">' . $text . '</a>', $group_link, $total_items, $group->name, $text, $notification_link );
				} else {
					return apply_filters( $filter, array(
						'link' => $notification_link,
						'text' => $text
					), $group_link, $total_items, $group->name, $text, $notification_link );
				}
			} else {
				$user_fullname = trs_core_get_user_displayname( $requesting_user_id );
				$text = sprintf( __( '%s requests group membership', 'trendr' ), $user_fullname );
				$filter = 'trs_groups_single_new_membership_request_notification';
				$notification_link = $group_link . 'admin/membership-requests/?n=1';

				if ( 'string' == $format ) {
					return apply_filters( $filter, '<a href="' . $notification_link . '" title="' . sprintf( __( '%s requests group membership', 'trendr' ), $user_fullname ) . '">' . $text . '</a>', $group_link, $user_fullname, $group->name, $text, $notification_link );
				} else {
					return apply_filters( $filter, array(
						'link' => $notification_link,
						'text' => $text
					), $group_link, $user_fullname, $group->name, $text, $notification_link );
				}
			}

			break;

		case 'membership_request_accepted':
			$group_id = $item_id;

			$group = new TRS_Groups_Group( $group_id );
			$group_link = trs_get_group_permalink( $group );

			if ( (int)$total_items > 1 ) {
				$text = sprintf( __( '%d accepted group membership requests', 'trendr' ), (int)$total_items, $group->name );
				$filter = 'trs_groups_multiple_membership_request_accepted_notification';
				$notification_link = trs_loggedin_user_domain() . trs_get_groups_slug() . '/?n=1';

				if ( 'string' == $format ) {
					return apply_filters( $filter, '<a href="' . $notification_link . '" title="' . __( 'Groups', 'trendr' ) . '">' . $text . '</a>', $total_items, $group->name, $text, $notification_link );
				} else {
					return apply_filters( $filter, array(
						'link' => $notification_link,
						'text' => $text
					), $total_items, $group->name, $text, $notification_link );
				}
			} else {
				$text = sprintf( __( 'Membership for group "%s" accepted', 'trendr' ), $group->name );
				$filter = 'trs_groups_single_membership_request_accepted_notification';
				$notification_link = $group_link . '?n=1';

				if ( 'string' == $format ) {
					return apply_filters( $filter, '<a href="' . $notification_link . '">' . $text . '</a>', $group_link, $group->name, $text, $notification_link );
				} else {
					return apply_filters( $filter, array(
						'link' => $notification_link,
						'text' => $text
					), $group_link, $group->name, $text, $notification_link );
				}
			}

			break;

		case 'membership_request_rejected':
			$group_id = $item_id;

			$group = new TRS_Groups_Group( $group_id );
			$group_link = trs_get_group_permalink( $group );

			if ( (int)$total_items > 1 ) {
				$text = sprintf( __( '%d rejected group membership requests', 'trendr' ), (int)$total_items, $group->name );
				$filter = 'trs_groups_multiple_membership_request_rejected_notification';
				$notification_link = trs_loggedin_user_domain() . trs_get_groups_slug() . '/?n=1';

				if ( 'string' == $format ) {
					return apply_filters( $filter, '<a href="' . $notification_link . '" title="' . __( 'Groups', 'trendr' ) . '">' . $text . '</a>', $total_items, $group->name );
				} else {
					return apply_filters( $filter, array(
						'link' => $notification_link,
						'text' => $text
					), $total_items, $group->name, $text, $notification_link );
				}
			} else {
				$text = sprintf( __( 'Membership for group "%s" rejected', 'trendr' ), $group->name );
				$filter = 'trs_groups_single_membership_request_rejected_notification';
				$notification_link = $group_link . '?n=1';

				if ( 'string' == $format ) {
					return apply_filters( $filter, '<a href="' . $notification_link . '">' . $text . '</a>', $group_link, $group->name, $text, $notification_link );
				} else {
					return apply_filters( $filter, array(
						'link' => $notification_link,
						'text' => $text
					), $group_link, $group->name, $text, $notification_link );
				}
			}

			break;

		case 'member_promoted_to_admin':
			$group_id = $item_id;

			$group = new TRS_Groups_Group( $group_id );
			$group_link = trs_get_group_permalink( $group );

			if ( (int)$total_items > 1 ) {
				$text = sprintf( __( 'You were promoted to an admin in %d groups', 'trendr' ), (int)$total_items );
				$filter = 'trs_groups_multiple_member_promoted_to_admin_notification';
				$notification_link = trs_loggedin_user_domain() . trs_get_groups_slug() . '?n=1';

				if ( 'string' == $format ) {
					return apply_filters( $filter, '<a href="' . $notification_link . '" title="' . __( 'Groups', 'trendr' ) . '">' . $text . '</a>', $total_items, $text, $notification_link );
				} else {
					return apply_filters( $filter, array(
						'link' => $notification_link,
						'text' => $text
					), $total_items, $text, $notification_link );
				}
			} else {
				$text = sprintf( __( 'You were promoted to an admin in the group "%s"', 'trendr' ), $group->name );
				$filter = 'trs_groups_single_member_promoted_to_admin_notification';
				$notification_link = $group_link . '?n=1';

				if ( 'string' == $format ) {
					return apply_filters( $filter, '<a href="' . $notification_link . '">' . $text . '</a>', $group_link, $group->name, $text, $notification_link );
				} else {
					return apply_filters( $filter, array(
						'link' => $notification_link,
						'text' => $text
					), $group_link, $group->name, $text, $notification_link );
				}
			}

			break;

		case 'member_promoted_to_mod':
			$group_id = $item_id;

			$group = new TRS_Groups_Group( $group_id );
			$group_link = trs_get_group_permalink( $group );

			if ( (int)$total_items > 1 ) {
				$text = sprintf( __( 'You were promoted to a mod in %d groups', 'trendr' ), (int)$total_items );
				$filter = 'trs_groups_multiple_member_promoted_to_mod_notification';
				$notification_link = trs_loggedin_user_domain() . trs_get_groups_slug() . '?n=1';

				if ( 'string' == $format ) {
					return apply_filters( $filter, '<a href="' . $notification_link . '" title="' . __( 'Groups', 'trendr' ) . '">' . $text . '</a>', $total_items, $text, $notification_link );
				} else {
					return apply_filters( $filter, array(
						'link' => $notification_link,
						'text' => $text
					), $total_items, $text, $notification_link );
				}
			} else {
				$text = sprintf( __( 'You were promoted to a mod in the group "%s"', 'trendr' ), $group->name );
				$filter = 'trs_groups_single_member_promoted_to_mod_notification';
				$notification_link = $group_link . '?n=1';

				if ( 'string' == $format ) {
					return apply_filters( $filter, '<a href="' . $notification_link . '">' . $text . '</a>', $group_link, $group->name, $text, $notification_link );
				} else {
					return apply_filters( $filter, array(
						'link' => $notification_link,
						'text' => $text
					), $group_link, $group->name, $text, $notification_link );
				}
			}

			break;

		case 'group_invite':
			$group_id = $item_id;
			$group = new TRS_Groups_Group( $group_id );
			$group_link = trs_get_group_permalink( $group );

			$notification_link = trs_loggedin_user_domain() . trs_get_groups_slug() . '/invites/?n=1';

			if ( (int)$total_items > 1 ) {
				$text = sprintf( __( 'You have %d new group invitations', 'trendr' ), (int)$total_items );
				$filter = 'trs_groups_multiple_group_invite_notification';

				if ( 'string' == $format ) {
					return apply_filters( $filter, '<a href="' . $notification_link . '" title="' . __( 'Group Invites', 'trendr' ) . '">' . $text . '</a>', $total_items, $text, $notification_link );
				} else {
					return apply_filters( $filter, array(
						'link' => $notification_link,
						'text' => $text
					), $total_items, $text, $notification_link );
				}
			} else {
				$text = sprintf( __( 'You have an invitation to the group: %s', 'trendr' ), $group->name );
				$filter = 'trs_groups_single_group_invite_notification';

				if ( 'string' == $format ) {
					return apply_filters( $filter, '<a href="' . $notification_link . '">' . $text . '</a>', $group_link, $group->name, $text, $notification_link );
				} else {
					return apply_filters( $filter, array(
						'link' => $notification_link,
						'text' => $text
					), $group_link, $group->name, $text, $notification_link );
				}
			}

			break;
	}

	do_action( 'groups_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return false;
}
?>