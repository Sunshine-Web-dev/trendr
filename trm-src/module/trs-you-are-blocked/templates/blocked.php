<?php get_header( 'trendr' ); ?>


				<p>&nbsp;</p>
				<h1 style="text-align:center;"><?php _e( 'Blocked Profile', 'trsblock' ); ?></h1>
				<h2 style="text-align:center;"><?php _e( 'You have selected to block this profile', 'trsblock' ); ?></h2>
				<p style="text-align:center;"><a href="<?php echo trs_loggedin_user_domain() . 'settings/blocked/'; ?>" class="button button-large"><?php _e( 'Manage Blocked Users', 'trsblock' ); ?></a><br /><br /><br /><br /><br /></p>


<?php get_footer( 'trendr' ); ?>
