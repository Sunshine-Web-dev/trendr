<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function groups_notification_group_updated( $group_id ) {
	global $trs;

	$group    = new TRS_Groups_Group( $group_id );
	$sitename = trm_specialchars_decode( get_blog_option( trs_get_root_blog_id(), 'blogname' ), ENT_QUOTES );
	$subject  = '[' . $sitename . '] ' . __( 'Group Details Updated', 'trendr' );

	$user_ids = TRS_Groups_Member::get_group_member_ids( $group->id );
	foreach ( (array)$user_ids as $user_id ) {
		if ( 'no' == trs_get_user_meta( $user_id, 'notification_groups_group_updated', true ) ) continue;

		$ud = trs_core_get_core_userdata( $user_id );

		// Set up and send the message
		$to = $ud->user_email;

		$group_link = site_url( trs_get_groups_root_slug(). '/' . $group->slug );
		$settings_link = trs_core_get_user_domain( $user_id ) . trs_get_settings_slug() . '/notifications/';

		$message = sprintf( __(
'Group details for the group "%1$s" were updated:

To view the group: %2$s

---------------------
', 'trendr' ), $group->name, $group_link );

		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'trendr' ), $settings_link );

		/* Send the message */
		$to      = apply_filters( 'groups_notification_group_updated_to', $to );
		$subject = apply_filters_ref_array( 'groups_notification_group_updated_subject', array( $subject, &$group ) );
		$message = apply_filters_ref_array( 'groups_notification_group_updated_message', array( $message, &$group, $group_link, $settings_link ) );

		trm_mail( $to, $subject, $message );

		unset( $message, $to );
	}

	do_action( 'trs_groups_sent_updated_email', $user_ids, $subject, '', $group_id );
}

function groups_notification_new_membership_request( $requesting_user_id, $admin_id, $group_id, $membership_id ) {
	global $trs;

	trs_core_add_notification( $requesting_user_id, $admin_id, 'groups', 'new_membership_request', $group_id );

	if ( 'no' == trs_get_user_meta( $admin_id, 'notification_groups_membership_request', true ) )
		return false;

	$requesting_user_name = trs_core_get_user_displayname( $requesting_user_id );
	$group = new TRS_Groups_Group( $group_id );

	$ud = trs_core_get_core_userdata($admin_id);
	$requesting_ud = trs_core_get_core_userdata($requesting_user_id);

	$group_requests = trs_get_group_permalink( $group ) . 'admin/membership-requests';
	$profile_link = trs_core_get_user_domain( $requesting_user_id );
	$settings_link = trs_core_get_user_domain( $requesting_user_id ) . trs_get_settings_slug() . '/notifications/';

	// Set up and send the message
	$to       = $ud->user_email;
	$sitename = trm_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	$subject  = '[' . $sitename . '] ' . sprintf( __( 'Membership request for group: %s', 'trendr' ), $group->name );

$message = sprintf( __(
'%1$s wants to join the group "%2$s".

Because you are the administrator of this group, you must either accept or reject the membership request.

To view all pending membership requests for this group, please visit:
%3$s

To view %4$s\'s profile: %5$s

---------------------
', 'trendr' ), $requesting_user_name, $group->name, $group_requests, $requesting_user_name, $profile_link );

	$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'trendr' ), $settings_link );

	/* Send the message */
	$to      = apply_filters( 'groups_notification_new_membership_request_to', $to );
	$subject = apply_filters_ref_array( 'groups_notification_new_membership_request_subject', array( $subject, &$group ) );
	$message = apply_filters_ref_array( 'groups_notification_new_membership_request_message', array( $message, &$group, $requesting_user_name, $profile_link, $group_requests, $settings_link ) );

	trm_mail( $to, $subject, $message );

	do_action( 'trs_groups_sent_membership_request_email', $admin_id, $subject, $message, $requesting_user_id, $group_id, $membership_id );
}

function groups_notification_membership_request_completed( $requesting_user_id, $group_id, $accepted = true ) {
	global $trs;

	// Post a screen notification first.
	if ( $accepted )
		trs_core_add_notification( $group_id, $requesting_user_id, 'groups', 'membership_request_accepted' );
	else
		trs_core_add_notification( $group_id, $requesting_user_id, 'groups', 'membership_request_rejected' );

	if ( 'no' == trs_get_user_meta( $requesting_user_id, 'notification_membership_request_completed', true ) )
		return false;

	$group = new TRS_Groups_Group( $group_id );

	$ud = trs_core_get_core_userdata($requesting_user_id);

	$group_link = trs_get_group_permalink( $group );
	$settings_link = trs_core_get_user_domain( $requesting_user_id ) . trs_get_settings_slug() . '/notifications/';

	// Set up and send the message
	$to       = $ud->user_email;
	$sitename = trm_specialchars_decode( get_blog_option( trs_get_root_blog_id(), 'blogname' ), ENT_QUOTES );

	if ( $accepted ) {
		$subject = '[' . $sitename . '] ' . sprintf( __( 'Membership request for group "%s" accepted', 'trendr' ), $group->name );
		$message = sprintf( __(
'Your membership request for the group "%1$s" has been accepted.

To view the group please login and visit: %2$s

---------------------
', 'trendr' ), $group->name, $group_link );

	} else {
		$subject = '[' . $sitename . '] ' . sprintf( __( 'Membership request for group "%s" rejected', 'trendr' ), $group->name );
		$message = sprintf( __(
'Your membership request for the group "%1$s" has been rejected.

To submit another request please log in and visit: %2$s

---------------------
', 'trendr' ), $group->name, $group_link );
	}

	$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'trendr' ), $settings_link );

	/* Send the message */
	$to      = apply_filters( 'groups_notification_membership_request_completed_to', $to );
	$subject = apply_filters_ref_array( 'groups_notification_membership_request_completed_subject', array( $subject, &$group ) );
	$message = apply_filters_ref_array( 'groups_notification_membership_request_completed_message', array( $message, &$group, $group_link, $settings_link ) );

	trm_mail( $to, $subject, $message );

	do_action( 'trs_groups_sent_membership_approved_email', $requesting_user_id, $subject, $message, $group_id );
}

function groups_notification_promoted_member( $user_id, $group_id ) {
	global $trs;

	if ( groups_is_user_admin( $user_id, $group_id ) ) {
		$promoted_to = __( 'an administrator', 'trendr' );
		$type = 'member_promoted_to_admin';
	} else {
		$promoted_to = __( 'a moderator', 'trendr' );
		$type = 'member_promoted_to_mod';
	}

	// Post a screen notification first.
	trs_core_add_notification( $group_id, $user_id, 'groups', $type );

	if ( 'no' == trs_get_user_meta( $user_id, 'notification_groups_admin_promotion', true ) )
		return false;

	$group = new TRS_Groups_Group( $group_id );
	$ud = trs_core_get_core_userdata($user_id);

	$group_link = trs_get_group_permalink( $group );
	$settings_link = trs_core_get_user_domain( $user_id ) . trs_get_settings_slug() . '/notifications/';

	// Set up and send the message
	$to       = $ud->user_email;
	$sitename = trm_specialchars_decode( get_blog_option( trs_get_root_blog_id(), 'blogname' ), ENT_QUOTES );
	$subject  = '[' . $sitename . '] ' . sprintf( __( 'You have been promoted in the group: "%s"', 'trendr' ), $group->name );

	$message = sprintf( __(
'You have been promoted to %1$s for the group: "%2$s".

To view the group please visit: %3$s

---------------------
', 'trendr' ), $promoted_to, $group->name, $group_link );

	$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'trendr' ), $settings_link );

	/* Send the message */
	$to      = apply_filters( 'groups_notification_promoted_member_to', $to );
	$subject = apply_filters_ref_array( 'groups_notification_promoted_member_subject', array( $subject, &$group ) );
	$message = apply_filters_ref_array( 'groups_notification_promoted_member_message', array( $message, &$group, $promoted_to, $group_link, $settings_link ) );

	trm_mail( $to, $subject, $message );

	do_action( 'trs_groups_sent_promoted_email', $user_id, $subject, $message, $group_id );
}
add_action( 'groups_promoted_member', 'groups_notification_promoted_member', 10, 2 );

function groups_notification_group_invites( &$group, &$member, $inviter_user_id ) {
	global $trs;

	$inviter_ud = trs_core_get_core_userdata( $inviter_user_id );
	$inviter_name = trs_core_get_userlink( $inviter_user_id, true, false, true );
	$inviter_link = trs_core_get_user_domain( $inviter_user_id );

	$group_link = trs_get_group_permalink( $group );

	if ( !$member->invite_sent ) {
		$invited_user_id = $member->user_id;

		// Post a screen notification first.
		trs_core_add_notification( $group->id, $invited_user_id, 'groups', 'group_invite' );

		if ( 'no' == trs_get_user_meta( $invited_user_id, 'notification_groups_invite', true ) )
			return false;

		$invited_ud = trs_core_get_core_userdata($invited_user_id);

		$settings_link = trs_core_get_user_domain( $invited_user_id ) . trs_get_settings_slug() . '/notifications/';
		$invited_link = trs_core_get_user_domain( $invited_user_id );
		$invites_link = $invited_link . trs_get_groups_slug() . '/invites';

		// Set up and send the message
		$to       = $invited_ud->user_email;
		$sitename = trm_specialchars_decode( get_blog_option( trs_get_root_blog_id(), 'blogname' ), ENT_QUOTES );
		$subject  = '[' . $sitename . '] ' . sprintf( __( 'You have an invitation to the group: "%s"', 'trendr' ), $group->name );

		$message = sprintf( __(
'One of your friends %1$s has invited you to the group: "%2$s".

To view your group invites visit: %3$s

To view the group visit: %4$s

To view %5$s\'s profile visit: %6$s

---------------------
', 'trendr' ), $inviter_name, $group->name, $invites_link, $group_link, $inviter_name, $inviter_link );

		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'trendr' ), $settings_link );

		/* Send the message */
		$to      = apply_filters( 'groups_notification_group_invites_to', $to );
		$subject = apply_filters_ref_array( 'groups_notification_group_invites_subject', array( $subject, &$group ) );
		$message = apply_filters_ref_array( 'groups_notification_group_invites_message', array( $message, &$group, $inviter_name, $inviter_link, $invites_link, $group_link, $settings_link ) );

		trm_mail( $to, $subject, $message );

		do_action( 'trs_groups_sent_invited_email', $invited_user_id, $subject, $message, $group );
	}
}

?>