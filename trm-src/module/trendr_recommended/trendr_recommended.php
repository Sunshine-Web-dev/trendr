<?php
/**
 * Plugin Name:  WordPress ReCommended For trendr
 * Plugin URI:   https://trmbrigade.com/wordpress/module/related-posts/?utm_source=related-posts-lite&utm_medium=plugin-uri&utm_campaign=pro-upgrade-rp
 * Description:  Showing related posts thumbnails under the posts.
 * Version:      1.6.5
 * Author:       TRMBrigade
 * Author URI:   https://TRMBrigade.com/?utm_source=related-posts-lite&utm_medium=author-link&utm_campaign=pro-upgrade-rp
 */

/*
Copyright 2010 to 2018

This product was first developed by Maria I Shaldybina and later on maintained and developed by Adnan (TRMBrigade.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/
class  trendrRecommended {
    function __construct() {

        $this->constant();
        // initialization
        add_action( 'admin_enqueue_scripts', array(
             $this,
            'admin_scripts'
        ) );

        // Compatibility for old default image path.

        add_action( 'admin_menu', array(
             $this,
            'admin_menu'
        ) );


        add_action( 'trm_enqueue_scripts', array(
             $this,
            'front_scripts'
        ) );

        add_action('trm_footer',array($this,'frontend_scripts_page'));
        add_action('trs_activity_type_tabs',array($this,'front_page_tab'));
        //add_action('trs_screens',array($this,'front_page'));
        //add_action('trs_activity_type_tabs',array($this,'front_page_tab'));
        add_filter('action_distance',array($this,'action_distance'));
        add_action('trm_ajax_get_recommended',array($this,'get_recommended'));
        //add_action('admin_bar',array($this,'admin_bar'));
        add_filter('recommended_activities',array($this,'recommended_activities'));
        add_action('trm_ajax_clear_cache',array($this,'clear_cache'));
       // add_action('admin_bar_menu',array($this,'admin_bar'),300);
    }

    function clear_cache()
    {
        update_option('new','new');
        trm_die();
    }

    function admin_bar()
    {
        global $trm_admin_bar;
        $trm_admin_bar->add_menu(
            array(
                'id'=>'clear_cache',
                'title'=>'clear cache',
                'href'=>'#'
            )
        );
    }



    function frontend_scripts_page()
    {

    }

    function front_page_tab()
    {
        require plugin_dir_path(__FILE__) .'inc/rpt_tab.php';
    }
    function get_recommended()
    {
        global $activities_template;

        $number = get_option('recommended_number');
        $priority = get_option('priority_values');
        $priority_value_for_edit = json_decode('"' . $priority . '"');
        $priority_value_edit = json_decode($priority_value_for_edit,true);
        $sort = get_option('recommended_sort');

        $data_recommended = $this->recommended($number,$priority_value_edit,$sort);
  // var_dump($data_recommended);

        $activities_template['total_count'] = count($data_recommended);
        $activities_template['current_index'] = $_POST['page'];
        $activities_template['activity'] = array();
        $activity = $data_recommended;


        $length = min($_POST['page'] + 20,count($data_recommended));
        for($index = $_POST['page']+1;$index < $_POST['page'] + 20;$index ++)
        {
            array_push($activities_template['activity'],$data_recommended[$index]);
        }

        // $length = min($activities_template['current_index']+20,count($activity));
        // for($index = $activities_template['current_index'] + 1;$index<$length;$index++)
        // {
        //     array_push($activities_template['activity'],$activity[$index]);
        // }
trm_die();
        ob_start();
        require plugin_dir_path(__FILE__) .'inc/rpt-deactivate-form.php';
        $result = array('content'=>ob_get_contents(),'page'=>$_POST['page'] + 19,'total_page'=>count($data_recommended));
        ob_end_clean();
        echo json_encode($result);
        trm_die();
    }


    function admin_scripts( $page ) {

        if ( 'toplevel_page_related-posts-thumbnails' === $page ) {
            //trm_enqueue_style( 'rpt_admin_css', plugins_url( 'assets/css/admin.css', __FILE__ ), false, RELATED_POSTS_THUMBNAILS_VERSION );
         //   trm_enqueue_style( 'jquery-ui', 'http://code.jquery.com/ui/1.11.2/858483/smoothness/jquery-ui.css' );
          //  trm_enqueue_style( 'trm-color-picker' );
            trm_enqueue_script( 'rpt_admin_js', plugins_url( 'assets/js/admin.js', __FILE__ ), RELATED_POSTS_THUMBNAILS_VERSION );
        }

    }

    function trs_recommend_add( $qs, $object) {
        if(!trs_is_user())
        {
            return $qs;
        }

        switch(trs_current_action())
        {
            case 'recommend':

        }
    }

    function action_distance($pointarray)
    {
        $radius = 6371;
        $w = deg2rad($pointarray['point']['lat']);$w1 = deg2rad($pointarray['point']['lng']);
        $w2 = deg2rad($pointarray['point1']['lat']);$w3 = deg2rad($pointarray['point1']['lng']);
        $deltav1 = $w2 - $w; $deltav2 = $w3 - $w1;
        $a = sin($deltav1/2) * sin($deltav1 / 2) + cos($w) * cos($w2) * sin($deltav2/2) * sin($deltav2/2);
        $c = 2 * atan2(sqrt($a),sqrt(1-$a));
        $d = $radius * $c;

        return $d;
    }

   // function front_scripts() {
      //   echo '<script>ajax_url = "'.admin_url('admin-ajax.php') . '"</script>';
     //   trm_enqueue_script('frontend_script',plugins_url('assets/js/frontend.js',__FILE__));
       // trm_enqueue_style( 'rpt_front_style', plugins_url( 'assets/css/front.css', __FILE__ ), false, RELATED_POSTS_THUMBNAILS_VERSION );
   // }

    function front_page()
    {
        trs_core_new_nav_item( array(
            'name'                =>  __( 'Recommended', 'trs-recommend' ),
            'slug'                => 'recommended',
            'position'            => $this->params['adminbar_myaccount_order'],
            'screen_function'     => 'trs_recommend_screen',
            'default_subnav_slug' => 'recommend',
            'item_css_id'         => 'recommend-following'
        ) );

         if ( trs_is_active( 'activity' ) && apply_filters( 'trs_follow_show_activity_subnav', true ) ) {

            trs_core_new_subnav_item( array(
                'name'            => _x( 'Recommend', 'Activity subnav tab', 'trs-recommend' ),
                'slug'            => 'recommend',
                'parent_url'      => trailingslashit( $domain . trs_get_activity_slug() ),
                'parent_slug'     => trs_get_activity_slug(),
                'screen_function' => 'trs_recommend_screen_activity',
                'position'        => 25,
                'item_css_id'     => 'activity-recommend'
            ) );
        }
            // $title = get_option('recommended_top_text');
            // $number = get_option('recommended_number');
            // $priority = get_option('priority_values');
            // $priority_value_for_edit = json_decode('"' . $priority . '"');
            // $priority_value_edit = json_decode($priority_value_for_edit,true);
            // $sort = get_option('recommended_sort');
            // $data_recommended = $this->recommended($number,$priority_value_edit,$sort);

            // $activities_template['total_count'] = count($data_recommended);
            // $activities_template['current_index'] = -1;
            // $activities_template['activity'] = array();

            // $activity = $data_recommended;

            // $length = min(count($activity),20);
            // for($index = 0;$index < $length;$index ++)
            // {
            //     array_push($activities_template['activity'],$activity[$index]);
            // }
            // require plugin_dir_path(__FILE__).'inc/rpt-widget.php';
            // die;
    }

    function recommended_activities($pagearray)
    {
        global $activity,$activities_template;

        $id = $pagearray['current_index'];$page = $pagearray['page'];
        if($id < $page - 2)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function recommended($number,$priority,$sort)
    {
        global $trmdb;
        $data = array();
        $data_priority = array();

        $cachetime = get_option('cachetime')*3600 + get_option('cacheminutes')*60 + get_option('cachesec');
        $currenttime = time();
        $initdata = array();
        // var_dump($priority);
        foreach($priority as $key => $value)
        {
            array_push($data_priority,array($key => $value));
        }

        //sort the data stream
        for($index = 1;$index < count($data_priority);$index ++)
        {
            for($index_compare = 0;$index_compare < $index; $index_compare ++)
            {
                $priority = 0;$priority_compare = 0;
                foreach($data_priority[$index] as $datas)
                {
                    if(is_array($datas))
                    {
                        $priority = $datas['priority'];
                    }
                    else
                    {
                        $priority = $datas;
                    }
                }

                foreach($data_priority[$index_compare] as $datas)
                {
                     if(is_array($datas))
                    {
                        $priority_compare = $datas['priority'];
                    }
                    else
                    {
                        $priority_compare = $datas;
                    }
                }

                if($priority_compare > $priority)
                {
                    $data_compared = $data_priority[$index];
                    $data_priority[$index] = $data_priority[$index_compare];
                    $data_priority[$index_compare] = $data_compared;
                }
            }
        }

         $data_filter = array();
        $data_filtered = array();

        if($currenttime - get_option('cache_new_time') > $cachetime || get_option('new') == 'new')
        {
            //filter the datas with the priority

            $type = get_option('activity_type');
            if(!is_array($type))
            {
              $type_for_edit = json_decode('"' . $type . '"');
              $type = json_decode($type_for_edit,true);
            }

            $where_type = '';
              $strPrefix = trs_core_get_table_prefix();
            if($type)
            {
                foreach($type as $key => $types)
                {
                    $type[$key] = '"' . $types . '"';
                }
                $where_type = 'and '.$strPrefix.'trs_activity.type in (' . join(',',$type) . ')';
                $where_type_string = 'where type in (' . join(',',$type) .')';
            }

            //'.$strPrefix.'trs_activity_meta.meta_key = "liked_count"
            $q = 'Select '.$strPrefix.'trs_activity.*,'.$strPrefix.'trs_activity_meta.meta_value from '.$strPrefix.'trs_activity_meta Inner Join '.$strPrefix.'trs_activity on '.$strPrefix.'trs_activity.id = '.$strPrefix.'trs_activity_meta.activity_id where '.$strPrefix.'trs_activity_meta.meta_key = "liked_count" ' . $where_type;
            // echo $q;
            $initdata_before = $trmdb->get_results($q,'ARRAY_A');

            $initdata_before_datas = array($initdata_before[0]['id']);
            for($index =1;$index<count($initdata_before);$index++)
            {
                $data_init_data = unserialize($initdata_before[$index]);
                array_push($initdata_before_datas,$initdata_before[$index]['id']);

                for($index_compare=0;$index_compare<$index-1;$index_compare++)
                {
                    $data_init_datas = unserialize($initdata_before[$index_compare]);
                    if(array_keys($data_init_data[$index])[0]>array_keys($data_init_data[$index_compare])[0])
                    {
                        $data_init_data_swap = $initdata_before[$index];
                        $initdata_before[$index] = $initdata_before[$index_compare];
                        $initdata_before[$index_compare] = $data_init_data_swap;
                    }
                }
            }

            array_slice($initdata_before,0,min(count($initdata_before),$number));
            // echo "initdata_before";
// var_dump($initdata_before);

// echo 'Select * from trm_trs_activity ' . $where_type_string . ' and  id not in (' . join(',',$initdata_before_datas) . ') order by date_recorded desc limit ' . ($number-count($initdata_before));
            $initdata = $trmdb->get_results('Select * from trm_trs_activity ' . $where_type_string . ' and  id not in (' . join(',',$initdata_before_datas) . ') order by date_recorded desc limit ' . ($number-count($initdata_before)),'ARRAY_A');


            $initdata = array_merge($initdata,$initdata_before);
            set_transient('initdata',$initdata,1000);
            update_option('cache_new_time',time());update_option('new','old');
        }
        else
        {
           $initdata = get_transient('initdata');
        }
// var_dump($initdata);
// var_dump($data_priority);
        foreach($data_priority as $data_priority_values)
        {
            foreach($data_priority_values as $key => $value)
            {
                $data_filtered = $this->filter_activity($key,$value,$initdata);
                $data_filter = array_merge($data_filter,$data_filtered);
                // echo "data_filter k ".$key." v: ";
                // var_dump($value);

                if(count($data_filter) > $number)
                {
                    $data_filter = array_slice($data_filter,0,$number);
                }
                // var_dump($data_filter);
            }
        }
// var_dump($data_filter);
        return $data_filter;

    }

    function filter_activity($key,$priority_value,$initdata)
    {
        global $trmdb;
        $id = get_current_user_id();
        $data_key = array();
        switch($key)
        {
            case 'favourite':

                $user_fabourite = get_user_meta($id,'trs_favorite_activities')[0];
                $data_key_array = array();

               $hashnum = $priority_value['hashtag'];
               // var_dump($user_fabourite);
                $data_tag = array();
                $out = array();
                foreach($initdata as $key => $initdatas)
                {
                    if(in_array($initdatas['id'],$user_fabourite))
                    {
                      // var_dump($initdatas);
                        preg_match_all("|<[^>]+>#(.*)</[^>]+>|U",$initdatas['content'],$out);
                        // echo "out";
                        // var_dump($out);
                        if($out[1][0] && !$data_tag[$out[1][0]])
                        {
                            $data_tag[$out[1][0]] = array();
                        }
                        foreach($out[1] as $outs)
                        {
                            array_push($data_tag[$outs],$initdatas);
                        }
                    }
                }
                // var_dump($data_tag);
                $data_tag_count = array();

                foreach($data_tag as $key => $value)
                {
                    $data_tag_count[$key] = count($value);
                }
                ksort($data_tag_count);
                $index = 0;
                $tag_array = array();
                foreach ($data_tag_count as $key => $value) {
                    array_push($tag_array,$key);
                    $index++;
                    if($index >= $hashnum)
                    {
                        break;
                    }
                }

               foreach($initdata as $initdatas)
               {
                    foreach($tag_array as $tag_arrays)
                    {
                        $out = array();
                        if($priority_value['search_hash'] == 'hash to content')
                        {
                            if(preg_match_all("|(" . strtolower($tag_arrays) . ")|",strtolower($initdatas['content']),$out))
                            {
                                if(count($out) > 0)
                                {
                                    array_push($data_key,$initdatas);
                                }
                            }
                        }
                        else
                        {
                            preg_match_all("|<[^>]+>#(.*)</[^>]+>|U",$initdatas['content'],$out);

                            if(in_array($tag_arrays,$out[1]))
                            {
                                array_push($data_key,$initdatas);
                            }
                        }
                    }

               }

                break;
            case 'liked':
                $user_liked = get_user_meta($id,'trs_liked_activities')[0];
                $data_key_array = array();
                foreach($user_liked as $key => $value)
                {
                    array_push($data_key_array,$key);
                }

                $hashnum = $priority_value['hashtag'];
                $data_tag = array();
                $out = array();
                foreach($initdata as $key => $initdatas)
                {
                    if(in_array($initdatas['id'],$data_key_array))
                    {
                        preg_match_all("|<[^>]+>#(.*)</[^>]+>|U",$initdatas['content'],$out);

                        if($out[1][0] && !$data_tag[$out[1][0]])
                        {
                            $data_tag[$out[1][0]] = array();
                        }
                        if($out[1][0])
                        {
                            array_push($data_tag[$out[1][0]],$initdatas);
                        }
                    }
                }

                $data_tag_count = array();
                foreach($data_tag as $key => $value)
                {
                    $data_tag_count[$key] = count($value);
                }

                ksort($data_tag_count);

                $index = 0;
                $tag_array = array();
                foreach ($data_tag_count as $key => $value) {
                    array_push($tag_array,$key);
                    $index++;
                    if($index >= $hashnum)
                    {
                        break;
                    }
                }
                foreach($initdata as $initdatas)
               {
                    foreach($tag_array as $tag_arrays)
                    {
                        $out = array();

                        if($priority_value['search_hash'] == 'hash to content')
                        {
                            if(preg_match_all("|(" . strtolower($tag_arrays) . ")|",strtolower($initdatas['content']),$out))
                            {
                                if(count($out) > 0)
                                {
                                    array_push($data_key,$initdatas);
                                }
                            }
                        }
                        else
                        {
                            preg_match_all("|<[^>]+>#(.*)</[^>]+>|U",$initdatas['content'],$out);
                            if(in_array($tag_arrays,$out))
                            {
                                array_push($data_key,$initdatas);
                            }
                        }
                    }

               }
                break;
            case 'checkin':
                $user_checkin = array('lat'=>get_user_meta($id,'trsci_latest_lat')[0],'lng'=>get_user_meta($id,'trsci_latest_lng')[0]);
                if($user_checkin['lat'])
                {
                    $id_array = array();
                    foreach($initdata as $initdatas)
                    {
                        array_push($id_array,$initdatas['id']);
                    }
                    $data_checkin_ready_data = $trmdb->get_results('Select * from trm_trs_activity_meta where (meta_key = "trsci_activity_lat" || meta_key="trsci_activity_lng") and activity_id in (' . join(',',$id_array) . ')','ARRAY_A');
                    $data_checkin = array();
                    foreach($data_checkin_ready_data as $key => $value)
                    {
                        $field = '';
                        if($value['meta_key'] == 'trsci_activity_lng')
                        {
                            $field = 'lng';
                        }
                        else if($value['meta_key'] == 'trsci_activity_lat')
                        {
                            $field = 'lat';
                        }

                        if($field)
                        {
                            $data_checkin[$value['activity_id']][$field] = $value['meta_value'];
                        }

                    }

                    foreach($data_checkin as $key => $value)
                    {
                        $distance = apply_filters('action_distance',array('point'=>$user_checkin,'point1'=>$value));
                        if($distance < $priority_value['radius'])
                        {
                            array_push($data_key,$key);
                        }
                    }
                }
                break;
            case 'fllower':
                $id_results = $trmdb->get_results('Select leader_id as user_id from trm_trs_follow where follow_id = ' . $id ,'ARRAY_A');
                $data_user_id = array();
                foreach($id_results as $id_result)
                {
                    array_push($data_user_id,$id_result['user_id']);
                }

                foreach($initdata as $initdatas)
                {
                    if(in_array($initdatas['user_id'],$data_user_id))
                    {
                        array_push($data_key,$initdatas);
                    }
                }
                break;
            case 'friend':
            // var_dump("--------");
                $id_results = $trmdb->get_results('Select friend_user_id as id from trm_trs_friends where initiator_user_id = ' . $id,'ARRAY_A');
                $id_results_other = $trmdb->get_results('Select initiator_user_id as id from trm_trs_friends where friend_user_id = '. $id,'ARRAY_A');


                $id_result_array = array_merge($id_results,$id_results_other);
                $id_result_data = array();
                foreach($id_result_array as $id_result_arrays)
                {
                    array_push($id_result_data,$id_result_arrays['id']);
                }

                foreach($initdata as $initdatas)
                {
                    if(in_array($initdatas['user_id'],$id_result_data))
                    {

                        array_push($data_key,$initdatas);
                    }
                }
                break;
            case 'group':
                $group_users = $trmdb->get_results('Select user_id from trm_trs_groups_members where group_id in (Select group_id from trm_trs_groups_members where user_id = ' . $id .')','ARRAY_A');
                $group_user_ids = array();

                foreach($group_users as $group_user)
                {
                    array_push($group_user_ids,$group_user['user_id']);
                }

                foreach($initdata as $initdatas)
                {
                    if(in_array($initdatas['user_id'],$group_user_ids))
                    {
                        array_push($data_key,$initdatas);
                    }
                }
                break;
        }
        // var_dump($data_key);
        return $data_key;
    }

    function constant() {

        define( 'RELATED_POSTS_THUMBNAILS_VERSION', '1.6.2' );
        define( 'RELATED_POSTS_THUMBNAILS_FEEDBACK_SERVER', 'https://trmbrigade.com/' );
        define( 'RELATED_POSTS_THUMBNAILS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

    }

      function display_categories_list( $categoriesall, $categories, $selected_categories, $all_name, $specific_name ) {
      ?>
       <input id="<?php
        echo $all_name;
        ?>" class="select_all" type="checkbox" name="<?php
        echo $all_name;
        ?>" value="1" <?php
        if ( $categoriesall == '1' ) {
            echo 'checked="checked"';
        }
        ?>/>
        <label for="<?php
        echo $all_name;
        ?>"><?php
        _e( 'All', 'related-posts-thumbnails' );
        ?></label>

        <div class="select_specific" <?php
        if ( $categoriesall == '1' ):
          ?> style="display:none" <?php
        endif;
        ?>>
            <?php
        foreach ( $categories as $category ):
          ?>
           <input type="checkbox" name="<?php
            echo $specific_name;
            ?>[]" id="<?php
            echo $specific_name;
            ?>_<?php
            echo $category->category_nicename;
            ?>" value="<?php
            echo $category->cat_ID;
            ?>" <?php
            if ( in_array( $category->cat_ID, (array) $selected_categories ) ) {
                echo 'checked="checked"';
            }
            ?>/>
            <label for="<?php
            echo $specific_name;
            ?>_<?php
            echo $category->category_nicename;
            ?>"><?php
            echo $category->cat_name;
            ?></label><br />
            <?php
        endforeach;
        ?>
       </div>

    <?php
    }


    function admin_menu() {

        $page = add_menu_page( __( 'Related Posts Thumbnails', 'related-posts-thumbnails' ), __( 'TRS_Recommend', 'related-posts-thumbnails' ), 'administrator', 'related-posts-thumbnails', array(
             $this,
            'admin_interface'
        ), 'dashicons-screenoptions' );

    }

    function admin_interface() {

        include_once RELATED_POSTS_THUMBNAILS_PLUGIN_DIR . '/inc/rpt-settings.php';
    }

}

 function trs_recommend_load($has_activaties)
{
  return true;
    $buddy = new trendrRecommended();
    global $trs,$activities_template;
    $scope = $trs->current_action;
    if($scope == 'recommend')
    {
        $number = get_option('recommended_number');
        $priority = get_option('priority_values');
        $priority_value_for_edit = json_decode('"' . $priority . '"');
        $priority_value_edit = json_decode($priority_value_for_edit,true);
        $sort = get_option('recommended_sort');
        echo "1";
        $data_recommended = $buddy->recommended($number,$priority_value_edit,$sort);

        $activities_template->total_activity_count = count($data_recommended);
        $activities_template->activities = array();
        $activity = $data_recommended;
        var_dump($activities_template);
        if(!$activities_template->current_index)
        $activities_template->current_index =-1;
        $length_index = $activities_template->current_index + 1;


        $length_last = min($activities_template->current_index + 21,$activities_template->total_activity_count);

        for($index = $length_index;$index < $length_last;$index ++)
        {
            array_push($activities_template->activities,$data_recommended[$index]);
        }
        $activities_template->activity_count = count($activities_template->activities);
        var_dump($activities_template->activities);
    }

    if($scope == 'recommend')
    {
        if($activities_template->total_activity_count > 0)
        {
            return true;
        }
    }
    else
    {
        return $has_activaties;
    }


}

add_filter('trs_has_activities','trs_recommend_load',20,2);
add_action( 'init', 'related_posts_thumbnails' );
add_filter('trs_ajax_querystring','trs_ajax_myquerystring',20,2);

function trs_ajax_myquerystring($qs,$object)
{
    global $trs;
    if($object == 'activity')
    {
        $trs->current_action = preg_split('[scope=]',$qs)[1];
    }

    return $qs;
}



function related_posts_thumbnails() {

    global $related_posts_thumbnails;
    $related_posts_thumbnails = new trendrRecommended();

}



?>
