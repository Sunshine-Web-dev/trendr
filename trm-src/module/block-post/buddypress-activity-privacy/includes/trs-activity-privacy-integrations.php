<?php
/**
 * TRS Activity Privacy Integrations with others plugins 
 *  
 * @package TRS-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Integration with Buddypress Followers
if( function_exists('trs_follow_is_following') ) {

	add_filter('trs_more_visibility_activity_filter', 'trs_follow_visibility_activity', 10, 3);
	function trs_follow_visibility_activity($remove_from_stream, $visibility, $activity){
		$trs_loggedin_user_id = trs_loggedin_user_id();

		switch ($visibility) {
			case 'followers':
				$args = array(
					'leader_id'   => $activity->user_id,
					'follower_id' => $trs_loggedin_user_id
				);
				$is_following = trs_follow_is_following($args);

				if( !$is_following ) 
					$remove_from_stream = true;
				break;
			case 'groupfollowers' :
				$args = array(
					'leader_id'   => $activity->user_id,
					'follower_id' => $trs_loggedin_user_id
				);
				$is_following = trs_follow_is_following($args);

				$group_is_user_member = groups_is_user_member( $trs_loggedin_user_id, $activity->item_id );

	            if( !$is_following || !$group_is_user_member)
	                $remove_from_stream = true;

				# code...
			default:
				# code...
				break;
		}		



		return $remove_from_stream;
	}

	// @TODO this code should be removed  since the privacy levers are managed from admins
	//add_filter('trs_profile_activity_privacy_levels_filter', 'trs_get_profile__follow_activity_privacy_levels', 10, 1);
	function trs_get_profile__follow_activity_privacy_levels($profile_activity_privacy_levels){
		$profile_activity_privacy_levels [] = 'followers';

		return $profile_activity_privacy_levels;
	}

	// @TODO this code should be removed  since the privacy levers are managed from admins
	//add_filter('trs_groups_activity_privacy_levels_filter', 'trs_get_profile__follow_groups_privacy_levels', 10, 1);
	function trs_get_profile__follow_groups_privacy_levels($groups_activity_privacy_levels){
		$groups_activity_privacy_levels [] = 'followers';
		//followers in the group
		$groups_activity_privacy_levels [] = 'groupfollowers';

		return $groups_activity_privacy_levels;
	}

	// @TODO this code should be removed  since the privacy levers are managed from admins
	//add_filter('trs_profile_activity_visibility_levels_filter', 'trs_get_profile_follow_activity_visibility_levels', 10, 1);
	function trs_get_profile_follow_activity_visibility_levels($profile_activity_visibility_levels){
		$profile_activity_visibility_levels ['follow'] = array(
		        'id'      => 'followers',
		        'label'   => __( 'My Followers', 'trs-activity-privacy' ),
		        'default' => false,
		        'position' => 30
		);

		return $profile_activity_visibility_levels;
	}

	// @TODO this code should be removed  since the privacy levers are managed from admins
	//add_filter('trs_groups_activity_visibility_levels_filter', 'trs_get_groups_follow_activity_visibility_levels', 10, 1);
	function trs_get_groups_follow_activity_visibility_levels($groups_activity_visibility_levels){
		$groups_activity_visibility_levels ['followers'] = array(
		        'id'      => 'followers',
		        'label'   => __( 'My Followers', 'trs-activity-privacy' ),
		        'default' => false,
		        'position' => 35
		);
		$groups_activity_visibility_levels ['groupfollowers'] = array(
		        'id'      => 'groupfollowers',
		        'label'   => __( 'My Followers in Group', 'trs-activity-privacy' ),
		        'default' => false,
		        'position' => 45
		);

		return $groups_activity_visibility_levels;
	}
}

// Fix/Integration with Buddypress Activity Plus
if( function_exists('med_plugin_init') ) {

	add_action( 'trm_footer', 'trs_activity_privacy_fix_trs_activity_plus' );
	function trs_activity_privacy_fix_trs_activity_plus() {
	?>
	<script type="text/javascript">

	if ( typeof jq == "undefined" )
		var jq = jQuery;

	jq(document).ready( function() {

		form = jq("#post-intro");
		text = form.find('textarea[name="field"]');
		//remove event handler previously attached to #med_submit
		 try {
			jq("#med_submit").die( "click" );
		 } catch(e) {
			jq("#med_submit").off( "click");
		 }

		jq(document).delegate("#med_submit", 'click', function (event) {

			event.preventDefault();
			var params = _medActiveHandler.get();
			var group_id = jq('#whats-new-post-in').length ?jq('#whats-new-post-in').val() : 0;
			
			jq.post(ajaxurl, {
				"action": "med_update_activity_contents", 
				"data": params, 
				// add visibility level to the ajax post
				"visibility" : jq("select#activity-privacy").val(),
				"content": text.val(), 
				"group_id": group_id,
				'period_in_min':jq('#period_in_min').val(),

			}, function (data) {
				_medActiveHandler.destroy();
				text.val('');

				jq('#period_in_min').val(0);
				jq('#publish').prepend(data.activity);
				/**
				 * Handle image scaling in previews.
				 */
				jq(".med_final_link img").each(function () {
					jq(this).width(jq(this).parents('div').width());
				});

				//reset the privacy selection
				jq("select#activity-privacy option[selected]").prop('selected', true).trigger('change');
				<?php if( trs_ap_is_use_custom_styled_selectbox() ) { ?>
				jq('select.trs-ap-selectbox').customStyle('2');
				<?php } ?>
			});
		});
	});

	</script>
	<?php 
	}
	
}