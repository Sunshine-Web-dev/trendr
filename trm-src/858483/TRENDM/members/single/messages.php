<?php

/**
 * trendr - Users Messages
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<div class="contour-select no-ajax" id="contour-box" role="navigation">
	<ul>

		<?php trs_get_options_nav(); ?>

	</ul>
</div><!-- .contour-select -->

<?php

	if ( trs_is_current_action( 'compose' ) ) :
		locate_template( array( 'members/single/messages/compose.php' ), true );

	elseif ( trs_is_current_action( 'view' ) ) :
		locate_template( array( 'members/single/messages/single.php' ), true );

	else :
		do_action( 'trs_before_member_messages_content' ); ?>

	<div class="messages" role="main">

		<?php
			if ( trs_is_current_action( 'notices' ) )
				locate_template( array( 'members/single/messages/notices-loop.php' ), true );
			else
				locate_template( array( 'members/single/messages/messages-loop.php' ), true );
		?>

	</div><!-- .messages -->

	<?php do_action( 'trs_after_member_messages_content' ); ?>

<?php endif; ?>
