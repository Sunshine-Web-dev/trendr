<?php
/*
Plugin Name:  Hashtags
Plugin URI: http://trmnder.org/extend/module/trmnder-activity-strmeam-hashtags/
Description: Enable #hashtags linking within activity strmeam content - converts before database.
Author: rich @etiviti
Author URI: http://etivite.com
License: GNU GENERAL PUBLIC LICENSE 3.0 http://www.gnu.org/licenses/gpl.txt
Version: 0.5.1
Text Domain: trs-activity-hashtags
Network: trmue
*/

//TODO - We really need unicode support =) For example �#tag� works ok, but �#?????� � nope.
//TODO - support post db content filter rewrite on #tag
if ( ! function_exists( 'get_plugins' ) ) {
    require_once( ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/plugin.php' );
}
$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) ) ;
$plugin_file = basename( ( __FILE__ ) ) ;


define( 'TRS_HASHTAGS_VERSION' , $plugin_folder[ $plugin_file ][ 'Version' ] ) ;
define( 'TRS_HASHTAGS_DB_VERSION' , $plugin_folder[ $plugin_file ][ 'Version' ] ) ;
define( 'TRS_HASHTAGS_BASENAME' , plugin_basename( __FILE__ ) ) ;
if ( ! defined( 'TRS_ACTIVITY_HASHTAGS_SLUG' ) ) {
    define( 'TRS_ACTIVITY_HASHTAGS_SLUG' , 'tag' ) ;
}

function ls_trs_hashtags_init() {

	if ( !trs_is_active( 'activity' ) )
		return;		

	//if ( file_exists( dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' ) )
	//	load_textdomain( 'trs-activity-hashtags', dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' );

	$data = maybe_unserialize( get_option( 'etivite_trs_activity_stream_hashtags' ) );
		

		
	require( dirname( __FILE__ ) . '/trs-activity-hashtags.php' );
	
	//same set used for atme mentions
	//add_filter( 'trs_activity_comment_content', 'etivite_trs_activity_hashtags_filter' );
	add_filter( 'trs_activity_new_update_content', 'etivite_trs_activity_hashtags_filter' );

	//add_filter( 'group_forum_topic_text_before_save', 'etivite_trs_activity_hashtags_filter' );
	//add_filter( 'group_forum_post_text_before_save', 'etivite_trs_activity_hashtags_filter' );
	//add_filter( 'groups_activity_new_update_content', 'etivite_trs_activity_hashtags_filter' );		
	
	//what about blog posts in the activity stream
	//if ( $data['blogactivity']['enabled'] ) {
	//	add_filter( 'trs_blogs_activity_new_post_content', 'etivite_trs_activity_hashtags_filter' );
	//	//add_filter( 'trs_blogs_activity_new_comment_content', 'etivite_trs_activity_hashtags_filter' );
//	}
	
	//what about general blog posts/comments?
	//if ( $data['blogposts']['enabled'] ) {
	//	add_filter( 'get_comment_text' , 'etivite_trs_activity_hashtags_filter', 9999 );
		//add_filter( 'the_content', 'etivite_trs_activity_hashtags_filter', 9999 );
//	}
	
	//support edit activity stream plugin
//	add_filter( 'trs_edit_activity_action_edit_content', 'etivite_trs_activity_hashtags_filter' );
	
	//ignore this - if we wanted to filter after - this would be it 
	//but then we can't search by the #hashtag via search_terms (since the trick is the ending </a>)
	//as the search_term uses LIKE %%term%% so we would match #child #children
	//add_filter( 'trs_get_activity_content_body', 'etivite_trs_activity_hashtags_filter' );
	
	//add_action( trs_core_admin_hook(), 'etivite_trs_activity_hashtags_admin_add_admin_menu' );
	
}
add_action( 'trs_include', 'ls_trs_hashtags_init', 88 );
//add_action( 'trs_init', 'ls_trs_hashtags_init', 88 );
function etivite_plugin_get_version() {
    $plugin_data = get_plugin_data( __FILE__ ) ;
    $plugin_version = $plugin_data[ 'Version' ] ;
    return $plugin_version ;
}
function trs_hashtags_tableCreate( $charset_collate ) {
    global $trs ;
    //$activity_table = 'trs_activity' ;
    $toSql = $sql[] = "CREATE TABLE " . TRS_HASHTAGS_TABLE . " (
		  		id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                hashtag_name VARCHAR(255) NOT NULL,
                                hashtag_slug TEXT NOT NULL,
                                value_id bigint(20) NOT NULL,
                                taxonomy varchar(255) DEFAULT '',
                                if_activity_component VARCHAR(255) DEFAULT '',
                                if_activity_item_id bigint(20),
                                hide_sitewide bool DEFAULT 0,
                                user_id int NOT NULL,
                                created_ts DATETIME NOT NULL,
				KEY hashtag_name (hashtag_name),
                                KEY taxonomy (taxonomy),
                                KEY if_activity_item_id (if_activity_item_id),
                                KEY if_activity_component (if_activity_component),
				KEY hide_sitewide (hide_sitewide),
                                KEY user_id (user_id),
                                KEY created_ts (created_ts)
		 	   ) {$charset_collate};" ;
    return $toSql ;
}

function trs_hashtags_is_installed() {
    trs_hashtags_set_constants() ;
    if ( get_site_option( 'trs-hashtags-db-version' ) < TRS_HASHTAGS_DB_VERSION ) {
        trs_hashtags_install_upgrade() ;
    }
}

register_activation_hook( __FILE__ , 'trs_hashtags_is_installed' ) ;

/**
 * trs_hashtags_install_upgrade()
 *
 * Installs and/or upgrades the database tables
 * This will only run if the database version constant is
 * greater than the stored database version value or no database version found
 * @author Stergatu Eleni <stergatu@cti.gr>
 * @version 1.0, 8/4/2014 now uses add_site_option instead of add_option
 */
function trs_hashtags_install_upgrade() {
    global $trmdb ;
    global $trs ;

    $charset_collate = '' ;
    if ( ! empty( $trmdb->charset ) ) {
        $charset_collate = "DEFAULT CHARACTER SET $trmdb->charset" ;
    }

    //If there is a previous version installed then move the variables to the sitemeta table
    if ( (get_site_option( 'trs-hashtags-db-version' )) && (get_site_option( 'trs-hashtags-db-version' ) < TRS_HASHTAGS_DB_VERSION) ) {
        $sql[] = trs_hashtags_tableCreate( $charset_collate ) ;
    }
    if ( ! get_site_option( 'trs-hashtags-db-version' ) ) {
        $sql[] = trs_hashtags_tableCreate( $charset_collate ) ;
        add_site_option( 'trs-hashtags-db-version' , TRS_HASHTAGS_DB_VERSION ) ;
    }
    update_site_option( 'trs-hashtags-db-version' , TRS_HASHTAGS_DB_VERSION ) ;

    require_once( ABSPATH . "Backend-WeaprEcqaKejUbRq-trendr/includes/upgrade.php" ) ;
    dbDelta( $sql ) ;
}

/**
 * @author Stergatu Eleni
 * @version 1, 8/4/2014
 */
function trs_hashtags_set_constants() {
    global $trs ;
    if ( ! defined( 'TRS_HASHTAGS_TABLE' ) ) {
        define( 'TRS_HASHTAGS_TABLE' , $trs->table_prefix . 'trs_hashtags' ) ;
    }
    do_action( 'trs_hashtags_constants_loaded' ) ;
}



?>
