<?php

/**
 * trendr - Users Settings
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<div class="contour-select no-ajax" id="contour-box" role="navigation">


</div>

<?php

if ( trs_is_current_action( 'notifications' ) ) :
	 locate_template( array( 'members/single/settings/notifications.php' ), true );

elseif ( trs_is_current_action( 'delete-account' ) ) :
	 locate_template( array( 'members/single/settings/delete-account.php' ), true );

elseif ( trs_is_current_action( 'general' ) ) :
	locate_template( array( 'members/single/settings/general.php' ), true );

else :
	locate_template( array( 'members/single/module.php' ), true );

endif;

?>
