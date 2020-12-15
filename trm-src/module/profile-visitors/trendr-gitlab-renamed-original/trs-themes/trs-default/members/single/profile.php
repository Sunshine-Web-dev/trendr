<?php

/**
 * trendr - Users Profile
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php if ( trs_is_my_profile() ) : ?>

	<div class="contour-select no-ajax" id="subnav" role="navigation">
		<ul>

			<?php trs_get_options_nav(); ?>

		</ul>
	</div><!-- .contour-select -->

<?php endif; ?>

<?php do_action( 'trs_before_profile_content' ); ?>

<div class="profile" role="main">

	<?php
		// Profile Edit
		if ( trs_is_current_action( 'edit' ) )
			locate_template( array( 'members/single/profile/edit.php' ), true );

		// Change Avatar
		elseif ( trs_is_current_action( 'change-portrait' ) )
			locate_template( array( 'members/single/profile/change-portrait.php' ), true );

		// Display XProfile
		elseif ( trs_is_active( 'xprofile' ) )
			locate_template( array( 'members/single/profile/profile-loop.php' ), true );

		// Display WordPress profile (fallback)
		else
			locate_template( array( 'members/single/profile/profile-trm.php' ), true );
	?>

</div><!-- .profile -->

<?php do_action( 'trs_after_profile_content' ); ?>