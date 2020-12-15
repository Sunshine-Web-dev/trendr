<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * trs_like_add_admin_page_menu()
 *
 * Adds "trendr Like" to the main trendr admin menu.
 *
 */
function trs_like_add_admin_page_menu() {
    add_submenu_page(
            'options-general.php' , 'trendr Like' , 'trendr Like' , 'manage_options' , 'trs-like-settings' , 'trs_like_admin_page'
    );
}

add_action( 'admin_menu' , 'trs_like_add_admin_page_menu' );

/**
 * trs_like_admin_page_verify_nonce()
 *
 * When the settings form  is submitted, verifies the nonce to ensure security.
 *
 */
function trs_like_admin_page_verify_nonce() {
    if ( isset( $_POST['_key'] ) && isset( $_POST['trs_like_updated'] ) ) {
        $nonce = $_REQUEST['_key'];
        if ( !trm_verify_nonce( $nonce , 'trs-like-admin' ) ) {
            trm_die( __( 'You do not have permission to do that.' ) );
        }
    }
}

add_action( 'init' , 'trs_like_admin_page_verify_nonce' );

/**
 * trs_like_admin_page()
 *
 * Outputs the admin settings page.
 *
 */
function trs_like_admin_page() {
    global $current_user;

    trm_get_current_user();

    /* Update our options if the form has been submitted */
    if ( isset( $_POST['_key'] ) && isset( $_POST['trs_like_updated'] ) ) {

        /* Add each text string to the $strings_to_save array */
        foreach ( $_POST as $key => $value ) {
            if ( preg_match( "/text_string_/i" , $key ) ) {
                $default = trs_like_get_text( str_replace( 'trs_like_admin_text_string_' , '' , $key ) , 'default' );
                $strings_to_save[str_replace( 'trs_like_admin_text_string_' , '' , $key )] = array('default' => $default , 'custom' => stripslashes( $value ));
            }
        }

        /* Now actually save the data to the options table */
        update_site_option(
                'trs_like_settings' , array(
            'likers_visibility' => $_POST['trs_like_admin_likers_visibility'] ,
            'post_to_activity_stream' => $_POST['trs_like_admin_post_to_activity_stream'] ,
            'show_excerpt' => $_POST['trs_like_admin_show_excerpt'] ,
            'excerpt_length' => $_POST['trs_like_admin_excerpt_length'] ,
            'text_strings' => $strings_to_save ,
            'translate_nag' => trs_like_get_settings( 'translate_nag' ) ,
            'name_or_portrait' => $_POST['name_or_portrait'],
            'remove_fav_button' => $_POST['trs_like_remove_fav_button']
                )
        );

        /* Let the user know everything's cool */
        echo '<div class="updated"><p><strong>';
        _e( 'Settings saved.' , 'wordpress' );
        echo '</strong></p></div>';
    }

    $text_strings = trs_like_get_settings( 'text_strings' );
    $title = __( 'trendr Like' );
    ?>
    <style type="text/css">
        table input { width: 100%; }
        table label { display: block; }
    </style>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('select.name-or-portrait').change(function() {
                var value = jQuery(this).val();
                jQuery('select.name-or-portrait').val(value);
            });
        });
    </script>

    <div class="wrap">
        <h2><?php echo esc_html( $title ); ?></h2>
        <form action="" method="post">
            <input type="hidden" name="trs_like_updated" value="updated">

            <table class="form-table" id="trs-like-admin" style="max-width:650px;float:left;">
                <tr valign="top">
                    <th scope="row"><?php _e( 'Posting Settings' , 'trendr-like' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <span><?php _e( 'Posting Settings' , 'trendr-like' ); ?></span>
                            </legend>
                            <input type="checkbox" id="trs_like_admin_post_to_activity_stream" name="trs_like_admin_post_to_activity_stream" value="1"<?php if ( trs_like_get_settings( 'post_to_activity_stream' ) == 1 ) echo ' checked="checked"' ?>>
                            <label for="trs_like_admin_post_activity_updates">
                                <?php _e( "Post an activity update when something is liked" , 'trendr-like' ); ?>
                            </label>
                            <p class="description"><?php echo __( 'e.g. ' ) . $current_user->display_name . __( " liked Darren's activity. " ); ?></p>
                            <br />

                            <input type="checkbox" id="trs_like_admin_show_excerpt" name="trs_like_admin_show_excerpt" value="1"<?php if ( trs_like_get_settings( 'show_excerpt' ) == 1 ) echo ' checked="checked"' ?>>
                            <label for="trs_like_admin_show_excerpt"><?php _e( "Show a short excerpt of the activity that has been liked." , 'trendr-like' ); ?></label>
                            <p>Limit to <input type="text" maxlength="3" style="width: 40px" value="<?php echo trs_like_get_settings( 'excerpt_length' ); ?>" name="trs_like_admin_excerpt_length" /> characters.</p>

                        </fieldset>
                    </td>
                </tr>
                
                
                <tr valign="top">
                    <th scope="row"><?php _e( 'Remove Favorite Button' , 'trendr-like' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <span><?php _e( 'Remove Favorite Button' , 'trendr-like' ); ?></span>
                            </legend>
                            <input type="checkbox" id="trs_like_remove_fav_button" name="trs_like_remove_fav_button" value="1" <?php if ( trs_like_get_settings( 'remove_fav_button' ) == 1 ) { echo ' checked="checked" '; } ?>>
                            <label for="trs_like_remove_fav_button">
                                <?php _e( "Remove the trendr favorite button from activity." , 'trendr-like' ); ?>
                            </label>
                            <p class="description"><?php echo __( " Currently only uses jQuery to remove the buttons." , "trendr-like" ); ?></p>
                            <br />
                        </fieldset>
                    </td>
                </tr>

            </table> 

            <div id="trslike-about" style="float:right; background:#fff;max-width:300px;padding:20px;margin-bottom:30px;">
                <h3 class="hndle"><span>About trendr Like</span></h3>
                <p><strong>Version: <?php echo TRS_LIKE_VERSION; ?></strong></p>
                <div class="inside">

                    Gives users the ability to 'like' content across your trendr enabled site.

                    <p>Available for free on <a href="http://wordpress.org/module/trendr-like/">WordPress.org</a>.</p>

                    <strong>Want to help?</strong>
                    <ul>
                        <li><a href="http://wordpress.org/support/view/plugin-reviews/trendr-like">Give 5 stars on WordPress.org</a></li>
                        <li>Development takes place publicly on <a href="https://github.com/Darrenmeehan/trendr-Like">Github</a>. Is there any issues or bugs you have?</li>
                        <li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ZAJLLEJDBHAWL"><strong>Donate</strong></a></li>
                    </ul>

                    <strong>Need help?</strong>
                    <ul><li>Ask on the <a href="http://wordpress.org/support/plugin/trendr-like">WordPress.org forum</a></li></ul>

                </div>          
            </div>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e( 'Custom Messages' , 'trendr-like' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <span><?php _e( 'Custom Messages' , 'trendr-like' ); ?></span>
                            </legend>
                            <label for="trs_like_admin_post_activity_updates">
    <?php _e( "Change what messages are shown to users. For example, they can 'love' or 'dig' items instead of liking them." , "trs-like" ); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>   
            </table>
            <table class="widefat fixed" cellspacing="0">


                <thead>
                    <tr>
                        <th scope="col" id="default" class="column-name" style="width: 43%;"><?php _e( 'Default' , 'trendr-like' ); ?></th>
                        <th scope="col" id="custom" class="column-name" style=""><?php _e( 'Custom' , 'trendr-like' ); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th colspan="2" id="default" class="column-name"></th>
                    </tr>
                </tfoot>

    <?php foreach ( $text_strings as $key => $string ) : ?>
                    <tr valign="top">
                        <th scope="row" style="width:400px;"><label for="trs_like_admin_text_string_<?php echo $key; ?>"><?php echo htmlspecialchars( $string['default'] ); ?></label></th>
                        <td><input name="trs_like_admin_text_string_<?php echo $key; ?>" id="trs_like_admin_text_string_<?php echo $key; ?>" value="<?php echo htmlspecialchars( $string['custom'] ); ?>" class="regular-text" type="text"></td>
                    </tr>
    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="submit">
                <input class="button-primary" type="submit" name="trs-like-admin-submit" id="trs-like-admin-submit" value="<?php _e( 'Save Changes' , 'wordpress' ); ?>"/>
            </p>
    <?php trm_nonce_field( 'trs-like-admin' ) ?>
        </form>
    </div>
    <?php
}
