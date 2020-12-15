<?php

/**
 * trendr - Users Profile
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


<?php do_action( 'trs_before_profile_content' ); ?>

<div class="profile" role="main">

	<?php
		// Profile Edit
			locate_template( array( 'members/single/profile/edit.php' ), true );

		// Change Avatar
			locate_template( array( 'members/single/profile/change-profile-photo.php' ), true );

		// Display XProfile
			locate_template( array( 'members/single/profile/profile-loop.php' ), true );

		// Display trendr profile (fallback)
			locate_template( array( 'members/single/profile/profile-trm.php' ), true );
	?>

</div><!-- .profile -->

<?php do_action( 'trs_after_profile_content' ); ?>