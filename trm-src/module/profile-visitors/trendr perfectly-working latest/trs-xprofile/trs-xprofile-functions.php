<?php
/********************************************************************************
 * Business Functions
 *
 * Business functions are where all the magic happens in trendr. They will
 * handle the actual saving or manipulation of information. Usually they will
 * hand off to a database class for data access, then return
 * true or false on success or failure.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*** Field Group Management **************************************************/

function xprofile_insert_field_group( $args = '' ) {
	$defaults = array(
		'field_group_id' => false,
		'name'           => false,
		'description'    => '',
		'can_delete'     => true
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( !$name )
		return false;

	$field_group              = new TRS_XProfile_Group( $field_group_id );
	$field_group->name        = $name;
	$field_group->description = $description;
	$field_group->can_delete  = $can_delete;

	return $field_group->save();
}

function xprofile_get_field_group( $field_group_id ) {
	$field_group = new TRS_XProfile_Group( $field_group_id );

	if ( empty( $field_group->id ) )
		return false;

	return $field_group;
}

function xprofile_delete_field_group( $field_group_id ) {
	$field_group = new TRS_XProfile_Group( $field_group_id );
	return $field_group->delete();
}

function xprofile_update_field_group_position( $field_group_id, $position ) {
	return TRS_XProfile_Group::update_position( $field_group_id, $position );
}


/*** Field Management *********************************************************/

function xprofile_insert_field( $args = '' ) {
	global $trs;

	extract( $args );

	/**
	 * Possible parameters (pass as assoc array):
	 *	'field_id'
	 *	'field_group_id'
	 *	'parent_id'
	 *	'type'
	 *	'name'
	 *	'description'
	 *	'is_required'
	 *	'can_delete'
	 *	'field_order'
	 *	'order_by'
	 *	'is_default_option'
	 *	'option_order'
	 */

	// Check we have the minimum details
	if ( !$field_group_id )
		return false;

	// Check this is a valid field type
	if ( !in_array( $type, (array) $trs->profile->field_types ) )
		return false;

	// Instantiate a new field object
	if ( $field_id )
		$field = new TRS_XProfile_Field( $field_id );
	else
		$field = new TRS_XProfile_Field;

	$field->group_id = $field_group_id;

	if ( !empty( $parent_id ) )
		$field->parent_id = $parent_id;

	if ( !empty( $type ) )
		$field->type = $type;

	if ( !empty( $name ) )
		$field->name = $name;

	if ( !empty( $description ) )
		$field->description = $description;

	if ( !empty( $is_required ) )
		$field->is_required = $is_required;

	if ( !empty( $can_delete ) )
		$field->can_delete = $can_delete;

	if ( !empty( $field_order ) )
		$field->field_order = $field_order;

	if ( !empty( $order_by ) )
		$field->order_by = $order_by;

	if ( !empty( $is_default_option ) )
		$field->is_default_option = $is_default_option;

	if ( !empty( $option_order ) )
		$field->option_order = $option_order;

	return $field->save();
}

function xprofile_get_field( $field_id ) {
	return new TRS_XProfile_Field( $field_id );
}

function xprofile_delete_field( $field_id ) {
	$field = new TRS_XProfile_Field( $field_id );
	return $field->delete();
}


/*** Field Data Management *****************************************************/

/**
 * Fetches profile data for a specific field for the user.
 *
 * When the field value is serialized, this function unserializes and filters each item in the array
 * that results.
 *
 * @package trendr Core
 * @param mixed $field The ID of the field, or the $name of the field.
 * @param int $user_id The ID of the user
 * @global object $trs Global trendr settings object
 * @uses TRS_XProfile_ProfileData::get_value_byid() Fetches the value based on the params passed.
 * @return mixed The profile field data.
 */
function xprofile_get_field_data( $field, $user_id = 0 ) {
	global $trs;

	if ( empty( $user_id ) )
		$user_id = $trs->displayed_user->id;

	if ( empty( $user_id ) )
		return false;

	if ( is_numeric( $field ) )
		$field_id = $field;
	else
		$field_id = xprofile_get_field_id_from_name( $field );

	if ( empty( $field_id ) )
		return false;

	$values = maybe_unserialize( TRS_XProfile_ProfileData::get_value_byid( $field_id, $user_id ) );

	if ( is_array( $values ) ) {
		$data = array();
		foreach( (array)$values as $value ) {
			$data[] = apply_filters( 'xprofile_get_field_data', $value, $field_id, $user_id );
		}
	} else {
		$data = apply_filters( 'xprofile_get_field_data', $values, $field_id, $user_id );
	}

	return $data;
}

/**
 * A simple function to set profile data for a specific field for a specific user.
 *
 * @package trendr Core
 * @param $field The ID of the field, or the $name of the field.
 * @param $user_id The ID of the user
 * @param $value The value for the field you want to set for the user.
 * @global object $trs Global trendr settings object
 * @uses xprofile_get_field_id_from_name() Gets the ID for the field based on the name.
 * @return true on success, false on failure.
 */
function xprofile_set_field_data( $field, $user_id, $value, $is_required = false ) {

	if ( is_numeric( $field ) )
		$field_id = $field;
	else
		$field_id = xprofile_get_field_id_from_name( $field );

	if ( empty( $field_id ) )
		return false;

	if ( $is_required && ( empty( $value ) || !is_array( $value ) && !strlen( trim( $value ) ) ) )
		return false;

	$field = new TRS_XProfile_Field( $field_id );

	// If the value is empty, then delete any field data that exists, unless the field is of a
	// type where null values are semantically meaningful
	if ( empty( $value ) && 'checkbox' != $field->type && 'multiselectbox' != $field->type ) {
		xprofile_delete_field_data( $field_id, $user_id );
		return true;
	}

	// Check the value is an acceptable value
	if ( 'checkbox' == $field->type || 'radio' == $field->type || 'selectbox' == $field->type || 'multiselectbox' == $field->type ) {
		$options = $field->get_children();

		foreach( $options as $option )
			$possible_values[] = $option->name;

		if ( is_array( $value ) ) {
			foreach( $value as $i => $single ) {
				if ( !in_array( $single, (array)$possible_values ) ) {
					unset( $value[$i] );
				}
			}

			// Reset the keys by merging with an empty array
			$value = array_merge( array(), $value );
		} else {
			if ( !in_array( $value, (array)$possible_values ) ) {
				return false;
			}
		}
	}

	$field           = new TRS_XProfile_ProfileData();
	$field->field_id = $field_id;
	$field->user_id  = $user_id;
	$field->value    = maybe_serialize( $value );

	return $field->save();
}

function xprofile_delete_field_data( $field, $user_id ) {
	if ( is_numeric( $field ) )
		$field_id = $field;
	else
		$field_id = xprofile_get_field_id_from_name( $field );

	if ( empty( $field_id ) || empty( $user_id ) )
		return false;

	$field = new TRS_XProfile_ProfileData( $field_id, $user_id );
	return $field->delete();
}

function xprofile_check_is_required_field( $field_id ) {
	$field = new TRS_Xprofile_Field( $field_id );

	// Define locale variable(s)
	$retval = false;

	// Super admins can skip required check
	if ( is_super_admin() && !is_admin() )
		$retval = false;

	// All other users will use the field's setting
	elseif ( isset( $field->is_required ) )
		$retval = $field->is_required;

	return (bool) $retval;
}

/**
 * Returns the ID for the field based on the field name.
 *
 * @package trendr Core
 * @param $field_name The name of the field to get the ID for.
 * @return int $field_id on success, false on failure.
 */
function xprofile_get_field_id_from_name( $field_name ) {
	return TRS_Xprofile_Field::get_id_from_name( $field_name );
}

/**
 * Fetches a random piece of profile data for the user.
 *
 * @package trendr Core
 * @param $user_id User ID of the user to get random data for
 * @param $exclude_fullname whether or not to exclude the full name field as random data.
 * @global object $trs Global trendr settings object
 * @global $trmdb trendr DB access object.
 * @global $current_user trendr global variable containing current logged in user information
 * @uses xprofile_format_profile_field() Formats profile field data so it is suitable for display.
 * @return $field_data The fetched random data for the user.
 */
function xprofile_get_random_profile_data( $user_id, $exclude_fullname = true ) {
	$field_data           = TRS_XProfile_ProfileData::get_random( $user_id, $exclude_fullname );

	if ( !$field_data )
		return false;

	$field_data[0]->value = xprofile_format_profile_field( $field_data[0]->type, $field_data[0]->value );

	if ( !$field_data[0]->value || empty( $field_data[0]->value ) )
		return false;

	return apply_filters( 'xprofile_get_random_profile_data', $field_data );
}

/**
 * Formats a profile field according to its type. [ TODO: Should really be moved to filters ]
 *
 * @package trendr Core
 * @param $field_type The type of field: datebox, selectbox, textbox etc
 * @param $field_value The actual value
 * @uses trs_format_time() Formats a time value based on the trendr date format setting
 * @return $field_value The formatted value
 */
function xprofile_format_profile_field( $field_type, $field_value ) {
	if ( !isset( $field_value ) || empty( $field_value ) )
		return false;

	$field_value = trs_unserialize_profile_field( $field_value );

	if ( 'datebox' == $field_type ) {
		$field_value = trs_format_time( $field_value, true );
	} else {
		$content = $field_value;
		$field_value = str_replace( ']]>', ']]&gt;', $content );
	}

	return stripslashes_deep( $field_value );
}

function xprofile_update_field_position( $field_id, $position, $field_group_id ) {
	return TRS_XProfile_Field::update_position( $field_id, $position, $field_group_id );
}

/**
 * Setup the portrait upload directory for a user.
 *
 * @package trendr Core
 * @param $directory The root directory name
 * @param $user_id The user ID.
 * @return array() containing the path and URL plus some other settings.
 */
function xprofile_portrait_upload_dir( $directory = false, $user_id = 0 ) {
	global $trs;

	if ( empty( $user_id ) )
		$user_id = $trs->displayed_user->id;

	if ( empty( $directory ) )
		$directory = 'portraits';

	$path    = trs_core_portrait_upload_path() . '/portraits/' . $user_id;
	$newbdir = $path;

	if ( !file_exists( $path ) )
		@trm_mkdir_p( $path );

	$newurl    = trs_core_portrait_url() . '/portraits/' . $user_id;
	$newburl   = $newurl;
	$newsubdir = '/portraits/' . $user_id;

	return apply_filters( 'xprofile_portrait_upload_dir', array(
		'path'    => $path,
		'url'     => $newurl,
		'subdir'  => $newsubdir,
		'basedir' => $newbdir,
		'baseurl' => $newburl,
		'error'   => false
	) );
}

/**
 * Syncs Xprofile data to the standard built in trendr profile data.
 *
 * @package trendr Core
 */
function xprofile_sync_trm_profile( $user_id = 0 ) {
	global $trs, $trmdb;

	if ( !empty( $trs->site_options['trs-disable-profile-sync'] ) && (int)$trs->site_options['trs-disable-profile-sync'] )
		return true;

	if ( empty( $user_id ) )
		$user_id = $trs->loggedin_user->id;

	if ( empty( $user_id ) )
		return false;

	$fullname = xprofile_get_field_data( trs_xprofile_fullname_field_name(), $user_id );
	$space    = strpos( $fullname, ' ' );

	if ( false === $space ) {
		$firstname = $fullname;
		$lastname = '';
	} else {
		$firstname = substr( $fullname, 0, $space );
		$lastname = trim( substr( $fullname, $space, strlen( $fullname ) ) );
	}

	update_user_meta( $user_id, 'nickname',   $fullname  );
	update_user_meta( $user_id, 'first_name', $firstname );
	update_user_meta( $user_id, 'last_name',  $lastname  );

	$trmdb->query( $trmdb->prepare( "UPDATE {$trmdb->users} SET display_name = %s WHERE ID = %d", $fullname, $user_id ) );
}
add_action( 'xprofile_updated_profile', 'xprofile_sync_trm_profile' );
add_action( 'trs_core_signup_user', 'xprofile_sync_trm_profile' );


/**
 * Syncs the standard built in trendr profile data to XProfile.
 *
 * @since 1.2.4
 * @package trendr Core
 */
function xprofile_sync_trs_profile( &$errors, $update, &$user ) {
	global $trs;

	if ( ( !empty( $trs->site_options['trs-disable-profile-sync'] ) && (int)$trs->site_options['trs-disable-profile-sync'] ) || !$update || $errors->get_error_codes() )
		return;

	xprofile_set_field_data( trs_xprofile_fullname_field_name(), $user->ID, $user->display_name );
}
add_action( 'user_profile_update_errors', 'xprofile_sync_trs_profile', 10, 3 );


/**
 * When a user is deleted, we need to clean up the database and remove all the
 * profile data from each table. Also we need to clean anything up in the usermeta table
 * that this component uses.
 *
 * @package trendr XProfile
 * @param $user_id The ID of the deleted user
 * @uses get_user_meta() Get a user meta value based on meta key from trm_usermeta
 * @uses delete_user_meta() Delete user meta value based on meta key from trm_usermeta
 * @uses delete_data_for_user() Removes all profile data from the xprofile tables for the user
 */
function xprofile_remove_data( $user_id ) {
	TRS_XProfile_ProfileData::delete_data_for_user( $user_id );

	// delete any portrait files.
	@unlink( get_user_meta( $user_id, 'trs_core_portrait_v1_path', true ) );
	@unlink( get_user_meta( $user_id, 'trs_core_portrait_v2_path', true ) );

	// unset the usermeta for portraits from the usermeta table.
	delete_user_meta( $user_id, 'trs_core_portrait_v1'      );
	delete_user_meta( $user_id, 'trs_core_portrait_v1_path' );
	delete_user_meta( $user_id, 'trs_core_portrait_v2'      );
	delete_user_meta( $user_id, 'trs_core_portrait_v2_path' );
}
add_action( 'trmmu_delete_user',  'xprofile_remove_data' );
add_action( 'delete_user',       'xprofile_remove_data' );
add_action( 'trs_make_spam_user', 'xprofile_remove_data' );

/*** XProfile Meta ****************************************************/

function trs_xprofile_delete_meta( $object_id, $object_type, $meta_key = false, $meta_value = false ) {
	global $trmdb, $trs;

	$object_id = (int) $object_id;

	if ( !$object_id )
		return false;

	if ( !isset( $object_type ) )
		return false;

	if ( !in_array( $object_type, array( 'group', 'field', 'data' ) ) )
		return false;

	$meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );

	if ( is_array( $meta_value ) || is_object( $meta_value ) )
		$meta_value = serialize( $meta_value );

	$meta_value = trim( $meta_value );

	if ( !$meta_key )
		$trmdb->query( $trmdb->prepare( "DELETE FROM " . $trs->profile->table_name_meta . " WHERE object_id = %d AND object_type = %s", $object_id, $object_type ) );
	else if ( $meta_value )
		$trmdb->query( $trmdb->prepare( "DELETE FROM " . $trs->profile->table_name_meta . " WHERE object_id = %d AND object_type = %s AND meta_key = %s AND meta_value = %s", $object_id, $object_type, $meta_key, $meta_value ) );
	else
		$trmdb->query( $trmdb->prepare( "DELETE FROM " . $trs->profile->table_name_meta . " WHERE object_id = %d AND object_type = %s AND meta_key = %s", $object_id, $object_type, $meta_key ) );

	// Delete the cached object
	trm_cache_delete( 'trs_xprofile_meta_' . $object_type . '_' . $object_id . '_' . $meta_key, 'trs' );

	return true;
}

function trs_xprofile_get_meta( $object_id, $object_type, $meta_key = '') {
	global $trmdb, $trs;

	$object_id = (int) $object_id;

	if ( !$object_id )
		return false;

	if ( !isset( $object_type ) )
		return false;

	if ( !in_array( $object_type, array( 'group', 'field', 'data' ) ) )
		return false;

	if ( !empty( $meta_key ) ) {
		$meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );

		if ( !$metas = trm_cache_get( 'trs_xprofile_meta_' . $object_type . '_' . $object_id . '_' . $meta_key, 'trs' ) ) {
			$metas = $trmdb->get_col( $trmdb->prepare( "SELECT meta_value FROM " . $trs->profile->table_name_meta . " WHERE object_id = %d AND object_type = %s AND meta_key = %s", $object_id, $object_type, $meta_key ) );
			trm_cache_set( 'trs_xprofile_meta_' . $object_type . '_' . $object_id . '_' . $meta_key, $metas, 'trs' );
		}
	} else {
		$metas = $trmdb->get_col( $trmdb->prepare( "SELECT meta_value FROM " . $trs->profile->table_name_meta . " WHERE object_id = %d AND object_type = %s", $object_id, $object_type ) );
	}

	if ( empty( $metas ) ) {
		if ( empty( $meta_key ) ) {
			return array();
		} else {
			return '';
		}
	}

	$metas = array_map( 'maybe_unserialize', (array)$metas );

	if ( 1 == count( $metas ) )
		return $metas[0];
	else
		return $metas;
}

function trs_xprofile_update_meta( $object_id, $object_type, $meta_key, $meta_value ) {
	global $trmdb, $trs;

	$object_id = (int) $object_id;

	if ( empty( $object_id ) )
		return false;

	if ( !isset( $object_type ) )
		return false;

	if ( !in_array( $object_type, array( 'group', 'field', 'data' ) ) )
		return false;

	$meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );

	if ( is_string( $meta_value ) )
		$meta_value = stripslashes( $trmdb->escape( $meta_value ) );

	$meta_value = maybe_serialize( $meta_value );

	if ( empty( $meta_value ) )
		return trs_xprofile_delete_meta( $object_id, $object_type, $meta_key );

	$cur = $trmdb->get_row( $trmdb->prepare( "SELECT * FROM " . $trs->profile->table_name_meta . " WHERE object_id = %d AND object_type = %s AND meta_key = %s", $object_id, $object_type, $meta_key ) );

	if ( empty( $cur ) )
		$trmdb->query( $trmdb->prepare( "INSERT INTO " . $trs->profile->table_name_meta . " ( object_id, object_type, meta_key, meta_value ) VALUES ( %d, %s, %s, %s )", $object_id, $object_type,  $meta_key, $meta_value ) );
	else if ( $cur->meta_value != $meta_value )
		$trmdb->query( $trmdb->prepare( "UPDATE " . $trs->profile->table_name_meta . " SET meta_value = %s WHERE object_id = %d AND object_type = %s AND meta_key = %s", $meta_value, $object_id, $object_type, $meta_key ) );
	else
		return false;

	// Update the cached object and recache
	trm_cache_set( 'trs_xprofile_meta_' . $object_type . '_' . $object_id . '_' . $meta_key, $meta_value, 'trs' );

	return true;
}

function trs_xprofile_update_fieldgroup_meta( $field_group_id, $meta_key, $meta_value ) {
	return trs_xprofile_update_meta( $field_group_id, 'group', $meta_key, $meta_value );
}

function trs_xprofile_update_field_meta( $field_id, $meta_key, $meta_value ) {
	return trs_xprofile_update_meta( $field_id, 'field', $meta_key, $meta_value );
}

function trs_xprofile_update_fielddata_meta( $field_data_id, $meta_key, $meta_value ) {
	return trs_xprofile_update_meta( $field_data_id, 'data', $meta_key, $meta_value );
}

/**
 * Return the field name for the Full Name xprofile field
 *
 * @package trendr
 * @since 1.5
 *
 * @return str The field name
 */
function trs_xprofile_fullname_field_name() {
	return apply_filters( 'trs_xprofile_fullname_field_name', TRS_XPROFILE_FULLNAME_FIELD_NAME );
}

?>