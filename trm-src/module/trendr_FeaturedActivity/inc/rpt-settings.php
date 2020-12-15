<?php
function time_elapsed_B($secs){
    $bit = array(
        ' year'        => $secs / 31556926 % 12,
        ' week'        => $secs / 604800 % 52,
        ' day'        => $secs / 86400 % 7,
        ' hour'        => $secs / 3600 % 24,
        ' minute'    => $secs / 60 % 60,
        ' second'    => $secs % 60
        );

    foreach($bit as $k => $v){
        if($v > 1)$ret[] = $v . $k . 's';
        if($v == 1)$ret[] = $v . $k;
        }
    array_splice($ret, count($ret)-1, 0, 'and');
    $ret[] = 'ago.';

    return join(' ', $ret);
    }

// Admin interface
    global $trmdb;
if ( isset( $_POST[ 'action' ] ) && ( $_POST[ 'action' ] == 'update' ) ) {

    if($_POST['feature_post_rate'] != null)
    {
        update_option('feature_post_rate',$_POST['feature_post_rate']);
    }
}



$feature_post_rate = get_option('feature_post_rate');
?>

<div class="wrap relpoststh">

  <h2><?php _e( 'Featured Posts Settings', 'fp-posts-thumbnails' ); ?></h2>


    <div class="trmbr-wrap">
      <div class="trmbr-tabsWrapper">


      <div id="relpoststh-settings" class="">

        <div class="metabox-holder rpth-setting-options">
          <div class="postbox" style="padding: 20px" id="Settings_options">
            <h2>
							<?php _e( 'Content Posts settings', 'pf-posts-thumbnails' ); ?>
						</h2>
            <form action="?page=FP-posts-thumbnails" method="POST" style="clear:both;" id="pf-posts-thumbnails">

            <table class="form-table">
              <tr>
                <th>
                  showing rate per feature post:
                </th>
                <td>
                  <input type="number" name="feature_post_rate" value="<?php echo $feature_post_rate;?>"/>

                </td>
              </tr>
              <tr>
                <th>

                </th>
                <td>


                  <input  name="Submit" type="submit" class="trmb-rpt-settings-submit button button-primary button-big"
                     value="<?php esc_html_e( 'Save Settings', 'related-posts-thumbnails' ); ?>">
                     <input type="hidden" name="action" value="update" />
                     <?php trm_nonce_field( 'fp-posts-thumbnails' ); ?>
                       </form>
                </td>
              </tr>

            </table>


          </div>
          <div class="postbox" style="padding: 20px" id="content_Posts">
            <h2>
							<?php _e( 'Featured Posts', 'related-posts-thumbnails' ); ?>
						</h2>

            <table class="form-table table">
              <tr>
                <th>
                  Post number:
                </th>
                <th>
                  User:
                </th>
                <th>
                  Content:
                </th>
                <th>
                  views
                </th>
                <th>
                  type
                </th>
                <th>
                  expire
                </th>

              </tr>
<?php
$Posts  = get_All_featured_post();

foreach ($Posts as $key => $Post) {
  $args = array();
  $args['activity_ids'] = (int)$Post->id;
$act = trs_activity_get_specific($args)['activities'][0];
$link = trs_activity_get_permalink((int)$Post->id);
 ?>
              <tr>
                <td>
                  <a href="<?php echo $link; ?> "><?php echo $Post->id;  ?></a>
                </td>
                <td>
                  <a href="<?php echo $link; ?> "><?php echo $act->user_nicename;  ?></a>
                </td>
                <td>
                  <a href="<?php echo $link; ?> "><?php echo $act->content;  ?></a>
                </td>
                <td>

                  <?php echo ($Post->views) ; ?>
                </td>
                <td>

                  <?php echo ($Post->type) ; ?>
                </td>
                <td>

                  <?php echo time_elapsed_B($Post->expire_in) ; ?>
                </td>
              </tr>
<?php } ?>
            </table>
              <!-- </table> -->

          </div>

          </div>
      </div>
    </div>
  </div>
</div>
