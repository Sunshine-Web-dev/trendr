<?php
/*******************************************************************************
 * Screen functions are the controllers of trendr. They will execute when
 * their specific URL is caught. They will first save or manipulate data using
 * business functions, then pass on the user to a template file.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Handles the display of the profile page by loading the correct template file.
 *
 * @package trendr XProfile
 * @uses trs_core_load_template() Looks for and loads a template file within the current member theme (folder/filename)
 */
function xprofile_screen_display_profile() {
	$new = isset( $_GET['new'] ) ? $_GET['new'] : '';

	do_action( 'xprofile_screen_display_profile', $new );
	trs_core_load_template( apply_filters( 'xprofile_template_display_profile', 'members/single/home' ) );
}

/**
 * Handles the display of the profile edit page by loading the correct template file.
 * Also checks to make sure this can only be accessed for the logged in users profile.
 *
 * @package trendr XProfile
 * @uses trs_is_my_profile() Checks to make sure the current user being viewed equals the logged in user
 * @uses trs_core_load_template() Looks for and loads a template file within the current member theme (folder/filename)
 */
function xprofile_screen_edit_profile() {
	global $trs;

	if ( !trs_is_my_profile() && !is_super_admin() )
		return false;

	// Make sure a group is set.
	if ( !trs_action_variable( 1 ) )
		trs_core_redirect( trs_displayed_user_domain() . $trs->profile->slug . '/edit/group/1' );

	// Check the field group exists
	if ( !trs_is_action_variable( 'group' ) || !xprofile_get_field_group( trs_action_variable( 1 ) ) ) {
		trs_do_404();
		return;
	}

	// Check to see if any new information has been submitted
	if ( isset( $_POST['field_ids'] ) ) {

		// Check the nonce
		check_admin_referer( 'trs_xprofile_edit' );

		// Check we have field ID's
		if ( empty( $_POST['field_ids'] ) )
			trs_core_redirect( trailingslashit( $trs->displayed_user->domain . $trs->profile->slug . '/edit/group/' . trs_action_variable( 1 ) ) );

		// Explode the posted field IDs into an array so we know which
		// fields have been submitted
		$posted_field_ids = explode( ',', $_POST['field_ids'] );
		$is_required      = array();

		// Loop through the posted fields formatting any datebox values
		// then validate the field
		foreach ( (array)$posted_field_ids as $field_id ) {
			if ( !isset( $_POST['field_' . $field_id] ) ) {

				if ( !empty( $_POST['field_' . $field_id . '_day'] ) && !empty( $_POST['field_' . $field_id . '_month'] ) && !empty( $_POST['field_' . $field_id . '_year'] ) ) {
					// Concatenate the values
					$date_value =   $_POST['field_' . $field_id . '_day'] . ' ' . $_POST['field_' . $field_id . '_month'] . ' ' . $_POST['field_' . $field_id . '_year'];

					// Turn the concatenated value into a timestamp
					$_POST['field_' . $field_id] = date( 'Y-m-d H:i:s', strtotime( $date_value ) );
				}

			}

			$is_required[$field_id] = xprofile_check_is_required_field( $field_id );
			if ( $is_required[$field_id] && empty( $_POST['field_' . $field_id] ) )
				$errors = true;
		}

		// There are errors
		if ( !empty( $errors ) ) {
			trs_core_add_message( __( 'Please make sure you fill in all required fields in this profile field group before saving.', 'trendr' ), 'error' );

		// No errors
		} else {
			// Reset the errors var
			$errors = false;

			// Now we've checked for required fields, lets save the values.
			foreach ( (array)$posted_field_ids as $field_id ) {

				// Certain types of fields (checkboxes, multiselects) may come through empty. Save them as an empty array so that they don't get overwritten by the default on the next edit.
				if ( empty( $_POST['field_' . $field_id] ) )
					$value = array();
				else
					$value = $_POST['field_' . $field_id];

				if ( !xprofile_set_field_data( $field_id, $trs->displayed_user->id, $value, $is_required[$field_id] ) )
					$errors = true;
				else
					do_action( 'xprofile_profile_field_data_updated', $field_id, $value );
			}

			do_action( 'xprofile_updated_profile', $trs->displayed_user->id, $posted_field_ids, $errors );

			// Set the feedback messages
			if ( $errors )
				trs_core_add_message( __( 'There was a problem updating some of your profile information, please try again.', 'trendr' ), 'error' );
			else
				trs_core_add_message( __( 'Changes saved.', 'trendr' ) );

			// Redirect back to the edit screen to display the updates and message
			trs_core_redirect( trailingslashit( trs_displayed_user_domain() . $trs->profile->slug . '/edit/group/' . trs_action_variable( 1 ) ) );
		}
	}

	do_action( 'xprofile_screen_edit_profile' );
	trs_core_load_template( apply_filters( 'xprofile_template_edit_profile', 'members/single/home' ) );
}

/**
 * Handles the uploading and cropping of a user portrait. Displays the change portrait page.
 *
 * @package trendr XProfile
 * @uses trs_is_my_profile() Checks to make sure the current user being viewed equals the logged in user
 * @uses trs_core_load_template() Looks for and loads a template file within the current member theme (folder/filename)
 */
function xprofile_screen_change_portrait() {
	global $trs;

	if ( !trs_is_my_profile() && !is_super_admin() )
		return false;

	if ( trs_action_variables() ) {
		trs_do_404();
		return;
	}

	$trs->portrait_admin->step = 'upload-image';

	if ( !empty( $_FILES ) ) {

		// Check the nonce
		check_admin_referer( 'trs_portrait_upload' );

		// Pass the file to the portrait upload handler
		if ( trs_core_portrait_handle_upload( $_FILES, 'xprofile_portrait_upload_dir' ) ) {
			$trs->portrait_admin->step = 'crop-image';

			// Make sure we include the jQuery jCrop file for image cropping
			add_action( 'trm_print_scripts', 'trs_core_add_jquery_cropper' );
		}
	}

	// If the image cropping is done, crop the image and save a full/thumb version
	if ( isset( $_POST['portrait-crop-submit'] ) ) {

		// Check the nonce
		check_admin_referer( 'trs_portrait_cropstore' );

		if ( !trs_core_portrait_handle_crop( array( 'item_id' => $trs->displayed_user->id, 'original_file' => $_POST['image_src'], 'crop_x' => $_POST['x'], 'crop_y' => $_POST['y'], 'crop_w' => $_POST['w'], 'crop_h' => $_POST['h'] ) ) )
			trs_core_add_message( __( 'There was a problem cropping your portrait, please try uploading it again', 'trendr' ), 'error' );
		else {
			trs_core_add_message( __( 'Your new portrait was uploaded successfully!', 'trendr' ) );
			do_action( 'xprofile_portrait_uploaded' );
		}
	}

	do_action( 'xprofile_screen_change_portrait' );

	trs_core_load_template( apply_filters( 'xprofile_template_change_portrait', 'members/single/home' ) );
}

?>