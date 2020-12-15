<?php
/*
Plugin Name: TRS Friends Suggestions Widget
Plugin URI: http://buddydev.com/plugins/trendr-friends-suggest/
Description: BuddyPress Friends Suggestion Widget  - displays friend suggestions for logged in users.
Version: 1.0.2
Author: gwu
Author URI: http://buddydev.com/members/gwu123/
 Last Updated: September 8, 2012
*/



//add_action("trm_print_scripts","trs_friend_suggest_add_js");
//function trs_friend_suggest_add_js(){
   // if(!is_user_logged_in())
       // return;
   //$fsuggest_url=plugin_dir_url(__FILE__);//with a trailing slash
   // trm_enqueue_script("friend-suggest-js",$fsuggest_url."friend-suggest.js",array("jquery"));
//}

//load text domain
function friend_suggest_load_textdomain() {
        $locale = apply_filters( 'friend_suggest_load_textdomain_get_locale', get_locale() );
	// if load .mo file
	if ( !empty( $locale ) ) {
		$mofile_default = sprintf( '%slanguages/%s.mo', plugin_dir_path(__FILE__), $locale );
		$mofile = apply_filters( 'friend_suggest_load_textdomain_mofile', $mofile_default );

                if ( file_exists( $mofile ) ) 
                    // make sure file exists, and load it
			load_textdomain( "trs-show-friends", $mofile );
                      
		
	}
}
add_action ( 'trs_init', 'friend_suggest_load_textdomain', 2 );



//action takes place here
function  trs_show_friend_suggestions_list($limit=5){
	global $trs;
	$user_id = $trs->loggedin_user->id;
        $my_friends=(array)friends_get_friend_user_ids($user_id);//get all friend ids

        $my_friend_req=(array)friend_suggest_get_friendship_requested_user_ids($user_id);//get all friend request by me
       
        $possible_friends=array();//we will store the possible friend ids here
        foreach($my_friends as $friend_id)
                $possible_friends=array_merge($possible_friends,(array)friends_get_friend_user_ids($friend_id));

        //we have the list of friends of friends, we will just remove
        //now get only udifferent friend ids(unique)
        $possible_friends=array_unique($possible_friends);

        //intersect my friends with this array
        $my_friends[]=$trs->loggedin_user->id;//include me to
        $excluded_users=get_user_meta($user_id,"hidden_friend_suggestions",true);
        $excluded_users=$excluded_users;
        $excluded_users=array_merge($my_friends,(array)$excluded_users,(array)$my_friend_req);

        //we may check the preference of the user regarding , like not add
        
        $possible_friends=array_diff($possible_friends,$excluded_users);//get those user who are not my friend and also exclude me too
        if(!empty($possible_friends)){
           shuffle($possible_friends);//randomize
           $possible_friends=array_slice($possible_friends, 0,$limit=5);
        }
         
         
        if(!empty($possible_friends)):?>
                       <ul id="members-list" class="item-list suggested-friend-item-list">
                        <?php 	foreach ($possible_friends as $possible_friend):?>
                            <li>
                               <?php $member_link= trs_core_get_user_domain($possible_friend);
                                     $member_name=  trs_core_get_user_displayname($possible_friend);

                                ?>
                                <div class="item-portrait">
                                        <a href="<?php echo $member_link;?>"><?php echo trs_core_fetch_portrait(array('type'=>'full','width'=>25,'height'=>25,'item_id'=>$possible_friend)); ?></a>
                                </div>

                                    <div class="item">
                                            <div class="item-title">
                                                    <a href="<?php echo $member_link; ?>"><?php echo $member_name; ?></a>
                                             </div>
                                    </div>
                                     <div class="action">
                                            <?php   trs_friend_suggest_hide_link($possible_friend); ?>
                                            <?php trs_add_friend_button( $possible_friend ); ?>
                                    </div>
                                    <div class="clear"></div>
            
                            </li>
			
                        <?php endforeach;?>
                              </ul>
                     <?php else:?>
                      <div id="friend-message" class="info">
                        <p><?php _e( "", 'trendr' ) ?></p>
                    </div>

                            <?php endif;?>
                    
   <?php
}
add_action( 'trs_before_directory_members_page' , 'trs_show_friend_suggestions_list' ) ;



function trs_friend_suggest_hide_link($possible_friend){
    $url=trs_get_root_domain()."/remove-friend-suggestion/?suggest_id=".$possible_friend."&_keys=".trm_create_nonce('friend-suggestion-remove-'.$possible_friend);
?>
<span class="remove-friend-suggestion"><a href="<?php echo $url;?>" title="Hide this suggestion">x</a></span>
<?php
}



function friend_suggest_get_friendship_requested_user_ids( $user_id ) {
		global $trmdb, $trs;

		return $trmdb->get_col( $trmdb->prepare( "SELECT friend_user_id FROM {$trs->friends->table_name} WHERE initiator_user_id = %d AND is_confirmed = 0", $user_id ) );
	}

  //fix trs bug of not showing the add friend button on profile pages

   add_filter('trs_get_add_friend_button', "friend_suggest_fix_trs_button_bug");

   function friend_suggest_fix_trs_button_bug($button){
       if($button['id']=='not_friends')
           $button['block_self']=false;
       return $button;
   }
?>