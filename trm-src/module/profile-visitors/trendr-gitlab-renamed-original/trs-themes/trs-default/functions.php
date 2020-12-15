<?php
/**
 * TRS-Default theme functions and definitions
 *
 * Sets up the theme and provides some helper functions. Some helper functions
 * are used in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress and trendr to change core functionality.
 *
 * The first function, trs_dtheme_setup(), sets up the theme by registering support
 * for various features in WordPress, such as post thumbnails and navigation menus, and
 * for trendr, action buttons and javascript localisation.
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development, http://codex.wordpress.org/Child_Themes
 * and http://codex.trendr.org/theme-development/building-a-trendr-child-theme/), you can override
 * certain functions (those wrapped in a function_exists() call) by defining them first in your
 * child theme's functions.php file. The child theme's functions.php file is included before the
 * parent theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook. The hook can be removed by using remove_action() or
 * remove_filter() and you can attach your own function to the hook.
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 *
 * @package trendr
 * @sutrsackage TRS-Default
 * @since 1.2
 */

if ( !function_exists( 'trs_is_active' ) )
	return;

// If trendr is not activated, switch back to the default TRM theme
if ( !defined( 'TRS_VERSION' ) )
	switch_theme( TRM_DEFAULT_THEME, TRM_DEFAULT_THEME );

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * Used to set the width of images and content. Should be equal to the width the theme
 * is designed for, generally via the style.css stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 591;

if ( !function_exists( 'trs_dtheme_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress and trendr features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * To override trs_dtheme_setup() in a child theme, add your own trs_dtheme_setup to your child theme's
 * functions.php file.
 *
 * @global object $trs Global trendr settings object
 * @since 1.5
 */
function trs_dtheme_setup() {
	global $trs;

	// Load the AJAX functions for the theme
	require( TEMPLATEPATH . '/_inc/ajax.php' );

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// This theme uses post thumbnails
	add_theme_support( 'post-thumbnails' );

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Add responsive layout support to trs-default without forcing child
	// themes to inherit it if they don't want to
	add_theme_support( 'trs-default-responsive' );

	// This theme uses trm_nav_menu() in one location.

	// This theme allows users to set a custom background
	add_custom_background( 'trs_dtheme_custom_background_style' );

	// Add custom header support if allowed
	if ( !defined( 'TRS_DTHEME_DISABLE_CUSTOM_HEADER' ) ) {
		define( 'HEADER_TEXTCOLOR', 'FFFFFF' );

		// The height and width of your custom header. You can hook into the theme's own filters to change these values.
		// Add a filter to trs_dtheme_header_image_width and trs_dtheme_header_image_height to change these values.
		define( 'HEADER_IMAGE_WIDTH',  apply_filters( 'trs_dtheme_header_image_width',  1250 ) );
		define( 'HEADER_IMAGE_HEIGHT', apply_filters( 'trs_dtheme_header_image_height', 133  ) );

		// We'll be using post thumbnails for custom header images on posts and pages. We want them to be 1250 pixels wide by 133 pixels tall.
		// Larger images will be auto-cropped to fit, smaller ones will be ignored.

		// Add a way for the custom header to be styled in the admin panel that controls custom headers.
		add_custom_image_header( 'trs_dtheme_header_style', 'trs_dtheme_admin_header_style' );
	}

	if ( !is_admin() ) {
		// Register buttons for the relevant component templates
		// Friends button
		if ( trs_is_active( 'friends' ) )
			add_action( 'trs_member_header_actions',    'trs_add_friend_button' );

		// Activity button
		if ( trs_is_active( 'activity' ) )
			add_action( 'trs_member_header_actions',    'trs_send_public_message_button' );

		// Messages button
		if ( trs_is_active( 'messages' ) )
			add_action( 'trs_member_header_actions',    'trs_send_private_message_button' );

		// Group buttons
		if ( trs_is_active( 'groups' ) ) {
			add_action( 'trs_group_header_actions',     'trs_group_join_button' );
			add_action( 'trs_group_header_actions',     'trs_group_new_topic_button' );
			add_action( 'trs_directory_groups_actions', 'trs_group_join_button' );
		}

		// Blog button
		if ( trs_is_active( 'blogs' ) )
			add_action( 'trs_directory_blogs_actions',  'trs_blogs_visit_blog_button' );
	}
}
add_action( 'after_setup_theme', 'trs_dtheme_setup' );
endif;

if ( !function_exists( 'trs_dtheme_enqueue_scripts' ) ) :
/**
 * Enqueue theme javascript safely
 *
 * @see http://codex.wordpress.org/Function_Reference/trm_enqueue_script
 * @since 1.5
 */
function trs_dtheme_enqueue_scripts() {
	// Bump this when changes are made to bust cache
	$version = '20120110';

	// Enqueue the global JS - Ajax will not work without it
	trm_enqueue_script( 'dtheme-ajax-js', get_template_directory_uri() . '/_inc/global.js', array( 'jquery' ), $version );

	// Add words that we need to use in JS to the end of the page so they can be translated and still used.
	$params = array(
		'my_favs'           => __( 'My Favorites', 'trendr' ),
		'accepted'          => __( 'Accepted', 'trendr' ),
		'rejected'          => __( 'Rejected', 'trendr' ),
		'show_all_comments' => __( 'Show all comments for this thread', 'trendr' ),
		'show_all'          => __( 'Show all', 'trendr' ),
		'comments'          => __( 'comments', 'trendr' ),
		'close'             => __( 'Close', 'trendr' ),
		'view'              => __( 'View', 'trendr' ),
		'mark_as_fav'	    => __( 'Favorite', 'trendr' ),
		'remove_fav'	    => __( 'Remove Favorite', 'trendr' )
	);

	trm_localize_script( 'dtheme-ajax-js', 'TRS_DTheme', $params );
}
add_action( 'trm_enqueue_scripts', 'trs_dtheme_enqueue_scripts' );
endif;

if ( !function_exists( 'trs_dtheme_enqueue_styles' ) ) :
/**
 * Enqueue theme CSS safely
 *
 * For maximum flexibility, trendr Default's stylesheet is enqueued, using trm_enqueue_style().
 * If you're building a child theme of trs-default, your stylesheet will also be enqueued,
 * automatically, as dependent on trs-default's CSS. For this reason, trs-default child themes are
 * not recommended to include trs-default's stylesheet using @import.
 *
 * If you would prefer to use @import, or would like to change the way in which stylesheets are
 * enqueued, you can override trs_dtheme_enqueue_styles() in your theme's functions.php file.
 *
 * @see http://codex.wordpress.org/Function_Reference/trm_enqueue_style
 * @see http://codex.trendr.org/releases/1-5-developer-and-designer-information/
 * @since 1.5
 */
function trs_dtheme_enqueue_styles() {
	
	// Bump this when changes are made to bust cache
	$version = '20120110';

	// Register our main stylesheet
	trm_register_style( 'trs-default-main', get_template_directory_uri() . '/_inc/css/default.css', array(), $version );

	// If the current theme is a child of trs-default, enqueue its stylesheet
	if ( is_child_theme() && 'trs-default' == get_template() ) {
		trm_enqueue_style( get_stylesheet(), get_stylesheet_uri(), array( 'trs-default-main' ), $version );
	}

	// Enqueue the main stylesheet
	trm_enqueue_style( 'trs-default-main' );

	// Default CSS RTL
	if ( is_rtl() )
		trm_enqueue_style( 'trs-default-main-rtl',  get_template_directory_uri() . '/_inc/css/default-rtl.css', array( 'trs-default-main' ), $version );

	// Responsive layout
	if ( current_theme_supports( 'trs-default-responsive' ) ) {
		trm_enqueue_style( 'trs-default-responsive', get_template_directory_uri() . '/_inc/css/responsive.css', array( 'trs-default-main' ), $version );

		if ( is_rtl() )
			trm_enqueue_style( 'trs-default-responsive-rtl', get_template_directory_uri() . '/_inc/css/responsive-rtl.css', array( 'trs-default-responsive' ), $version );
	}
}
add_action( 'trm_enqueue_scripts', 'trs_dtheme_enqueue_styles' );
endif;

if ( !function_exists( 'trs_dtheme_admin_header_style' ) ) :
/**
 * Styles the header image displayed on the Appearance > Header admin panel.
 *
 * Referenced via add_custom_image_header() in trs_dtheme_setup().
 *
 * @since 1.2
 */
function trs_dtheme_admin_header_style() {
?>
	<style type="text/css">
		#headimg {
			position: relative;
			color: #fff;
			background: url(<?php header_image() ?>);
			-moz-border-radius-bottomleft: 6px;
			-webkit-border-bottom-left-radius: 6px;
			-moz-border-radius-bottomright: 6px;
			-webkit-border-bottom-right-radius: 6px;
			margin-bottom: 20px;
			height: 133px;
		}

		#headimg h1{
			position: absolute;
			bottom: 15px;
			left: 15px;
			width: 44%;
			margin: 0;
			font-family: Arial, Tahoma, sans-serif;
		}
		#headimg h1 a{
			color:#<?php header_textcolor() ?>;
			text-decoration: none;
			border-bottom: none;
		}
		#headimg #desc{
			color:#<?php header_textcolor() ?>;
			font-size:1em;
			margin-top:-0.5em;
		}

		#desc {
			display: none;
		}

		<?php if ( 'blank' == get_header_textcolor() ) { ?>
		#headimg h1, #headimg #desc {
			display: none;
		}
		#headimg h1 a, #headimg #desc {
			color:#<?php echo HEADER_TEXTCOLOR ?>;
		}
		<?php } ?>
	</style>
<?php
}
endif;

if ( !function_exists( 'trs_dtheme_custom_background_style' ) ) :
/**
 * The style for the custom background image or colour.
 *
 * Referenced via add_custom_background() in trs_dtheme_setup().
 *
 * @see _custom_background_cb()
 * @since 1.5
 */
function trs_dtheme_custom_background_style() {
	$background = get_background_image();
	$color = get_background_color();
	if ( ! $background && ! $color )
		return;

	$style = $color ? "background-color: #$color;" : '';

	if ( $style && !$background ) {
		$style .= ' background-image: none;';

	} elseif ( $background ) {
		$image = " background-image: url('$background');";

		$repeat = get_theme_mod( 'background_repeat', 'repeat' );
		if ( ! in_array( $repeat, array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) ) )
			$repeat = 'repeat';
		$repeat = " background-repeat: $repeat;";

		$position = get_theme_mod( 'background_position_x', 'left' );
		if ( ! in_array( $position, array( 'center', 'right', 'left' ) ) )
			$position = 'left';
		$position = " background-position: top $position;";

		$attachment = get_theme_mod( 'background_attachment', 'scroll' );
		if ( ! in_array( $attachment, array( 'fixed', 'scroll' ) ) )
			$attachment = 'scroll';
		$attachment = " background-attachment: $attachment;";

		$style .= $image . $repeat . $position . $attachment;
	}
?>
	<style type="text/css">
		body { <?php echo trim( $style ); ?> }
	</style>
<?php
}
endif;

if ( !function_exists( 'trs_dtheme_header_style' ) ) :
/**
 * The styles for the post thumbnails / custom page headers.
 *
 * Referenced via add_custom_image_header() in trs_dtheme_setup().
 *
 * @global TRM_Query $post The current TRM_Query object for the current post or page
 * @since 1.2
 */
function trs_dtheme_header_style() {
	global $post;

	$header_image = '';

	if ( is_singular() && current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $post->ID ) ) {
		$image = trm_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'post-thumbnail' );

		// $src, $width, $height
		if ( !empty( $image ) && $image[1] >= HEADER_IMAGE_WIDTH )
			$header_image = $image[0];

	} else {
		$header_image = get_header_image();
	}
?>

	<style type="text/css">
		<?php if ( !empty( $header_image ) ) : ?>
			#header { background-image: url(<?php echo $header_image ?>); }
		<?php endif; ?>

		<?php if ( 'blank' == get_header_textcolor() ) { ?>
		#header h1, #header #desc { display: none; }
		<?php } else { ?>
		#header h1 a, #desc { color:#<?php header_textcolor() ?>; }
		<?php } ?>
	</style>

<?php
}
endif;

if ( !function_exists( 'trs_dtheme_widgets_init' ) ) :
/**
 * Register widgetised areas, including one sidebar and four widget-ready columns in the footer.
 *
 * To override trs_dtheme_widgets_init() in a child theme, remove the action hook and add your own
 * function tied to the init hook.
 *
 * @since 1.5
 */
function trs_dtheme_widgets_init() {
	// Register the widget columns
	// Area 1, located in the sidebar. Empty by default.
	register_sidebar( array(
		'name'          => 'Sidebar',
		'id'            => 'sidebar-1',
		'description'   => __( 'The sidebar widget area', 'trendr' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widgettitle">',
		'after_title'   => '</h3>'
	) );

	// Area 2, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'First Footer Widget Area', 'trendr' ),
		'id' => 'first-footer-widget-area',
		'description' => __( 'The first footer widget area', 'trendr' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>',
	) );

	// Area 3, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'Second Footer Widget Area', 'trendr' ),
		'id' => 'second-footer-widget-area',
		'description' => __( 'The second footer widget area', 'trendr' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>',
	) );

	// Area 4, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'Third Footer Widget Area', 'trendr' ),
		'id' => 'third-footer-widget-area',
		'description' => __( 'The third footer widget area', 'trendr' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>',
	) );

	// Area 5, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'Fourth Footer Widget Area', 'trendr' ),
		'id' => 'fourth-footer-widget-area',
		'description' => __( 'The fourth footer widget area', 'trendr' ),
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>',
	) );
}
add_action( 'widgets_init', 'trs_dtheme_widgets_init' );
endif;

if ( !function_exists( 'trs_dtheme_blog_comments' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own trs_dtheme_blog_comments(), and that function will be used instead.
 *
 * Used as a callback by trm_list_comments() for displaying the comments.
 *
 * @param mixed $comment Comment record from database
 * @param array $args Arguments from trm_list_comments() call
 * @param int $depth Comment nesting level
 * @see trm_list_comments()
 * @since 1.2
 */
function trs_dtheme_blog_comments( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	if ( 'pingback' == $comment->comment_type )
		return false;

	if ( 1 == $depth )
		$portrait_size = 50;
	else
		$portrait_size = 25;
	?>

	<li <?php comment_class() ?> id="comment-<?php comment_ID() ?>">
		<div class="comment-portrait-box">
			<div class="avb">
				<a href="<?php echo get_comment_author_url() ?>" rel="nofollow">
					<?php if ( $comment->user_id ) : ?>
						<?php echo trs_core_fetch_portrait( array( 'item_id' => $comment->user_id, 'width' => $portrait_size, 'height' => $portrait_size, 'email' => $comment->comment_author_email ) ) ?>
					<?php else : ?>
						<?php echo get_portrait( $comment, $portrait_size ) ?>
					<?php endif; ?>
				</a>
			</div>
		</div>

		<div class="comment-content">
			<div class="comment-meta">
				<p>
					<?php
						/* translators: 1: comment author url, 2: comment author name, 3: comment permalink, 4: comment date/timestamp*/
						printf( __( '<a href="%1$s" rel="nofollow">%2$s</a> said on <a href="%3$s"><span class="time-since">%4$s</span></a>', 'trendr' ), get_comment_author_url(), get_comment_author(), get_comment_link(), get_comment_date() );
					?>
				</p>
			</div>

			<div class="comment-entry">
				<?php if ( $comment->comment_approved == '0' ) : ?>
				 	<em class="moderate"><?php _e( 'Your comment is awaiting moderation.', 'trendr' ); ?></em>
				<?php endif; ?>

				<?php comment_text() ?>
			</div>

			<div class="comment-options">
					<?php if ( comments_open() ) : ?>
						<?php comment_reply_link( array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ); ?>
					<?php endif; ?>

					<?php if ( current_user_can( 'edit_comment', $comment->comment_ID ) ) : ?>
						<?php printf( '<a class="button comment-edit-link trs-secondary-action" href="%1$s" title="%2$s">%3$s</a> ', get_edit_comment_link( $comment->comment_ID ), esc_attr__( 'Edit comment', 'trendr' ), __( 'Edit', 'trendr' ) ) ?>
					<?php endif; ?>

			</div>

		</div>

<?php
}
endif;

if ( !function_exists( 'trs_dtheme_page_on_front' ) ) :
/**
 * Return the ID of a page set as the home page.
 *
 * @return false|int ID of page set as the home page
 * @since 1.2
 */
function trs_dtheme_page_on_front() {
	if ( 'page' != get_option( 'show_on_front' ) )
		return false;

	return apply_filters( 'trs_dtheme_page_on_front', get_option( 'page_on_front' ) );
}
endif;

if ( !function_exists( 'trs_dtheme_activity_secondary_portraits' ) ) :
/**
 * Add secondary portrait image to this activity stream's record, if supported.
 *
 * @param string $action The text of this activity
 * @param TRS_Activity_Activity $activity Activity object
 * @package trendr Theme
 * @return string
 * @since 1.2.6
 */
function trs_dtheme_activity_secondary_portraits( $action, $activity ) {
	switch ( $activity->component ) {
		case 'groups' :
		case 'friends' :
			// Only insert portrait if one exists
			if ( $secondary_portrait = trs_get_activity_secondary_portrait() ) {
				$reverse_content = strrev( $action );
				$position        = strpos( $reverse_content, 'a<' );
				$action          = substr_replace( $action, $secondary_portrait, -$position - 2, 0 );
			}
			break;
	}

	return $action;
}
add_filter( 'trs_get_activity_action_pre_meta', 'trs_dtheme_activity_secondary_portraits', 10, 2 );
endif;

if ( !function_exists( 'trs_dtheme_show_notice' ) ) :
/**
 * Show a notice when the theme is activated - workaround by Ozh (http://old.nabble.com/Activation-hook-exist-for-themes--td25211004.html)
 *
 * @since 1.2
 */
function trs_dtheme_show_notice() {
	global $pagenow;

	// Bail if trs-default theme was not just activated
	if ( empty( $_GET['activated'] ) || ( 'themes.php' != $pagenow ) || !is_admin() )
		return;

	?>

	<div id="message" class="updated fade">
		<p><?php printf( __( 'Theme activated! This theme contains <a href="%s">custom header image</a> support and <a href="%s">sidebar widgets</a>.', 'trendr' ), admin_url( 'themes.php?page=custom-header' ), admin_url( 'widgets.php' ) ) ?></p>
	</div>

	<style type="text/css">#message2, #message0 { display: none; }</style>

	<?php
}
add_action( 'admin_notices', 'trs_dtheme_show_notice' );
endif;


if ( !function_exists( 'trs_dtheme_comment_form' ) ) :
/**
 * Applies trendr customisations to the post comment form.
 *
 * @global string $user_identity The display name of the user
 * @param array $default_labels The default options for strings, fields etc in the form
 * @see comment_form()
 * @since 1.5
 */
function trs_dtheme_comment_form( $default_labels ) {
	global $user_identity;

	$commenter = trm_get_current_commenter();
	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );
	$fields =  array(
		'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name', 'trendr' ) . ( $req ? '<span class="required"> *</span>' : '' ) . '</label> ' .
		            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
		'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email', 'trendr' ) . ( $req ? '<span class="required"> *</span>' : '' ) . '</label> ' .
		            '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
		'url'    => '<p class="comment-form-url"><label for="url">' . __( 'Website', 'trendr' ) . '</label>' .
		            '<input id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>',
	);

	$new_labels = array(
		'comment_field'  => '<p class="form-textarea"><textarea name="comment" id="comment" cols="60" rows="10" aria-required="true"></textarea></p>',
		'fields'         => apply_filters( 'comment_form_default_fields', $fields ),
		'logged_in_as'   => '',
		'must_log_in'    => '<p class="alert">' . sprintf( __( 'You must be <a href="%1$s">logged in</a> to post a comment.', 'trendr' ), trm_login_url( get_permalink() ) )	. '</p>',
		'title_reply'    => __( 'Leave a reply', 'trendr' )
	);

	return apply_filters( 'trs_dtheme_comment_form', array_merge( $default_labels, $new_labels ) );
}
add_filter( 'comment_form_defaults', 'trs_dtheme_comment_form', 10 );
endif;

if ( !function_exists( 'trs_dtheme_before_comment_form' ) ) :
/**
 * Adds the user's portrait before the comment form box.
 *
 * The 'comment_form_top' action is used to insert our HTML within <div id="reply">
 * so that the nested comments comment-reply javascript moves the entirety of the comment reply area.
 *
 * @see comment_form()
 * @since 1.5
 */
function trs_dtheme_before_comment_form() {
?>
	<div class="comment-portrait-box">
		<div class="avb">
			<?php if ( trs_loggedin_user_id() ) : ?>
				<a href="<?php echo trs_loggedin_user_domain() ?>">
					<?php echo get_portrait( trs_loggedin_user_id(), 50 ) ?>
				</a>
			<?php else : ?>
				<?php echo get_portrait( 0, 50 ) ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="comment-content standard-form">
<?php
}
add_action( 'comment_form_top', 'trs_dtheme_before_comment_form' );
endif;

if ( !function_exists( 'trs_dtheme_after_comment_form' ) ) :
/**
 * Closes tags opened in trs_dtheme_before_comment_form().
 *
 * @see trs_dtheme_before_comment_form()
 * @see comment_form()
 * @since 1.5
 */
function trs_dtheme_after_comment_form() {
?>

	</div><!-- .comment-content standard-form -->

<?php
}
add_action( 'comment_form', 'trs_dtheme_after_comment_form' );
endif;

if ( !function_exists( 'trs_dtheme_sidebar_login_redirect_to' ) ) :
/**
 * Adds a hidden "redirect_to" input field to the sidebar login form.
 *
 * @since 1.5
 */
function trs_dtheme_sidebar_login_redirect_to() {
	$redirect_to = apply_filters( 'trs_no_access_redirect', !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '' );
?>
	<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
<?php
}
add_action( 'trs_sidebar_login_form', 'trs_dtheme_sidebar_login_redirect_to' );
endif;


/**
 * Adds the no-js class to the body tag.
 *
 * This function ensures that the <body> element will have the 'no-js' class by default. If you're
 * using JavaScript for some visual functionality in your theme, and you want to provide noscript
 * support, apply those styles to body.no-js.
 *
 * The no-js class is removed by the JavaScript created in trs_dtheme_remove_nojs_body_class().
 *
 * @package trendr
 * @since 1.5.1
 * @see trs_dtheme_remove_nojs_body_class()
 */
function trs_dtheme_add_nojs_body_class( $classes ) {
	$classes[] = 'no-js';
	return array_unique( $classes );
}
add_filter( 'trs_get_the_body_class', 'trs_dtheme_add_nojs_body_class' );

/**
 * Dynamically removes the no-js class from the <body> element.
 *
 * By default, the no-js class is added to the body (see trs_dtheme_add_no_js_body_class()). The
 * JavaScript in this function is loaded into the <body> element immediately after the <body> tag
 * (note that it's hooked to trs_before_header), and uses JavaScript to switch the 'no-js' body class
 * to 'js'. If your theme has styles that should only apply for JavaScript-enabled users, apply them
 * to body.js.
 *
 * This technique is borrowed from WordPress, Backend-WeaprEcqaKejUbRq-trendr/admin-header.php.
 *
 * @package trendr
 * @since 1.5.1
 * @see trs_dtheme_add_nojs_body_class()
 */
function trs_dtheme_remove_nojs_body_class() {
?><script type="text/javascript">//<![CDATA[
(function(){var c=document.body.className;c=c.replace(/no-js/,'js');document.body.className=c;})();
//]]></script>
<?php
}
add_action( 'trs_before_header', 'trs_dtheme_remove_nojs_body_class' );

?>
