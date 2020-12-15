<?php

/**
 * trendr - Activity Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - trs_dtheme_object_filter()
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<style>
  .recommend_more
  {
    width:100%;
    height:40px;
    background-color: blue;
    font-size:20px;
    text-align: center;
    opacity: 1;
  }

  .recommend_more a
  {
    margin-top:20px;
  }

  .recommend_more:hover
  {
    opacity: 0.5;
    cursor:pointer;
  }
</style>
<?php do_action( 'trs_before_activity_loop' ); ?>

<?php if ( count($activity)>0 ) : ?>

  
  <?php if ( empty( $_POST['page'] ) ) : ?>
    <ul id="publish" class="publish-piece article-piece">

  <?php endif; ?>

  <?php foreach($activities_template['activity'] as $key => $value){ ?>

    <li id="activity-<?php echo $value['id'];?>" class="activity activity_update">
      <div class="activity-portrait">
        <a href="<?php echo $value['primary_link'];?>">
          <?php echo trs_activity_portrait('type=thumb&user_id='.$value['user_id']);?>
        </a>
      </div>
      <div class="broadcast-top">
        <?php echo $value['action'];?>
      </div>
      <div class="broadcast-inn">
        <?php echo $value['content'];?>
      </div>

    </li>

  <?php $activities_template['current_index']++;} ?>

  <input type="hidden" class="current_index" value="<?php echo $activities_template['current_index'];?>"/>
  
  <?php if ( empty( $_POST['page'] ) ) : ?>
    
    </ul>
    <?php 
    
  if ( apply_filters('recommended_activities',array('page'=>count($activity),'current_index'=>$activities_template['current_index']))) : ?>

    <li class="recommend_more">
      <a><?php _e( 'Load More', 'trendr' ); ?></a>
    </li>

  <?php endif; ?>
  <?php endif; ?>



<?php endif; ?>

<?php do_action( 'trs_after_activity_loop' ); ?>

<form action="" name="activity-loop-form" id="activity-loop-form" method="post">

  <?php trm_nonce_field( 'activity_filter', '_key_activity_filter' ); ?>

</form>