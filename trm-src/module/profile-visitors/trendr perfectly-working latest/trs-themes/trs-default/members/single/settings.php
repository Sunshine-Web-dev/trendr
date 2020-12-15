<?php

/**
 * trendr - Users Settings
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<div class="contour-select no-ajax" id="subnav" role="navigation">
	<ul>
		<?php if ( trs_is_my_profile() ) : ?>
		
			<?php trs_get_options_nav(); ?>
		
		<?php endif; ?>
	</ul>
</div>

<?php

if ( trs_is_current_action( 'notifications' ) ) :
	 locate_template( array( 'members/single/settings/notifications.php' ), true );

elseif ( trs_is_current_action( 'delete-account' ) ) :
	 locate_template( array( 'members/single/settings/delete-account.php' ), true );

elseif ( trs_is_current_action( 'general' ) ) :
	locate_template( array( 'members/single/settings/general.php' ), true );

else :
	locate_template( array( 'members/single/plugins.php' ), true );

endif;

?>
