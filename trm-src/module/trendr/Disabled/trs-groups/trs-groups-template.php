<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Output the groups component slug
 *
 * @package trendr
 * @sutrsackage Groups Template
 * @since 1.5
 *
 * @uses trs_get_groups_slug()
 */
function trs_groups_slug() {
	echo trs_get_groups_slug();
}
	/**
	 * Return the groups component slug
	 *
	 * @package trendr
	 * @sutrsackage Groups Template
	 * @since 1.5
	 */
	function trs_get_groups_slug() {
		global $trs;
		return apply_filters( 'trs_get_groups_slug', $trs->groups->slug );
	}

/**
 * Output the groups component root slug
 *
 * @package trendr
 * @sutrsackage Groups Template
 * @since 1.5
 *
 * @uses trs_get_groups_root_slug()
 */
function trs_groups_root_slug() {
	echo trs_get_groups_root_slug();
}
	/**
	 * Return the groups component root slug
	 *
	 * @package trendr
	 * @sutrsackage Groups Template
	 * @since 1.5
	 */
	function trs_get_groups_root_slug() {
		global $trs;
		return apply_filters( 'trs_get_groups_root_slug', $trs->groups->root_slug );
	}

/**
 * Output group directory permalink
 *
 * @package trendr
 * @sutrsackage Groups Template
 * @since 1.5
 * @uses trs_get_groups_directory_permalink()
 */
function trs_groups_directory_permalink() {
	echo trs_get_groups_directory_permalink();
}
	/**
	 * Return group directory permalink
	 *
	 * @package trendr
	 * @sutrsackage Groups Template
	 * @since 1.5
	 * @uses apply_filters()
	 * @uses traisingslashit()
	 * @uses trs_get_root_domain()
	 * @uses trs_get_groups_root_slug()
	 * @return string
	 */
	function trs_get_groups_directory_permalink() {
		return apply_filters( 'trs_get_groups_directory_permalink', trailingslashit( trs_get_root_domain() . '/' . trs_get_groups_root_slug() ) );
	}

/*****************************************************************************
 * Groups Template Class/Tags
 **/

class TRS_Groups_Template {
	var $current_group = -1;
	var $group_count;
	var $groups;
	var $group;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_group_count;

	var $single_group = false;

	var $sort_by;
	var $order;

	function trs_groups_template( $user_id, $type, $page, $per_page, $max, $slug, $search_terms, $populate_extras, $include = false, $exclude = false, $show_hidden = false ) {
		$this->__construct( $user_id, $type, $page, $per_page, $max, $slug, $search_terms, $include, $populate_extras, $exclude, $show_hidden );
	}

	function __construct( $user_id, $type, $page, $per_page, $max, $slug, $search_terms, $populate_extras, $include = false, $exclude = false, $show_hidden = false ){

		global $trs;

		$this->pag_page = isset( $_REQUEST['grpage'] ) ? intval( $_REQUEST['grpage'] ) : $page;
		$this->pag_num  = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;

		if ( $trs->loggedin_user->is_super_admin || ( is_user_logged_in() && $user_id == $trs->loggedin_user->id ) )
			$show_hidden = true;

		if ( 'invites' == $type ) {
			$this->groups = groups_get_invites_for_user( $user_id, $this->pag_num, $this->pag_page, $exclude );
		} else if ( 'single-group' == $type ) {
			$group           = new stdClass;
			$group->group_id = TRS_Groups_Group::get_id_from_slug( $slug );
			$this->groups    = array( $group );
		} else {
			$this->groups = groups_get_groups( array(
				'type'            => $type,
				'per_page'        => $this->pag_num,
				'page'            => $this->pag_page,
				'user_id'         => $user_id,
				'search_terms'    => $search_terms,
				'include'         => $include,
				'exclude'         => $exclude,
				'populate_extras' => $populate_extras,
				'show_hidden'     => $show_hidden
			) );
		}

		if ( 'invites' == $type ) {
			$this->total_group_count = (int)$this->groups['total'];
			$this->group_count       = (int)$this->groups['total'];
			$this->groups            = $this->groups['groups'];
		} else if ( 'single-group' == $type ) {
			$this->single_group      = true;
			$this->total_group_count = 1;
			$this->group_count       = 1;
		} else {
			if ( empty( $max ) || $max >= (int)$this->groups['total'] ) {
				$this->total_group_count = (int)$this->groups['total'];
			} else {
				$this->total_group_count = (int)$max;
			}

			$this->groups = $this->groups['groups'];

			if ( !empty( $max ) ) {
				if ( $max >= count( $this->groups ) ) {
					$this->group_count = count( $this->groups );
				} else {
					$this->group_count = (int)$max;
				}
			} else {
				$this->group_count = count( $this->groups );
			}
		}

		// Build pagination links
		if ( (int)$this->total_group_count && (int)$this->pag_num ) {
			$this->pag_links = paginate_links( array(
				'base'      => add_query_arg( array( 'grpage' => '%#%', 'num' => $this->pag_num, 's' => $search_terms, 'sortby' => $this->sort_by, 'order' => $this->order ) ),
				'format'    => '',
				'total'     => ceil( (int)$this->total_group_count / (int)$this->pag_num ),
				'current'   => $this->pag_page,
				'prev_text' => _x( '&larr;', 'Group pagination previous text', 'trendr' ),
				'next_text' => _x( '&rarr;', 'Group pagination next text', 'trendr' ),
				'mid_size'  => 1
			) );
		}
	}

	function has_groups() {
		if ( $this->group_count )
			return true;

		return false;
	}

	function next_group() {
		$this->current_group++;
		$this->group = $this->groups[$this->current_group];

		return $this->group;
	}

	function rewind_groups() {
		$this->current_group = -1;
		if ( $this->group_count > 0 ) {
			$this->group = $this->groups[0];
		}
	}

	function groups() {
		if ( $this->current_group + 1 < $this->group_count ) {
			return true;
		} elseif ( $this->current_group + 1 == $this->group_count ) {
			do_action('group_loop_end');
			// Do some cleaning up after the loop
			$this->rewind_groups();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_group() {
		global $group;

		$this->in_the_loop = true;
		$this->group = $this->next_group();

		if ( $this->single_group )
			$this->group = new TRS_Groups_Group( $this->group->group_id );

		if ( 0 == $this->current_group ) // loop has just started
			do_action('group_loop_start');
	}
}

function trs_has_groups( $args = '' ) {
	global $groups_template, $trs;

	/***
	 * Set the defaults based on the current page. Any of these will be overridden
	 * if arguments are directly passed into the loop. Custom plugins should always
	 * pass their parameters directly to the loop.
	 */
	$slug    = false;
	$type    = 'active';
	$user_id = 0;
	$order   = '';

	// User filtering
	if ( !empty( $trs->displayed_user->id ) )
		$user_id = $trs->displayed_user->id;

	// Type
	if ( 'my-groups' == $trs->current_action ) {
		if ( 'most-popular' == $order ) {
			$type = 'popular';
		} elseif ( 'alphabetically' == $order ) {
			$type = 'alphabetical';
		}
	} elseif ( 'invites' == $trs->current_action ) {
		$type = 'invites';
	} elseif ( isset( $trs->groups->current_group->slug ) && $trs->groups->current_group->slug ) {
		$type = 'single-group';
		$slug = $trs->groups->current_group->slug;
	}

	$defaults = array(
		'type'            => $type,
		'page'            => 1,
		'per_page'        => 20,
		'max'             => false,
		'show_hidden'     => false,

		'user_id'         => $user_id, // Pass a user ID to limit to groups this user has joined
		'slug'            => $slug,    // Pass a group slug to only return that group
		'search_terms'    => '',       // Pass search terms to return only matching groups
		'include'         => false,    // Pass comma separated list or array of group ID's to return only these groups
		'exclude'         => false,    // Pass comma separated list or array of group ID's to exclude these groups

		'populate_extras' => true      // Get extra meta - is_member, is_banned
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r );

	if ( empty( $search_terms ) ) {
		if ( isset( $_REQUEST['group-filter-box'] ) && !empty( $_REQUEST['group-filter-box'] ) )
			$search_terms = $_REQUEST['group-filter-box'];
		elseif ( isset( $_REQUEST['s'] ) && !empty( $_REQUEST['s'] ) )
			$search_terms = $_REQUEST['s'];
		else
			$search_terms = false;
	}

	$groups_template = new TRS_Groups_Template( (int)$user_id, $type, (int)$page, (int)$per_page, (int)$max, $slug, $search_terms, (bool)$populate_extras, $include, $exclude, $show_hidden );
	return apply_filters( 'trs_has_groups', $groups_template->has_groups(), $groups_template );
}

function trs_groups() {
	global $groups_template;
	return $groups_template->groups();
}

function trs_the_group() {
	global $groups_template;
	return $groups_template->the_group();
}

function trs_group_is_visible( $group = false ) {
	global $trs, $groups_template;

	if ( $trs->loggedin_user->is_super_admin )
		return true;

	if ( !$group )
		$group = $groups_template->group;

	if ( 'public' == $group->status ) {
		return true;
	} else {
		if ( groups_is_user_member( $trs->loggedin_user->id, $group->id ) ) {
			return true;
		}
	}

	return false;
}

function trs_group_id() {
	echo trs_get_group_id();
}
	function trs_get_group_id( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_id', $group->id );
	}

function trs_group_name() {
	echo trs_get_group_name();
}
	function trs_get_group_name( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_name', $group->name );
	}

function trs_group_type() {
	echo trs_get_group_type();
}
	function trs_get_group_type( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		if ( 'public' == $group->status ) {
			$type = __( "Public Group", "trendr" );
		} else if ( 'hidden' == $group->status ) {
			$type = __( "Hidden Group", "trendr" );
		} else if ( 'private' == $group->status ) {
			$type = __( "Private Group", "trendr" );
		} else {
			$type = ucwords( $group->status ) . ' ' . __( 'Group', 'trendr' );
		}

		return apply_filters( 'trs_get_group_type', $type );
	}

function trs_group_status() {
	echo trs_get_group_status();
}
	function trs_get_group_status( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_status', $group->status );
	}

function trs_group_portrait( $args = '' ) {
	echo trs_get_group_portrait( $args );
}
	function trs_get_group_portrait( $args = '' ) {
		global $trs, $groups_template;

		$defaults = array(
			'type' => 'full',
			'width' => false,
			'height' => false,
			'class' => 'portrait',
			'id' => false,
			'alt' => __( 'Group logo of %s', 'trendr' )
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		/* Fetch the portrait from the folder, if not provide backwards compat. */
		if ( !$portrait = trs_core_fetch_portrait( array( 'item_id' => $groups_template->group->id, 'object' => 'group', 'type' => $type, 'portrait_dir' => 'group-portraits', 'alt' => $alt, 'css_id' => $id, 'class' => $class, 'width' => $width, 'height' => $height ) ) )
			$portrait = '<img src="' . esc_attr( $groups_template->group->portrait_thumb ) . '" class="portrait" alt="' . esc_attr( $groups_template->group->name ) . '" />';

		return apply_filters( 'trs_get_group_portrait', $portrait );
	}

function trs_group_portrait_thumb() {
	echo trs_get_group_portrait_thumb();
}
	function trs_get_group_portrait_thumb( $group = false ) {
		return trs_get_group_portrait( 'type=thumb' );
	}

function trs_group_portrait_mini() {
	echo trs_get_group_portrait_mini();
}
	function trs_get_group_portrait_mini( $group = false ) {
		return trs_get_group_portrait( 'type=thumb&width=30&height=30' );
	}

function trs_group_last_active() {
	echo trs_get_group_last_active();
}
	function trs_get_group_last_active( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		$last_active = $group->last_activity;

		if ( !$last_active )
			$last_active = groups_get_groupmeta( $group->id, 'last_activity' );

		if ( empty( $last_active ) ) {
			return __( 'not yet active', 'trendr' );
		} else {
			return apply_filters( 'trs_get_group_last_active', trs_core_time_since( $last_active ) );
		}
	}

function trs_group_permalink() {
	echo trs_get_group_permalink();
}
	function trs_get_group_permalink( $group = false ) {
		global $groups_template, $trs;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_permalink', trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/' . $group->slug . '/' );
	}

function trs_group_admin_permalink() {
	echo trs_get_group_admin_permalink();
}
	function trs_get_group_admin_permalink( $group = false ) {
		global $groups_template, $trs;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_admin_permalink', trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/' . $group->slug . '/admin' );
	}

function trs_group_slug() {
	echo trs_get_group_slug();
}
	function trs_get_group_slug( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_slug', $group->slug );
	}

function trs_group_description() {
	echo trs_get_group_description();
}
	function trs_get_group_description( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_description', stripslashes($group->description) );
	}

function trs_group_description_editable() {
	echo trs_get_group_description_editable();
}
	function trs_get_group_description_editable( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_description_editable', $group->description );
	}

function trs_group_description_excerpt() {
	echo trs_get_group_description_excerpt();
}
	function trs_get_group_description_excerpt( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_description_excerpt', trs_create_excerpt( $group->description ) );
	}


function trs_group_public_status() {
	echo trs_get_group_public_status();
}
	function trs_get_group_public_status( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		if ( $group->is_public ) {
			return __( 'Public', 'trendr' );
		} else {
			return __( 'Private', 'trendr' );
		}
	}

function trs_group_is_public() {
	echo trs_get_group_is_public();
}
	function trs_get_group_is_public( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_is_public', $group->is_public );
	}

function trs_group_date_created() {
	echo trs_get_group_date_created();
}
	function trs_get_group_date_created( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_date_created', trs_core_time_since( strtotime( $group->date_created ) ) );
	}

function trs_group_is_admin() {
	global $trs;

	return $trs->is_item_admin;
}

function trs_group_is_mod() {
	global $trs;

	return $trs->is_item_mod;
}

function trs_group_list_admins( $group = false ) {
	global $groups_template;

	if ( !$group )
		$group = $groups_template->group;

	if ( $group->admins ) { ?>
		<ul id="group-admins">
			<?php foreach( (array)$group->admins as $admin ) { ?>
				<li>
					<a href="<?php echo trs_core_get_user_domain( $admin->user_id, $admin->user_nicename, $admin->user_login ) ?>"><?php echo trs_core_fetch_portrait( array( 'item_id' => $admin->user_id, 'email' => $admin->user_email, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ?></a>
				</li>
			<?php } ?>
		</ul>
	<?php } else { ?>
		<span class="activity"><?php _e( 'No Admins', 'trendr' ) ?></span>
	<?php } ?>
<?php
}

function trs_group_list_mods( $group = false ) {
	global $groups_template;

	if ( empty( $group ) )
		$group = $groups_template->group;

	if ( !empty( $group->mods ) ) : ?>

		<ul id="group-mods">

			<?php foreach( (array)$group->mods as $mod ) { ?>

				<li>
					<a href="<?php echo trs_core_get_user_domain( $mod->user_id, $mod->user_nicename, $mod->user_login ) ?>"><?php echo trs_core_fetch_portrait( array( 'item_id' => $mod->user_id, 'email' => $mod->user_email, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ?></a>
				</li>

			<?php } ?>

		</ul>

<?php else : ?>

		<span class="activity"><?php _e( 'No Mods', 'trendr' ) ?></span>

<?php endif;

}

/**
 * Return a list of user_ids for a group's admins
 *
 * @package trendr
 * @since 1.5
 *
 * @param obj $group (optional) The group being queried. Defaults to the current group in the loop
 * @param str $format 'string' to get a comma-separated string, 'array' to get an array
 * @return mixed $admin_ids A string or array of user_ids
 */
function trs_group_admin_ids( $group = false, $format = 'string' ) {
	global $groups_template;

	if ( !$group )
		$group = $groups_template->group;

	$admin_ids = array();

	if ( $group->admins ) {
		foreach( $group->admins as $admin ) {
			$admin_ids[] = $admin->user_id;
		}
	}

	if ( 'string' == $format )
		$admin_ids = implode( ',', $admin_ids );

	return apply_filters( 'trs_group_admin_ids', $admin_ids );
}

/**
 * Return a list of user_ids for a group's moderators
 *
 * @package trendr
 * @since 1.5
 *
 * @param obj $group (optional) The group being queried. Defaults to the current group in the loop
 * @param str $format 'string' to get a comma-separated string, 'array' to get an array
 * @return mixed $mod_ids A string or array of user_ids
 */
function trs_group_mod_ids( $group = false, $format = 'string' ) {
	global $groups_template;

	if ( !$group )
		$group = $groups_template->group;

	$mod_ids = array();

	if ( $group->mods ) {
		foreach( $group->mods as $mod ) {
			$mod_ids[] = $mod->user_id;
		}
	}

	if ( 'string' == $format )
		$mod_ids = implode( ',', $mod_ids );

	return apply_filters( 'trs_group_mod_ids', $mod_ids );
}

function trs_group_all_members_permalink() {
	echo trs_get_group_all_members_permalink();
}
	function trs_get_group_all_members_permalink( $group = false ) {
		global $groups_template, $trs;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_all_members_permalink', trs_get_group_permalink( $group ) . 'members' );
	}

function trs_group_search_form() {
	global $groups_template, $trs;

	$action = $trs->displayed_user->domain . trs_get_groups_slug() . '/my-groups/search/';
	$label = __('Filter Groups', 'trendr');
	$name = 'group-filter-box';

?>
	<form action="<?php echo $action ?>" id="group-search-form" method="post">
		<label for="<?php echo $name ?>" id="<?php echo $name ?>-label"><?php echo $label ?></label>
		<input type="search" name="<?php echo $name ?>" id="<?php echo $name ?>" value="<?php echo $value ?>"<?php echo $disabled ?> />

		<?php trm_nonce_field( 'group-filter-box', '_key_group_filter' ) ?>
	</form>
<?php
}

function trs_group_show_no_groups_message() {
	global $trs;

	if ( !groups_total_groups_for_user( $trs->displayed_user->id ) )
		return true;

	return false;
}

function trs_group_is_activity_permalink() {

	if ( !trs_is_single_item() || !trs_is_groups_component() || !trs_is_current_action( trs_get_activity_slug() ) )
		return false;

	return true;
}

function trs_groups_pagination_links() {
	echo trs_get_groups_pagination_links();
}
	function trs_get_groups_pagination_links() {
		global $groups_template;

		return apply_filters( 'trs_get_groups_pagination_links', $groups_template->pag_links );
	}

function trs_groups_pagination_count() {
	echo trs_get_groups_pagination_count();
}
	function trs_get_groups_pagination_count() {
		global $trs, $groups_template;

		$start_num = intval( ( $groups_template->pag_page - 1 ) * $groups_template->pag_num ) + 1;
		$from_num = trs_core_number_format( $start_num );
		$to_num = trs_core_number_format( ( $start_num + ( $groups_template->pag_num - 1 ) > $groups_template->total_group_count ) ? $groups_template->total_group_count : $start_num + ( $groups_template->pag_num - 1 ) );
		$total = trs_core_number_format( $groups_template->total_group_count );

		return apply_filters( 'trs_get_groups_pagination_count', sprintf( __( 'Viewing group %1$s to %2$s (of %3$s groups)', 'trendr' ), $from_num, $to_num, $total ) );
	}

function trs_groups_auto_join() {
	global $trs;

	return apply_filters( 'trs_groups_auto_join', (bool)$trs->groups->auto_join );
}

function trs_group_total_members( $group = false ) {
	echo trs_get_group_total_members( $group );
}
	function trs_get_group_total_members( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_total_members', $group->total_member_count );
	}

function trs_group_member_count() {
	echo trs_get_group_member_count();
}
	function trs_get_group_member_count() {
		global $groups_template;

		if ( 1 == (int) $groups_template->group->total_member_count )
			return apply_filters( 'trs_get_group_member_count', sprintf( __( '%s member', 'trendr' ), trs_core_number_format( $groups_template->group->total_member_count ) ) );
		else
			return apply_filters( 'trs_get_group_member_count', sprintf( __( '%s members', 'trendr' ), trs_core_number_format( $groups_template->group->total_member_count ) ) );
	}

function trs_group_forum_permalink() {
	echo trs_get_group_forum_permalink();
}
	function trs_get_group_forum_permalink( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_forum_permalink', trs_get_group_permalink( $group ) . 'forum' );
	}

function trs_group_forum_topic_count( $args = '' ) {
	echo trs_get_group_forum_topic_count( $args );
}
	function trs_get_group_forum_topic_count( $args = '' ) {
		global $groups_template;

		$defaults = array(
			'showtext' => false
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if ( !$forum_id = groups_get_groupmeta( $groups_template->group->id, 'forum_id' ) )
			return false;

		if ( !trs_is_active( 'forums' ) )
			return false;

		if ( !$groups_template->group->forum_counts )
			$groups_template->group->forum_counts = trs_forums_get_forum_topicpost_count( (int)$forum_id );

		if ( (bool) $showtext ) {
			if ( 1 == (int) $groups_template->group->forum_counts[0]->topics )
				$total_topics = sprintf( __( '%d topic', 'trendr' ), (int) $groups_template->group->forum_counts[0]->topics );
			else
				$total_topics = sprintf( __( '%d topics', 'trendr' ), (int) $groups_template->group->forum_counts[0]->topics );
		} else {
			$total_topics = (int) $groups_template->group->forum_counts[0]->topics;
		}

		return apply_filters( 'trs_get_group_forum_topic_count', $total_topics, (bool)$showtext );
	}

function trs_group_forum_post_count( $args = '' ) {
	echo trs_get_group_forum_post_count( $args );
}
	function trs_get_group_forum_post_count( $args = '' ) {
		global $groups_template;

		$defaults = array(
			'showtext' => false
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if ( !$forum_id = groups_get_groupmeta( $groups_template->group->id, 'forum_id' ) )
			return false;

		if ( !trs_is_active( 'forums' ) )
			return false;

		if ( !$groups_template->group->forum_counts )
			$groups_template->group->forum_counts = trs_forums_get_forum_topicpost_count( (int)$forum_id );

		if ( (bool) $showtext ) {
			if ( 1 == (int) $groups_template->group->forum_counts[0]->posts )
				$total_posts = sprintf( __( '%d post', 'trendr' ), (int) $groups_template->group->forum_counts[0]->posts );
			else
				$total_posts = sprintf( __( '%d posts', 'trendr' ), (int) $groups_template->group->forum_counts[0]->posts );
		} else {
			$total_posts = (int) $groups_template->group->forum_counts[0]->posts;
		}

		return apply_filters( 'trs_get_group_forum_post_count', $total_posts, (bool)$showtext );
	}

function trs_group_is_forum_enabled( $group = false ) {
	global $groups_template;

	if ( !$group )
		$group = $groups_template->group;

	if ( trs_is_active( 'forums' ) ) {
		if ( trs_forums_is_installed_correctly() ) {
			if ( $group->enable_forum )
				return true;

			return false;
		} else {
			return false;
		}
	}

	return false;
}

function trs_group_show_forum_setting( $group = false ) {
	global $groups_template;

	if ( !$group )
		$group = $groups_template->group;

	if ( $group->enable_forum )
		echo ' checked="checked"';
}

function trs_group_show_status_setting( $setting, $group = false ) {
	global $groups_template;

	if ( !$group )
		$group = $groups_template->group;

	if ( $setting == $group->status )
		echo ' checked="checked"';
}

/**
 * Get the 'checked' value, if needed, for a given invite_status on the group create/admin screens
 *
 * @package trendr
 * @sutrsackage Groups Template
 * @since 1.5
 *
 * @param str $setting The setting you want to check against ('members', 'mods', or 'admins')
 * @param obj $group (optional) The group whose status you want to check
 */
function trs_group_show_invite_status_setting( $setting, $group = false ) {
	$group_id = isset( $group->id ) ? $group->id : false;

	$invite_status = trs_group_get_invite_status( $group_id );

	if ( $setting == $invite_status )
		echo ' checked="checked"';
}

/**
 * Get the invite status of a group
 *
 * 'invite_status' became part of trendr in TRS 1.5. In order to provide backward compatibility,
 * groups without a status set will default to 'members', ie all members in a group can send
 * invitations. Filter 'trs_group_invite_status_fallback' to change this fallback behavior.
 *
 * This function can be used either in or out of the loop.
 *
 * @package trendr
 * @sutrsackage Groups Template
 * @since 1.5
 *
 * @param int $group_id (optional) The id of the group whose status you want to check
 * @return mixed Returns false when no group can be found. Otherwise returns the group invite
 *    status, from among 'members', 'mods', and 'admins'
 */
function trs_group_get_invite_status( $group_id = false ) {
	global $trs, $groups_template;

	if ( !$group_id ) {
		if ( isset( $trs->groups->current_group->id ) ) {
			// Default to the current group first
			$group_id = $trs->groups->current_group->id;
		} else if ( isset( $groups_template->group->id ) ) {
			// Then see if we're in the loop
			$group_id = $groups_template->group->id;
		} else {
			return false;
		}
	}

	$invite_status = groups_get_groupmeta( $group_id, 'invite_status' );

	// Backward compatibility. When 'invite_status' is not set, fall back to a default value
	if ( !$invite_status ) {
		$invite_status = apply_filters( 'trs_group_invite_status_fallback', 'members' );
	}

	return apply_filters( 'trs_group_get_invite_status', $invite_status, $group_id );
}

/**
 * Can the logged-in user send invitations in the specified group?
 *
 * @package trendr
 * @sutrsackage Groups Template
 * @since 1.5
 *
 * @param int $group_id (optional) The id of the group whose status you want to check
 * @return bool $can_send_invites
 */
function trs_groups_user_can_send_invites( $group_id = false ) {
	global $trs;

	$can_send_invites = false;
	$invite_status    = false;

	if ( is_user_logged_in() ) {
		if ( is_super_admin() ) {
			// Super admins can always send invitations
			$can_send_invites = true;

		} else {
			// If no $group_id is provided, default to the current group id
			if ( !$group_id )
				$group_id = isset( $trs->groups->current_group->id ) ? $trs->groups->current_group->id : 0;

			// If no group has been found, bail
			if ( !$group_id )
				return false;

			$invite_status = trs_group_get_invite_status( $group_id );
			if ( !$invite_status )
				return false;

			switch ( $invite_status ) {
				case 'admins' :
					if ( groups_is_user_admin( trs_loggedin_user_id(), $group_id ) )
						$can_send_invites = true;
					break;

				case 'mods' :
					if ( groups_is_user_mod( trs_loggedin_user_id(), $group_id ) || groups_is_user_admin( trs_loggedin_user_id(), $group_id ) )
						$can_send_invites = true;
					break;

				case 'members' :
					if ( groups_is_user_member( trs_loggedin_user_id(), $group_id ) )
						$can_send_invites = true;
					break;
			}
		}
	}

	return apply_filters( 'trs_groups_user_can_send_invites', $can_send_invites, $group_id, $invite_status );
}

/**
 * Since trendr 1.0, this generated the group settings admin/member screen.
 * As of trendr 1.5 (r4489), and because this function outputs HTML, it was moved into /trs-default/groups/single/admin.php.
 *
 * @deprecated 1.5
 * @deprecated No longer used.
 * @since 1.0
 * @todo Remove in 1.4
 */
function trs_group_admin_memberlist( $admin_list = false, $group = false ) {
	global $groups_template;

	_deprecated_function( __FUNCTION__, '1.5', 'No longer used. See /trs-default/groups/single/admin.php' );

	if ( empty( $group ) )
		$group = $groups_template->group;


	if ( $admins = groups_get_group_admins( $group->id ) ) : ?>

		<ul id="admins-list" class="item-list<?php if ( !empty( $admin_list ) ) : ?> single-line<?php endif; ?>">

		<?php foreach ( (array)$admins as $admin ) { ?>

			<?php if ( !empty( $admin_list ) ) : ?>

			<li>

				<?php echo trs_core_fetch_portrait( array( 'item_id' => $admin->user_id, 'type' => 'thumb', 'width' => 30, 'height' => 30, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ?>

				<h5>

					<?php echo trs_core_get_userlink( $admin->user_id ); ?>

					<span class="small">
						<a class="button confirm admin-demote-to-member" href="<?php trs_group_member_demote_link($admin->user_id) ?>"><?php _e( 'Demote to Member', 'trendr' ) ?></a>
					</span>
				</h5>
			</li>

			<?php else : ?>

			<li>

				<?php echo trs_core_fetch_portrait( array( 'item_id' => $admin->user_id, 'type' => 'thumb', 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ?>

				<h5><?php echo trs_core_get_userlink( $admin->user_id ) ?></h5>
				<span class="activity">
					<?php echo trs_core_get_last_activity( strtotime( $admin->date_modified ), __( 'joined %s', 'trendr') ); ?>
				</span>

				<?php if ( trs_is_active( 'friends' ) ) : ?>

					<div class="action">

						<?php trs_add_friend_button( $admin->user_id ); ?>

					</div>

				<?php endif; ?>

			</li>

			<?php endif;
		} ?>

		</ul>

	<?php else : ?>

		<div id="message" class="info">
			<p><?php _e( 'This group has no administrators', 'trendr' ); ?></p>
		</div>

	<?php endif;
}

function trs_group_mod_memberlist( $admin_list = false, $group = false ) {
	global $groups_template, $group_mods;

	if ( empty( $group ) )
		$group = $groups_template->group;

	if ( $group_mods = groups_get_group_mods( $group->id ) ) { ?>

		<ul id="mods-list" class="item-list<?php if ( $admin_list ) { ?> single-line<?php } ?>">

		<?php foreach ( (array)$group_mods as $mod ) { ?>

			<?php if ( !empty( $admin_list ) ) { ?>

			<li>

				<?php echo trs_core_fetch_portrait( array( 'item_id' => $mod->user_id, 'type' => 'thumb', 'width' => 30, 'height' => 30, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ?>

				<h5>
					<?php echo trs_core_get_userlink( $mod->user_id ); ?>

					<span class="small">
						<a href="<?php trs_group_member_promote_admin_link( array( 'user_id' => $mod->user_id ) ) ?>" class="button confirm mod-promote-to-admin" title="<?php _e( 'Promote to Admin', 'trendr' ); ?>"><?php _e( 'Promote to Admin', 'trendr' ); ?></a>
						<a class="button confirm mod-demote-to-member" href="<?php trs_group_member_demote_link($mod->user_id) ?>"><?php _e( 'Demote to Member', 'trendr' ) ?></a>
					</span>
				</h5>
			</li>

			<?php } else { ?>

			<li>

				<?php echo trs_core_fetch_portrait( array( 'item_id' => $mod->user_id, 'type' => 'thumb', 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ?>

				<h5><?php echo trs_core_get_userlink( $mod->user_id ) ?></h5>

				<span class="activity"><?php echo trs_core_get_last_activity( strtotime( $mod->date_modified ), __( 'joined %s', 'trendr') ); ?></span>

				<?php if ( trs_is_active( 'friends' ) ) : ?>

					<div class="action">
						<?php trs_add_friend_button( $mod->user_id ) ?>
					</div>

				<?php endif; ?>

			</li>

			<?php } ?>
		<?php } ?>

		</ul>

	<?php } else { ?>

		<div id="message" class="info">
			<p><?php _e( 'This group has no moderators', 'trendr' ); ?></p>
		</div>

	<?php }
}

function trs_group_has_moderators( $group = false ) {
	global $group_mods, $groups_template;

	if ( !$group )
		$group = $groups_template->group;

	return apply_filters( 'trs_group_has_moderators', groups_get_group_mods( $group->id ) );
}

function trs_group_member_promote_mod_link( $args = '' ) {
	echo trs_get_group_member_promote_mod_link( $args );
}
	function trs_get_group_member_promote_mod_link( $args = '' ) {
		global $members_template, $groups_template, $trs;

		$defaults = array(
			'user_id' => $members_template->member->user_id,
			'group'   => &$groups_template->group
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		return apply_filters( 'trs_get_group_member_promote_mod_link', trm_nonce_url( trs_get_group_permalink( $group ) . 'admin/manage-members/promote/mod/' . $user_id, 'groups_promote_member' ) );
	}

function trs_group_member_promote_admin_link( $args = '' ) {
	echo trs_get_group_member_promote_admin_link( $args );
}
	function trs_get_group_member_promote_admin_link( $args = '' ) {
		global $members_template, $groups_template, $trs;

		$defaults = array(
			'user_id' => !empty( $members_template->member->user_id ) ? $members_template->member->user_id : false,
			'group'   => &$groups_template->group
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		return apply_filters( 'trs_get_group_member_promote_admin_link', trm_nonce_url( trs_get_group_permalink( $group ) . 'admin/manage-members/promote/admin/' . $user_id, 'groups_promote_member' ) );
	}

function trs_group_member_demote_link( $user_id = 0 ) {
	global $members_template;

	if ( !$user_id )
		$user_id = $members_template->member->user_id;

	echo trs_get_group_member_demote_link( $user_id );
}
	function trs_get_group_member_demote_link( $user_id = 0, $group = false ) {
		global $members_template, $groups_template, $trs;

		if ( !$group )
			$group = $groups_template->group;

		if ( !$user_id )
			$user_id = $members_template->member->user_id;

		return apply_filters( 'trs_get_group_member_demote_link', trm_nonce_url( trs_get_group_permalink( $group ) . 'admin/manage-members/demote/' . $user_id, 'groups_demote_member' ) );
	}

function trs_group_member_ban_link( $user_id = 0 ) {
	global $members_template;

	if ( !$user_id )
		$user_id = $members_template->member->user_id;

	echo trs_get_group_member_ban_link( $user_id );
}
	function trs_get_group_member_ban_link( $user_id = 0, $group = false ) {
		global $members_template, $groups_template, $trs;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_member_ban_link', trm_nonce_url( trs_get_group_permalink( $group ) . 'admin/manage-members/ban/' . $user_id, 'groups_ban_member' ) );
	}

function trs_group_member_unban_link( $user_id = 0 ) {
	global $members_template;

	if ( !$user_id )
		$user_id = $members_template->member->user_id;

	echo trs_get_group_member_unban_link( $user_id );
}
	function trs_get_group_member_unban_link( $user_id = 0, $group = false ) {
		global $members_template, $groups_template;

		if ( !$user_id )
			$user_id = $members_template->member->user_id;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_member_unban_link', trm_nonce_url( trs_get_group_permalink( $group ) . 'admin/manage-members/unban/' . $user_id, 'groups_unban_member' ) );
	}


function trs_group_member_remove_link( $user_id = 0 ) {
	global $members_template;

	if ( !$user_id )
		$user_id = $members_template->member->user_id;

	echo trs_get_group_member_remove_link( $user_id );
}
	function trs_get_group_member_remove_link( $user_id = 0, $group = false ) {
		global $members_template, $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_member_remove_link', trm_nonce_url( trs_get_group_permalink( $group ) . 'admin/manage-members/remove/' . $user_id, 'groups_remove_member' ) );
	}

function trs_group_admin_tabs( $group = false ) {
	global $trs, $groups_template;

	if ( !$group )
		$group = ( $groups_template->group ) ? $groups_template->group : $trs->groups->current_group;

	$current_tab = trs_action_variable( 0 );
?>
	<?php if ( $trs->is_item_admin || $trs->is_item_mod ) { ?>
		<li<?php if ( 'edit-details' == $current_tab || empty( $current_tab ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/' . $group->slug ?>/admin/edit-details"><?php _e( 'Details', 'trendr' ); ?></a></li>
	<?php } ?>

	<?php
		if ( !$trs->is_item_admin )
			return false;
	?>
	<li<?php if ( 'group-settings' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/' . $group->slug ?>/admin/group-settings"><?php _e( 'Settings', 'trendr' ); ?></a></li>

	<?php if ( !(int)trs_get_option( 'trs-disable-portrait-uploads' ) ) : ?>
		<li<?php if ( 'group-portrait'   == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/' . $group->slug ?>/admin/group-portrait"><?php _e( 'Avatar', 'trendr' ); ?></a></li>
	<?php endif; ?>

	<li<?php if ( 'manage-members' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/' . $group->slug ?>/admin/manage-members"><?php _e( 'Members', 'trendr' ); ?></a></li>

	<?php if ( $groups_template->group->status == 'private' ) : ?>
		<li<?php if ( 'membership-requests' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/' . $group->slug ?>/admin/membership-requests"><?php _e( 'Requests', 'trendr' ); ?></a></li>
	<?php endif; ?>

	<?php do_action( 'groups_admin_tabs', $current_tab, $group->slug ) ?>

	<li<?php if ( 'delete-group' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/' . $group->slug ?>/admin/delete-group"><?php _e( 'Delete', 'trendr' ); ?></a></li>
<?php
}

function trs_group_total_for_member() {
	echo trs_get_group_total_for_member();
}
	function trs_get_group_total_for_member() {
		return apply_filters( 'trs_get_group_total_for_member', TRS_Groups_Member::total_group_count() );
	}

function trs_group_form_action( $page ) {
	echo trs_get_group_form_action( $page );
}
	function trs_get_group_form_action( $page, $group = false ) {
		global $trs, $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_group_form_action', trs_get_group_permalink( $group ) . $page );
	}

function trs_group_admin_form_action( $page = false ) {
	echo trs_get_group_admin_form_action( $page );
}
	function trs_get_group_admin_form_action( $page = false, $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		if ( !$page )
			$page = trs_action_variable( 0 );

		return apply_filters( 'trs_group_admin_form_action', trs_get_group_permalink( $group ) . 'admin/' . $page );
	}

function trs_group_has_requested_membership( $group = false ) {
	global $groups_template;

	if ( !$group )
		$group = $groups_template->group;

	if ( groups_check_for_membership_request( trs_loggedin_user_id(), $group->id ) )
		return true;

	return false;
}

/**
 * trs_group_is_member()
 *
 * Checks if current user is member of a group.
 *
 * @uses is_super_admin Check if current user is super admin
 * @uses apply_filters Creates trs_group_is_member filter and passes $is_member
 * @usedby groups/activity.php, groups/single/forum/edit.php, groups/single/forum/topic.php to determine template part visibility
 * @global array $trs trendr Master global
 * @global object $groups_template Current Group (usually in template loop)
 * @param object $group Group to check is_member
 * @return bool If user is member of group or not
 */
function trs_group_is_member( $group = false ) {
	global $trs, $groups_template;

	// Site admins always have access
	if ( $trs->loggedin_user->is_super_admin )
		return true;

	if ( !$group )
		$group = $groups_template->group;

	return apply_filters( 'trs_group_is_member', !empty( $group->is_member ) );
}

/**
 * Checks if a user is banned from a group.
 *
 * If this function is invoked inside the groups template loop (e.g. the group directory), then
 * check $groups_template->group->is_banned instead of making another SQL query.
 * However, if used in a single group's pages, we must use groups_is_user_banned().
 *
 * @global object $trs trendr global settings
 * @global TRS_Groups_Template $groups_template Group template loop object
 * @param object $group Group to check if user is banned from the group
 * @param int $user_id
 * @return bool If user is banned from the group or not
 * @since 1.5
 */
function trs_group_is_user_banned( $group = false, $user_id = 0 ) {
	global $trs, $groups_template;

	// Site admins always have access
	if ( $trs->loggedin_user->is_super_admin )
		return false;

	if ( !$group ) {
		$group = $groups_template->group;

		if ( !$user_id && isset( $group->is_banned ) )
			return apply_filters( 'trs_group_is_user_banned', $group->is_banned );
	}

	if ( !$user_id )
		$user_id = $trs->loggedin_user->id;

	return apply_filters( 'trs_group_is_user_banned', groups_is_user_banned( $user_id, $group->id ) );
}

function trs_group_accept_invite_link() {
	echo trs_get_group_accept_invite_link();
}
	function trs_get_group_accept_invite_link( $group = false ) {
		global $groups_template, $trs;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_accept_invite_link', trm_nonce_url( $trs->loggedin_user->domain . trs_get_groups_slug() . '/invites/accept/' . $group->id, 'groups_accept_invite' ) );
	}

function trs_group_reject_invite_link() {
	echo trs_get_group_reject_invite_link();
}
	function trs_get_group_reject_invite_link( $group = false ) {
		global $groups_template, $trs;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_reject_invite_link', trm_nonce_url( $trs->loggedin_user->domain . trs_get_groups_slug() . '/invites/reject/' . $group->id, 'groups_reject_invite' ) );
	}

function trs_group_leave_confirm_link() {
	echo trs_get_group_leave_confirm_link();
}
	function trs_get_group_leave_confirm_link( $group = false ) {
		global $groups_template, $trs;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_group_leave_confirm_link', trm_nonce_url( trs_get_group_permalink( $group ) . 'leave-group/yes', 'groups_leave_group' ) );
	}

function trs_group_leave_reject_link() {
	echo trs_get_group_leave_reject_link();
}
	function trs_get_group_leave_reject_link( $group = false ) {
		global $groups_template, $trs;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_get_group_leave_reject_link', trs_get_group_permalink( $group ) );
	}

function trs_group_send_invite_form_action() {
	echo trs_get_group_send_invite_form_action();
}
	function trs_get_group_send_invite_form_action( $group = false ) {
		global $groups_template, $trs;

		if ( !$group )
			$group = $groups_template->group;

		return apply_filters( 'trs_group_send_invite_form_action', trs_get_group_permalink( $group ) . 'send-invites/send' );
	}

function trs_has_friends_to_invite( $group = false ) {
	global $groups_template, $trs;

	if ( !trs_is_active( 'friends' ) )
		return false;

	if ( !$group )
		$group = $groups_template->group;

	if ( !friends_check_user_has_friends( $trs->loggedin_user->id ) || !friends_count_invitable_friends( $trs->loggedin_user->id, $group->id ) )
		return false;

	return true;
}

function trs_group_new_topic_button( $group = false ) {
	echo trs_get_group_new_topic_button();
}
	function trs_get_group_new_topic_button( $group = false ) {
		global $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		if ( !is_user_logged_in() || trs_group_is_user_banned() || !trs_is_group_forum() || trs_is_group_forum_topic() )
			return false;

		$button = trs_button( array (
			'id'                => 'new_topic',
			'component'         => 'groups',
			'must_be_logged_in' => true,
			'block_self'        => true,
			'wrapper_class'     => 'group-button',
			'link_href'         => '#post-new',
			'link_class'        => 'group-button show-hide-new',
			'link_id'           => 'new-topic-button',
			'link_text'         => __( 'New Topic', 'trendr' ),
			'link_title'        => __( 'New Topic', 'trendr' ),
		) );

		// Filter and return the HTML button
		return trs_get_button( apply_filters( 'trs_get_group_new_topic_button', $button ) );
	}

function trs_group_join_button( $group = false ) {
	echo trs_get_group_join_button( $group );
}
	function trs_get_group_join_button( $group = false ) {
		global $trs, $groups_template;

		if ( !$group )
			$group = $groups_template->group;

		if ( !is_user_logged_in() || trs_group_is_user_banned( $group ) )
			return false;

		// Group creation was not completed or status is unknown
		if ( !$group->status )
			return false;

		// Already a member
		if ( $group->is_member ) {

			// Stop sole admins from abandoning their group
	 		$group_admins = groups_get_group_admins( $group->id );
		 	if ( 1 == count( $group_admins ) && $group_admins[0]->user_id == $trs->loggedin_user->id )
				return false;

			$button = array(
				'id'                => 'leave_group',
				'component'         => 'groups',
				'must_be_logged_in' => true,
				'block_self'        => false,
				'wrapper_class'     => 'group-button ' . $group->status,
				'wrapper_id'        => 'groupbutton-' . $group->id,
				'link_href'         => trm_nonce_url( trs_get_group_permalink( $group ) . 'leave-group', 'groups_leave_group' ),
				'link_text'         => __( 'Leave Group', 'trendr' ),
				'link_title'        => __( 'Leave Group', 'trendr' ),
				'link_class'        => 'group-button leave-group',
			);

		// Not a member
		} else {

			// Show different buttons based on group status
			switch ( $group->status ) {
				case 'hidden' :
					return false;
					break;

				case 'public':
					$button = array(
						'id'                => 'join_group',
						'component'         => 'groups',
						'must_be_logged_in' => true,
						'block_self'        => false,
						'wrapper_class'     => 'group-button ' . $group->status,
						'wrapper_id'        => 'groupbutton-' . $group->id,
						'link_href'         => trm_nonce_url( trs_get_group_permalink( $group ) . 'join', 'groups_join_group' ),
						'link_text'         => __( 'Join Group', 'trendr' ),
						'link_title'        => __( 'Join Group', 'trendr' ),
						'link_class'        => 'group-button join-group',
					);
					break;

				case 'private' :

					// Member has not requested membership yet
					if ( !trs_group_has_requested_membership( $group ) ) {
						$button = array(
							'id'                => 'request_membership',
							'component'         => 'groups',
							'must_be_logged_in' => true,
							'block_self'        => false,
							'wrapper_class'     => 'group-button ' . $group->status,
							'wrapper_id'        => 'groupbutton-' . $group->id,
							'link_href'         => trm_nonce_url( trs_get_group_permalink( $group ) . 'request-membership', 'groups_request_membership' ),
							'link_text'         => __( 'Request Membership', 'trendr' ),
							'link_title'        => __( 'Request Membership', 'trendr' ),
							'link_class'        => 'group-button request-membership',
						);

					// Member has requested membership already
					} else {
						$button = array(
							'id'                => 'membership_requested',
							'component'         => 'groups',
							'must_be_logged_in' => true,
							'block_self'        => false,
							'wrapper_class'     => 'group-button pending ' . $group->status,
							'wrapper_id'        => 'groupbutton-' . $group->id,
							'link_href'         => trs_get_group_permalink( $group ),
							'link_text'         => __( 'Request Sent', 'trendr' ),
							'link_title'        => __( 'Request Sent', 'trendr' ),
							'link_class'        => 'group-button pending membership-requested',
						);
					}

					break;
			}
		}

		// Filter and return the HTML button
		return trs_get_button( apply_filters( 'trs_get_group_join_button', $button ) );
	}

function trs_group_status_message( $group = false ) {
	global $groups_template;

	if ( !$group )
		$group = $groups_template->group;

	if ( 'private' == $group->status ) {
		if ( !trs_group_has_requested_membership() )
			if ( is_user_logged_in() )
				_e( 'This is a private group and you must request group membership in order to join.', 'trendr' );
			else
				_e( 'This is a private group. To join you must be a registered site member and request group membership.', 'trendr' );
		else
			_e( 'This is a private group. Your membership request is awaiting approval from the group administrator.', 'trendr' );
	} else {
		_e( 'This is a hidden group and only invited members can join.', 'trendr' );
	}
}

function trs_group_hidden_fields() {
	if ( isset( $_REQUEST['s'] ) ) {
		echo '<input type="hidden" id="search_terms" value="' . esc_attr( $_REQUEST['s'] ) . '" name="search_terms" />';
	}

	if ( isset( $_REQUEST['letter'] ) ) {
		echo '<input type="hidden" id="selected_letter" value="' . esc_attr( $_REQUEST['letter'] ) . '" name="selected_letter" />';
	}

	if ( isset( $_REQUEST['groups_search'] ) ) {
		echo '<input type="hidden" id="search_terms" value="' . esc_attr( $_REQUEST['groups_search'] ) . '" name="search_terms" />';
	}
}

function trs_total_group_count() {
	echo trs_get_total_group_count();
}
	function trs_get_total_group_count() {
		return apply_filters( 'trs_get_total_group_count', groups_get_total_group_count() );
	}

function trs_total_group_count_for_user( $user_id = 0 ) {
	echo trs_get_total_group_count_for_user( $user_id );
}
	function trs_get_total_group_count_for_user( $user_id = 0 ) {
		return apply_filters( 'trs_get_total_group_count_for_user', groups_total_groups_for_user( $user_id ) );
	}


/***************************************************************************
 * Group Members Template Tags
 **/

class TRS_Groups_Group_Members_Template {
	var $current_member = -1;
	var $member_count;
	var $members;
	var $member;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_group_count;

	function trs_groups_group_members_template( $group_id, $per_page, $max, $exclude_admins_mods, $exclude_banned, $exclude ) {
		$this->__construct( $group_id, $per_page, $max, $exclude_admins_mods, $exclude_banned, $exclude );
	}

	function __construct( $group_id, $per_page, $max, $exclude_admins_mods, $exclude_banned, $exclude ) {
		global $trs;

		$this->pag_page = isset( $_REQUEST['mlpage'] ) ? intval( $_REQUEST['mlpage'] ) : 1;
		$this->pag_num = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;

		$this->members = TRS_Groups_Member::get_all_for_group( $group_id, $this->pag_num, $this->pag_page, $exclude_admins_mods, $exclude_banned, $exclude );

		if ( !$max || $max >= (int)$this->members['count'] )
			$this->total_member_count = (int)$this->members['count'];
		else
			$this->total_member_count = (int)$max;

		$this->members = $this->members['members'];

		if ( $max ) {
			if ( $max >= count($this->members) )
				$this->member_count = count($this->members);
			else
				$this->member_count = (int)$max;
		} else {
			$this->member_count = count($this->members);
		}

		$this->pag_links = paginate_links( array(
			'base' => add_query_arg( 'mlpage', '%#%' ),
			'format' => '',
			'total' => ceil( $this->total_member_count / $this->pag_num ),
			'current' => $this->pag_page,
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
			'mid_size' => 1
		));
	}

	function has_members() {
		if ( $this->member_count )
			return true;

		return false;
	}

	function next_member() {
		$this->current_member++;
		$this->member = $this->members[$this->current_member];

		return $this->member;
	}

	function rewind_members() {
		$this->current_member = -1;
		if ( $this->member_count > 0 ) {
			$this->member = $this->members[0];
		}
	}

	function members() {
		if ( $this->current_member + 1 < $this->member_count ) {
			return true;
		} elseif ( $this->current_member + 1 == $this->member_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_members();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_member() {
		global $member;

		$this->in_the_loop = true;
		$this->member = $this->next_member();

		if ( 0 == $this->current_member ) // loop has just started
			do_action('loop_start');
	}
}

function trs_group_has_members( $args = '' ) {
	global $trs, $members_template;

	$defaults = array(
		'group_id' => trs_get_current_group_id(),
		'per_page' => 20,
		'max' => false,
		'exclude' => false,
		'exclude_admins_mods' => 1,
		'exclude_banned' => 1
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$members_template = new TRS_Groups_Group_Members_Template( $group_id, $per_page, $max, (int)$exclude_admins_mods, (int)$exclude_banned, $exclude );
	return apply_filters( 'trs_group_has_members', $members_template->has_members(), $members_template );
}

function trs_group_members() {
	global $members_template;

	return $members_template->members();
}

function trs_group_the_member() {
	global $members_template;

	return $members_template->the_member();
}

function trs_group_member_portrait() {
	echo trs_get_group_member_portrait();
}
	function trs_get_group_member_portrait() {
		global $members_template;

		return apply_filters( 'trs_get_group_member_portrait', trs_core_fetch_portrait( array( 'item_id' => $members_template->member->user_id, 'type' => 'full', 'email' => $members_template->member->user_email, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) );
	}

function trs_group_member_portrait_thumb() {
	echo trs_get_group_member_portrait_thumb();
}
	function trs_get_group_member_portrait_thumb() {
		global $members_template;

		return apply_filters( 'trs_get_group_member_portrait_thumb', trs_core_fetch_portrait( array( 'item_id' => $members_template->member->user_id, 'type' => 'thumb', 'email' => $members_template->member->user_email, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) );
	}

function trs_group_member_portrait_mini( $width = 30, $height = 30 ) {
	echo trs_get_group_member_portrait_mini( $width, $height );
}
	function trs_get_group_member_portrait_mini( $width = 30, $height = 30 ) {
		global $members_template;

		return apply_filters( 'trs_get_group_member_portrait_mini', trs_core_fetch_portrait( array( 'item_id' => $members_template->member->user_id, 'type' => 'thumb', 'width' => $width, 'height' => $height, 'email' => $members_template->member->user_email, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) );
	}

function trs_group_member_name() {
	echo trs_get_group_member_name();
}
	function trs_get_group_member_name() {
		global $members_template;

		return apply_filters( 'trs_get_group_member_name', $members_template->member->display_name );
	}

function trs_group_member_url() {
	echo trs_get_group_member_url();
}
	function trs_get_group_member_url() {
		global $members_template;

		return apply_filters( 'trs_get_group_member_url', trs_core_get_user_domain( $members_template->member->user_id, $members_template->member->user_nicename, $members_template->member->user_login ) );
	}

function trs_group_member_link() {
	echo trs_get_group_member_link();
}
	function trs_get_group_member_link() {
		global $members_template;

		return apply_filters( 'trs_get_group_member_link', '<a href="' . trs_core_get_user_domain( $members_template->member->user_id, $members_template->member->user_nicename, $members_template->member->user_login ) . '">' . $members_template->member->display_name . '</a>' );
	}

function trs_group_member_domain() {
	echo trs_get_group_member_domain();
}
	function trs_get_group_member_domain() {
		global $members_template;

		return apply_filters( 'trs_get_group_member_domain', trs_core_get_user_domain( $members_template->member->user_id, $members_template->member->user_nicename, $members_template->member->user_login ) );
	}

function trs_group_member_is_friend() {
	echo trs_get_group_member_is_friend();
}
	function trs_get_group_member_is_friend() {
		global $members_template;

		if ( !isset( $members_template->member->is_friend ) )
			$friend_status = 'not_friends';
		else
			$friend_status = ( 0 == $members_template->member->is_friend ) ? 'pending' : 'is_friend';

		return apply_filters( 'trs_get_group_member_is_friend', $friend_status );
	}

function trs_group_member_is_banned() {
	echo trs_get_group_member_is_banned();
}
	function trs_get_group_member_is_banned() {
		global $members_template, $groups_template;

		return apply_filters( 'trs_get_group_member_is_banned', $members_template->member->is_banned );
	}

function trs_group_member_css_class() {
	global $members_template;

	if ( $members_template->member->is_banned )
		echo apply_filters( 'trs_group_member_css_class', 'banned-user' );
}

function trs_group_member_joined_since() {
	echo trs_get_group_member_joined_since();
}
	function trs_get_group_member_joined_since() {
		global $members_template;

		return apply_filters( 'trs_get_group_member_joined_since', trs_core_get_last_activity( $members_template->member->date_modified, __( 'joined %s', 'trendr') ) );
	}

function trs_group_member_id() {
	echo trs_get_group_member_id();
}
	function trs_get_group_member_id() {
		global $members_template;

		return apply_filters( 'trs_get_group_member_id', $members_template->member->user_id );
	}

function trs_group_member_needs_pagination() {
	global $members_template;

	if ( $members_template->total_member_count > $members_template->pag_num )
		return true;

	return false;
}

function trs_group_pag_id() {
	echo trs_get_group_pag_id();
}
	function trs_get_group_pag_id() {
		global $trs;

		return apply_filters( 'trs_get_group_pag_id', 'pag' );
	}

function trs_group_member_pagination() {
	echo trs_get_group_member_pagination();
	trm_nonce_field( 'trs_groups_member_list', '_member_pag_nonce' );
}
	function trs_get_group_member_pagination() {
		global $members_template;
		return apply_filters( 'trs_get_group_member_pagination', $members_template->pag_links );
	}

function trs_group_member_pagination_count() {
	echo trs_get_group_member_pagination_count();
}
	function trs_get_group_member_pagination_count() {
		global $members_template;

		$start_num = intval( ( $members_template->pag_page - 1 ) * $members_template->pag_num ) + 1;
		$from_num = trs_core_number_format( $start_num );
		$to_num = trs_core_number_format( ( $start_num + ( $members_template->pag_num - 1 ) > $members_template->total_member_count ) ? $members_template->total_member_count : $start_num + ( $members_template->pag_num - 1 ) );
		$total = trs_core_number_format( $members_template->total_member_count );

		return apply_filters( 'trs_get_group_member_pagination_count', sprintf( __( 'Viewing members %1$s to %2$s (of %3$s members)', 'trendr' ), $from_num, $to_num, $total ) );
	}

function trs_group_member_admin_pagination() {
	echo trs_get_group_member_admin_pagination();
	trm_nonce_field( 'trs_groups_member_admin_list', '_member_admin_pag_nonce' );
}
	function trs_get_group_member_admin_pagination() {
		global $members_template;

		return $members_template->pag_links;
	}


/***************************************************************************
 * Group Creation Process Template Tags
 **/

/**
 * Determine if the current logged in user can create groups.
 *
 * @package trendr Groups
 * @since 1.5
 *
 * @uses apply_filters() To call 'trs_user_can_create_groups'.
 * @uses trs_get_option() To retrieve value of 'trs_restrict_group_creation'. Defaults to 0.
 * @uses is_super_admin() To determine if current user if super admin.
 *
 * @return bool True if user can create groups. False otherwise.
 */
function trs_user_can_create_groups() {
	// Super admin can always create groups
	if ( is_super_admin() )
		return true;

	// Get group creation option, default to 0 (allowed)
	$restricted = (int) trs_get_option( 'trs_restrict_group_creation', 0 );

	// Allow by default
	$can_create = true;

	// Are regular users restricted?
	if ( $restricted )
		$can_create = false;

	return apply_filters( 'trs_user_can_create_groups', $can_create, $restricted );
}

function trs_group_creation_tabs() {
	global $trs;

	if ( !is_array( $trs->groups->group_creation_steps ) )
		return false;

	if ( !$trs->groups->current_create_step )
		$trs->groups->current_create_step = array_shift( array_keys( $trs->groups->group_creation_steps ) );

	$counter = 1;

	foreach ( (array)$trs->groups->group_creation_steps as $slug => $step ) {
		$is_enabled = trs_are_previous_group_creation_steps_complete( $slug ); ?>

		<li<?php if ( $trs->groups->current_create_step == $slug ) : ?> class="current"<?php endif; ?>><?php if ( $is_enabled ) : ?><a href="<?php echo trs_get_root_domain() . '/' . trs_get_groups_root_slug() ?>/create/step/<?php echo $slug ?>/"><?php else: ?><span><?php endif; ?><?php echo $counter ?>. <?php echo $step['name'] ?><?php if ( $is_enabled ) : ?></a><?php else: ?></span><?php endif ?></li><?php
		$counter++;
	}

	unset( $is_enabled );

	do_action( 'groups_creation_tabs' );
}

function trs_group_creation_stage_title() {
	global $trs;

	echo apply_filters( 'trs_group_creation_stage_title', '<span>&mdash; ' . $trs->groups->group_creation_steps[$trs->groups->current_create_step]['name'] . '</span>' );
}

function trs_group_creation_form_action() {
	echo trs_get_group_creation_form_action();
}
	function trs_get_group_creation_form_action() {
		global $trs;

		if ( !trs_action_variable( 1 ) )
			$trs->action_variables[1] = array_shift( array_keys( $trs->groups->group_creation_steps ) );

		return apply_filters( 'trs_get_group_creation_form_action', trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/create/step/' . trs_action_variable( 1 ) );
	}

function trs_is_group_creation_step( $step_slug ) {
	global $trs;

	/* Make sure we are in the groups component */
	if ( !trs_is_groups_component() || !trs_is_current_action( 'create' ) )
		return false;

	/* If this the first step, we can just accept and return true */
	if ( !trs_action_variable( 1 ) && array_shift( array_keys( $trs->groups->group_creation_steps ) ) == $step_slug )
		return true;

	/* Before allowing a user to see a group creation step we must make sure previous steps are completed */
	if ( !trs_is_first_group_creation_step() ) {
		if ( !trs_are_previous_group_creation_steps_complete( $step_slug ) )
			return false;
	}

	/* Check the current step against the step parameter */
	if ( trs_is_action_variable( $step_slug ) )
		return true;

	return false;
}

function trs_is_group_creation_step_complete( $step_slugs ) {
	global $trs;

	if ( !isset( $trs->groups->completed_create_steps ) )
		return false;

	if ( is_array( $step_slugs ) ) {
		$found = true;

		foreach ( (array)$step_slugs as $step_slug ) {
			if ( !in_array( $step_slug, $trs->groups->completed_create_steps ) )
				$found = false;
		}

		return $found;
	} else {
		return in_array( $step_slugs, $trs->groups->completed_create_steps );
	}

	return true;
}

function trs_are_previous_group_creation_steps_complete( $step_slug ) {
	global $trs;

	/* If this is the first group creation step, return true */
	if ( array_shift( array_keys( $trs->groups->group_creation_steps ) ) == $step_slug )
		return true;

	reset( $trs->groups->group_creation_steps );
	unset( $previous_steps );

	/* Get previous steps */
	foreach ( (array)$trs->groups->group_creation_steps as $slug => $name ) {
		if ( $slug == $step_slug )
			break;

		$previous_steps[] = $slug;
	}

	return trs_is_group_creation_step_complete( $previous_steps );
}

function trs_new_group_id() {
	echo trs_get_new_group_id();
}
	function trs_get_new_group_id() {
		global $trs;

		if ( isset( $trs->groups->new_group_id ) )
			$new_group_id = $trs->groups->new_group_id;
		else
			$new_group_id = 0;

		return apply_filters( 'trs_get_new_group_id', $new_group_id );
	}

function trs_new_group_name() {
	echo trs_get_new_group_name();
}
	function trs_get_new_group_name() {
		global $trs;

		if ( isset( $trs->groups->current_group->name ) )
			$name = $trs->groups->current_group->name;
		else
			$name = '';

		return apply_filters( 'trs_get_new_group_name', $name );
	}

function trs_new_group_description() {
	echo trs_get_new_group_description();
}
	function trs_get_new_group_description() {
		global $trs;

		if ( isset( $trs->groups->current_group->description ) )
			$description = $trs->groups->current_group->description;
		else
			$description = '';

		return apply_filters( 'trs_get_new_group_description', $description );
	}

function trs_new_group_enable_forum() {
	echo trs_get_new_group_enable_forum();
}
	function trs_get_new_group_enable_forum() {
		global $trs;
		return (int) apply_filters( 'trs_get_new_group_enable_forum', $trs->groups->current_group->enable_forum );
	}

function trs_new_group_status() {
	echo trs_get_new_group_status();
}
	function trs_get_new_group_status() {
		global $trs;
		return apply_filters( 'trs_get_new_group_status', $trs->groups->current_group->status );
	}

function trs_new_group_portrait( $args = '' ) {
	echo trs_get_new_group_portrait( $args );
}
	function trs_get_new_group_portrait( $args = '' ) {
		global $trs;

		$defaults = array(
			'type' => 'full',
			'width' => false,
			'height' => false,
			'class' => 'portrait',
			'id' => 'portrait-crop-preview',
			'alt' => __( 'Group portrait', 'trendr' ),
			'no_grav' => false
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		return apply_filters( 'trs_get_new_group_portrait', trs_core_fetch_portrait( array( 'item_id' => $trs->groups->current_group->id, 'object' => 'group', 'type' => $type, 'portrait_dir' => 'group-portraits', 'alt' => $alt, 'width' => $width, 'height' => $height, 'class' => $class, 'no_grav' => $no_grav ) ) );
	}

function trs_group_creation_previous_link() {
	echo trs_get_group_creation_previous_link();
}
	function trs_get_group_creation_previous_link() {
		global $trs;

		foreach ( (array)$trs->groups->group_creation_steps as $slug => $name ) {
			if ( trs_is_action_variable( $slug ) )
				break;

			$previous_steps[] = $slug;
		}

		return apply_filters( 'trs_get_group_creation_previous_link', trailingslashit( trs_get_root_domain() ) . trs_get_groups_root_slug() . '/create/step/' . array_pop( $previous_steps ) );
	}

function trs_is_last_group_creation_step() {
	global $trs;

	$last_step = array_pop( array_keys( $trs->groups->group_creation_steps ) );

	if ( $last_step == $trs->groups->current_create_step )
		return true;

	return false;
}

function trs_is_first_group_creation_step() {
	global $trs;

	$first_step = array_shift( array_keys( $trs->groups->group_creation_steps ) );

	if ( $first_step == $trs->groups->current_create_step )
		return true;

	return false;
}

function trs_new_group_invite_friend_list() {
	echo trs_get_new_group_invite_friend_list();
}
	function trs_get_new_group_invite_friend_list( $args = '' ) {
		global $trs;

		if ( !trs_is_active( 'friends' ) )
			return false;

		$defaults = array(
			'group_id'  => false,
			'separator' => 'li'
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if ( empty( $group_id ) )
			$group_id = !empty( $trs->groups->new_group_id ) ? $trs->groups->new_group_id : $trs->groups->current_group->id;

		if ( $friends = friends_get_friends_invite_list( $trs->loggedin_user->id, $group_id ) ) {
			$invites = groups_get_invites_for_group( $trs->loggedin_user->id, $group_id );

			for ( $i = 0, $count = count( $friends ); $i < $count; ++$i ) {
				$checked = '';

				if ( !empty( $invites ) ) {
					if ( in_array( $friends[$i]['id'], $invites ) )
						$checked = ' checked="checked"';
				}

				$items[] = '<' . $separator . '><input' . $checked . ' type="checkbox" name="friends[]" id="f-' . $friends[$i]['id'] . '" value="' . esc_attr( $friends[$i]['id'] ) . '" /> ' . $friends[$i]['full_name'] . '</' . $separator . '>';
			}
		}

		if ( !empty( $items ) )
			return implode( "\n", (array)$items );

		return false;
	}

function trs_directory_groups_search_form() {
	global $trs;

	$default_search_value = trs_get_search_default_text( 'groups' );
	$search_value         = !empty( $_REQUEST['s'] ) ? stripslashes( $_REQUEST['s'] ) : $default_search_value; ?>

	<form action="" method="get" id="search-groups-form">
		<label><input type="text" name="s" id="groups_search" value="<?php echo esc_attr( $search_value ) ?>"  onfocus="if (this.value == '<?php echo $default_search_value ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php echo $default_search_value ?>';}" /></label>
		<input type="submit" id="groups_search_submit" name="groups_search_submit" value="<?php _e( 'Search', 'trendr' ) ?>" />
	</form>

<?php
}

/**
 * Displays group header tabs
 *
 * @package trendr
 * @todo Deprecate?
 */
function trs_groups_header_tabs() {
	global $create_group_step, $completed_to_step;
?>
	<li<?php if ( !trs_action_variable( 0 ) || trs_is_action_variable( 'recently-active', 0 ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo $trs->displayed_user->domain . trs_get_groups_slug() ?>/my-groups/recently-active"><?php _e( 'Recently Active', 'trendr' ) ?></a></li>
	<li<?php if ( trs_is_action_variable( 'recently-joined', 0 ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo $trs->displayed_user->domain . trs_get_groups_slug() ?>/my-groups/recently-joined"><?php _e( 'Recently Joined', 'trendr' ) ?></a></li>
	<li<?php if ( trs_is_action_variable( 'most-popular', 0 ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo $trs->displayed_user->domain . trs_get_groups_slug() ?>/my-groups/most-popular"><?php _e( 'Most Popular', 'trendr' ) ?></a></li>
	<li<?php if ( trs_is_action_variable( 'admin-of', 0 ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo $trs->displayed_user->domain . trs_get_groups_slug() ?>/my-groups/admin-of"><?php _e( 'Administrator Of', 'trendr' ) ?></a></li>
	<li<?php if ( trs_is_action_variable( 'mod-of', 0 ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo $trs->displayed_user->domain . trs_get_groups_slug() ?>/my-groups/mod-of"><?php _e( 'Moderator Of', 'trendr' ) ?></a></li>
	<li<?php if ( trs_is_action_variable( 'alphabetically' ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo $trs->displayed_user->domain . trs_get_groups_slug() ?>/my-groups/alphabetically"><?php _e( 'Alphabetically', 'trendr' ) ?></a></li>
<?php
	do_action( 'groups_header_tabs' );
}

/**
 * Displays group filter titles
 *
 * @package trendr
 * @todo Deprecate?
 */
function trs_groups_filter_title() {
	$current_filter = trs_action_variable( 0 );

	switch ( $current_filter ) {
		case 'recently-active': default:
			_e( 'Recently Active', 'trendr' );
			break;
		case 'recently-joined':
			_e( 'Recently Joined', 'trendr' );
			break;
		case 'most-popular':
			_e( 'Most Popular', 'trendr' );
			break;
		case 'admin-of':
			_e( 'Administrator Of', 'trendr' );
			break;
		case 'mod-of':
			_e( 'Moderator Of', 'trendr' );
			break;
		case 'alphabetically':
			_e( 'Alphabetically', 'trendr' );
		break;
	}
	do_action( 'trs_groups_filter_title' );
}

function trs_is_group_admin_screen( $slug ) {
	if ( !trs_is_groups_component() || !trs_is_current_action( 'admin' ) )
		return false;

	if ( trs_is_action_variable( $slug ) )
		return true;

	return false;
}

/************************************************************************************
 * Group Avatar Template Tags
 **/

function trs_group_current_portrait() {
	global $trs;

	if ( $trs->groups->current_group->portrait_full ) { ?>

		<img src="<?php echo esc_attr( $trs->groups->current_group->portrait_full ) ?>" alt="<?php _e( 'Group Avatar', 'trendr' ) ?>" class="portrait" />

	<?php } else { ?>

		<img src="<?php echo $trs->groups->image_base . '/none.gif' ?>" alt="<?php _e( 'No Group Avatar', 'trendr' ) ?>" class="portrait" />

	<?php }
}

function trs_get_group_has_portrait() {
	global $trs;

	if ( !empty( $_FILES ) || !trs_core_fetch_portrait( array( 'item_id' => $trs->groups->current_group->id, 'object' => 'group', 'no_grav' => true ) ) )
		return false;

	return true;
}

function trs_group_portrait_delete_link() {
	echo trs_get_group_portrait_delete_link();
}
	function trs_get_group_portrait_delete_link() {
		global $trs;

		return apply_filters( 'trs_get_group_portrait_delete_link', trm_nonce_url( trs_get_group_permalink( $trs->groups->current_group ) . '/admin/group-portrait/delete', 'trs_group_portrait_delete' ) );
	}

function trs_group_portrait_edit_form() {
	groups_portrait_upload();
}

function trs_custom_group_boxes() {
	do_action( 'groups_custom_group_boxes' );
}

function trs_custom_group_admin_tabs() {
	do_action( 'groups_custom_group_admin_tabs' );
}

function trs_custom_group_fields_editable() {
	do_action( 'groups_custom_group_fields_editable' );
}

function trs_custom_group_fields() {
	do_action( 'groups_custom_group_fields' );
}


/************************************************************************************
 * Membership Requests Template Tags
 **/

class TRS_Groups_Membership_Requests_Template {
	var $current_request = -1;
	var $request_count;
	var $requests;
	var $request;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_request_count;

	function trs_groups_membership_requests_template( $group_id, $per_page, $max ) {
		$this->__construct( $group_id, $per_page, $max );
	}


	function __construct( $group_id, $per_page, $max ) {

		global $trs;

		$this->pag_page = isset( $_REQUEST['mrpage'] ) ? intval( $_REQUEST['mrpage'] ) : 1;
		$this->pag_num = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;

		$this->requests = TRS_Groups_Group::get_membership_requests( $group_id, $this->pag_num, $this->pag_page );

		if ( !$max || $max >= (int)$this->requests['total'] )
			$this->total_request_count = (int)$this->requests['total'];
		else
			$this->total_request_count = (int)$max;

		$this->requests = $this->requests['requests'];

		if ( $max ) {
			if ( $max >= count($this->requests) )
				$this->request_count = count($this->requests);
			else
				$this->request_count = (int)$max;
		} else {
			$this->request_count = count($this->requests);
		}

		$this->pag_links = paginate_links( array(
			'base' => add_query_arg( 'mrpage', '%#%' ),
			'format' => '',
			'total' => ceil( $this->total_request_count / $this->pag_num ),
			'current' => $this->pag_page,
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
			'mid_size' => 1
		) );
	}

	function has_requests() {
		if ( $this->request_count )
			return true;

		return false;
	}

	function next_request() {
		$this->current_request++;
		$this->request = $this->requests[$this->current_request];

		return $this->request;
	}

	function rewind_requests() {
		$this->current_request = -1;

		if ( $this->request_count > 0 )
			$this->request = $this->requests[0];
	}

	function requests() {
		if ( $this->current_request + 1 < $this->request_count ) {
			return true;
		} elseif ( $this->current_request + 1 == $this->request_count ) {
			do_action('group_request_loop_end');
			// Do some cleaning up after the loop
			$this->rewind_requests();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_request() {
		global $request;

		$this->in_the_loop = true;
		$this->request = $this->next_request();

		if ( 0 == $this->current_request ) // loop has just started
			do_action('group_request_loop_start');
	}
}

function trs_group_has_membership_requests( $args = '' ) {
	global $requests_template, $groups_template;

	$defaults = array(
		'group_id' => $groups_template->group->id,
		'per_page' => 10,
		'max'      => false
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$requests_template = new TRS_Groups_Membership_Requests_Template( $group_id, $per_page, $max );
	return apply_filters( 'trs_group_has_membership_requests', $requests_template->has_requests(), $requests_template );
}

function trs_group_membership_requests() {
	global $requests_template;

	return $requests_template->requests();
}

function trs_group_the_membership_request() {
	global $requests_template;

	return $requests_template->the_request();
}

function trs_group_request_user_portrait_thumb() {
	global $requests_template;

	echo apply_filters( 'trs_group_request_user_portrait_thumb', trs_core_fetch_portrait( array( 'item_id' => $requests_template->request->user_id, 'type' => 'thumb', 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) );
}

function trs_group_request_reject_link() {
	echo trs_get_group_request_reject_link();
}
	function trs_get_group_request_reject_link() {
		global $requests_template, $groups_template;

		return apply_filters( 'trs_get_group_request_reject_link', trm_nonce_url( trs_get_group_permalink( $groups_template->group ) . '/admin/membership-requests/reject/' . $requests_template->request->id, 'groups_reject_membership_request' ) );
	}

function trs_group_request_accept_link() {
	echo trs_get_group_request_accept_link();
}
	function trs_get_group_request_accept_link() {
		global $requests_template, $groups_template;

		return apply_filters( 'trs_get_group_request_accept_link', trm_nonce_url( trs_get_group_permalink( $groups_template->group ) . '/admin/membership-requests/accept/' . $requests_template->request->id, 'groups_accept_membership_request' ) );
	}

function trs_group_request_user_link() {
	echo trs_get_group_request_user_link();
}
	function trs_get_group_request_user_link() {
		global $requests_template;

		return apply_filters( 'trs_get_group_request_user_link', trs_core_get_userlink( $requests_template->request->user_id ) );
	}

function trs_group_request_time_since_requested() {
	global $requests_template;

	echo apply_filters( 'trs_group_request_time_since_requested', sprintf( __( 'requested %s', 'trendr' ), trs_core_time_since( strtotime( $requests_template->request->date_modified ) ) ) );
}

function trs_group_request_comment() {
	global $requests_template;

	echo apply_filters( 'trs_group_request_comment', strip_tags( stripslashes( $requests_template->request->comments ) ) );
}

/************************************************************************************
 * Invite Friends Template Tags
 **/

class TRS_Groups_Invite_Template {
	var $current_invite = -1;
	var $invite_count;
	var $invites;
	var $invite;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_invite_count;

	function trs_groups_invite_template( $user_id, $group_id ) {
		$this->__construct( $user_id, $group_id );
	}

	function __construct( $user_id, $group_id ) {

		global $trs;

		$this->invites = groups_get_invites_for_group( $user_id, $group_id );
		$this->invite_count = count( $this->invites );

	}

	function has_invites() {
		if ( $this->invite_count )
			return true;

		return false;
	}

	function next_invite() {
		$this->current_invite++;
		$this->invite = $this->invites[$this->current_invite];

		return $this->invite;
	}

	function rewind_invites() {
		$this->current_invite = -1;
		if ( $this->invite_count > 0 )
			$this->invite = $this->invites[0];
	}

	function invites() {
		if ( $this->current_invite + 1 < $this->invite_count ) {
			return true;
		} elseif ( $this->current_invite + 1 == $this->invite_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_invites();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_invite() {
		global $invite, $group_id;

		$this->in_the_loop = true;
		$user_id = $this->next_invite();

		$this->invite = new stdClass;
		$this->invite->user = new TRS_Core_User( $user_id );
		$this->invite->group_id = $group_id; // Globaled in trs_group_has_invites()

		if ( 0 == $this->current_invite ) // loop has just started
			do_action('loop_start');
	}
}

function trs_group_has_invites( $args = '' ) {
	global $trs, $invites_template, $group_id;

	$defaults = array(
		'group_id' => false,
		'user_id' => $trs->loggedin_user->id
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( !$group_id ) {
		// Backwards compatibility
		if ( !empty( $trs->groups->current_group ) )
			$group_id = $trs->groups->current_group->id;

		if ( !empty( $trs->groups->new_group_id ) )
			$group_id = $trs->groups->new_group_id;
	}

	if ( !$group_id )
		return false;

	$invites_template = new TRS_Groups_Invite_Template( $user_id, $group_id );
	return apply_filters( 'trs_group_has_invites', $invites_template->has_invites(), $invites_template );
}

function trs_group_invites() {
	global $invites_template;

	return $invites_template->invites();
}

function trs_group_the_invite() {
	global $invites_template;

	return $invites_template->the_invite();
}

function trs_group_invite_item_id() {
	echo trs_get_group_invite_item_id();
}
	function trs_get_group_invite_item_id() {
		global $invites_template;

		return apply_filters( 'trs_get_group_invite_item_id', 'uid-' . $invites_template->invite->user->id );
	}

function trs_group_invite_user_portrait() {
	echo trs_get_group_invite_user_portrait();
}
	function trs_get_group_invite_user_portrait() {
		global $invites_template;

		return apply_filters( 'trs_get_group_invite_user_portrait', $invites_template->invite->user->portrait_thumb );
	}

function trs_group_invite_user_link() {
	echo trs_get_group_invite_user_link();
}
	function trs_get_group_invite_user_link() {
		global $invites_template;

		return apply_filters( 'trs_get_group_invite_user_link', trs_core_get_userlink( $invites_template->invite->user->id ) );
	}

function trs_group_invite_user_last_active() {
	echo trs_get_group_invite_user_last_active();
}
	function trs_get_group_invite_user_last_active() {
		global $invites_template;

		return apply_filters( 'trs_get_group_invite_user_last_active', $invites_template->invite->user->last_active );
	}

function trs_group_invite_user_remove_invite_url() {
	echo trs_get_group_invite_user_remove_invite_url();
}
	function trs_get_group_invite_user_remove_invite_url() {
		global $invites_template;

		return trm_nonce_url( site_url( trs_get_groups_slug() . '/' . $invites_template->invite->group_id . '/invites/remove/' . $invites_template->invite->user->id ), 'groups_invite_uninvite_user' );
	}

/***
 * Groups RSS Feed Template Tags
 */

/**
 * Hook group activity feed to <head>
 *
 * @since 1.5
 */
function trs_groups_activity_feed() {
	if ( !trs_is_active( 'groups' ) || !trs_is_active( 'activity' ) || !trs_is_group() )
		return; ?>

	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo( 'name' ) ?> | <?php trs_current_group_name() ?> | <?php _e( 'Group Activity RSS Feed', 'trendr' ) ?>" href="<?php trs_group_activity_feed_link() ?>" />

<?php
}
add_action( 'trs_head', 'trs_groups_activity_feed' );

function trs_group_activity_feed_link() {
	echo trs_get_group_activity_feed_link();
}
	function trs_get_group_activity_feed_link() {
		global $trs;

		return apply_filters( 'trs_get_group_activity_feed_link', trs_get_group_permalink( $trs->groups->current_group ) . 'feed/' );
	}

/**
 * Echoes the output of trs_get_current_group_id()
 *
 * @package trendr
 * @since 1.5
 */
function trs_current_group_id() {
	echo trs_get_current_group_id();
}
	/**
	 * Returns the ID of the current group
	 *
	 * @package trendr
	 * @since 1.5
	 * @uses apply_filters() Filter trs_get_current_group_id to modify this output
	 *
	 * @return int $current_group_id The id of the current group, if there is one
	 */
	function trs_get_current_group_id() {
		$current_group = groups_get_current_group();

		$current_group_id = isset( $current_group->id ) ? (int)$current_group->id : 0;

		return apply_filters( 'trs_get_current_group_id', $current_group_id, $current_group );
	}

/**
 * Echoes the output of trs_get_current_group_slug()
 *
 * @package trendr
 * @since 1.5
 */
function trs_current_group_slug() {
	echo trs_get_current_group_slug();
}
	/**
	 * Returns the slug of the current group
	 *
	 * @package trendr
	 * @since 1.5
	 * @uses apply_filters() Filter trs_get_current_group_slug to modify this output
	 *
	 * @return str $current_group_slug The slug of the current group, if there is one
	 */
	function trs_get_current_group_slug() {
		$current_group = groups_get_current_group();

		$current_group_slug = isset( $current_group->slug ) ? $current_group->slug : '';

		return apply_filters( 'trs_get_current_group_slug', $current_group_slug, $current_group );
	}

/**
 * Echoes the output of trs_get_current_group_name()
 *
 * @package trendr
 */
function trs_current_group_name() {
	echo trs_get_current_group_name();
}
	/**
	 * Returns the name of the current group
	 *
	 * @package trendr
	 * @since 1.5
	 * @uses apply_filters() Filter trs_get_current_group_name to modify this output
	 *
	 * @return str The name of the current group, if there is one
	 */
	function trs_get_current_group_name() {
		global $trs;

		$name = apply_filters( 'trs_get_group_name', $trs->groups->current_group->name );
		return apply_filters( 'trs_get_current_group_name', $name );
	}

function trs_groups_action_link( $action = '', $query_args = '', $nonce = false ) {
	echo trs_get_groups_action_link( $action, $query_args, $nonce );
}
	function trs_get_groups_action_link( $action = '', $query_args = '', $nonce = false ) {
		global $trs;

		// Must be displayed user
		if ( empty( $trs->groups->current_group->id ) )
			return;

		// Append $action to $url if there is no $type
		if ( !empty( $action ) )
			$url = trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/' . $trs->groups->current_group->slug . '/' . $action;
		else
			$url = trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/' . $trs->groups->current_group->slug;

		// Add a slash at the end of our user url
		$url = trailingslashit( $url );

		// Add possible query arg
		if ( !empty( $query_args ) && is_array( $query_args ) )
			$url = add_query_arg( $query_args, $url );

		// To nonce, or not to nonce...
		if ( true === $nonce )
			$url = trm_nonce_url( $url );
		elseif ( is_string( $nonce ) )
			$url = trm_nonce_url( $url, $nonce );

		// Return the url, if there is one
		if ( !empty( $url ) )
			return $url;
	}
?>