<?php
/**
 * @package Make
 */

/**
 * Global includes.
 */
//@ini_set( 'upload_max_size' , '1M' );
//@ini_set( 'post_max_size', '1M');
//@ini_set( 'max_execution_time', '300' );
// Change the upload size to 1MB

function create_stories_table(){
    global $trs, $trmdb;
    if ( ! empty($trmdb->charset) )
    $charset_collate = "DEFAULT CHARACTER SET $trmdb->charset";
    if ( ! empty($trmdb->collate) )
    $charset_collate .= " COLLATE $trmdb->collate";
    $trs_prefix = trs_core_get_table_prefix();
    //$trs_prefix = 'trs_';
	$localities  = "CREATE TABLE IF NOT EXISTS {$trs_prefix} localities ( Lat_Long_pk int(11) NOT NULL) $charset_collate;";
	$trmdb->query($localities);
    $sql = "CREATE TABLE IF NOT EXISTS {$trs_prefix}trs_stories (
              id bigint(20) NOT NULL,
              user_id bigint(20) NOT NULL,
              type varchar(75) NOT NULL,
              action text NOT NULL,
              content longtext NOT NULL,
              primary_link varchar(150) NOT NULL,
              date_recorded datetime NOT NULL,
              location text NOT NULL,
              categories text NOT NULL, 
			  Latitude float NOT NULL,
			  Longitude float NOT NULL,
			  Lat_Long_fk int(8))  $charset_collate;";
    $trmdb->query($sql);
    $add_field="ALTER TABLE {$trs->profile->table_name_meta}
    ADD COLUMN user_id bigint(20) NOT NULL AFTER id;";
    $trmdb->query($add_field);
    $add_field="ALTER TABLE {$trs->profile->table_name_meta}
    ADD COLUMN user_id bigint(20) NOT NULL AFTER id;";
    $trmdb->query($add_field);
}
add_action("init","create_stories_table");
add_filter( 'upload_size_limit', 'trmse_163236_change_upload_size' ); 
function trmse_163236_change_upload_size()
{
    return 1000 * 1024;
}


//activity_homepage.
function trs_profile_homepage(){
global $trs;    
if(is_user_logged_in() && is_front_page())      {
    
trm_redirect( TRS_ACTIVITY_SLUG . '/following' ) ;    

}   
        }
add_action('trm','trs_profile_homepage');


function followers_bar() {

     // if ( trs_is_user_activity() ||trs_is_activity_component() ) {
   // if ( trs_is_user_activity() ) {

        $args[ 'user_id' ] = trs_displayed_user_id() ;
                $args1 = trs_get_user_firstname( $leader_fullname );





    // do not do anything if user isn't logged in


        if ( empty( $instance['max_users'] ) ) {
            $instance['max_users'] = 16;
        }

        // logged-in user isn't following anyone, so stop!
        if ( ! $followers = trs_get_follower_ids( array( 'user_id' => trs_displayed_user_id() ) ) ) {
        }

        // show the users the logged-in user is following
        if ( trs_has_members( 'include=' . $followers . '&max=' . $instance['max_users'] ) ) {
        

    ?> <?php while ( trs_members() ) : trs_the_member(); ?>
                     
                     <div class="followers-p">
                        <a href="<?php trs_member_permalink() ?>"><?php trs_member_portrait('type=full&width=35&height=35') ?></a></div>
                        
                <?php endwhile; ?>




    <?php
        }

}

add_action( 'followers_bar', 'followers_bar', 1 );


 ///////////////////////////////////////          

if ( !function_exists( 'trs_dtheme_setup' ) ) :

function trs_dtheme_setup() {
	global $trs;


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
		//if ( trs_is_active( 'blogs' ) )
			//add_action( 'trs_directory_blogs_actions',  'trs_blogs_visit_blog_button' );
	}
}
add_action( 'after_setup_theme', 'trs_dtheme_setup' );
endif;
 
// Set the content width based on the theme's design and stylesheet.
if ( ! isset( $content_width ) )
	$content_width = 884;

//if ( !function_exists( 'trend_enqueue_styles' ) ) :
//function trend_enqueue_styles() {
	// Bump this when changes are made to bust cache
	//$version = '1';

	// Register our main stylesheet
	//trm_register_style( 'trend-main', get_template_directory_uri() . '/a/src.css', array(), $version );
	// Enqueue the main stylesheet
	//trm_enqueue_style( 'trend-main' );
	
//}
//add_action( 'trm_enqueue_scripts', 'trend_enqueue_styles' );
//endif;

add_filter( 'script_loader_src', 'trmse47206_src' );
add_filter( 'style_loader_src', 'trmse47206_src' );
function trmse47206_src( $url )
{
    if( is_admin() ) return $url;
    return str_replace( site_url(), '', $url );
}
// /trm_deregister_script( 'jquery' ); 
function trs_core_jquery() {
	global $trs;
		trm_deregister_script('jquery');
		//trm_register_script('jquery', 'https://cdn.jsdelivr.net/g/jquery@1.8.3',true);

		//trm_register_script('uploader', 'http://cdn.jsdelivr.net/valums-file-uploader/2.0/fileuploader.js');
		//trm_register_script('tipsy', 'http://cdn.imnjb.me/libs/valums-file-uploader/2.0/fileuploader.js');
		//trm_register_script('uploader', 'http://cdn.lukej.me/valums-file-uploader/2.0/fileuploader.js');
        trm_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js');  
        //,jquery.ui@1.11.3,valums-file-uploader,jquery.pagepiling,tipsy@0.1.7,jquery.ui@1.11.3,jquery.magnific-popup@0.9.9
		trm_print_scripts( 'jquery' );
		trm_enqueue_script('general', get_template_directory_uri() . '/a/global.js', $version,'1',true);

	// Add words that we need to use in JS to the end of the page so they can be translated and still used.
		//trm_print_scripts( 'misc' );
	$params = array(
	'add_photos' => __('Add Media', 'tr'),
			'add_remote_image' => __('Add image URL', 'tr'),
			//'add_another_remote_image' => __('Add another image URL', 'tr'),
			'add_videos' => __('Add videos', 'tr'),
			'add_video' => __('Add video', 'tr'),
			'add_links' => __('Add links', 'tr'),
			'add_link' => __('Add link', 'tr'),
			'add' => __('Add', 'tr'),
			'cancel' => __('Cancel', 'tr'),
			'preview' => __('Preview', 'tr'), 
			'drop_files' => __('Drop files here to upload', 'tr'),
			'upload_file' => __('Photo/Video', 'tr'),
			//'no_thumbnail' => __('No thumbnail', 'tr'),
			'paste_video_url' => __('Paste video URL here', 'tr'),
			'paste_link_url' => __('Paste link URL here', 'tr'),
			'paste_photo_url' => __('Paste photo URL here', 'tr'),
		'accepted'          => __( 'Accepted', 'tr' ),
		'rejected'          => __( 'Rejected', 'tr' ),
		'show_all_comments' => __( 'Show all comments for this thread', 'tr' ),
		'show_all'          => __( 'Show all', 'tr' ),
		'comments'          => __( 'comments', 'tr' ),
		'close'             => __( 'Close', 'tr' ),
		'view'              => __( 'View', 'tr' ),
		'mark_as_fav'	    => __( 'watch later', 'tr' ),
		'remove_fav'	    => __( 'unwatch', 'tr' ),
		'my_favs'           => __( 'My Favorites', 'tr' ),

	);
	trm_localize_script( 'general', 'TRS_DTheme', $params );
		trm_print_scripts( 'general' );
}
add_action( 'trs_footer', 'trs_core_jquery');


function js_plugin_url () {
        $data = apply_filters(
            'med_js_data_object',
            array(
                'root_url' => MED_PLUGIN_URL,
                'temp_img_url' => MED_TEMP_IMAGE_URL,
                'base_img_url' => MED_BASE_IMAGE_URL,
                'theme' => Med_Data::get('theme', 'default'),
                'alignment' => Med_Data::get('alignment', 'left'),
            )
        );
        printf('<script type="text/javascript">var _med_data=%s;</script>', json_encode($data));
        //if ('default' != $data['theme'] && !current_theme_supports('med_toolbar_icons')) {
            //$url = MED_PLUGIN_URL;
            //echo <<<EOFontIconCSS

//EOFontIconCSS;
        //}
    }


            add_action('trs_footer',  'js_plugin_url',10);


if ( !function_exists( 'trs_dtheme_main_nav' ) ) :
function trs_dtheme_main_nav( $args ) {
	global $trs;

	$pages_args = array(
		'depth'      => 0,
		'echo'       => false,
		'exclude'    => '',
		'title_li'   => ''
	);
	$menu = trm_page_menu( $pages_args );
	$menu = str_replace( array( '<div class="menu"><ul>', '</ul></div>' ), array( '<ul id="nav">', '</ul><!-- #nav -->' ), $menu );
	echo $menu;

	do_action( 'trs_nav_items' );
}
endif;


if ( ! function_exists( 'ttfmake_setup' ) ) :
function ttfmake_setup() {
	global $trs;
	require( TEMPLATEPATH . '/a/ajax.php' );

	// This theme uses post thumbnails
	add_theme_support( 'post-thumbnails' );
	// This theme uses trm_nav_menu() in one location.
	//register_nav_menus( array(
		//'primary' => __( 'Primary Navigation', 'tr' ),
	//) );

	if ( !is_admin() ) {
		// Messages button
		if ( trs_is_active( 'messages' ) )
			add_action( 'trs_member_header_actions',    'trs_send_private_message_button' );

	}
}
add_action( 'after_setup_theme', 'trs_dtheme_setup' );
endif;
add_action( 'after_setup_theme', 'ttfmake_setup' );


if ( ! function_exists( 'ttfmake_get_site_header_class' ) ) :

function ttfmake_get_site_header_class() {

}
endif;



function is_ajax()
{
    return(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}
