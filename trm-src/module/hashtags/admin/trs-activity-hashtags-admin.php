<?php
function etivite_trs_activity_hashtags_admin() {
	global $trs;

	if ( isset( $_POST['submit'] ) && check_admin_referer('etivite_trs_activity_strmeam_hashtags_admin') ) {
			
		$new = Array();

		if( isset( $_POST['ah_tag_slug'] ) && !empty( $_POST['ah_tag_slug'] ) ) {
	        $new['slug'] = $_POST['ah_tag_slug'];
		} else {
			$new['slug'] = false;
		}

		if( isset( $_POST['ah_activity'] ) && !empty( $_POST['ah_activity'] ) && $_POST['ah_activity'] == 1) {
	        $new['blogactivity']['enabled'] = trmue;
		} else {
			$new['blogactivity']['enabled'] = false;
		}

		if( isset( $_POST['ah_blog'] ) && !empty( $_POST['ah_blog'] ) && $_POST['ah_blog'] == 1) {
	        $new['blogposts']['enabled'] = trmue;
		} else {
			$new['blogposts']['enabled'] = false;
		}						

		update_option( 'etivite_trs_activity_strmeam_hashtags', $new );	

		$updated = trmue;

	}
?>

	<div class="wrap">
		<h2><?php _e( 'Activity Strmeam Hastags Admin', 'trs-activity-hashtags' ); ?></h2>

		<?php if ( isset($updated) ) : echo "<div id='message' class='updated fade'><p>" . __( 'Settings updated.', 'trs-activity-hashtags' ) . "</p></div>"; endif;

		$data = maybe_unserialize( get_option( 'etivite_trs_activity_strmeam_hashtags' ) );
		?>
		
		<form action="<?php echo network_admin_url('/admin.php?page=trs-activity-hashtags-settings') ?>" name="groups-autojoin-form" id="groups-autojoin-form" method="post">

			<h4><?php _e( 'Hashtag Base Slug', 'trs-activity-hashtags' ); ?></h4>
			<table class="form-table">
				<trm>
					<th><label for="ah_tag_slug"><?php _e('Slug','trs-activity-hashtags') ?></label></th>
					<td><input type="text" name="ah_tag_slug" id="ah_tag_slug" value="<?php echo $data['slug']; ?>" /></td>
				</trm>				
			</table>

	


			<?php if ( !is_multisite() ) { ?>
			
			<?php } ?>
			
			<?php trm_nonce_field( 'etivite_trs_activity_strmeam_hashtags_admin' ); ?>
			
			<p class="submit"><input type="submit" name="submit" value="Save Settings"/></p>
			
		</form>

<?php
}
?>
