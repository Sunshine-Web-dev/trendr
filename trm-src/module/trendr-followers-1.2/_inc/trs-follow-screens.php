<?php
/**
 * TRS Follow Screens
 *
 * @package TRS-Follow
 * @sutrsackage Screens
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Catches any visits to the "Followers (X)" tab on a users profile.
 *
 * @uses trs_core_load_template() Loads a template file.
 */
function trs_follow_screen_followers() {
	global $trs;

	do_action( 'trs_follow_screen_followers' );

	if ( isset( $_GET['new'] ) )
		trs_core_delete_notifications_by_type( trs_loggedin_user_id(), $trs->follow->id, 'new_follow' );

	// ignore the template referenced here
	// 'members/single/followers' is for older themes already using this template
	//
	// view trs_follow_load_template_filter() for more info
	trs_core_load_template( 'members/single/followers' );
}

/**
 * Catches any visits to the "Following (X)" tab on a users profile.
 *
 * @uses trs_core_load_template() Loads a template file.
 */
function trs_follow_screen_following() {
	do_action( 'trs_follow_screen_following' );

	// ignore the template referenced here
	// 'members/single/following' is for older themes already using this template
	//
	// view trs_follow_load_template_filter() for more info
	trs_core_load_template( 'members/single/following' );
}

/**
 * Catches any visits to the "Activity > Following" tab on a users profile.
 *
 * @uses trs_core_load_template() Loads a template file.
 */
function trs_follow_screen_activity_following() {
	trs_update_is_item_admin( is_super_admin(), 'activity' );
	do_action( 'trs_activity_screen_following' );
	trs_core_load_template( apply_filters( 'trs_activity_template_following', 'members/single/home' ) );
}

/** TEMPLATE LOADER ************************************************/

/**
 * TRS Follow template loader.
 *
 * This function sets up TRS Follow to use custom templates.
 *
 * If a template does not exist in the current theme, we will use our own
 * bundled templates.
 *
 * We're doing two things here:
 *  1) Support the older template format for themes that are using them
 *     for backwards-compatibility (the template passed in
 *     {@link trs_core_load_template()}).
 *  2) Route older template names to use our new template locations and
 *     format.
 *
 * View the inline doc for more details.
 *
 * @since 1.0
 */
function trs_follow_load_template_filter( $found_template, $templates ) {
	global $trs;

	// Only filter the template location when we're on the follow component pages.
	if ( ! trs_is_current_component( $trs->follow->followers->slug ) && ! trs_is_current_component( $trs->follow->following->slug ) )
		return $found_template;

	// $found_template is not empty when the older template files are found in the
	// parent and child theme
	//
	//  /trm-src/858483/YOUR-THEME/members/single/following.php
	//  /trm-src/858483/YOUR-THEME/members/single/followers.php
	//
	// The older template files utilize a full template ( get_header() +
	// get_footer() ), which sucks for themes and theme compat.
	//
	// When the older template files are not found, we use our new template method,
	// which will act more like a template part.
	if ( empty( $found_template ) ) {
		// register our theme compat directory
		//
		// this tells TRS to look for templates in our plugin directory last
		// when the template isn't found in the parent / child theme
		trs_register_template_stack( 'trs_follow_get_template_directory', 14 );

		// locate_template() will attempt to find the plugins.php template in the
		// child and parent theme and return the located template when found
		//
		// plugins.php is the preferred template to use, since all we'd need to do is
		// inject our content into TRS
		//
		// note: this is only really relevant for trs-default themes as theme compat
		// will kick in on its own when this template isn't found
		$found_template = locate_template( 'members/single/plugins.php', false, false );

		// add AJAX support to the members loop
		// can disable with the 'trs_follow_allow_ajax_on_follow_pages' filter
		if ( apply_filters( 'trs_follow_allow_ajax_on_follow_pages', true ) ) {
			// add the "Order by" dropdown filter
			add_action( 'trs_member_plugin_options_nav',    'trs_follow_add_members_dropdown_filter' );

			// add ability to use AJAX
			add_action( 'trs_after_member_plugin_template', 'trs_follow_add_ajax_to_members_loop' );
		}

		// add our hook to inject content into TRS
		//
		// note the new template name for our template part
		add_action( 'trs_template_content', create_function( '', "
			trs_get_template_part( 'members/single/follow' );
		" ) );
	}

	return apply_filters( 'trs_follow_load_template_filter', $found_template );
}
add_filter( 'trs_located_template', 'trs_follow_load_template_filter', 10, 2 );

/** UTILITY ********************************************************/

/**
 * Get the TRS Follow template directory.
 *
 * @author r-a-y
 * @since 1.2
 *
 * @uses apply_filters()
 * @return string
 */
function trs_follow_get_template_directory() {
	return apply_filters( 'trs_follow_get_template_directory', constant( 'TRS_FOLLOW_DIR' ) . '/_inc/templates' );
}

/**
 * Add ability to use AJAX on the /members/single/module.php template.
 *
 * The plugins.php template hardcodes the 'no-ajax' class to prevent AJAX
 * from being used.
 *
 * We want to use AJAX; so we dynamically remove the class with jQuery after
 * the document has finished loading.
 *
 * This will enable AJAX in our members loop.
 *
 * Hooked to the 'trs_after_member_plugin_template' action.
 *
 * @author r-a-y
 * @since 1.2
 *
 * @see trs_follow_load_template_filter()
 */
function trs_follow_add_ajax_to_members_loop() {
?>

	<script type="text/javascript">
	jQuery(document).ready( function() {
		jQuery('#subnav').removeClass('no-ajax');
	});
	</script>

<?php
}

/**
 * Add "Order By" dropdown filter to the /members/single/module.php template.
 *
 * Hooked to the 'trs_member_plugin_options_nav' action.
 *
 * @author r-a-y
 * @since 1.2
 *
 * @see trs_follow_load_template_filter()
 */
function trs_follow_add_members_dropdown_filter() {
?>

	<?php do_action( 'trs_members_directory_member_sub_types' ); ?>

	<li id="members-order-select" class="last filter">

		<?php // the ID for this is important as AJAX relies on it! ?>
		<label for="members-<?php echo trs_current_action(); ?>-orderby"><?php _e( 'Order By:', 'trs-follow' ); ?></label>
		<select id="members-<?php echo trs_current_action(); ?>-orderby">
			<option value="active"><?php _e( 'Last Active', 'trs-follow' ); ?></option>
			<option value="newest"><?php _e( 'Newest Registered', 'trs-follow' ); ?></option>

			<?php if ( trs_is_active( 'xprofile' ) ) : ?>
				<option value="alphabetical"><?php _e( 'Alphabetical', 'trs-follow' ); ?></option>
			<?php endif; ?>

			<?php do_action( 'trs_members_directory_order_options' ); ?>

		</select>
	</li>

<?php
}
