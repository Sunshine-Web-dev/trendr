<?php
if ( !defined( 'ABSPATH' ) ) exit;

//MODIFIED 6-18-18
//function etivite_trs_activity_hashtags_current_activity() {
//  global $activities_template;
//  return $activities_template->current_activity;
//}


function ls_trs_hashtags_get_from_string( $string ) {
    $hashtags = FALSE;
    $pattern = '/(#\w+)/u';
    $string = trm_strip_all_tags( $string );
    preg_match_all( $pattern, $string, $matches );

    if ( $matches ) {
    $hashtagsArray = array_count_values( $matches[0] );
    $hashtags = array_unique( array_keys( $hashtagsArray ) );
    }
    return $hashtags;
}
function etivite_trs_activity_hashtags_filter( $content ) {
    global $trs ;
    $hashtags = ls_trs_hashtags_get_from_string( $content ) ;
    if ( $hashtags ) {
//but we need to watch for edits and if something was already wrapped in html link - thus check for space or word boundary prior
        foreach ( ( array ) $hashtags as $hashtag ) {
            $pattern = "/(^|\s|\b)" . $hashtag . "($|\b)/u" ;
            $hashtag_noHash = str_replace( "#" , '' , $hashtag ) ;
            $content = str_replace( $hashtag , ' <a href="' . $trs->root_domain . "/" . $trs->activity->slug . "/" . TRS_ACTIVITY_HASHTAGS_SLUG . "/" . urlencode( htmlspecialchars( $hashtag_noHash ) ) . '"  id="tg">' . $hashtag . '</a>' , $content ) ;
        }
    }
    return $content ;
}

add_filter( 'trs_activity_new_update_content' , 'etivite_trs_activity_hashtags_filter' ) ;
//add_filter( 'trs_get_activity_content_body' , 'etivite_trs_activity_hashtags_filter' ) ;

function etivite_trs_activity_hashtags_querystring( $qs, $object) {
    global $trs;

    if ( !trs_is_activity_component() || $trs->current_action != TRS_ACTIVITY_HASHTAGS_SLUG )
        return $qs;

    if ( empty( $trs->action_variables[0] ) )
        return $qs;

    //if ( 'feed' == $trs->action_variables[1] )
        //return $query_string;

    if ( strlen( $qs ) < 1 )
        return 'display_comments=true&search_terms=#'. $trs->action_variables[0] . '<';

    /* Now pass the querystring to override default values. */
    $qs .= '&display_comments=true&search_terms=#'. $trs->action_variables[0] . '<';

    return $qs;
}
add_filter( 'trendr_ajax_call', 'etivite_trs_activity_hashtags_querystring', 11, 2 );


/**
 *
 * @return type
 * @version 3, stergatu 27/11/2014
 */
function ls_trs_hashtags_header() {
    global $trs ;
    global $trs , $trs_unfiltered_uri;

    if ( ! trs_is_activity_component() || $trs->current_action != TRS_ACTIVITY_HASHTAGS_SLUG )
    return; 
   printf( "<a class='tag-title'>  Results for," ."<a class='tag-result'>  " . __( " #%s ", 'trs-hashtags' ), urldecode( $trs->action_variables[0] ) . '</a>' );
echo ' <div class="reset-hashtags" ;">  <a href="' . trs_displayed_user_domain(). '/' . '">' . __( 'X', 'trs-hashtags' ) . '</a></div>';   
}
add_action( 'trs_before_member_header', 'ls_trs_hashtags_header' );




/**
 * Add a description under the activity post form about the hashtag usage
 * @author stergatu
 * @version 1, 11/4/2014
 */
//function ls_trs_hashtags_add_hashtags_text() {
  //  _e( "<a class='post-title'>  " . __( "Add a location to your posts. ", 'trs-hashtags' ) , 'trs-hashtags' ) ;
//}

//add_action( 'trs_activity_post_form_options' , 'ls_trs_hashtags_add_hashtags_text' ) ;


/**
 *
 * @global trmdb $trmdb
 * @param array $args
 * @return array of activity ids
 * @version 3, 24/4/2014
 */
function ls_trs_hashtags_get_activity_ids( $args = array() ) {
    global $trmdb;
    global $trs;
    trs_hashtags_set_constants();
    $toWhere = ls_trs_hashtags_generate_query_limitations( $args );
    $results = $trmdb->get_col(
        "
    SELECT value_id
    FROM " . TRS_HASHTAGS_TABLE . " WHERE  1=1   " . $toWhere );
    return $results;
}

/**
 * Create the query criteria
 * @param array $args  {
 *     An array of arguments. All items are optional.
 * @param string $hashtag_name. A specific hashtag
 *      @param int user_id. A specific user_id
 * @param bool $if_activity_item_id. If it is a item frome the trs_activity table
 * @param string $special. A complex where critirions
 * @param bool $hide_sitewide. If we can search the hide_sitewide activity records
 * }
 * @return string
 * @version 3, 25/8/2014 added taxonomy limitations
 * v2, 25/4/2014
 * @author stergatu
 */
function ls_trs_hashtags_generate_query_limitations( $args = array() ) {
    global $trs;

    $data = maybe_unserialize( get_site_option( 'ls_trs_hashtags' ) );
    $taxonomy_limit = '';
    if ( $data['blogposts']['use_taxonomy'] != '1' ) {
    $taxonomy_limit = ' AND taxonomy = "" ';
    }

    if ( ! $args ) {
    $args = array();
    }
    $query_hashtag = '';
    if ( isset( $args['hashtag_name'] ) ) {
    $query_hashtag = ' AND hashtag_name ="' . urldecode( $args['hashtag_name'] ) . '" ';
    }
    $query_user = '';
    if ( array_key_exists( 'user_id', $args ) && $args['user_id'] != 0 ) {
    $query_user = ' AND user_id=' . absint( $args['user_id'] );
    }
    $query_item_id = '';
    if ( array_key_exists( 'if_activity_item_id', $args ) && $args['if_activity_item_id'] != 0 ) {
    $query_item_id = ' AND if_activity_item_id=' . absint( $args['if_activity_item_id'] );
    }

   // $args = ls_trs_hashtags_show_hidden_hashtags( $args );
    $args =$args ;

    $query_special = '';
    if ( array_key_exists( 'special', $args ) ) {
    $query_special = ' AND ' . $args['special'];
    }
   // $query_hide_sitewide = '';
   // if ( array_key_exists( 'hide_sitewide', $args ) && $args['hide_sitewide'] != '' ) {
    //$query_hide_sitewide = ' AND hide_sitewide=' . $args['hide_sitewide'];
   // }

    $toWhere = $taxonomy_limit . $query_hashtag . $query_user . $query_item_id . $query_special ;
    return $toWhere;
}



/**
 * Fetches tags and categories from post as hashtags
 *
 * @global trmdb $trmdb
 * @param TRS_Activity_Activity $activity
 * @return array|TRM_Error The requested term data or empty array if no terms found. TRM_Error if any of the $taxonomies don't exist.
 * @author stergatu
 * @since
 * @version 1, 10/4/2013
 */
function ls_trs_hashtags_getblogpost_tags_as_hashtags( $activity ) {
    global $trmdb;
    $blog_id = $activity->item_id;
    $post_id = $activity->secondary_item_id;
    switch_to_blog( $blog_id );
    $post_types_use_as_trs_hashtags = array( 'post_tag', 'category' );
    $types = apply_filters( 'custom_post_type_use_as_trs_hashtags', $post_types_use_as_trs_hashtags );
//$tags = trm_get_object_terms( $post_id , $types ) ;

    $tagsfrompost = trm_get_object_terms( $post_id, $types, array( 'fields' => 'all' ) );
    $tags = array_map( "only_usefull", $tagsfrompost );

    restore_current_blog();

    return $tags;
}
/**
 *
 * @param array $a
 * @return array
 * @version 1, 25/8/2014
 */
function only_usefull( $a ) {
    $x['name'] = $a->name;
    $x['taxonomy'] = $a->taxonomy;
    return $x;
}



/**
 *
 * @global type $trmdb
 * @param object $activity
 * @author Stergatu Lena <stergatu@cti.gr>
 * @version v3, 19/5/2015 fix  a bug
 * v2, 25/8/2014 add check for blog posts taxomony
 * v1, 8/4/2014
 */
function ls_trs_hashtags_add_activity_id( $activity ) {
    global $trmdb ;
    trs_hashtags_set_constants() ;
    do_action( 'pre_hashtags_insert' , $activity ) ;


   
    $hashtags_included_to_content = str_replace( "#" , '' , ls_trs_hashtags_get_from_string( $activity->content ) ) ;
    foreach ( $hashtags_included_to_content as $hashtag ) {
        $trmdb->insert(
                TRS_HASHTAGS_TABLE , array (
            'hashtag_name' => htmlspecialchars( $hashtag ) ,
            //'hashtag_slug' => urlencode( htmlspecialchars( $hashtag ) ) ,
            'value_id' => $activity->id ,
            'if_activity_component' => $activity->component ,
            'if_activity_item_id' => $activity->item_id ,
            //'hide_sitewide' => $activity->hide_sitewide ,
            'created_ts' => $activity->date_recorded ,
            'user_id' => $activity->user_id
        ) ) ;
    }
}

add_action( 'trs_activity_after_save' , 'ls_trs_hashtags_add_activity_id' ) ;
/**
 * When saved an updated activity post, it deletes previous inserted hashtags from this activity
 * @global type $trmdb
 * @param type $activity
 * @author Stergatu Lena <stergatu@cti.gr>
 * @version 1, 8/4/2014
 */
function ls_trs_hashtags_delete_activity_id( $activity ) {
    global $trmdb ;
    trs_hashtags_set_constants() ;

    //if ( is_int( $activity ) ) {
     //   $trmdb->delete( TRS_HASHTAGS_TABLE , array ( 'value_id' => $activity , 'table_name' => 'trs_activity' ) ) ;
 //   } else {
   //     $trmdb->delete( TRS_HASHTAGS_TABLE , array ( 'value_id' => $activity->id , 'table_name' => 'trs_activity' ) ) ;
   // }
}

add_action( 'pre_hashtags_insert' , 'ls_trs_hashtags_delete_activity_id' ) ;
add_action( 'trs_activity_action_delete_activity' , 'ls_trs_hashtags_delete_activity_id' ) ;
/**
 * Clear hashtags for deleted activity items.
 *function ls_trs_hashtags_cloud2() {
    if ( trs_is_user_activity() ) {
    //$t=__(trs_displayed_user_fullname(), 'trs-hashtags' ) ;
            $args = array(
                'include'  => trs_get_following_ids(),
            );
    $qs=  trs_get_following_ids(  );
$trs = trs_follow_total_follow_counts( array('user_id' => trs_displayed_user_id()));
        $ids = implode( ',', (array)trs_follow_get_following( ) );
        $toHead = __( 'Hashtags by', 'trs-hashtags' ) ;
        $args[ 'user_id' ] = $qs;
    }
    //if ( trs_is_group_activity() || trs_is_group_home() ) {
      //  $toHead = __( 'Hashtags in group' , 'trs-hashtags' ) ;
       /// $args[ 'if_activity_item_id' ] = trs_get_current_group_id() ;
  //  }
    echo '<div class="hashtitle" align="left"><h5>' . $toHead . '</h5>' ;

    echo ls_trs_hashtags_generate_cloud( $args ) ;
    echo '</div>' ;
}

----------------
useful taken from activity function
  $activity                    = new TRS_Activity_Activity( $id );
  $activity->user_id           = $user_id;
  $activity->component         = $component;
  $activity->type              = $type;
  $activity->action            = $action;
  $activity->content           = $content;
  $activity->primary_link      = $primary_link;
  $activity->item_id           = $item_id;
  $activity->secondary_item_id = $secondary_item_id;
  $activity->date_recorded     = $recorded_time;
  $activity->hide_sitewide     = $hide_sitewide;

add_action( 'trs_before_member_header', 'ls_trs_hashtags_cloud2', 1 );
 * @since 1.0
 * @author stergatu
 * @param array $deleted_ids IDs of deleted activity items.
 */
function ls_trs_hashtags_clear_deleted_activity( $deleted_ids ) {
    global $trmdb ;
    trs_hashtags_set_constants() ;
    foreach ( ( array ) $deleted_ids as $deleted_id ) {
       // $trmdb->delete( TRS_HASHTAGS_TABLE , array ( 'value_id' => $deleted_id , 'table_name' => 'trs_activity' ) ) ;
    }
}

add_action( 'trs_activity_deleted_activities' , 'ls_trs_hashtags_clear_deleted_activity' ) ;
//if an activity is marked as spam deleted from the trs_hashtags table
add_action( 'trs_activity_action_spam_activity' , 'ls_trs_hashtags_clear_deleted_activity' ) ;









/**
 *
 * @global type $trs
 * @global type $tr_query
 * @return boolean
 * @version 2, stergatu
 */
////makes the hashtags to explore function

function etivite_trs_activity_hashtags_action_router() {
    global $trs, $trm_query;

    if ( !trs_is_activity_component() || trs_is_user_activity()||$trs->current_action != TRS_ACTIVITY_HASHTAGS_SLUG || !$trs->activity->slug  )
        return false;

    if ( empty( $trs->action_variables[0] ) )
        return false;

        trs_core_load_template( 'activity/index' );

    
}
add_action( 'trm', 'etivite_trs_activity_hashtags_action_router', 3 );


////makes the hashtags to member's profile function

function etivite_trs_activity_hashtags_action_router_personal() {
    global $trs, $trm_query;

    if ( !trs_is_activity_component() || !trs_is_user_activity()||$trs->current_action != TRS_ACTIVITY_HASHTAGS_SLUG || !$trs->activity->slug  )
            return false;

    if ( empty( $trs->action_variables[0] ) )
        return false;

        trs_core_load_template( 'members/single/home' );

    
}
add_action( 'trm', 'etivite_trs_activity_hashtags_action_router_personal', 3 );






/**
 * Fetches hashtags from database with the link and count
 * @global trmdb $trmdb
 * @param array $args
 * @return array
 * @version 2, 25/8/2014
 * v1, 8/5/2014
 */
function ls_trs_hashtags_get_hashtags( $args = array() ) {
    global $trmdb;
    global $trs;
    $link = $trs->root_domain . "/" . $trs->activity->slug . "/" . TRS_ACTIVITY_HASHTAGS_SLUG . "/";
    trs_hashtags_set_constants();

    $data = maybe_unserialize( get_site_option( 'ls_trs_hashtags' ) );

    if ( $data['style']['show_hashsymbol'] == '1' ) {
    $hashtag_name = ' CONCAT( "#", hashtag_name)';
    } else {
    $hashtag_name = 'hashtag_name ';
    }


    $toWhere = ls_trs_hashtags_generate_query_limitations( $args );

    $results = $trmdb->get_results( 'SELECT COUNT(hashtag_name) as count, '
        . $hashtag_name . ' as name, '
        . 'CONCAT("' . $link . '", hashtag_name) as link
        FROM ' . TRS_HASHTAGS_TABLE . ' WHERE 1=1 ' . $toWhere . ' GROUP BY hashtag_name' );

    return $results;
}

function ls_trs_hashtags_get_hashtags_personal( $args = array() ) {
    global $trmdb;
    global $trs;
        $link =  trs_displayed_user_domain() . $trs->activity->slug . "/" .TRS_ACTIVITY_HASHTAGS_SLUG . "/";
    trs_hashtags_set_constants();

    $data = maybe_unserialize( get_site_option( 'ls_trs_hashtags' ) );

    if ( $data['style']['show_hashsymbol'] == '1' ) {
    $hashtag_name = ' CONCAT( "#", hashtag_name)';
    } else {
    $hashtag_name = 'hashtag_name ';
    }

    $toWhere = ls_trs_hashtags_generate_query_limitations( $args );

    $results = $trmdb->get_results( 'SELECT COUNT(hashtag_name) as count, '
        . $hashtag_name . ' as name, '
        . 'CONCAT("' . $link . '", hashtag_name) as link
        FROM ' . TRS_HASHTAGS_TABLE . ' WHERE 1=1 ' . $toWhere . ' GROUP BY hashtag_name' );

    return $results;
}

/**
 * Generates hashtags list
 * @uses trm_generate_tag_cloud()
 * @global type $trmdb
 * @param array $args, see trm_generate_tag_cloud() for args values
 * @return string
 * @author Stergatu Lena <stergatu@cti.gr>
 * @version 3, 8/5/2014
 * @todo add filters instead of if clauses
 */

////redirecting hashtags to explore page

function ls_trs_hashtags_generate_cloud( $args = array() ) {
    $hashtags = ls_trs_hashtags_get_hashtags( $args );
    $defaults = array(
    'smallest' => 10, 'largest' => 10, 'unit' => 'pt', 'number' => 15,
    'format' => 'flat', 'orderby' => 'count', 'order' => 'DESC',
    'topic_count_text_callback' => 'default_topic_count_text',
    'topic_count_scale_callback' => 'default_topic_count_scale', 'filter' => 1
    );
    $args = trm_parse_args( $args, $defaults );
    extract( $args );
    $tag_cloud = trm_generate_tag_cloud( $hashtags, $args );
    $tag_cloud = '<div class="hashtags">' . $tag_cloud . '</div>';

    return $tag_cloud;
}
////redirecting hashtags to member's profile

function ls_trs_hashtags_generate_cloud_personal( $args = array() ) {
    $hashtags = ls_trs_hashtags_get_hashtags_personal( $args );
    $defaults = array(
    'smallest' => 10, 'largest' => 10, 'unit' => 'pt', 'number' => 6,
    'format' => 'flat',  'orderby' => 'count', 'order' => 'DESC',
    'topic_count_text_callback' => 'default_topic_count_text',
    'topic_count_scale_callback' => 'default_topic_count_scale', 'filter' => 1
    );
    $args = trm_parse_args( $args, $defaults );
    extract( $args );
    $tag_cloud = trm_generate_tag_cloud( $hashtags, $args );
    $tag_cloud = '<div class="hashtags">' . $tag_cloud . '</div>';

    return $tag_cloud;
}



/**
 * echo's the tagcloud
 * @version 2, 24/4/2014
 */
function ls_trs_hashtags_cloud() {
    $args = array () ;
    if ( trs_is_activity_component() ) {
        $toHead = __( 'Popular hashtags across network' , 'trs-hashtags' ) ;
    }
    //if ( trs_is_user_activity() ) {
       // $toHead = __( 'Hashtags by user' , 'trs-hashtags' ) ;
       // $args[ 'user_id' ] = trs_displayed_user_id() ;
   // }
    //if ( trs_is_group_activity() || trs_is_group_home() ) {
      //  $toHead = __( 'Hashtags in group' , 'trs-hashtags' ) ;
       /// $args[ 'if_activity_item_id' ] = trs_get_current_group_id() ;
  //  }
    echo '<div class="hashtitle-e" align="left"><h5>' . $toHead . '</h5>' ;
    echo ls_trs_hashtags_generate_cloud( $args ) ;
    echo '</div>' ;
}

add_action( 'trs_before_directory_activity_list', 'ls_trs_hashtags_cloud', 1 );

function ls_trs_hashtags_cloud_personal() {

     // if ( trs_is_user_activity() ||trs_is_activity_component() ) {
   // if ( trs_is_user_activity() ) {

        $args[ 'user_id' ] = trs_displayed_user_id() ;
                $args1 = trs_get_user_firstname( $leader_fullname );

    //}
    //if ( trs_is_group_activity() || trs_is_group_home() ) {
      //  $toHead = __( 'Hashtags in group' , 'trs-hashtags' ) ;
       /// $args[ 'if_activity_item_id' ] = trs_get_current_group_id() ;
  //  }
                        $toFoot = __( ' Top Hashtags From: ', 'trs-hashtags' ) ;
                        $toHead = __( $args1,  'trs-hashtags' ) ;

    echo '<div class="hashtitle" align="left">' ;

if ( trs_has_members( 'type=newest&max=12&user_id=' . trs_displayed_user_id() ) & is_user_logged_in() ) : 
;?>
          <?php    echo '<h5>' . $toHead . "'s friends".'</h5>' ;?>

                <?php while ( trs_members() ) : trs_the_member(); ?>
                    
                        <a href="<?php trs_member_permalink() ?>"><?php trs_member_portrait('type=full&width=55&height=56') ?></a>
                    
                <?php endwhile; ?>


        <?php endif; 
    // do not do anything if user isn't logged in


        if ( empty( $instance['max_users'] ) ) {
            $instance['max_users'] = 16;
        }

        // logged-in user isn't following anyone, so stop!
        if ( ! $followers = trs_get_follower_ids( array( 'user_id' => trs_displayed_user_id() ) ) ) {
        }

        // show the users the logged-in user is following
       // if ( trs_has_members( 'include=' . $followers . '&max=' . $instance['max_users'] ) ) {
        //

   // ? >
 //  < ?php    echo ' < h5>' . $toHead . "'s followers".'< /h5>' ;? >
              //  < ? php while ( trs_members() ) : trs_the_member(); ? >
                     
                       //  a href="< ?php trs_member_permalink() ? >">< ?php trs_member_portrait('//type=full&width=55&height=56') ? >< /a>
                //< ?php endwhile; ?>



    <?php
      //  }
    echo '<div class="hash-title" align="left"><h5>'.$toFoot . $args1 . '</h5>' ;
    echo ls_trs_hashtags_generate_cloud_personal( $args ) ;
    echo '</div>' ;

printf( __( ' <div class="side-footer"><a href="/about">About</a> . <a href="/feedback">Feedback</a> . <a href="/terms">Terms & Privacy</a> .  &copy; trendr 2018</div>' ), get_bloginfo( 'name' ) );




}

add_action( 'trs_profile_hashtag', 'ls_trs_hashtags_cloud_personal', 1 );


    
?>
