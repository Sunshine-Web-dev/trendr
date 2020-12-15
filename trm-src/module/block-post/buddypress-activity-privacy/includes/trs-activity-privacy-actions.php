<?php
/**
 * Buddypress Activity Privacy actions
 *
 * @package TRS-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Add visibility level to user activity meta
 * @param  [type] $content     [description]
 * @param  [type] $user_id     [description]
 * @param  [type] $activity_id [description]
 * @return [type]              [description]
 */
function trs_add_visibility_to_activity( $content, $user_id, $activity_id ) {
    $visibility = 'public';

    /*
    if ( !empty( $_POST['cookie'] ) )
        $_TRS_COOKIE = trm_parse_args( str_replace( '; ', '&', urldecode( $_POST['cookie'] ) ) );
    else
        $_TRS_COOKIE =$_COOKIE;

    $visibility = $_TRS_COOKIE['trs-visibility'];
    */ 
    $levels = trs_get_profile_activity_privacy_levels();
    $levels += trs_get_groups_activity_privacy_levels();

  //  if( isset( $_POST['visibility'] ) && in_array( esc_attr( $_POST['visibility'] ), $levels ) )
        $visibility = esc_attr($_POST['visibility']);

    trs_activity_update_meta( $activity_id, 'activity-privacy', $visibility );
}
add_action( 'trs_activity_posted_update', 'trs_add_visibility_to_activity', 10, 3 );

/**
 * Add visibility level to group activity meta
 * @param  [type] $content     [description]
 * @param  [type] $user_id     [description]
 * @param  [type] $group_id    [description]
 * @param  [type] $activity_id [description]
 * @return [type]              [description]
 */
function trs_add_visibility_to_group_activity( $content, $user_id, $group_id, $activity_id ) {
    $visibility = 'public';

    $levels = trs_get_groups_activity_privacy_levels();
    if( isset( $_POST['visibility'] ) && in_array( esc_attr( $_POST['visibility'] ), $levels ) )
        $visibility = esc_attr($_POST['visibility']);

    trs_activity_update_meta( $activity_id, 'activity-privacy', $visibility );
}
add_action( 'trs_groups_posted_update', 'trs_add_visibility_to_group_activity', 10, 4 );

/**
 * Return Html Select box for activity privacy UI
 * @return [type] [description]
 */
function trs_add_activitiy_visibility_selectbox() {
	echo '<span name="activity-visibility" id="activity-visibility">';
	if ( trs_is_group_home() )
		trs_groups_activity_visibility();
	else
		trs_profile_activity_visibility();
	echo '</span>';
}
add_action('trs_activity_post_form_options','trs_add_activitiy_visibility_selectbox');

function trs_update_activitiy_visibility_selectbox($content) {
    $is_super_admin = is_super_admin();
    if( ($is_super_admin && trs_ap_is_admin_allowed_to_view_edit_privacy_levels()) ||
        (!$is_super_admin && trs_activity_user_can_delete() && trs_ap_is_members_allowed_to_edit_privacy_levels()) ) {

        global $trs;
        $visibility = trs_activity_get_meta( trs_get_activity_id(), 'activity-privacy' );

        global $trs_activity_privacy;
        $group_id = trs_get_activity_item_id();

        //if is not a group activity or a new blog post
        if( !isset( $group_id ) || $group_id == 0 ||  'new_blog_post' == trs_get_activity_type() )
            $visibility_levels = trs_get_profile_activity_visibility_levels();
        else
            $visibility_levels = trs_get_groups_activity_visibility_levels();

        //sort visibility_levels by position
        uasort ($visibility_levels, 'trs_activity_privacy_cmp_position');

        $html = '<select class="trs-ap-selectbox" autocomplete="off">';
        foreach ($visibility_levels as $visibility_level) {
            if( isset($visibility_level["disabled"]) && $visibility_level["disabled"] )
                continue;
            $html .= '<option class="fa fa-' . $visibility_level["id"] . '" ' . ( $visibility_level['id'] == $visibility ? " selected='selected'" : '' ) . ' value="' . $visibility_level["id"] . '">' . $visibility_level["label"] . '</option>';
        }
        $html .= '</select>';

        $html = apply_filters( 'trs_get_update_activitiy_visibility_selectbox', $html );
        echo $html;
    }
}
add_action('trs_activity_entry_meta', 'trs_update_activitiy_visibility_selectbox',10);

// @TODO insert the selectbox on the activity meta (just after the activity time)
function trs_update_activitiy_visibility_selectbox_meta( $content ) {
    $is_super_admin = is_super_admin();
    if( ($is_super_admin && trs_ap_is_admin_allowed_to_view_edit_privacy_levels() ) ||
        (trs_activity_user_can_delete() && trs_ap_is_members_allowed_to_edit_privacy_levels() ) ) {

        global $trs;

        $visibility = trs_activity_get_meta( trs_get_activity_id(), 'activity-privacy' );

        global $trs_activity_privacy;
        $group_id = trs_get_activity_item_id();

        //if is not a group activity or a new blog post
        if( !isset( $group_id ) || $group_id == 0 ||  'new_blog_post' == trs_get_activity_type() )
            $visibility_levels = trs_get_profile_activity_visibility_levels();
        else
            $visibility_levels = trs_get_groups_activity_visibility_levels();

        //sort visibility_levels by position
        uasort ($visibility_levels, 'trs_activity_privacy_cmp_position');

        $html = '<small style="font-size:9px !important;">&nbsp;&nbsp;&middot;&nbsp;</small><select class="trs-ap-selectbox" autocomplete="off">';
        foreach ($visibility_levels as $visibility_level) {
            if( $visibility_level["disabled"] )
                continue;
            $html .= '<option class="fa fa-' . $visibility_level["id"] . '" ' . ( $visibility_level['id'] == $visibility ? " selected='selected'" : '' ) . ' value="' . $visibility_level["id"] . '">' . $visibility_level["label"] . '</option>';
        }
        $html .= '</select>';

        $html = apply_filters( 'trs_get_update_activitiy_visibility_selectbox', $html );
        $content .= $html;
    }
     return $content;
}
//add_action('trs_insert_activity_meta', 'trs_update_activitiy_visibility_selectbox_meta',10, 1);


function trs_activitiy_privacy_activity_visibility_meta( $content ) {
    $is_super_admin = is_super_admin();
    if( ($is_super_admin && trs_ap_is_admin_allowed_to_view_edit_privacy_levels() ) ||
        (trs_activity_user_can_delete() && trs_ap_is_members_allowed_to_edit_privacy_levels() ) ||
        !trs_ap_show_privacy_in_activity_meta() ) {
        return $content;

    } else {
        $group_id = trs_get_activity_item_id();

        //if is not a group activity or a new blog post
        if( !isset( $group_id ) || $group_id == 0 ||  'new_blog_post' == trs_get_activity_type() )
            $visibility_levels = trs_get_profile_activity_visibility_levels();
        else
            $visibility_levels = trs_get_groups_activity_visibility_levels();

        global $trs;
        $default_visibility = 'public';
        $visibility = trs_activity_get_meta( trs_get_activity_id(), 'activity-privacy' );


        if(!isset($visibility) || strlen($visibility) == 0)
           $visibility = $default_visibility;

        $visibility_label = '';
        if(!trs_ap_is_use_fontawsome()) {
            $visibility_label = $visibility_levels[$visibility]['label'];
        }

        $html = '&nbsp;&middot;&nbsp;<i title="'. $visibility_levels[$visibility]['label'] .'" class="trs-activity-visibility fa fa-' . $visibility .'">' . $visibility_label .'</i>';
        $content .= $html;
    }
    return $content;
}
add_action('trs_insert_activity_meta', 'trs_activitiy_privacy_activity_visibility_meta',10, 1);



function trs_add_custom_style_selectbox(){
    if( trs_ap_is_use_custom_styled_selectbox() ) {
    ?>
    <script type="text/javascript">
    if ( typeof jq == "undefined" )
        var jq = jQuery;
    jq(document).ready( function() {
        if (jq.isFunction(jq.fn.customSelect)) {
        //if (  jq.isFunction(jq.fn.customStyle)  ) {
            //fix width problem
            //http://stackoverflow.com/questions/6132141/jquery-why-does-width-sometimes-return-0-after-inserting-elements-with-html
            setTimeout(function(){
               jq('select.trs-ap-selectbox').customSelect();
               //jq('select.trs-ap-selectbox').customStyle(2);
            });
        }
    });
    </script>
    <?php
    }
}
add_action('trs_after_activity_loop', 'trs_add_custom_style_selectbox');
