<?php

// Admin interface
    global $trmdb;
if ( isset( $_POST[ 'action' ] ) && ( $_POST[ 'action' ] == 'update' ) ) {
    $recommended_number = '10';
    $recommend_sort = 'random';
    $recommended_text = '';
    $priority_values = '';
    $priority_value_edit = array();
    update_option('new','new');
    update_option('cache_new_time',time());

    if($_POST['relpoststh_auto'] != null)
    {
        update_option('recommended_number',$_POST['relpoststh_auto']);
        $recommended_number = $_POST['relpoststh_auto'];
    }

    if($_POST['rpt_post_sort'] != null)
    {
        update_option('recommended_sort',$_POST['rpt_post_sort']);
        $recommended_sort = $_POST['rpt_post_sort'];
    }

    if($_POST['relpoststh_top_text'])
    {
        update_option('recommended_top_text',$_POST['relpoststh_top_text']);
        $recommended_text = $_POST['relpoststh_top_text'];
    }

    if($_POST['priority_values'] != null)
    {
        update_option('priority_values',$_POST['priority_values']);
    }

    if($_POST['cachetime'] != null)
    {
      update_option('cachetime',$_POST['cachetime']);
    }

    if($_POST['hashnum'] != null)
    {
      update_option('hashnum',$_POST['hashnum']);
    }

    if($_POST['type'])
    {
      update_option('activity_type',$_POST['type']);
    }

    if($_POST['cacheminutes'] != null)
    {
      update_option('cacheminutes',$_POST['cacheminutes']);
    }

    if($_POST['cachesec'] != null)
    {
      update_option('cachesec',$_POST['cachesec']);
    }
    $posts = get_posts(array('post_type'=>'page'));
    $enable = false;
    foreach($posts as $post)
    {
      if($post->post_content == '[recommend_settings][/recommend_settings]')
      {
        $enable = true;
      }
    }

    if(!$enable)
    {
      $my_post = array(
          'post_title'    => 'recommended',
          'post_content'  => '[recommend_settings][/recommend_settings]',
          'post_status'   => 'publish',
          'post_type'=>'page'
        );
      trm_insert_post( $my_post );
    }
}
$type = get_option('activity_type')?get_option('activity_type'):array();
if(!is_array($type))
{
  $type_for_edit = json_decode('"' . $type . '"');
  $type = json_decode($type_for_edit,true);
}

$cachetime = get_option('cachetime');
$cacheminutes = get_option('cacheminutes');
$cachesec = get_option('cachesec');

$type_activity = $trmdb->get_results('Select type from trm_trs_activity group by type','ARRAY_A');
$priority_values = get_option('priority_values');
$priority_value_for_edit = json_decode('"' . $priority_values . '"');
$priority_value_edit = json_decode($priority_value_for_edit,true);
$hashtag = get_option('hashnum');
$recommend_number = get_option('recommended_number');
$recommend_sort = get_option('recommended_sort');
$recommended_text = get_option('recommended_top_text');
?>

<div class="wrap relpoststh">

  <div class="icon32" id="icon-options-general"><br></div>
  <h2><?php _e( 'Recommended Settings', 'related-posts-thumbnails' ); ?></h2>

  <form action="?page=related-posts-thumbnails" method="POST" style="clear:both;" id="related-posts-thumbnails">
    <input type="hidden" name="action" value="update" />
    <?php trm_nonce_field( 'related-posts-thumbnails' ); ?>

    <div class="trmbr-wrap"><div class="trmbr-tabsWrapper">
      <div class="trmbr-button-container top">
        <div class="setting-notification">
            <?php echo __( 'Settings have changed, you should save them!', 'related-posts-thumbnails' ); ?>
       </div>
       <input type="button" class="button-primary button-big" value="clear cache" id="clear_cache" style="float:right;"/>
       <input type="button" name="Submit" class="trmb-rpt-settings-submit button button-primary button-big"
				 value="<?php esc_html_e( 'Save Settings', 'related-posts-thumbnails' ); ?>" id="trmb_rpt_save_setting_top">
      </div>

      <div id="relpoststh-settings" class="">
        <ul class="nav-tab-wrapper">
          <li>
						<a href="#skeleton_general_options" class="nav-tab" id="content_general_options-tab">
						<?php _e( 'Candidate Settings', 'related-posts-thumbnails' ); ?>
						</a>
					</li>
        </ul>

        <div class="metabox-holder rpth-setting-options">
          <div class="postbox" style="padding: 20px" id="content_general_options">
            <h2>
							<?php _e( 'Candidate Settings', 'related-posts-thumbnails' ); ?>
						</h2>

            <table class="form-table">
              <tr valign="top">
                <th scope="row">
                  <?php _e( 'number of all activities', 'related-posts-thumbnails' ); ?>:
                </th>
                <td>
                  <?php
                    global $trmdb;
                    $count = $trmdb->get_results('Select Count(*) as count from trm_trs_activity','ARRAY_A')[0]['count']; echo $count;
                    ?>
                </td>
              </tr>
              <tr>
                <th>
                  candidate number:
                </th>
                <td>
                  <input type="text" name="relpoststh_auto" value="<?php echo $recommend_number;?>"/>

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
              <tr>
                <th scope="row">Type of Activities:</th>
                <td>
                  <input type="hidden" class="select_type_input" name="type" value=""/>
                  <select multiple="multiple" class="select_type">
                    <?php foreach($type_activity as $type_activities){?>
                    <option value="<?php echo $type_activities['type'];?>" <?php if(in_array($type_activities['type'],$type)){echo 'selected';}?>><?php echo $type_activities['type'];?></option>
                    <?php }?>
                  </select>
                </td>
              </tr>
              <tr>
                <th scope="row">Cache Time:</th>
                <td><input type="number" name="cachetime" value="<?php echo $cachetime?$cachetime:0;?>" class="cachetime"/>Hours<input type="number" name="cacheminutes" value="<?php echo $cacheminutes?$cacheminutes:0;?>" class="cacheminutes" style="margin-left:10px;"/>min<input type="number" name="cachesec" value="<?php echo $cachesec?$cachesec:0;?>" class="cachesec" style="margin-left:10px;"/>sec</td>
              </tr>
              <table class="form-table">
              <tr>
                <th>
                  <?php _e( '<h1>priority</h1>', 'related-posts-thumbnails' ); ?>
                </th>
              </tr>
              <tr>
                <input type="hidden" name="priority_values" class="priority_values" value="<?php echo $priority_values;?>"/>
                  <table id="priority" style="border-color:grey;border-style:solid;">
                    <thead style="border-color:grey;border-style:solid;">
                      <th style="border-color:grey;border-style:solid;width:5%;">Enable</th>
                      <th style="border-color:grey;border-style:solid;width:15%;">Name</th>
                      <th style="border-color:grey;border-style:solid;width:30%;">Description</th>
                      <th style="border-color:grey;border-style:solid;width:1%;">Priority</th>
                      <th style="border-color:grey;border-style:solid;width:60%;">Additional Field</th>
                    </thead>
                    <tbody>
                      <tr>
                        <td style="border-color:grey;border-style:solid;text-align:center;"><input type="checkbox" checked disabled field="favourite"/></td>
                        <td style="border-color:grey;border-style:solid;text-align:center;color:red;">
                          Favourite
                        </td>
                        <td style="border-color:grey;border-style:solid;text-align: center;">
                          Favourite Activities
                        </td>
                        <td style="border-color:grey;border-style:solid;">
                          <input type="number"  style="border-color:grey;" value="<?php echo $priority_value_edit['favourite']['priority'];?>" field="priority"/>
                        </td>
                        <td style="border-color:grey;border-style:solid;text-align:center;">
                            Depth of Hashtag:<input type="number"  style="border-color:grey;width:10%;" value="<?php echo $priority_value_edit['favourite']['hashtag'];?>" field="hashtag"/>
                            Search Type:<select name="hash" field="search_hash">
                              <option value="hash to content" <?php if($priority_value_edit['favourite']['search_hash'] == 'hash to content'){echo 'selected';}?>>hash to content</option>
                              <option value="hash to hash" <?php if($priority_value_edit['favourite']['search_hash'] == 'hash to hash'){echo 'selected';}?>>hash to hash</option>
                            </select>
                        </td>
                      </tr>
                      <tr>
                        <td style="border-color:grey;border-style:solid;text-align:center;"><input type="checkbox" checked disabled field="liked"/></td>
                        <td style="border-color:grey;border-style:solid;text-align:center;color:red;">
                          Liked
                        </td>
                        <td style="border-color:grey;border-style:solid;text-align: center;">
                          Liked Activities
                        </td>
                        <td style="border-color:grey;border-style:solid;">
                          <input type="number"  style="border-color:grey;" value="<?php echo $priority_value_edit['liked']['priority'];?>" field="priority"/>
                        </td>
                        <td style="border-color:grey;border-style:solid;text-align:center;">
                          Depth of Hashtag:<input type="number"  style="border-color:grey;width:10%;" value="<?php echo $priority_value_edit['liked']['hashtag'];?>" field="hashtag"/>
                          Search Type:<select name="hash" field="search_hash">
                              <option value="hash to content" <?php if($priority_value_edit['liked']['search_hash'] == 'hash to content'){echo 'selected';}?>>hash to content</option>
                              <option value="hash to hash" <?php if($priority_value_edit['liked']['search_hash'] == 'hash to hash'){echo 'selected';}?>>hash to hash</option>
                            </select>
                        </td>

                      </tr>
                      <tr>
                        <td style="border-color:grey;border-style:solid;text-align:center;"><input type="checkbox" <?php echo $priority_value_edit['checkin']?'checked="checked"':'';?> field="checkin"/></td>
                        <td style="border-color:grey;border-style:solid;text-align:center;color:red;">
                          Checkin
                        </td>
                        <td style="border-color:grey;border-style:solid;text-align: center;">
                          Radius from your address
                        </td>
                        <td style="border-color:grey;border-style:solid;">
                          <input type="number"  style="border-color:grey;" value="<?php echo $priority_value_edit['checkin']['priority'];?>" field="priority"/>
                        </td>
                        <td style="border-color:grey;border-style:solid;text-align:center;">
                          radius:<input type="number"  style="border-color:grey;" value="<?php echo $priority_value_edit['checkin']['radius'];?>" field="radius"/>
                        </td>
                      </tr>
                      <tr>
                        <td style="border-color:grey;border-style:solid;text-align:center;"><input type="checkbox" <?php echo $priority_value_edit['fllower']?'checked="checked"':'';?> field="fllower"/></td>
                        <td style="border-color:grey;border-style:solid;text-align:center;color:red;">
                          Fllowers
                        </td>
                        <td style="border-color:grey;border-style:solid;text-align: center;">
                          fllower users Activities
                        </td>
                        <td style="border-color:grey;border-style:solid;">
                          <input type="number" value="<?php echo $priority_value_edit['fllower'];?>" />
                        </td>
                        <td style="border-color:grey;border-style:solid;text-align:center;">

                        </td>
                      </tr>
                      <tr>
                        <td style="border-color:grey;border-style:solid;text-align:center;"><input type="checkbox" <?php echo $priority_value_edit['friend']?'checked="checked"':''?> field="friend"/></td>
                        <td style="border-color:grey;border-style:solid;text-align:center;color:red;">
                          Friend
                        </td>
                        <td style="border-color:grey;border-style:solid;text-align: center;">
                          Activities Of Friends
                        </td>
                        <td style="border-color:grey;border-style:solid;">
                          <input type="number"  style="border-color:grey;" value="<?php echo $priority_value_edit['friend']?>"/>
                        </td>
                        <td style="border-color:grey;border-style:solid;"></td>
                      </tr>
                      <tr>
                        <td style="border-color:grey;border-style:solid;text-align:center;"><input type="checkbox" <?php echo $priority_value_edit['group']?'checked="checked"':''?> field="group"/></td>
                        <td style="border-color:grey;border-style:solid;text-align:center;color:red;">
                          Group
                        </td>
                        <td style="border-color:grey;border-style:solid;text-align: center;">
                          Activities Of Group
                        </td>
                        <td style="border-color:grey;border-style:solid;">
                          <input type="number"  style="border-color:grey;" value="<?php echo $priority_value_edit['group'];?>"/>
                        </td>
                        <td style="border-color:grey;border-style:solid;">

                        </td>
                      </tr>
                    </tbody>
                  </table>
              </tr>
              </form>
            </table>
              <!-- </table> -->
          </div>
