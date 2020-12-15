<?php
/**
 * trendr Member Template Tags
 *
 * Functions that are safe to use inside your template files and themes
 *
 * @package trendr
 * @sutrsackage Members
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Output the members component slug
 *
 * @package trendr
 * @sutrsackage Members Template
 * @since 1.5
 *
 * @uses trs_get_members_slug()
 */
function trs_members_slug() {
	echo trs_get_members_slug();
}
	/**
	 * Return the members component slug
	 *
	 * @package trendr
	 * @sutrsackage Members Template
	 * @since 1.5
	 */
	function trs_get_members_slug() {
		global $trs;
		return apply_filters( 'trs_get_members_slug', $trs->members->slug );
	}

/**
 * Output the members component root slug
 *
 * @package trendr
 * @sutrsackage Members Template
 * @since 1.5
 *
 * @uses trs_get_members_root_slug()
 */
function trs_members_root_slug() {
	echo trs_get_members_root_slug();
}
	/**
	 * Return the members component root slug
	 *
	 * @package trendr
	 * @sutrsackage Members Template
	 * @since 1.5
	 */
	function trs_get_members_root_slug() {
		global $trs;
		return apply_filters( 'trs_get_members_root_slug', $trs->members->root_slug );
	}

/**
 * Output member directory permalink
 *
 * @package trendr
 * @sutrsackage Members Template
 * @since 1.5
 * @uses trs_get_members_directory_permalink()
 */
function trs_members_directory_permalink() {
	echo trs_get_members_directory_permalink();
}
	/**
	 * Return member directory permalink
	 *
	 * @package trendr
	 * @sutrsackage Members Template
	 * @since 1.5
	 * @uses apply_filters()
	 * @uses traisingslashit()
	 * @uses trs_get_root_domain()
	 * @uses trs_get_members_root_slug()
	 * @return string
	 */
	function trs_get_members_directory_permalink() {
		return apply_filters( 'trs_get_members_directory_permalink', trailingslashit( trs_get_root_domain() . '/' . trs_get_members_root_slug() ) );
	}

/**
 * Output the sign-up slug
 *
 * @package trendr
 * @sutrsackage Members Template
 * @since 1.5
 *
 * @uses trs_get_signup_slug()
 */
function trs_signup_slug() {
	echo trs_get_signup_slug();
}
	/**
	 * Return the sign-up slug
	 *
	 * @package trendr
	 * @sutrsackage Members Template
	 * @since 1.5
	 */
	function trs_get_signup_slug() {
		global $trs;

		if ( !empty( $trs->pages->register->slug ) )
			$slug = $trs->pages->register->slug;
		elseif ( defined( 'TRS_REGISTER_SLUG' ) )
			$slug = TRS_REGISTER_SLUG;
		else
			$slug = 'register';

		return apply_filters( 'trs_get_signup_slug', $slug );
	}

/**
 * Output the activation slug
 *
 * @package trendr
 * @sutrsackage Members Template
 * @since 1.5
 *
 * @uses trs_get_activate_slug()
 */
function trs_activate_slug() {
	echo trs_get_activate_slug();
}
	/**
	 * Return the activation slug
	 *
	 * @package trendr
	 * @sutrsackage Members Template
	 * @since 1.5
	 */
	function trs_get_activate_slug() {
		global $trs;

		if ( !empty( $trs->pages->activate->slug ) )
			$slug = $trs->pages->activate->slug;
		elseif ( defined( 'TRS_ACTIVATION_SLUG' ) )
			$slug = TRS_ACTIVATION_SLUG;
		else
			$slug = 'activate';

		return apply_filters( 'trs_get_activate_slug', $slug );
	}

/***
 * Members template loop that will allow you to loop all members or friends of a member
 * if you pass a user_id.
 */

class TRS_Core_Members_Template {
	var $current_member = -1;
	var $member_count;
	var $members;
	var $member;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_member_count;

	function trs_core_members_template( $type, $page_number, $per_page, $max, $user_id, $search_terms, $include, $populate_extras, $exclude, $meta_key, $meta_value ) {
		$this->__construct( $type, $page_number, $per_page, $max, $user_id, $search_terms, $include, $populate_extras, $exclude, $meta_key, $meta_value );
	}

	function __construct( $type, $page_number, $per_page, $max, $user_id, $search_terms, $include, $populate_extras, $exclude, $meta_key, $meta_value ) {
		global $trs;

		$this->pag_page  = !empty( $_REQUEST['upage'] ) ? intval( $_REQUEST['upage'] ) : (int)$page_number;
		$this->pag_num   = !empty( $_REQUEST['num'] )   ? intval( $_REQUEST['num'] )   : (int)$per_page;
		$this->type      = $type;

		if ( isset( $_REQUEST['letter'] ) && '' != $_REQUEST['letter'] )
			$this->members = TRS_Core_User::get_users_by_letter( $_REQUEST['letter'], $this->pag_num, $this->pag_page, $populate_extras, $exclude );
		else if ( false !== $include )
			$this->members = TRS_Core_User::get_specific_users( $include, $this->pag_num, $this->pag_page, $populate_extras );
		else
			$this->members = trs_core_get_users( array( 'type' => $this->type, 'per_page' => $this->pag_num, 'page' => $this->pag_page, 'user_id' => $user_id, 'include' => $include, 'search_terms' => $search_terms, 'populate_extras' => $populate_extras, 'exclude' => $exclude, 'meta_key' => $meta_key, 'meta_value' => $meta_value ) );

		if ( !$max || $max >= (int)$this->members['total'] )
			$this->total_member_count = (int)$this->members['total'];
		else
			$this->total_member_count = (int)$max;

		$this->members = $this->members['users'];

		if ( $max ) {
			if ( $max >= count( $this->members ) ) {
				$this->member_count = count( $this->members );
			} else {
				$this->member_count = (int)$max;
			}
		} else {
			$this->member_count = count( $this->members );
		}

		if ( (int)$this->total_member_count && (int)$this->pag_num ) {
			$this->pag_links = paginate_links( array(
				'base'      => add_query_arg( 'upage', '%#%' ),
				'format'    => '',
				'total'     => ceil( (int)$this->total_member_count / (int)$this->pag_num ),
				'current'   => (int) $this->pag_page,
				'prev_text' => _x( '&larr;', 'Member pagination previous text', 'trendr' ),
				'next_text' => _x( '&rarr;', 'Member pagination next text', 'trendr' ),
				'mid_size'   => 1
			) );
		}
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
			do_action('member_loop_end');
			// Do some cleaning up after the loop
			$this->rewind_members();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_member() {
		global $member, $trs;

		$this->in_the_loop = true;
		$this->member = $this->next_member();

		if ( 0 == $this->current_member ) // loop has just started
			do_action('member_loop_start');
	}
}

function trs_rewind_members() {
	global $members_template;

	return $members_template->rewind_members();
}

function trs_has_members( $args = '' ) {
	global $trs, $members_template;

	/***
	 * Set the defaults based on the current page. Any of these will be overridden
	 * if arguments are directly passed into the loop. Custom plugins should always
	 * pass their parameters directly to the loop.
	 */
	$type         = 'active';
	$user_id      = 0;
	$page         = 1;
	$search_terms = null;

	// User filtering
	if ( !empty( $trs->displayed_user->id ) )
		$user_id = $trs->displayed_user->id;

	// type: active ( default ) | random | newest | popular | online | alphabetical
	$defaults = array(
		'type'            => $type,
		'page'            => $page,
		'per_page'        => 20,
		'max'             => false,

		'include'         => false,         // Pass a user_id or a list (comma-separated or array) of user_ids to only show these users
		'exclude'         => false,         // Pass a user_id or a list (comma-separated or array) of user_ids to exclude these users

		'user_id'         => $user_id,      // Pass a user_id to only show friends of this user
		'search_terms'    => $search_terms, // Pass search_terms to filter users by their profile data

		'meta_key'        => false,	        // Only return users with this usermeta
		'meta_value'	  => false,	        // Only return users where the usermeta value matches. Requires meta_key

		'populate_extras' => true           // Fetch usermeta? Friend count, last active etc.
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r );

	// Pass a filter if ?s= is set.
	if ( is_null( $search_terms ) ) {
		if ( !empty( $_REQUEST['s'] ) )
			$search_terms = $_REQUEST['s'];
		else
			$search_terms = false;
	}

	// Set per_page to max if max is larger than per_page
	if ( !empty( $max ) && ( $per_page > $max ) )
		$per_page = $max;

	// Make sure we return no members if we looking at friendship requests and there are none.
	if ( empty( $include ) && trs_is_friends_component() && trs_is_current_action( 'requests' ) )
		return false;

	$members_template = new TRS_Core_Members_Template( $type, $page, $per_page, $max, $user_id, $search_terms, $include, (bool)$populate_extras, $exclude, $meta_key, $meta_value );
	return apply_filters( 'trs_has_members', $members_template->has_members(), $members_template );
}

function trs_the_member() {
	global $members_template;
	return $members_template->the_member();
}

function trs_members() {
	global $members_template;
	return $members_template->members();
}

function trs_members_pagination_count() {
	echo trs_get_members_pagination_count();
}
	function trs_get_members_pagination_count() {
		global $trs, $members_template;

		if ( empty( $members_template->type ) )
			$members_template->type = '';

		$start_num = intval( ( $members_template->pag_page - 1 ) * $members_template->pag_num ) + 1;
		$from_num  = trs_core_number_format( $start_num );
		$to_num    = trs_core_number_format( ( $start_num + ( $members_template->pag_num - 1 ) > $members_template->total_member_count ) ? $members_template->total_member_count : $start_num + ( $members_template->pag_num - 1 ) );
		$total     = trs_core_number_format( $members_template->total_member_count );

		if ( 'active' == $members_template->type )
			$pag = sprintf( __( 'Viewing member %1$s to %2$s (of %3$s active members)', 'trendr' ), $from_num, $to_num, $total );
		else if ( 'popular' == $members_template->type )
			$pag = sprintf( __( 'Viewing member %1$s to %2$s (of %3$s members with friends)', 'trendr' ), $from_num, $to_num, $total );
		else if ( 'online' == $members_template->type )
			$pag = sprintf( __( 'Viewing member %1$s to %2$s (of %3$s members online)', 'trendr' ), $from_num, $to_num, $total );
		else
			$pag = sprintf( __( 'Viewing member %1$s to %2$s (of %3$s members)', 'trendr' ), $from_num, $to_num, $total );

		return apply_filters( 'trs_members_pagination_count', $pag );
	}

function trs_members_pagination_links() {
	echo trs_get_members_pagination_links();
}
	function trs_get_members_pagination_links() {
		global $members_template;

		return apply_filters( 'trs_get_members_pagination_links', $members_template->pag_links );
	}

/**
 * trs_member_user_id()
 *
 * Echo id from trs_get_member_user_id()
 *
 * @uses trs_get_member_user_id()
 */
function trs_member_user_id() {
	echo trs_get_member_user_id();
}
	/**
	 * trs_get_member_user_id()
	 *
	 * Get the id of the user in a members loop
	 *
	 * @global object $members_template
	 * @return string Members id
	 */
	function trs_get_member_user_id() {
		global $members_template;

		return apply_filters( 'trs_get_member_user_id', $members_template->member->id );
	}

/**
 * trs_member_user_nicename()
 *
 * Echo nicename from trs_get_member_user_nicename()
 *
 * @uses trs_get_member_user_nicename()
 */
function trs_member_user_nicename() {
	echo trs_get_member_user_nicename();
}
	/**
	 * trs_get_member_user_nicename()
	 *
	 * Get the nicename of the user in a members loop
	 *
	 * @global object $members_template
	 * @return string Members nicename
	 */
	function trs_get_member_user_nicename() {
		global $members_template;
		return apply_filters( 'trs_get_member_user_nicename', $members_template->member->user_nicename );
	}

/**
 * trs_member_user_login()
 *
 * Echo login from trs_get_member_user_login()
 *
 * @uses trs_get_member_user_login()
 */
function trs_member_user_login() {
	echo trs_get_member_user_login();
}
	/**
	 * trs_get_member_user_login()
	 *
	 * Get the login of the user in a members loop
	 *
	 * @global object $members_template
	 * @return string Members login
	 */
	function trs_get_member_user_login() {
		global $members_template;
		return apply_filters( 'trs_get_member_user_login', $members_template->member->user_login );
	}

/**
 * trs_member_user_email()
 *
 * Echo email address from trs_get_member_user_email()
 *
 * @uses trs_get_member_user_email()
 */
function trs_member_user_email() {
	echo trs_get_member_user_email();
}
	/**
	 * trs_get_member_user_email()
	 *
	 * Get the email address of the user in a members loop
	 *
	 * @global object $members_template
	 * @return string Members email address
	 */
	function trs_get_member_user_email() {
		global $members_template;
		return apply_filters( 'trs_get_member_user_email', $members_template->member->user_email );
	}

function trs_member_is_loggedin_user() {
	global $trs, $members_template;
	return apply_filters( 'trs_member_is_loggedin_user', $trs->loggedin_user->id == $members_template->member->id ? true : false );
}

function trs_member_portrait( $args = '' ) {
	echo apply_filters( 'trs_member_portrait', trs_get_member_portrait( $args ) );
}
	function trs_get_member_portrait( $args = '' ) {
		global $trs, $members_template;

		$defaults = array(
			'type' => 'thumb',
			'width' => false,
			'height' => false,
			'class' => 'portrait',
			'id' => false,
			'alt' => __( 'Profile picture of %s', 'trendr' )
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		return apply_filters( 'trs_get_member_portrait', trs_core_fetch_portrait( array( 'item_id' => $members_template->member->id, 'type' => $type, 'alt' => $alt, 'css_id' => $id, 'class' => $class, 'width' => $width, 'height' => $height, 'email' => $members_template->member->user_email ) ) );
	}

function trs_member_permalink() {
	echo trs_get_member_permalink();
}
	function trs_get_member_permalink() {
		global $members_template;

		return apply_filters( 'trs_get_member_permalink', trs_core_get_user_domain( $members_template->member->id, $members_template->member->user_nicename, $members_template->member->user_login ) );
	}
	function trs_member_link() { echo trs_get_member_permalink(); }
	function trs_get_member_link() { return trs_get_member_permalink(); }

/**
 * Echoes trs_get_member_name()
 *
 * @package trendr
 */
function trs_member_name() {
	echo apply_filters( 'trs_member_name', trs_get_member_name() );
}
	/**
	 * Used inside a trs_has_members() loop, this function returns a user's full name
	 *
	 * Full name is, by default, pulled from xprofile's Full Name field. When this field is
	 * empty, we try to get an alternative name from the TRM users table, in the following order
	 * of preference: display_name, user_nicename, user_login.
	 *
	 * @package trendr
	 *
	 * @uses apply_filters() Filter trs_get_the_member_name() to alter the function's output
	 * @return str The user's fullname for display
	 */
	function trs_get_member_name() {
		global $members_template;

		// Generally, this only fires when xprofile is disabled
		if ( empty( $members_template->member->fullname ) ) {
			// Our order of preference for alternative fullnames
			$name_stack = array(
				'display_name',
				'user_nicename',
				'user_login'
			);

			foreach ( $name_stack as $source ) {
				if ( !empty( $members_template->member->{$source} ) ) {
					// When a value is found, set it as fullname and be done
					// with it
					$members_template->member->fullname = $members_template->member->{$source};
					break;
				}
			}
		}

		return apply_filters( 'trs_get_member_name', $members_template->member->fullname );
	}
	add_filter( 'trs_get_member_name', 'trm_filter_kses' );
	add_filter( 'trs_get_member_name', 'stripslashes' );
	add_filter( 'trs_get_member_name', 'strip_tags' );

function trs_member_last_active() {
	echo trs_get_member_last_active();
}
	function trs_get_member_last_active() {
		global $members_template;

		if ( isset( $members_template->member->last_activity ) )
			$last_activity = trs_core_get_last_activity( $members_template->member->last_activity, __( 'active %s', 'trendr' ) );
		else
			$last_activity = __( 'Never active', 'trendr' );

		return apply_filters( 'trs_member_last_active', $last_activity );
	}

function trs_member_latest_update( $args = '' ) {
	echo trs_get_member_latest_update( $args );
}
	function trs_get_member_latest_update( $args = '' ) {
		global $trs, $members_template;

		$defaults = array(
			'length'    => 225,
			'view_link' => true
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r );

		if ( !trs_is_active( 'activity' ) || empty( $members_template->member->latest_update ) || !$update = maybe_unserialize( $members_template->member->latest_update ) )
			return false;

		$update_content = apply_filters( 'trs_get_activity_latest_update_excerpt', sprintf( _x( '- &quot;%s &quot;', 'member latest update in member directory', 'trendr' ), trim( strip_tags( trs_create_excerpt( $update['content'], $length ) ) ) ) );

		// If $view_link is true and the text returned by trs_create_excerpt() is different from the original text (ie it's
		// been truncated), add the "View" link.
		if ( $view_link && ( $update_content != $update['content'] ) ) {
			$view = __( 'View', 'trendr' );

			$update_content .= '<span class="activity-read-more"><a href="' . trs_activity_get_permalink( $update['id'] ) . '" rel="nofollow">' . $view . '</a></span>';
		}

		return apply_filters( 'trs_get_member_latest_update', $update_content );
	}

function trs_member_profile_data( $args = '' ) {
	echo trs_get_member_profile_data( $args );
}
	function trs_get_member_profile_data( $args = '' ) {
		global $trs, $members_template;

		if ( !trs_is_active( 'xprofile' ) )
			return false;

		// Declare local variables
		$data    = false;
		$user_id = 0;

		// Guess at default $user_id
		if ( !empty( $members_template->member->id ) )
			$user_id = $members_template->member->id;
		elseif ( !empty( $trs->displayed_user->id ) )
			$user_id = $trs->displayed_user->id;

		$defaults = array(
			'field'   => false,   // Field name
			'user_id' => $user_id
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		// Populate the user if it hasn't been already.
		if ( empty( $members_template->member->profile_data ) && method_exists( 'TRS_XProfile_ProfileData', 'get_all_for_user' ) )
			$members_template->member->profile_data = TRS_XProfile_ProfileData::get_all_for_user( $user_id );

		// Get the field data if there is data to get
		if ( !empty( $members_template->member->profile_data ) )
			$data = xprofile_format_profile_field( $members_template->member->profile_data[$field]['field_type'], $members_template->member->profile_data[$field]['field_data'] );

		return apply_filters( 'trs_get_member_profile_data', $data );
	}

function trs_member_registered() {
	echo trs_get_member_registered();
}
	function trs_get_member_registered() {
		global $members_template;

		$registered = esc_attr( trs_core_get_last_activity( $members_template->member->user_registered, __( 'registered %s', 'trendr' ) ) );

		return apply_filters( 'trs_member_last_active', $registered );
	}

function trs_member_random_profile_data() {
	global $members_template;

	if ( trs_is_active( 'xprofile' ) ) { ?>
		<?php $random_data = xprofile_get_random_profile_data( $members_template->member->id, true ); ?>
			<strong><?php echo trm_filter_kses( $random_data[0]->name ) ?></strong>
			<?php echo trm_filter_kses( $random_data[0]->value ) ?>
	<?php }
}

function trs_member_hidden_fields() {
	if ( isset( $_REQUEST['s'] ) )
		echo '<input type="hidden" id="search_terms" value="' . esc_attr( $_REQUEST['s'] ) . '" name="search_terms" />';

	if ( isset( $_REQUEST['letter'] ) )
		echo '<input type="hidden" id="selected_letter" value="' . esc_attr( $_REQUEST['letter'] ) . '" name="selected_letter" />';

	if ( isset( $_REQUEST['members_search'] ) )
		echo '<input type="hidden" id="search_terms" value="' . esc_attr( $_REQUEST['members_search'] ) . '" name="search_terms" />';
}

function trs_directory_members_search_form() {
	global $trs;

	$default_search_value = trs_get_search_default_text( 'members' );
	$search_value         = !empty( $_REQUEST['s'] ) ? stripslashes( $_REQUEST['s'] ) : $default_search_value; ?>

	<form action="" method="get" id="search-members-form">
		<label><input type="text" name="s" id="members_search" value="<?php echo esc_attr( $search_value ) ?>"  onfocus="if (this.value == '<?php echo $default_search_value ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php echo $default_search_value ?>';}" /></label>
		<input type="submit" id="members_search_submit" name="members_search_submit" value="<?php _e( 'Search', 'trendr' ) ?>" />
	</form>

<?php
}

function trs_total_site_member_count() {
	echo trs_get_total_site_member_count();
}
	function trs_get_total_site_member_count() {
		return apply_filters( 'trs_get_total_site_member_count', trs_core_number_format( trs_core_get_total_member_count() ) );
	}

/** Navigation and other misc template tags **/

/**
 * Uses the $trs->trs_nav global to render out the navigation within a trendr install.
 * Each component adds to this navigation array within its own [component_name]setup_nav() function.
 *
 * This navigation array is the top level navigation, so it contains items such as:
 *      [Blog, Profile, Messages, Groups, Friends] ...
 *
 * The function will also analyze the current component the user is in, to determine whether
 * or not to highlight a particular nav item.
 *
 * @package trendr Core
 * @todo Move to a back-compat file?
 * @deprecated Does not seem to be called anywhere in the core
 * @global object $trs Global trendr settings object
 */
function trs_get_loggedin_user_nav() {
	global $trs;

	// Loop through each navigation item
	foreach( (array) $trs->trs_nav as $nav_item ) {
		// If the current component matches the nav item id, then add a highlight CSS class.
		if ( !trs_is_directory() && $trs->active_components[$trs->current_component] == $nav_item['css_id'] )
			$selected = ' class="current selected"';
		else
			$selected = '';

		/* If we are viewing another person (current_userid does not equal loggedin_user->id)
		   then check to see if the two users are friends. if they are, add a highlight CSS class
		   to the friends nav item if it exists. */
		if ( !trs_is_my_profile() && $trs->displayed_user->id ) {
			$selected = '';

			if ( trs_is_active( 'friends' ) ) {
				if ( $nav_item['css_id'] == $trs->friends->id ) {
					if ( friends_check_friendship( $trs->loggedin_user->id, $trs->displayed_user->id ) )
						$selected = ' class="current selected"';
				}
			}
		}

		// echo out the final list item
		echo apply_filters_ref_array( 'trs_get_loggedin_user_nav_' . $nav_item['css_id'], array( '<li id="li-nav-' . $nav_item['css_id'] . '" ' . $selected . '><a id="my-' . $nav_item['css_id'] . '" href="' . $nav_item['link'] . '">' . $nav_item['name'] . '</a></li>', &$nav_item ) );
	}

	// Always add a log out list item to the end of the navigation
	$logout_link = '<li><a id="trm-logout" href="' .  trm_logout_url( trs_get_root_domain() ) . '">' . __( 'Log Out', 'trendr' ) . '</a></li>';

	echo apply_filters( 'trs_logout_nav_link', $logout_link );
}

/**
 * Uses the $trs->trs_nav global to render out the user navigation when viewing another user other than
 * yourself.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 */
function trs_get_displayed_user_nav() {
	global $trs;

	foreach ( (array)$trs->trs_nav as $user_nav_item ) {
		if ( !$user_nav_item['show_for_displayed_user'] && !trs_is_my_profile() )
			continue;

		if ( $trs->current_component == $user_nav_item['slug'] )
			$selected = ' class="current selected"';
		else
			$selected = '';

		if ( $trs->loggedin_user->domain )
			$link = str_replace( $trs->loggedin_user->domain, $trs->displayed_user->domain, $user_nav_item['link'] );
		else
			$link = $trs->displayed_user->domain . $user_nav_item['link'];

		echo apply_filters_ref_array( 'trs_get_displayed_user_nav_' . $user_nav_item['css_id'], array( '<li id="' . $user_nav_item['css_id'] . '-personal-li" ' . $selected . '><a id="user-' . $user_nav_item['css_id'] . '" href="' . $link . '">' . $user_nav_item['name'] . '</a></li>', &$user_nav_item ) );
	}
}

/** Avatars *******************************************************************/

function trs_loggedin_user_portrait( $args = '' ) {
	echo trs_get_loggedin_user_portrait( $args );
}
	function trs_get_loggedin_user_portrait( $args = '' ) {
		global $trs;

		$defaults = array(
			'type'   => 'thumb',
			'width'  => false,
			'height' => false,
			'html'   => true,
			'alt'    => __( 'Profile picture of %s', 'trendr' )
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		return apply_filters( 'trs_get_loggedin_user_portrait', trs_core_fetch_portrait( array( 'item_id' => $trs->loggedin_user->id, 'type' => $type, 'width' => $width, 'height' => $height, 'html' => $html, 'alt' => $alt ) ) );
	}

function trs_displayed_user_portrait( $args = '' ) {
	echo trs_get_displayed_user_portrait( $args );
}
	function trs_get_displayed_user_portrait( $args = '' ) {
		global $trs;

		$defaults = array(
			'type'   => 'thumb',
			'width'  => false,
			'height' => false,
			'html'   => true,
			'alt'    => __( 'Profile picture of %s', 'trendr' )
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		return apply_filters( 'trs_get_displayed_user_portrait', trs_core_fetch_portrait( array( 'item_id' => $trs->displayed_user->id, 'type' => $type, 'width' => $width, 'height' => $height, 'html' => $html, 'alt' => $alt ) ) );
	}

function trs_displayed_user_email() {
	echo trs_get_displayed_user_email();
}
	function trs_get_displayed_user_email() {
		global $trs;

		// If displayed user exists, return email address
		if ( isset( $trs->displayed_user->userdata->user_email ) )
			$retval = $trs->displayed_user->userdata->user_email;
		else
			$retval = '';

		return apply_filters( 'trs_get_displayed_user_email', esc_attr( $retval ) );
	}

function trs_last_activity( $user_id = 0 ) {
	echo apply_filters( 'trs_last_activity', trs_get_last_activity( $user_id ) );
}
	function trs_get_last_activity( $user_id = 0 ) {
		global $trs;

		if ( empty( $user_id ) )
			$user_id = $trs->displayed_user->id;

		$last_activity = trs_core_get_last_activity( trs_get_user_meta( $user_id, 'last_activity', true ), __('active %s', 'trendr') );

		return apply_filters( 'trs_get_last_activity', $last_activity );
	}

function trs_user_firstname() {
	echo trs_get_user_firstname();
}
	function trs_get_user_firstname( $name = false ) {
		global $trs;

		// Try to get displayed user
		if ( empty( $name ) )
			$name = $trs->displayed_user->fullname;

		// Fall back on logged in user
		if ( empty( $name ) )
			$name = $trs->loggedin_user->fullname;

		$fullname = (array)explode( ' ', $name );

		return apply_filters( 'trs_get_user_firstname', $fullname[0], $fullname );
	}

function trs_loggedin_user_link() {
	echo trs_get_loggedin_user_link();
}
	function trs_get_loggedin_user_link() {
		return apply_filters( 'trs_get_loggedin_user_link', trs_loggedin_user_domain() );
	}

function trs_displayed_user_link() {
	echo trs_get_displayed_user_link();
}
	function trs_get_displayed_user_link() {
		return apply_filters( 'trs_get_displayed_user_link', trs_displayed_user_domain() );
	}
	function trs_user_link() { trs_displayed_user_domain(); } // Deprecated.

function trs_displayed_user_id() {
	global $trs;
	return apply_filters( 'trs_displayed_user_id', !empty( $trs->displayed_user->id ) ? $trs->displayed_user->id : 0 );
}
	function trs_current_user_id() { return trs_displayed_user_id(); }

function trs_loggedin_user_id() {
	global $trs;
	return apply_filters( 'trs_loggedin_user_id', !empty( $trs->loggedin_user->id ) ? $trs->loggedin_user->id : 0 );
}

function trs_displayed_user_domain() {
	global $trs;
	return apply_filters( 'trs_displayed_user_domain', isset( $trs->displayed_user->domain ) ? $trs->displayed_user->domain : '' );
}

function trs_loggedin_user_domain() {
	global $trs;
	return apply_filters( 'trs_loggedin_user_domain', isset( $trs->loggedin_user->domain ) ? $trs->loggedin_user->domain : '' );
}

function trs_displayed_user_fullname() {
	echo trs_get_displayed_user_fullname();
}
	function trs_get_displayed_user_fullname() {
		global $trs;
		return apply_filters( 'trs_displayed_user_fullname', isset( $trs->displayed_user->fullname ) ? $trs->displayed_user->fullname : '' );
	}
	function trs_user_fullname() { echo trs_get_displayed_user_fullname(); }


function trs_loggedin_user_fullname() {
	echo trs_get_loggedin_user_fullname();
}
	function trs_get_loggedin_user_fullname() {
		global $trs;
		return apply_filters( 'trs_get_loggedin_user_fullname', isset( $trs->loggedin_user->fullname ) ? $trs->loggedin_user->fullname : '' );
	}

function trs_displayed_user_username() {
	echo trs_get_displayed_user_username();
}
	function trs_get_displayed_user_username() {
		global $trs;

		if ( !empty( $trs->displayed_user->id ) ) {
			$username = trs_core_get_username( $trs->displayed_user->id, $trs->displayed_user->userdata->user_nicename, $trs->displayed_user->userdata->user_login );
		} else {
			$username = '';
		}

		return apply_filters( 'trs_get_displayed_user_username', $username );
	}

function trs_loggedin_user_username() {
	echo trs_get_loggedin_user_username();
}
	function trs_get_loggedin_user_username() {
		global $trs;

		if ( !empty( $trs->loggedin_user->id ) ) {
			$username = trs_core_get_username( $trs->loggedin_user->id, $trs->loggedin_user->userdata->user_nicename, $trs->loggedin_user->userdata->user_login );
		} else {
			$username = '';
		}

		return apply_filters( 'trs_get_loggedin_user_username', $username );
	}

/** Signup Form ***************************************************************/

function trs_has_custom_signup_page() {
	if ( locate_template( array( 'register.php' ), false ) || locate_template( array( '/registration/register.php' ), false ) )
		return true;

	return false;
}

function trs_signup_page() {
	echo trs_get_signup_page();
}
	function trs_get_signup_page() {
		global $trs;

		if ( trs_has_custom_signup_page() ) {
			$page = trailingslashit( trs_get_root_domain() . '/' . trs_get_signup_slug() );
		} else {
			$page = trs_get_root_domain() . '/trm-signup.php';
		}

		return apply_filters( 'trs_get_signup_page', $page );
	}

function trs_has_custom_activation_page() {
	if ( locate_template( array( 'activate.php' ), false ) || locate_template( array( '/registration/activate.php' ), false ) )
		return true;

	return false;
}

function trs_activation_page() {
	echo trs_get_activation_page();
}
	function trs_get_activation_page() {
		global $trs;

		if ( trs_has_custom_activation_page() )
			$page = trailingslashit( trs_get_root_domain() . '/' . $trs->pages->activate->slug );
		else
			$page = trailingslashit( trs_get_root_domain() ) . 'trm-activate.php';

		return apply_filters( 'trs_get_activation_page', $page );
	}

function trs_signup_username_value() {
	echo trs_get_signup_username_value();
}
	function trs_get_signup_username_value() {
		$value = '';
		if ( isset( $_POST['signup_username'] ) )
			$value = $_POST['signup_username'];

		return apply_filters( 'trs_get_signup_username_value', $value );
	}

function trs_signup_email_value() {
	echo trs_get_signup_email_value();
}
	function trs_get_signup_email_value() {
		$value = '';
		if ( isset( $_POST['signup_email'] ) )
			$value = $_POST['signup_email'];

		return apply_filters( 'trs_get_signup_email_value', $value );
	}

function trs_signup_with_blog_value() {
	echo trs_get_signup_with_blog_value();
}
	function trs_get_signup_with_blog_value() {
		$value = '';
		if ( isset( $_POST['signup_with_blog'] ) )
			$value = $_POST['signup_with_blog'];

		return apply_filters( 'trs_get_signup_with_blog_value', $value );
	}

function trs_signup_blog_url_value() {
	echo trs_get_signup_blog_url_value();
}
	function trs_get_signup_blog_url_value() {
		$value = '';
		if ( isset( $_POST['signup_blog_url'] ) )
			$value = $_POST['signup_blog_url'];

		return apply_filters( 'trs_get_signup_blog_url_value', $value );
	}

function trs_signup_blog_title_value() {
	echo trs_get_signup_blog_title_value();
}
	function trs_get_signup_blog_title_value() {
		$value = '';
		if ( isset( $_POST['signup_blog_title'] ) )
			$value = $_POST['signup_blog_title'];

		return apply_filters( 'trs_get_signup_blog_title_value', $value );
	}

function trs_signup_blog_privacy_value() {
	echo trs_get_signup_blog_privacy_value();
}
	function trs_get_signup_blog_privacy_value() {
		$value = '';
		if ( isset( $_POST['signup_blog_privacy'] ) )
			$value = $_POST['signup_blog_privacy'];

		return apply_filters( 'trs_get_signup_blog_privacy_value', $value );
	}

function trs_signup_portrait_dir_value() {
	echo trs_get_signup_portrait_dir_value();
}
	function trs_get_signup_portrait_dir_value() {
		global $trs;

		// Check if signup_portrait_dir is passed
		if ( !empty( $_POST['signup_portrait_dir'] ) )
			$signup_portrait_dir = $_POST['signup_portrait_dir'];

		// If not, check if global is set
		elseif ( !empty( $trs->signup->portrait_dir ) )
			$signup_portrait_dir = $trs->signup->portrait_dir;

		// If not, set false
		else
			$signup_portrait_dir = false;

		return apply_filters( 'trs_get_signup_portrait_dir_value', $trs->signup->portrait_dir );
	}

function trs_current_signup_step() {
	echo trs_get_current_signup_step();
}
	function trs_get_current_signup_step() {
		global $trs;

		return $trs->signup->step;
	}

function trs_signup_portrait( $args = '' ) {
	echo trs_get_signup_portrait( $args );
}
	function trs_get_signup_portrait( $args = '' ) {
		global $trs;

		$defaults = array(
			'size' => trs_core_portrait_full_width(),
			'class' => 'portrait',
			'alt' => __( 'Your Avatar', 'trendr' )
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		// Avatar DIR is found
		if ( $signup_portrait_dir = trs_get_signup_portrait_dir_value() ) {
			$grportrait_img = trs_core_fetch_portrait( array(
				'item_id'    => $signup_portrait_dir,
				'object'     => 'signup',
				'portrait_dir' => 'portraits/signups',
				'type'       => 'full',
				'width'      => $size,
				'height'     => $size,
				'alt'        => $alt,
				'class'      => $class
			) );

		// No portrait DIR was found
		} else {

			// Set default grportrait type
			if ( empty( $trs->grav_default->user ) )
				$default_grav = 'trmortrait';
			else if ( 'mystery' == $trs->grav_default->user )
				$default_grav = TRS_PLUGIN_URL . '/trs-core/images/mystery-man.jpg';
			else
				$default_grav = $trs->grav_default->user;

			// Create
			$grportrait_url    = apply_filters( 'trs_grportrait_url', 'http://www.grportrait.com/portrait/' );
			$md5_lcase_email = md5( strtolower( trs_get_signup_email_value() ) );
			$grportrait_img    = '<img src="' . $grportrait_url . $md5_lcase_email . '?d=' . $default_grav . '&amp;s=' . $size . '" width="' . $size . '" height="' . $size . '" alt="' . $alt . '" class="' . $class . '" />';
		}

		return apply_filters( 'trs_get_signup_portrait', $grportrait_img, $args );
	}

function trs_signup_allowed() {
	echo trs_get_signup_allowed();
}
	function trs_get_signup_allowed() {
		global $trs;

		$signup_allowed = false;

		if ( is_multisite() ) {
			if ( in_array( $trs->site_options['registration'], array( 'all', 'user' ) ) )
				$signup_allowed = true;

		} else {
			if ( get_option( 'users_can_register') )
				$signup_allowed = true;
		}

		return apply_filters( 'trs_get_signup_allowed', $signup_allowed );
	}

/**
 * Hook member activity feed to <head>
 *
 * @since 1.5
 */
function trs_members_activity_feed() {
	if ( !trs_is_active( 'activity' ) || !trs_is_user() )
		return; ?>

	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo( 'name' ) ?> | <?php trs_displayed_user_fullname() ?> | <?php _e( 'Activity RSS Feed', 'trendr' ) ?>" href="<?php trs_member_activity_feed_link() ?>" />

<?php
}
add_action( 'trs_head', 'trs_members_activity_feed' );


function trs_members_component_link( $component, $action = '', $query_args = '', $nonce = false ) {
	echo trs_get_members_component_link( $component, $action, $query_args, $nonce );
}
	function trs_get_members_component_link( $component, $action = '', $query_args = '', $nonce = false ) {
		global $trs;

		// Must be displayed user
		if ( empty( $trs->displayed_user->id ) )
			return;

		// Append $action to $url if there is no $type
		if ( !empty( $action ) )
			$url = $trs->displayed_user->domain . $trs->{$component}->slug . '/' . $action;
		else
			$url = $trs->displayed_user->domain . $trs->{$component}->slug;

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