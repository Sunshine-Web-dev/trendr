<?php
/**
 * TRS Activity Privacy Admin functions
 *
 * @package TRS-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Loads Buddypress Activity privacy plugin admin area
 *
 */
class TRSActivityPrivacy_Admin {

	var $setting_page = '';

	function __construct() {
		$this->setup_actions();

	}

	function setup_actions(){
		add_action( trs_core_admin_hook(), array( &$this, 'admin_menu' ) );
		//Welcome page redirect
		add_action( 'admin_init', array( &$this, 'do_activation_redirect' ), 1 );
        // Catch save submits
		add_action( 'admin_init', array( &$this, 'admin_submit' ) );


		// Modify Buddypress Activity Privacy admin links
		add_filter( 'plugin_action_links',               array( $this, 'modify_plugin_action_links' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'modify_plugin_action_links' ), 10, 2 );

	}

	function admin_menu() {
		$welcome_page = add_dashboard_page(
				__( 'Welcome to Buddypress Activity Privacy',  'trs-activity-privacy' ),
				__( 'Welcome to TRS Activity Privacy',  'trs-activity-privacy' ),
				'manage_options',
				'trs-activity-privacy-about',
				array( $this, 'about_screen' )
		);

		$this->settings_page = trs_core_do_network_admin() ? 'settings.php' : 'options-general.php';
	    $hook = add_submenu_page( $this->settings_page, __( 'BuddyPress Activity Privacy', 'trs-activity-privacy' ), __( 'TRS Activity Privacy', 'trs-activity-privacy' ), 'manage_options', 'trs-activity-privacy', array( &$this, 'admin_page' ) );

	    //add_action( "admin_print_styles-$hook", 'trs_core_add_admin_menu_styles' );
	    add_action( "admin_print_scripts-$hook", array( &$this, 'enqueue_scripts' ) );
	    add_action( "admin_print_styles-$hook", array( &$this, 'enqueue_styles' ) );

	    remove_submenu_page( 'index.php', 'trs-activity-privacy-about' );

	}

	/**
	 * Modifies the links in plugins table
	 * 
	 */
	public function modify_plugin_action_links( $links, $file ) {

		// Return normal links if not BuddyPress
		if ( plugin_basename( TRS_ACTIVITY_PRIVACY_PLUGIN_FILE_LOADER ) != $file )
			return $links;

		// Add a few links to the existing links array
		return array_merge( $links, array(
			'settings' => '<a href="' . add_query_arg( array( 'page' => 'trs-activity-privacy'      ), trs_get_admin_url( $this->settings_page ) ) . '">' . esc_html__( 'Settings', 'trs-activity-privacy' ) . '</a>',
			'about'    => '<a href="' . add_query_arg( array( 'page' => 'trs-activity-privacy-about'      ), trs_get_admin_url( 'index.php'          ) ) . '">' . esc_html__( 'About',    'trs-activity-privacy' ) . '</a>'
		) );
	}

	function admin_submit() {
	    if ( isset( $_POST['trsap-submit'] ) || isset( $_POST['trsap-reset'] )  ) {
	      if ( !is_super_admin() ) {
	        return;
	      }

	      check_admin_referer( 'trsap-settings' );

	      if( isset( $_POST['trsap-submit'] ) ){
	      	// settings 
	      	$allow_admin_ve_privacy_levels =  ( @$_POST['allow-admin-view-edit-privacy-levels'] )  ?  true : false;
	      	trs_update_option( 'trs_ap_allow_admin_ve_pl', $allow_admin_ve_privacy_levels );
	      
	      	$allow_members_e_privacy_levels = ( @$_POST['allow-members-edit-privacy-levels'] )  ? true : false;
	      	trs_update_option( 'trs_ap_allow_members_e_pl', $allow_members_e_privacy_levels );
	      
			$use_fontawsome = ( $_POST['use-fontawsome'] )  ? true : false;
	      	trs_update_option( 'trs_ap_use_fontawsome', $use_fontawsome );
	      
			$use_custom_styled_selectbox = ( @$_POST['use-custom-styled-selectbox'] )  ? true : false;
	      	trs_update_option( 'trs_ap_use_custom_styled_selectbox', $use_custom_styled_selectbox );
	      
			$show_privacy_levels_label = ( @$_POST['show-privacy-levels-label'] )  ? true : false;
	      	trs_update_option( 'trs_ap_show_privacy_ll', $show_privacy_levels_label );

			$show_privacy_in_activity_meta = ( @$_POST['show-privacy-in-activity-meta'] )  ? true : false;
			trs_update_option( 'trs_ap_show_privacy_in_am', $show_privacy_in_activity_meta);

	        $pavl = $_POST['pavl'];
	        $pavl_enabled = $_POST['pavl_enabled'];
	        $pavl_default = $_POST['pavl_default'];

	        // Register the visibility levels
	        $profile_activity_visibility_levels  = array(
	              'public' => array(
	                  'id'        => 'public',
	                  'label'     => __( 'Anyone', 'trs-activity-privacy' ),
	                  'default'   => ( $pavl_default ==  'public' )  ? true : false,
	                  'position'  => 10*( 1 + array_search('public', array_keys($pavl))),
	                  'disabled'  => ( $pavl_enabled ['public'] )  ? false : true
	              ),
	              'loggedin' => array(
	                  'id'        => 'loggedin',
	                  'label'     => __( 'Logged In Users', 'trs-activity-privacy' ),
	                  'default'   => ( $pavl_default == 'loggedin')  ? true : false,
	                  'position'  => 10*( 1 + array_search('loggedin', array_keys($pavl))),
	                  'disabled'  => ( $pavl_enabled ['loggedin'] )  ? false : true
	              )
	          );

	          if ( trs_is_active( 'friends' ) ) {
	              $profile_activity_visibility_levels['friends'] = array(
	                  'id'        => 'friends',
	                  'label'     => __( 'My Friends', 'trs-activity-privacy' ),
	                  'default'   => ( $pavl_default == 'friends')  ? true : false,
	                  'position'  => 10*( 1 + array_search('friends', array_keys($pavl))),
	                  'disabled'  => ( $pavl_enabled ['friends'] )  ? false : true
	              );
	          }

			  // https://trendr.trac.trendr.org/changeset/7193
			//  if ( function_exists('trs_activity_do_mentions') ) {
		         // if ( trs_activity_do_mentions() ) {
		          //    $profile_activity_visibility_levels['mentionedonly'] = array(
		                  //'id'        => 'mentionedonly',
		                 // 'label'     => __( 'Mentioned Only', 'trs-activity-privacy' ),
		                 // 'default'   => ( $pavl_default == 'mentionedonly')  ? true : false,
		                 // 'position'  => 10*( 1 + array_search('mentionedonly', array_keys($pavl))),
		                 // 'disabled'  => ( $pavl_enabled ['mentionedonly'] )  ? false : true
		           //   );
		        //  }

	      	 // } else {
	      	  
	          //    $profile_activity_visibility_levels['mentionedonly'] = array(
	              //    'id'        => 'mentionedonly',
	               //   'label'     => __( 'Mentioned Only', 'trs-activity-privacy' ),
	                //  'default'   => ( $pavl_default == 'mentionedonly')  ? true : false,
	                 // 'position'  => 10*( 1 + array_search('mentionedonly', array_keys($pavl))),
	                 // 'disabled'  => ( $pavl_enabled ['mentionedonly'] )  ? false : true
	          //    );     	  	

	          $profile_activity_visibility_levels['adminsonly'] = array(
	              'id'      => 'adminsonly',
	              'label'   => __( 'Admins Only', 'trs-activity-privacy' ),
	              'default'   => ( $pavl_default == 'adminsonly')  ? true : false,
	              'position'  => 10*( 1 + array_search('adminsonly', array_keys($pavl))),
	              'disabled'  => ( $pavl_enabled ['adminsonly'] )  ? false : true
	          );

	          $profile_activity_visibility_levels['onlyme'] = array(
	              'id'        => 'onlyme',
	              'label'     => __( 'Only Me', 'trs-activity-privacy' ),
	              'default'   => ( $pavl_default == 'onlyme')  ? true : false,
	              'position'  => 10*( 1 + array_search('onlyme', array_keys($pavl))),
	              'disabled'  => ( $pavl_enabled ['onlyme'] )  ? false : true
	          );	

	          //followers plugin integration
			  if( function_exists('trs_follow_is_following') ) {
		          $profile_activity_visibility_levels['followers'] = array(
		              'id'        => 'followers',
		              'label'     => __( 'My Followers', 'trs-activity-privacy' ),
		              'default'   => ( $pavl_default == 'followers')  ? true : false,
		              'position'  => 10*( 1 + array_search('followers', array_keys($pavl))),
		              'disabled'  => ( $pavl_enabled ['followers'] )  ? false : true
		          );
			  }

	          trs_update_option( 'trs_ap_profile_activity_visibility_levels', $profile_activity_visibility_levels );
	      
	          //Groups activity privacy

	          $gavl = $_POST['gavl'];
	          $gavl_enabled = $_POST['gavl_enabled'];
	          $gavl_default = $_POST['gavl_default'];

	          $groups_activity_visibility_levels = array(
	              'public' => array(
	                  'id'        => 'public',
	                  'label'     => __( 'Anyone', 'trs-activity-privacy' ),
	                  'default'   => ( $gavl_default == 'public')  ? true : false,
	                  'position'  => 10*( 1 + array_search('public', array_keys($gavl))),
	                  'disabled'  => ( $gavl_enabled ['public'] )  ? false : true       
	              ),
	              'loggedin' => array(
	                  'id'        => 'loggedin',
	                  'label'     => __( 'Logged In Users', 'trs-activity-privacy' ),
	                  'default'   => ( $gavl_default == 'loggedin')  ? true : false,
	                  'position'  => 10*( 1 + array_search('loggedin', array_keys($gavl))),
	                  'disabled'  => ( $gavl_enabled ['loggedin'] )  ? false : true         
	              )
	          );

	          if ( trs_is_active( 'friends' ) ) {
	              $groups_activity_visibility_levels['friends'] = array(
	                  'id'        => 'friends',
	                  'label'     => __( 'My Friends', 'trs-activity-privacy' ),
	                  'default'   => ( $gavl_default == 'friends')  ? true : false,
	                  'position'  => 10*( 1 + array_search('friends', array_keys($gavl))),
	                  'disabled'  => ( $gavl_enabled ['friends'] )  ? false : true            
	              );
	              if ( trs_is_active( 'groups' ) ) {
	                $groups_activity_visibility_levels['groupfriends'] = array(
	                    'id'        => 'groupfriends',
	                    'label'     => __( 'My Friends in Group', 'trs-activity-privacy' ),
	                    'default'   => ( $gavl_default == 'groupfriends')  ? true : false,
	                    'position'  => 10*( 1 + array_search('groupfriends', array_keys($gavl))),
	                    'disabled'  => ( $gavl_enabled ['groupfriends'] )  ? false : true          
	                );
	            }
	          }

	               // $groups_activity_visibility_levels['mentionedonly'] = array(
	                //   'id'        => 'mentionedonly',
	                 // 'label'     => __( 'Mentioned Only', 'trs-activity-privacy' ),
	                 //  'default'   => ( $gavl_default == 'mentionedonly')  ? true : false,
	                 // 'position'  => 10*( 1 + array_search('mentionedonly', array_keys($gavl))),
	                 //  'disabled'  => ( $gavl_enabled ['mentionedonly'] )  ? false : true            
	              //  );
	          
	          if ( trs_is_active( 'groups' ) ) {
	              $groups_activity_visibility_levels['grouponly'] = array(
	                  'id'        => 'grouponly',
	                  'label'     => __( 'Group Members', 'trs-activity-privacy' ),
	                  'default'   => ( $gavl_default == 'grouponly')  ? true : false,
	                  'position'  => 10*( 1 + array_search('grouponly', array_keys($gavl))),
	                  'disabled'  => ( $gavl_enabled ['grouponly'] )  ? false : true         
	              );

	              $groups_activity_visibility_levels['groupmoderators'] = array(
	                  'id'        => 'groupmoderators',
	                  'label'     => __( 'Group Moderators', 'trs-activity-privacy' ),
	                  'default'   => ( $gavl_default == 'groupmoderators')  ? true : false,
	                  'position'  => 10*( 1 + array_search('groupmoderators', array_keys($gavl))),
	                  'disabled'  => ( $gavl_enabled ['groupmoderators'] )  ? false : true          
	              );

	              $groups_activity_visibility_levels['groupadmins'] = array(
	                  'id'        => 'groupadmins',
	                  'label'     => __( 'Group Admins', 'trs-activity-privacy' ),
	                  'default'   => ( $gavl_default == 'groupadmins')  ? true : false,
	                  'position'  => 10*( 1 + array_search('groupadmins', array_keys($gavl))),
	                  'disabled'  => ( $gavl_enabled ['groupadmins'] )  ? false : true          
	              );
	        }   

	        $groups_activity_visibility_levels['adminsonly'] = array(
	              'id'        => 'adminsonly',
	              'label'     => __( 'Admins Only', 'trs-activity-privacy' ),
	              'default'   => ( $gavl_default == 'adminsonly')  ? true : false,
	              'position'  => 10*( 1 + array_search('adminsonly', array_keys($gavl))),
	              'disabled'  => ( $gavl_enabled ['adminsonly'] )  ? false : true      
	        );

	        $groups_activity_visibility_levels['onlyme'] = array(
	              'id'        => 'onlyme',
	              'label'     => __( 'Only Me', 'trs-activity-privacy' ),
	              'default'   => ( $gavl_default == 'onlyme')  ? true : false,
	              'position'  => 10*( 1 + array_search('onlyme', array_keys($gavl))),
	              'disabled'  => ( $gavl_enabled ['onlyme'] )  ? false : true    
	        );   

            //followers plugin integration
		    if( function_exists('trs_follow_is_following') ) {
		        $groups_activity_visibility_levels['followers'] = array(
		              'id'        => 'followers',
		              'label'     => __( 'My Followers', 'trs-activity-privacy' ),
		              'default'   => ( $gavl_default == 'followers')  ? true : false,
		              'position'  => 10*( 1 + array_search('followers', array_keys($gavl))),
		              'disabled'  => ( $gavl_enabled ['followers'] )  ? false : true    
		        );   	    	
		        $groups_activity_visibility_levels['groupfollowers'] = array(
		              'id'        => 'groupfollowers',
		              'label'     => __( 'My Followers in Group', 'trs-activity-privacy' ),
		              'default'   => ( $gavl_default == 'groupfollowers')  ? true : false,
		              'position'  => 10*( 1 + array_search('groupfollowers', array_keys($gavl))),
		              'disabled'  => ( $gavl_enabled ['groupfollowers'] )  ? false : true    
		        );   
		    }

	        trs_update_option( 'trs_ap_groups_activity_visibility_levels', $groups_activity_visibility_levels );
	        ?>
	        <div id="message" class="updated"><p><?php _e( 'Settings saved.', 'trs-activity-privacy' );?></p></div>
	        <?php

	      } else {
				global $trs_activity_privacy;
	          
	          	trs_update_option( 'trs_ap_profile_activity_visibility_levels', $trs_activity_privacy->profile_activity_visibility_levels );
	          	trs_update_option( 'trs_ap_groups_activity_visibility_levels', $trs_activity_privacy->groups_activity_visibility_levels );
	        
	      		trs_update_option( 'trs_ap_allow_admin_ve_pl', false );
	     	    trs_update_option( 'trs_ap_allow_members_e_pl', true );
	      		trs_update_option( 'trs_ap_use_fontawsome', true );
	     	    trs_update_option( 'trs_ap_use_custom_styled_selectbox', true );
	      		trs_update_option( 'trs_ap_show_privacy_ll', true );
	      		trs_update_option( 'trs_ap_show_privacy_in_am', true);
	        ?>
	        <div id="message" class="updated"><p><?php _e( 'Settings reseted.', 'trs-activity-privacy' );?></p></div>
	        <?php
	      } 
	    }
	}

	function admin_page() {  
	  ?>
	    <div class="wrap">
	    	<?php screen_icon( 'trendr' ); ?>
	    	<h2><?php _e( 'BuddyPress Activity Privacy', 'trs-activity-privacy' ); ?> <sup>v <?php echo TRS_ACTIVITY_PRIVACY_VERSION ?> </sup></h2>
	     
	      	<form method="post" action="">

		      <h3><label><?php _e('Profil Activity privacy', 'trs-activity-privacy') ?></label></h3>     
		      <div class="trsap-options-box options-box ui-sortable">
		      <h4><?php _e('Please check the box to enable the privacy and Drag&Drop to sort :', 'trs-activity-privacy') ?></h4> 
		      <?php 
		      //$html = "<ul>";
		      //
		      $html = "";
		      $profile_activity_visibility_levels = trs_get_profile_activity_visibility_levels();
		      uasort ($profile_activity_visibility_levels, 'trs_activity_privacy_cmp_position');
		      foreach ($profile_activity_visibility_levels as  $key => $pavl) {
		        $disabled = ( !$pavl["disabled"] ) ? 'checked' : '';
		        $default = ( $pavl["default"] ) ? 'checked' : '';
		        
		        $html .= ' <p class="sortable" style=""><span style="cursor: default;"> Ξ </span><label for="' . $pavl["id"] .'"><input type="checkbox" name="pavl_enabled[' . $pavl["id"] .']" ' . $disabled  .' /> &nbsp; ' . $pavl["label"] .'</label>';
		        $html .= '<input type="hidden" name="pavl[' . $key .']" value="' . $pavl["id"] .'" /><input name="pavl_default" id="pavl_default" value="' . $key .'" type="radio" ' . $default . '><span style="cursor: move;">Default Value</span>';
		        $html .= ' </p>';

		      //  $html .= ' <li><label for="' . $pavl["id"] .'">Position: <input type="text" name="position[' . $pavl["id"] .']" value="' . $pavl["position"] .'" /></label></p>';

		      }
		     // $html .= "</ul>";
		      echo $html;
		      ?>  
	      	 </div>
	           
	      	<h3><label><?php _e('Groups Activity privacy', 'trs-activity-privacy') ?></label></h3>
	     	 <div class="trsap-options-box options-box ui-sortable">
		          <h4><?php _e('Please check the box to enable the privacy and Drag&Drop to sort :', 'trs-activity-privacy') ?></h4> 
		          <?php 
		          $groups_activity_visibility_levels = trs_get_groups_activity_visibility_levels();
		          uasort ($groups_activity_visibility_levels, 'trs_activity_privacy_cmp_position');	
		          $html = '';
		          foreach ($groups_activity_visibility_levels as  $key => $pavl) {
		            $disabled = ( !$pavl["disabled"] ) ? 'checked' : '';
		            $default = ( $pavl["default"] ) ? 'checked' : '';
		            
		            $html .= ' <p class="sortable" style=""><span style="cursor: default;"> Ξ </span><label for="' . $pavl["id"] .'"><input type="checkbox" name="gavl_enabled[' . $pavl["id"] .']" ' . $disabled  .' /> &nbsp; ' . $pavl["label"] .'</label>';
		            $html .= '<input type="hidden" name="gavl[' . $key .']" value="' . $pavl["id"] .'" /><input name="gavl_default" id="gavl_default" value="' . $key .'" type="radio" ' . $default . '><span style="cursor: move;">Default Value</span>';
		            $html .= ' </p>';
		          }
		          echo $html;
		          ?>  
		      </div>

			<?php
		      	$allow_admin_ve_privacy_levels = trs_ap_is_admin_allowed_to_view_edit_privacy_levels();
		      	$allow_members_e_privacy_levels = trs_ap_is_members_allowed_to_edit_privacy_levels();
				$use_fontawsome = trs_ap_is_use_fontawsome();
				$use_custom_styled_selectbox = trs_ap_is_use_custom_styled_selectbox();
				$show_privacy_levels_label = trs_ap_show_privacy_levels_label();
				$show_privacy_in_activity_meta = trs_ap_show_privacy_in_activity_meta();
			?>

		    <h3><label><?php _e('Settings', 'trs-activity-privacy') ?></label></h3>     
  				<h4><?php _e('Main settings', 'trs-activity-privacy') ?></h4> 
				<table class="form-table">
					<tbody><tr><th scope="row"><?php _e('Admin privileges', 'trs-activity-privacy') ?></th><td>
					<input id="allow-admin-view-edit-privacy-levels" name="allow-admin-view-edit-privacy-levels" <?= ($allow_admin_ve_privacy_levels) ? 'checked' : ''; ?> type="checkbox" />
					<label for="allow-admin-view-edit-privacy-levels"><?php _e('Allow admin to view and edit the prviacy of all activities', 'trs-activity-privacy') ?></label>

					</td></tr><tr><th scope="row">Members privileges</th><td>
					<input id="allow-members-edit-privacy-levels" name="allow-members-edit-privacy-levels" <?= ($allow_members_e_privacy_levels) ? 'checked' : ''; ?> type="checkbox" />
					<label for="allow-members-edit-privacy-levels"><?php _e('Allow members to edit the privacy of their activities', 'trs-activity-privacy') ?></label>

					</td></tr>
					</tbody>
				</table>
				<h4><?php _e('UI settings', 'trs-activity-privacy') ?></h4>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><?php _e('Font Awesome Icons', 'trs-activity-privacy') ?></th>
						<td>
						<input id="use-fontawsome" name="use-fontawsome" <?= ($use_fontawsome) ? 'checked' : ''; ?> type="checkbox" />
						<label for="use-fontawsome"><?php _e('Use FontAwesome Icons', 'trs-activity-privacy') ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Custom styled selectbox', 'trs-activity-privacy') ?></th>
						<td>
						<input id="use-custom-styled-selectbox" name="use-custom-styled-selectbox" <?= ($use_custom_styled_selectbox) ? 'checked' : ''; ?> type="checkbox" />
						<label for="use-custom-styled-selectbox"><?php _e('Use custom styled selectbox', 'trs-activity-privacy') ?></label>

						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e('Privacy labels', 'trs-activity-privacy') ?></th>
						<td>
						<input id="show-privacy-levels-label" name="show-privacy-levels-label" <?= ($show_privacy_levels_label) ? 'checked' : ''; ?> type="checkbox">
						<label for="show-privacy-levels-label"><?php _e('Show the privacy label in selectbox (Use FontAwesome Icons should be checked if this is unchecked)', 'trs-activity-privacy') ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Privacy in activity meta', 'trs-activity-privacy') ?></th>
						<td>
						<input id="show-privacy-in-activity-meta" name="show-privacy-in-activity-meta" <?= ($show_privacy_in_activity_meta) ? 'checked' : ''; ?> type="checkbox">
						<label for="show-privacy-in-activity-meta"><?php _e('Show the privacy in activity meta', 'trs-activity-privacy') ?></label>
						</td>
					</tr>
					</tbody>
				</table>

		      <?php trm_nonce_field( 'trsap-settings' ) ?>
		      <br />
		      <input type="submit" name="trsap-submit" class="button-primary" value="<?php _e( "Save Settings", 'trs-activity-privacy' ) ?>" />
		      <input type="submit" name="trsap-reset" class="button-secondary" value="<?php _e( "Reset", 'trs-activity-privacy' ) ?>" />
		  </form>
		</div><!-- end-wrap -->  
	  <?php     

	}

  	public function about_screen() {
		$display_version = TRS_ACTIVITY_PRIVACY_VERSION;
		$settings_url = add_query_arg( array( 'page' => 'trs-activity-privacy'), trs_get_admin_url( $this->settings_page ) );
		?>
		<style type="text/css">
			/* Changelog / Update screen */

			.about-wrap .feature-section img {
				border: none;
				margin: 0 1.94% 10px 0;
				-webkit-border-radius: 3px;
				border-radius: 3px;
			}

			.about-wrap .feature-section.three-col img {
				margin: 0.5em 0 0.5em 5px;
				max-width: 100%;
				float: none;
			}

			.ie8 .about-wrap .feature-section.three-col img {
				margin-left: 0;
			}

			.about-wrap .feature-section.images-stagger-right img {
				float: right;
				margin: 0 5px 12px 2em;
			}

			.about-wrap .feature-section.images-stagger-left img {
				float: left;
				margin: 0 2em 12px 5px;
			}

			.about-wrap .feature-section img.image-100 {
				margin: 0 0 2em 0;
				width: 100%;
			}

			.about-wrap .feature-section img.image-66 {
				width: 65%;
			}

			.about-wrap .feature-section img.image-50 {
				max-width: 50%;
			}

			.about-wrap .feature-section img.image-30 {
				max-width: 31.2381%;
			}

			.ie8 .about-wrap .feature-section img {
				border-width: 1px;
				border-style: solid;
			}	

			.about-wrap .images-stagger-right img.image-30:nth-child(2) {
				margin-left: 1em;
			}

			.about-wrap .feature-section img {
			    background: none repeat scroll 0% 0% #FFF;
			    border: 1px solid #CCC;
			    box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.3);
			}

			.trsap-admin-badge {
				position: absolute;
				top: 0px;
				right: 0px;
				padding-top: 190px;
				height: 25px;
				width: 173px;
				color: #555;
				font-weight: bold;
				font-size: 11px;
				text-align: center;
				margin: 0px -5px;
				background: url('<?php echo TRS_ACTIVITY_PRIVACY_PLUGIN_URL; ?>includes/images/badge.png') no-repeat scroll 0% 0% transparent;
			}
		</style>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to BuddyPress Activity Privacy %s', 'trs-activity-privacy' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for upgrading to the latest version of TRS Activity Privacy! <br \> TRS Activity Privacy %s is ready to manage the activity privacy of your Site!', 'trs-activity-privacy' ), $display_version ); ?></div>
			<div class="trsap-admin-badge" style=""><?php printf( __( 'Version %s', 'trs-activity-privacy' ), $display_version ); ?></div>

			<div class="warning-text" style="color:red;font-weight:bold;text-align:center;"><?php _e( 'Please go to TRS Acitivity Privacy Configuration and save your settings to apply new update.', 'trs-activity-privacy'  ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url(  trs_get_admin_url( add_query_arg( array( 'page' => 'trs-activity-privacy-about' ), 'index.php' ) ) ); ?>">
					<?php _e( 'About', 'trs-activity-privacy' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'Add Privacy Controls To The BuddyPress Activity Stream!', 'trs-activity-privacy' ); ?></h3>

				<div class="feature-section">
					<p><?php _e( 'TRS Activity Privacy is a BuddyPress plugin who gives users the ability to restrict who can see their activity posts. ', 'trs-activity-privacy' ); ?></p>
					<p><?php _e( 'It gives each member multiple privacy options on activity posts and should encourage more confident participation on your social network.', 'trs-activity-privacy' );?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php printf( __( 'What&#39; new in %s ?', 'trs-activity-privacy' ), $display_version ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php _e( 'Allow admin to view and edit the prviacy of all activities (Check Admin privileges in plugin settings).', 'trs-activity-privacy' );?></li>
						<li><?php _e( 'Admin now have a control to enable/disable the members to edit the privacy of their activities.', 'trs-activity-privacy' );?></li>
						<li><?php _e( 'Admin now have a control to enable/disable the FontAwesome icon.', 'trs-activity-privacy' );?></li>
						<li><?php _e( 'Admin now have a finer control to enable/disable FontAwesome icon.', 'trs-activity-privacy' );?></li>
						<li><?php _e( 'Admin now have a control to enable/disable the custom styled selectbox.', 'trs-activity-privacy' );?></li>
						<li><?php _e( 'Admin now have a control to show/hide printing the privacy of activities in their meta.', 'trs-activity-privacy' );?></li>
						
						<strong><?php _e( 'Updates before current version','trs-activity-privacy' ); ?></strong>
						<br />
						<br />	
						<li class=""><?php _e( 'The plugin work now on Multisite Network!', 'trs-activity-privacy' ); ?></li>
						
						<li><?php _e( 'New Dropdown system with a nice icons ( By <a target="_BLANK" href="http://fontawesome.io/">Font Awesome</a> ).', 'trs-activity-privacy' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'How it\'s Work ?' , 'trs-activity-privacy' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo TRS_ACTIVITY_PRIVACY_PLUGIN_URL;?>/screenshot-1.png" class="image-50" />
					<p><?php _e( 'Once installed and activated, BuddyPress Activity Privacy adds following privacy controls to the post update box for members:', 'trs-activity-privacy' ); ?></p>
					<ul>
						<li><?php _e( 'Anyone', 'trs-activity-privacy' ); ?></li>
						<li><?php _e( 'Logged In Users', 'trs-activity-privacy' ); ?></li>
						<li><?php _e( 'My Friends', 'trs-activity-privacy' ); ?></li>
						<li><?php _e( 'Admin Only', 'trs-activity-privacy' ); ?></li>
						<li><?php _e( 'Only Me', 'trs-activity-privacy' ); ?></li>
					</ul>
					<p><?php _e( 'Certain privacy controls are component-dependent. For example, the "Friends Only" privacy option in the dropdown will not show up unless you have the Friends component activated in the BuddyPress settings panel.', 'trs-activity-privacy' ); ?></p>

				</div>
			</div>

			<div class="changelog">
			
				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo TRS_ACTIVITY_PRIVACY_PLUGIN_URL;?>/screenshot-2.png" class="image-50" />
					<p>
					<?php _e( 'When posting within a group the group-specific privacy options will be added to the dropdown, inlcluding:', 'trs-activity-privacy' ); ?>&nbsp;
					<ul>
						<li><?php _e( 'My Friends in a Group', 'trs-activity-privacy' ); ?></li>
						<li><?php _e( 'Group Members', 'trs-activity-privacy' ); ?></li>
						<li><?php _e( 'Group Moderators', 'trs-activity-privacy' ); ?></li>
						<li><?php _e( 'Group Admins', 'trs-activity-privacy' ); ?></li>
					</ul>
				</div>
			</div>


			<div class="changelog">
				<h3><?php _e( 'Update the Privacy of the ol ', 'trs-activity-privacy' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo TRS_ACTIVITY_PRIVACY_PLUGIN_URL;?>/screenshot-6.png" class="image-50" />
					<p><?php _e( 'Members can update the privacy of the old activity stream (new selectbox in activity meta).', 'trs-activity-privacy' ); ?></p>
					<p><?php _e( 'Admin can also update the privacy of all the old activity stream.', 'trs-activity-privacy' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Privacy control for Followers Plugin', 'trs-activity-privacy' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo TRS_ACTIVITY_PRIVACY_PLUGIN_URL;?>/screenshot-3.png" class="image-50" />
					<h4><?php _e( 'Fully integrated with Buddypress Follow', 'trs-activity-privacy' ); ?></h4>
					<p><?php _e( 'If you have <a href="http://trendr.org/plugins/trendr-followers/">BuddyPress Follow</a> installed in your site, TRS Activity Privacy adds new privacy levels :', 'trs-activity-privacy' ); ?></p>
					<ul>
						<li><?php _e( 'My Followers', 'trs-activity-privacy' ); ?></li>
						<li><?php _e( 'My followers in Group', 'trs-activity-privacy' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Integration for Buddypress Activity Plus Plugin', 'trs-activity-privacy' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo TRS_ACTIVITY_PRIVACY_PLUGIN_URL;?>/screenshot-5.png" class="image-50" />
					<p><?php _e( 'TRS Activity Privacy is released with Integration for <a href="http://trendr.org/plugins/trendr-activity-plus/">Buddypress Activity Plus</a>.', 'trs-activity-privacy' ); ?></p>
				</div>
			</div>			

			<div class="changelog">
				<h3><?php _e( 'TRS Acitivity Privacy Configuration', 'trs-activity-privacy' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo TRS_ACTIVITY_PRIVACY_PLUGIN_URL;?>/screenshot-7.png" class="image-50" />
					<h4><a href="<?php echo $settings_url;?>" title="<?php _e( 'Configure TRS Activity Privacy', 'trs-activity-privacy' ); ?>"><?php _e( 'Configure TRS Activity Privacy', 'trs-activity-privacy' ); ?></a></h4>
					<p><?php _e( 'From the settings menu of his trendr administration, the administrator is able to configure TRS Activity Privacy by :', 'trs-activity-privacy' ); ?></p>
					<ul>
						<li><?php _e( 'Enable/Disable a privacy level.', 'trs-activity-privacy' ); ?></li>
						<li><?php _e( 'Sort the privacy levels.', 'trs-activity-privacy' ); ?></li>
						<li><?php _e( 'Change the default privacy level.', 'trs-activity-privacy' ); ?></li>
					</ul>
				</div>
				
				<div class="return-to-dashboard">
					<a href="<?php echo $settings_url;?>" title="<?php _e( 'Configure TRS Activity Privacy', 'trs-activity-privacy' ); ?>"><?php _e( 'Go to the TRS Activity Privacy Settings page', 'trs-activity-privacy' );?></a>
				</div>
			</div>

		</div>
	<?php
  	}

	/**
	 * Welcome screen redirect
	 */
	function do_activation_redirect() {
		// Bail if no activation redirect
		if ( ! get_transient( '_trs_activity_privacy_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_trs_activity_privacy_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( isset( $_GET['activate-multi'] ) )
			return;

		$query_args = array( 'page' => 'trs-activity-privacy-about' );

		// Redirect to Buddypress Activity privacy about page
		trm_safe_redirect( add_query_arg( $query_args, trs_get_admin_url( 'index.php' ) ) );
	}  	

  	function enqueue_scripts() {
    	trm_enqueue_script( 'trsap-admin-js',  plugins_url( 'js/admin.js' ,  __FILE__ ), array( 'jquery', 'jquery-ui-sortable' ) );

  	}

  	function enqueue_styles() {
  		if(trs_ap_is_use_fontawsome()){
   	 		trm_enqueue_style( 'trs-font-awesome-css', plugins_url( 'css/font-awesome/css/font-awesome.min.css' ,  __FILE__ )); 
    	}
    	trm_enqueue_style( 'trs-activity-privacy-admin-css', plugins_url( 'css/admin.css' ,  __FILE__ )); 
  	}

  	//@TODO
  	function update(){
  	}
}