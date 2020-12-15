<?php
/**
 * Plugin Name: Cover Photo
 * Version:1.0.5
 * Author: SeventhQueen
 * Author URI: http://seventhqueen.com
 * Plugin URI: http://seventhqueen.com
 * License: GPL 
 * 
 * Description: Allows Users to upload Cover photo to their profiles
 */

class TRSCoverPhoto {

    function __construct() {

        //setup nav
        add_action( 'trs_xprofile_setup_nav', array( $this, 'setup_nav' ) );
       add_action( 'trs_profile_knobs', array( $this, 'edit_portrait' ), 20 );
     //  add_action( 'trs_before_member_header_meta', array( $this, 'edit_cover' ), 20 );
      // add_action( 'trs_profile_knobs', array( $this, 'edit_profile' ), 20 );
      // add_action( 'trs_profile_knobs', array( $this, 'edit_settings' ), 20 );
       // add_action( 'trs_profile_knobs', array( $this, 'compose' ), 20 );
       // add_action( 'trs_before_activity_entry', array( $this, 'post' ), 140 );
      //  add_action( 'trs_before_member_header', array( $this, 'post' ), 140 );

        //inject custom css class to body
        add_filter( 'body_class', array( $this, 'get_body_class' ), 30 );

        //add css for background change
        add_action( 'trm_head', array( $this, 'inject_css' ));
        add_action( 'trm_print_scripts', array( $this, 'inject_js' ) );
        add_action( 'trm_ajax_trscp_delete_cover', array( $this, 'ajax_delete_current_cover' ) );

    }

    //inject custom class for profile pages
    function get_body_class($classes){
        if( trs_is_user() && trscp_get_image() ) {
            $classes[] = 'user-page';
        }
        return $classes;
    }

   
         function edit_cover() {

        $output = '';

        if ( trs_is_my_profile()  ) {
                $message = __(" ", 'trscp');
    

            $output .= '<div class="cover-edit">';
            $output .= '<a href="' . trs_displayed_user_domain() . 'profile/change-cover/" class="button">' . $message . '</a>';
            $output .= '</div>';
        }
  
        echo $output;

    }

    //add a sub nav to My profile for adding cover
    function setup_nav() {
        global $trs;
        $profile_link = trs_loggedin_user_domain() . $trs->profile->slug . '/';
        trs_core_new_subnav_item(
            array(
                'name' => __('Change Cover', 'trscp'),
                'slug' => 'change-cover',
                'parent_url' => $profile_link,
                'parent_slug' => $trs->profile->slug,
                'screen_function' => array($this, 'screen_change_cover'),
                'user_has_access' => (trs_is_my_profile() ),
                'position' => 40
            )
        );

    }

    //screen function
    function screen_change_cover() {
        global $trs;
        //if the form was submitted, update here
        if (!empty($_POST['trscp_save_submit'])) {
            if (!trm_verify_nonce($_POST['_key'], 'trs_upload_profile_cover')) {
                die(__('Security check failed', 'trscp'));
            }

            $current_option = $_POST['cover_pos'];
            $allowed_options = array('center', 'bottom', 'top');

            if( in_array( $current_option, $allowed_options ) ) {
                $user_id = trs_loggedin_user_id();
                if (  ! trs_is_my_profile() ) {
                    $user_id = trs_displayed_user_id();
                }

                trs_update_user_meta( $user_id, 'profile_cover_pos', $current_option );
            }

            //handle the upload
            if ($this->handle_upload()) {
                trs_core_add_message(__('Cover photo uploaded successfully!', 'trscp'));
                @setcookie( 'trs-message', false, time() - 1000, COOKIEPATH );
            }
        }

        //hook the content
        add_action('trs_template_title', array($this, 'page_title'));
        add_action('trs_template_content', array($this, 'page_content'));
        trs_core_load_template(apply_filters('trs_core_template_plugin', 'members/single/plugins'));
    }

    //Change Cover Page title
    function page_title() {
        echo __('Change Cover Photo', 'trscp');
    }

    //Upload page content
    function page_content() {
        ?>

        <form name="cover_edit" id="cover_edit" method="post" class="standard-form" enctype="multipart/form-data">


            <label for="cover_upload">
                <input type="file" name="file" id="cover_upload" class="settings-input"/>
            </label>

            <h4 style="padding-bottom:0px;margin-top: 20px;">
                <?php _e("Choose cover photo position", "trscp");?>
            </h4>

                <?php

                $selected = trscp_get_image_position();
                $cover_options = array('center' => 'Center', 'top' => 'Top', 'bottom' => "Bottom");

                foreach( $cover_options as $key => $label ):
                    ?>
                    <label class="radio">
                        <input type="radio" name="cover_pos" id="cover_pos<?php echo $key; ?>" value="<?php echo $key; ?>" <?php echo checked($key,$selected); ?>> <?php echo $label;?>
                    </label>
                <?php
                endforeach;

                ?>
            
            <br/>
            <br/>

            <?php trm_nonce_field("trs_upload_profile_cover"); ?>
            <input type="hidden" name="action" id="action" value="trs_upload_profile_cover"/>

            <p class="submit">
                <input type="submit" id="trscp_save_submit" name="trscp_save_submit" class="button" value="<?php _e('Save', 'trscp') ?>"/>
            </p>            
                    <div style="clear:both;">

            <?php
            $image_url = trscp_get_image();
            if ( ! empty( $image_url ) ): ?>
                <div id="cover_delete">
                <p><?php _e('Delete your existing cove photo.', 'trscp'); ?></p>

                    <a href='#' id='trscp-del-image' data-buid="<?php echo trs_displayed_user_id();?>" class='btn btn-default btn-xs'><?php _e('Delete', 'trscp'); ?></a>
                </div>
            <?php endif; ?>
            </div>

        </form>
    <?php
    }

    //handles upload, a modified version of trs_core_portrait_handle_upload(from trs-core/trs-core-portraits.php)
    function handle_upload() {

        //include core files
        require_once(ABSPATH . '/Backend-WeaprEcqaKejUbRq-trendr/includes/file.php');
        $max_upload_size = $this->get_max_upload_size();
        $max_upload_size = $max_upload_size * 1024;//convert kb to bytes
        $file = $_FILES;

        //I am not changing the domain of erro messages as these are same as trs, so you should have a translation for this
        $uploadErrors = array(
            0 => __('There is no error, the file uploaded with success', 'trendr'),
            1 => __('Your image was bigger than the maximum allowed file size of: ', 'trendr') . size_format($max_upload_size),
            2 => __('Your image was bigger than the maximum allowed file size of: ', 'trendr') . size_format($max_upload_size),
            3 => __('The uploaded file was only partially uploaded', 'trendr'),
            4 => __('No file was uploaded', 'trendr'),
            6 => __('Missing a temporary folder', 'trendr')
        );

        if (isset($file['error']) && $file['error']) {
            trs_core_add_message(sprintf(__('Your upload failed, please try again. Error was: %s', 'trendr'), $uploadErrors[$file['file']['error']]), 'error');
            return false;
        }

        if (!($file['file']['size'] < $max_upload_size)) {
            trs_core_add_message(sprintf(__('The file you uploaded is too big. Please upload a file under %s', 'trendr'), size_format($max_upload_size)), 'error');
            return false;
        }

        if ((!empty($file['file']['type']) && !preg_match('/(jpe?g|gif|png)$/i', $file['file']['type'])) || !preg_match('/(jpe?g|gif|png)$/i', $file['file']['name'])) {
            trs_core_add_message(__('Please upload only JPG, GIF or PNG photos.', 'trendr'), 'error');
            return false;
        }

        $uploaded_file = trm_handle_upload($file['file'], array('action' => 'trs_upload_profile_cover'));

        //if file was not uploaded correctly
        if (!empty($uploaded_file['error'])) {
            trs_core_add_message(sprintf(__('Upload Failed! Error was: %s', 'trendr'), $uploaded_file['error']), 'error');
            return false;
        }

        $user_id = trs_loggedin_user_id();
        if (  ! trs_is_my_profile() ) {
            $user_id = trs_displayed_user_id();
        }

        //assume that the file uploaded successfully
        //delete any previous uploaded image
        self::delete_cover_for_user( $user_id );

        //save in user_meta
        trs_update_user_meta( $user_id, 'profile_cover', $uploaded_file['url'] );
        trs_update_user_meta( $user_id, 'profile_cover_file_path', $uploaded_file['file'] );

        @setcookie( 'trs-message', false, time() - 1000, COOKIEPATH );
        
        do_action('trscp_cover_uploaded', $uploaded_file['url']);//allow to do some other actions when a new background is uploaded
        return true;

    }

    //get the allowed upload size
    //there is no setting on single trm, on multisite, there is a setting, we will adhere to both
    function get_max_upload_size() {
        $max_file_sizein_kb = get_site_option('fileupload_maxk');//it wil be empty for standard trendr


        if (empty($max_file_sizein_kb)) {//check for the server limit since we are on single trm

            $max_upload_size = (int)(ini_get('upload_max_filesize'));
            $max_post_size = (int)(ini_get('post_max_size'));
            $memory_limit = (int)(ini_get('memory_limit'));
            $max_file_sizein_mb = min($max_upload_size, $max_post_size, $memory_limit);
            $max_file_sizein_kb = $max_file_sizein_mb * 1024;//convert mb to kb
        }
        return apply_filters('trscp_max_upload_size', $max_file_sizein_kb);


    }

    //inject css
    function inject_css() {
          if(strpos($_SERVER['HTTP_USER_AGENT'], 'Crosswalk') !== false) {

} elseif ((strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile/') !== false) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari/') == false)&& (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') == false)) {

} else {
        $image_url = trscp_get_image();
        if (empty($image_url) || apply_filters('trscp_iwilldo_it_myself', false)) {
            return;
        }

        
        $position = trscp_get_image_position();

        ?>

        <style type="text/css">        #contour{margin:160px 114px 0px 0px;}
#contour-image .portrait{top:175px;}
#contour-bottom{margin: 30px 3px -20px -5px;}
                        #cover {
                background-image: url("<?php echo $image_url;?>");
                background-repeat: no-repeat;height:375px;border-radius: 3px 3px 0 0;
margin:39px 114px 0 0px;padding:0;background-size: cover;border-bottom:0;

                background-position: <?php echo $position;?>;


            }


        </style>
     <?php


}   
        

    }

    //inject js if I am viewing my own profile
    function inject_js() {
        if ( ( trs_is_my_profile()  ) && trs_is_profile_component() && trs_is_current_action( 'change-cover' ) ) {
            trm_enqueue_script('trscp-js', plugin_dir_url(__FILE__) . 'trscp.js', array('jquery'));
        }
    }

    //ajax delete the existing image

    function ajax_delete_current_cover() {
        //validate nonce
        if (!trm_verify_nonce($_POST['_key'], "trs_upload_profile_cover")) {
            die('what!');
        }

        $user_id = trs_loggedin_user_id();
        if ( isset( $_POST['buid'] ) && (int)$_POST['buid'] != 0 ) {
            if ( trs_loggedin_user_id() != (int)$_POST['buid'] ) {
                $user_id = (int)$_POST['buid'];
            }
        }

        self::delete_cover_for_user( $user_id );

        $message = '<p>' . __('Cover photo deleted successfully!', 'trscp') . '</p>';//feedback but we don't do anything with it yet, should we do something
        echo $message;
        exit(0);

    }

    //reuse it
    function delete_cover_for_user( $user_id = null ) {

        if ( ! $user_id ) {
            $user_id = trs_loggedin_user_id();
        }

        //delete the associated image and send a message
        $old_file_path = get_user_meta( $user_id, 'profile_cover_file_path', true );
        if ($old_file_path) {
            @unlink( $old_file_path );//remove old files with each new upload
        }
        trs_delete_user_meta( $user_id, 'profile_cover_file_path' );
        trs_delete_user_meta( $user_id, 'profile_cover' );
    }
}


/**
 *
 * @param integer $user_id
 * @return string url of the image associated with current user or false
 */

function trscp_get_image( $user_id = false ){
    global $trs;
    if(!$user_id)
            $user_id = trs_displayed_user_id();
    
     if( empty( $user_id ) )
         return false;
     $image_url = trs_get_user_meta( $user_id, 'profile_cover', true );
     return apply_filters( 'trscp_get_image', $image_url, $user_id );
}
function trscp_get_image_position( $user_id = false ){
    global $trs;
    if( !$user_id )
            $user_id = trs_displayed_user_id();
    
     if( empty( $user_id ) )
         return false;
     

    $current_position = trs_get_user_meta( $user_id, 'profile_cover_pos', true);

    if( ! $current_position ) {
        $current_position = 'center';
    }

    return $current_position;
}


add_action( 'trs_include', 'sq_trs_cover_photo_init' );
function sq_trs_cover_photo_init()
{
    if ( function_exists( 'trs_is_active' ) ) {
        $trs_cover_photo = new TRSCoverPhoto();
    }
}

//load textdomain
add_action( 'plugins_loaded', 'kleo_trscp_load_textdomain' );
function kleo_trscp_load_textdomain() {
    load_plugin_textdomain( 'trscp', false, dirname(plugin_basename(__FILE__)) . "/languages/" );
}
