<?php
/**
 * PLugin Name: TRS Groups Suggest Widget
 * Author: Brajesh Singh 
 * Plugin URI: http://buddydev.com/plugins/trs-groups-suggest/
 * Author URI: http://buddydev.com
 * Version: 1.0.3
 * License:GPL
 * Last Updated: December 15, 2012
 * Description: Simple Group suggestion widget based on friends Groups 
 * Special thanks to @GWU for the idea of this widget
 */
//a helper class for group suggestion
//always implement a singleton pattern, there are numerous benefits for the helper class
class TRSDevTRSGroupSuggest{
    
    static $instance;
    
    private function __construct(){
     
        //load script
   //     add_action('trm_print_scripts',array(&$this,'load_js'));
        //ajax handling of hiding the suggestion
        add_action('trm_ajax_group_suggest_remove_suggestion',array(&$this,'hide_suggestion'));
        add_action( 'trs_before_directory_members_page' ,array(&$this,'suggestions_list'), 2 ) ;
        //load text domain
        add_action ( 'trs_loaded', array(&$this,'load_textdomain'), 3);

}
//public method for getting the instance/initializing
    function get_instance(){
        
        if(!isset (self::$instance))
                self::$instance=new self();
        return self::$instance;
    }
   

  
   
   //ajax helper for hiding,it keeps the hidden group in usermeta 
   function hide_suggestion(){
       
        $suggestion_id=$_POST['suggestion_id']  ;
        check_ajax_referer('group-suggestion-remove-'.$suggestion_id);

         if(empty ($suggestion_id)||!is_user_logged_in())
             return;
         
        global $trs;
        $user_id=trs_loggedin_user_id();
        $excluded=get_user_meta($user_id,"hidden_group_suggestions" ,true);
        $excluded=(array)($excluded);
        $excluded[]=$suggestion_id;
        
        update_user_meta($user_id,"hidden_group_suggestions",$excluded);
        
        exit(0);
   }
  //get the hidden group ids as an array, yep u read it right
   function get_hidden($user_id=null){
       if(!$user_id)
           $user_id=trs_loggedin_user_id ();
       return get_user_meta($user_id,"hidden_group_suggestions" ,true);
   }
   //show the list here
   function suggestions_list($limit,$user_id=null){
       global $trs,$trmdb;
       
       if(!$user_id)
  $user_id = trs_loggedin_user_id();
       
       //who are the friends of current user, let us have their ids as array
        $my_friends=(array)friends_get_friend_user_ids($user_id);//get all friend ids
        
        //find friend's groups of the user
        $friends_list="(".join(",",$my_friends).")";
       
        $friends_groups_sql=  "SELECT DISTINCT group_id FROM  {$trs->groups->table_name} g, {$trs->groups->table_name_members} m WHERE g.id=m.group_id AND g.status='public' AND m.user_id in {$friends_list} AND is_confirmed= 1"; 
        $friends_groups=$trmdb->get_col($friends_groups_sql);
        
       
        
        
        //get all current user  groups, includes pending,already a member, banned ,kicked etc
        $my_all_groups_sql=$trmdb->prepare( "SELECT DISTINCT group_id FROM {$trs->groups->table_name_members}  WHERE user_id = %d  ",$user_id);
        $my_groups=$trmdb->get_col($my_all_groups_sql);
        
        //groups the user has hidden
        $my_excluded=(array)self::get_hidden($user_id);
       
        //make an array of users group+groups hidden by user
        $excluded=array_merge($my_groups,$my_excluded);
        $excluded=array_unique($excluded);
       //so here we get the possible groups?
        $possible_groups=array_diff($friends_groups,$excluded);//we will store the possible group ids here
       
        
        if(!empty($possible_groups)){
           shuffle($possible_groups);//randomize
           $possible_groups=array_slice($possible_groups, 0,$limit=5);
        }
         
       //how to minim ize queries here by getting all the details in one query ?
        //
        //if we find and output individual group trough stanndard way,m that will be too many quesries, let us find it in a single query
        $list="(".join(',',$possible_groups).")";
        $query="SELECT * FROM {$trs->groups->table_name} WHERE id IN {$list}";
        
        $groups=$trmdb->get_results($query);
        shuffle($groups);//shuffle it
        if(!empty($groups)):?>
                       <ul id="groups-list" class="item-list suggested-group-item-list">
                        <?php   foreach ($groups as $group):?>
                            <li>
                               <?php $group_link= trs_get_group_permalink($group);
                                     $group_name=  $group->name;
                                     $group->is_member=false;

                                ?>
                                <div class="item-portrait">
                                        <a href="<?php echo $group_link;?>"><?php echo trs_core_fetch_portrait(array('type'=>'full','width'=>25,'height'=>25,'object'=>'group','item_id'=>$group->id)); ?></a>
                                </div>

                                    <div class="item">
                                            <div class="item-title">
                                                    <a href="<?php echo $group_link; ?>"><?php echo $group_name; ?></a>
                                             </div>
                                    </div>
                                     <div class="action">
                                            <?php   self::get_hide_suggestion_link($group->id); ?>
                                         
                                            <?php echo trs_get_group_join_button( $group); ?>
                                    </div>
                                    <div class="clear"></div>
            
                            </li>
      
                        <?php endforeach;?>
                              </ul>
                     <?php else:?>
                      <div id="message" class="info">
                        <p><?php _e( "", 'trendr' ) ?></p>
                    </div>

                            <?php endif;?>
                    
   <?php 
   }
   //ui
   //get the link for hide (x) button
  function get_hide_suggestion_link($possible_group_id){
    $url=trs_get_root_domain()."/remove-group-suggestion/?suggest_id=".$possible_group_id."&_keys=".trm_create_nonce('group-suggestion-remove-'.$possible_group_id);
?>
    <span class="remove-group-suggestion"><a href="<?php echo $url;?>" title="<?php __('Hide this suggestion','trs-group-suggest');?>">x</a></span>
<?php
}

 //load javascript if user is logged in and is viewing the site
 // function load_js(){
    // if(!is_user_logged_in()||is_admin())
     //   return;
  // $gsuggest_url=plugin_dir_url(__FILE__);//with a trailing slash
   // trm_enqueue_script("group-suggest-js",$gsuggest_url."group-suggest.js",array("jquery"));
  // } 
   
//load text domain
   //localization
    function load_textdomain(){

        $locale = apply_filters( 'group_suggest_load_textdomain_get_locale', get_locale() );
        
      
  // if load .mo file
  if ( !empty( $locale ) ) {
    $mofile_default = sprintf( '%slanguages/%s.mo', plugin_dir_path(__FILE__), $locale );
              
    $mofile = apply_filters( 'group_suggest_load_textdomain_mofile', $mofile_default );
    
                if ( file_exists( $mofile ) ) {
                    // make sure file exists, and load it
      load_textdomain( 'trs-group-suggest', $mofile );
    }
  }
}

   
}//end of helper class



 
 //initialize
    TRSDevTRSGroupSuggest::get_instance();
?>