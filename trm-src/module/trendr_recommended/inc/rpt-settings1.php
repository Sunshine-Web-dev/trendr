<?php

// Admin interface
if ( isset( $_POST[ 'action' ] ) && ( $_POST[ 'action' ] == 'update' ) ) {
    $recommended_number = '10';
    $recommend_sort = 'random';
    $recommended_text = '';
    $priority_values = '';
    $priority_value_edit = array();
    if($_POST['relpoststh_auto'])
    {
        update_option('recommended_number',$_POST['relpoststh_auto']);
        $recommended_number = $_POST['relpoststh_auto'];
    }

    if($_POST['rpt_post_sort'])
    {
        update_option('recommended_sort',$_POST['rpt_post_sort']);
        $recommended_sort = $_POST['rpt_post_sort'];
    }

    if($_POST['relpoststh_top_text'])
    {
        update_option('recommended_top_text',$_POST['relpoststh_top_text']);
        $recommended_text = $_POST['relpoststh_top_text'];
    }  

    if($_POST['priority_values'])
    {
        update_option('priority_values',$_POST['priority_values']);
    }
}

$priority_values = get_option('priority_values');
$priority_value_for_edit = json_decode('"' . $priority_values . '"');
$priority_value_edit = json_decode($priority_value_for_edit,true);


$recommend_number = get_option('recommended_number');
$recommend_sort = get_option('recommended_sort');
$recommended_text = get_option('recommended_top_text');
?>

<div class="wrap relpoststh">

  <div class="icon32" id="icon-options-general"><br></div>
  <h2><?php _e( 'Recommended Settings', 'related-posts-thumbnails' ); ?></h2>

  <form action="?page=related-posts-thumbnails" method="POST" style="clear:both;">
    <input type="hidden" name="action" value="update" />
    <?php trm_nonce_field( 'related-posts-thumbnails' ); ?>

    <div class="trmbr-wrap"><div class="trmbr-tabsWrapper">
      <div class="trmbr-button-container top">
        <div class="setting-notification">
            <?php echo __( 'Settings have changed, you should save them!', 'related-posts-thumbnails' ); ?>
       </div>
       <input type="submit" name="Submit" class="trmb-rpt-settings-submit button button-primary button-big"
				 value="<?php esc_html_e( 'Save Settings', 'related-posts-thumbnails' ); ?>" id="trmb_rpt_save_setting_top">
      </div>

      <div id="relpoststh-settings" class="">
        <ul class="nav-tab-wrapper">
          <li>
						<a href="#skeleton_general_options" class="nav-tab" id="content_general_options-tab">
						<?php _e( 'General Display Options', 'related-posts-thumbnails' ); ?>
						</a>
					</li>
          <li>
						<a href="#skeleton_thumbnail_options" class="nav-tab" id="content_thumbnail_options-tab">
							<?php _e( 'Recommended Settings', 'related-posts-thumbnails' ); ?>
						</a>
					</li>
        </ul>

        <div class="metabox-holder rpth-setting-options">
          <div class="postbox" style="padding: 20px" id="content_general_options">
            <h2>
							<?php _e( 'General Display Options', 'related-posts-thumbnails' ); ?>
						</h2>

            <table class="form-table">
              <tr valign="top">
                <th scope="row">
                  <?php _e( 'Recommended Number', 'related-posts-thumbnails' ); ?>:
                </th>
                <td>
                  <select  name="relpoststh_auto" id="relpoststh_auto" value="<?php echo $recommend_number;?>">
                        <?php for($index = 0;$index<10;$index++){?>
                        <option value="<?php echo 10*$index;?>"><?php echo 10*$index;?>%</option>\
                        <?php }?>
                  </select>
                </td>
              </tr>
              <tr>
                <th scope="row">
                  <?php _e( 'Sort by', 'related-posts-thumbnails' ); ?>:
                </th>
                <td>
                  <select class="rpt_post_sort" name="rpt_post_sort">
                    <option value="rand" <?php if($recommend_sort == 'rand'){echo 'selected';}?>>
                      <?php _e( 'Random', 'related-posts-thumbnails' ); ?>
                    </option>
                    <option value="latest" <?php if($recommend_sort == 'latest'){echo 'selected';}?>>
                      <?php _e( 'Latest Users', 'related-posts-thumbnails' ); ?>
                    </option>
                    <option value="group" 
                      <?php if($recommend_sort == 'group')
                            {
                              echo 'selected';
                        }?> >
                        <?php _e('Users in group','related-posts-thumbnails');?>
                    </option>
                    <option value="friends" <?php if($recommend_sort == 'friends'){echo 'selected';}?>>
                        <?php _e('Friend Users','related-posts-thumbnails'); ?>
                    </option>
                  </select>
                </td>
              </tr>
              <tr>
                <th scope="row">
									<?php _e( 'Top text', 'related-posts-thumbnails' ); ?>:
								</th>
                <td>
                  <input type="text" name="relpoststh_top_text" size="50" value="<?php echo $recommended_text;?>"/>
                </td>
              </tr>
              <!-- </table> -->
            </table>
          </div>


          <div class="postbox" style="padding: 20px; display:none;" id="content_thumbnail_options">
          	<h2>
							<?php _e( 'Recommended Settings', 'related-posts-thumbnails' ); ?>
						</h2>

            <table class="form-table">
              <tr>
                <th>
                  <?php _e( 'priority', 'related-posts-thumbnails' ); ?>
                </th>
                <td>
                  <select class="select-style" name="relpoststh_thsource"  id="relpoststh_thsource">
                    <option value="favourite">Favourite</option>
                    <option value="liked">Liked</option>
                    <option value="friend">Friend</option>
                    <option value="group">Group</option>
                    <option value="checkin">Checkin</option>
                    <option value="hashtag">Popular Hashtag</option>
                 </select>
                 <button type="button" class="button add_priority" style="margin-right:10px;">Add Priority</button>
                 <button type="button" class="button custom_priority" style="margin-right:10px;">Custom Priority</button>
                 <button type="button" class="button save_priority" style="margin-right:10px;">Save Priority</button>
                 <?php foreach($priority_value_edit as $key => $value){?>
                    <p>
                    <?php if(!is_array($value)){?>
                    <input value="<?php echo $key;?>" disabled/>
                    <input type="number" class="priority_edit" value="<?php echo $value;?>"/>
                    <?php }
                    else{
                        foreach($value as $key_value => $key_value_edit){
                            if($key_value != 'priority' && ($key == 'checkin' || $key == 'hashtag')){
                        ?>
                        <input value="<?php echo $key;?>" disabled style="margin-right:10px;"/>
                        <label style="margin-right:10px;">
                        <?php if($key == 'checkin'){echo 'radius:';}else{echo 'numberofhashtags:';}?>
                        <input type="number" value="<?php echo $key_value_edit;?>" <?php if($key == 'hashtag'){echo 'style="margin-right:10px;"';}?>/>
                        <?php if($key == 'checkin'){echo '<label style="margin-right:10px">km</label>';}?>
                        <?php }else if($key_value != 'priority'){?>
                    <input type="text" class="rpt_post_include" value="<?php echo $key_value_edit;?>" style="margin-right:10px;"/>
                    <?php }
                    else{?>
                    <input type="number" class="priority_edit" value="<?php echo $key_value_edit;?>"/>
                        <?php 
                    }}}?>
                    <button type="button" class="remove_priority">Remove Priority</button>
                </p>
                <?php }?>
                </td>

                <input type="hidden" name="priority_values" class="priority_values"/>
                </td>
              </tr>
            </table>

            </div>