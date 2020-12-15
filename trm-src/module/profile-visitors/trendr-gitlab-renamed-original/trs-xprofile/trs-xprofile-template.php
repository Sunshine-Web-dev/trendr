<?php
/***************************************************************************
 * XProfile Data Display Template Tags
 **/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

Class TRS_XProfile_Data_Template {
	var $current_group = -1;
	var $group_count;
	var $groups;
	var $group;

	var $current_field = -1;
	var $field_count;
	var $field_has_data;
	var $field;

	var $in_the_loop;
	var $user_id;

	function trs_xprofile_data_template( $user_id, $profile_group_id, $hide_empty_groups = false, $fetch_fields = false, $fetch_field_data = false, $exclude_groups = false, $exclude_fields = false, $hide_empty_fields = false ) {
		$this->__construct( $user_id, $profile_group_id, $hide_empty_groups, $fetch_fields, $fetch_field_data, $exclude_groups, $exclude_fields, $hide_empty_fields );
	}

	function __construct( $user_id, $profile_group_id, $hide_empty_groups = false, $fetch_fields = false, $fetch_field_data = false, $exclude_groups = false, $exclude_fields = false, $hide_empty_fields = false ) {
		$this->groups = TRS_XProfile_Group::get( array(
			'profile_group_id'  => $profile_group_id,
			'user_id'           => $user_id,
			'hide_empty_groups' => $hide_empty_groups,
			'hide_empty_fields' => $hide_empty_fields,
			'fetch_fields'      => $fetch_fields,
			'fetch_field_data'  => $fetch_field_data,
			'exclude_groups'    => $exclude_groups,
			'exclude_fields'    => $exclude_fields
		) );

		$this->group_count = count($this->groups);
		$this->user_id = $user_id;
	}

	function has_groups() {
		if ( $this->group_count )
			return true;

		return false;
	}

	function next_group() {
		$this->current_group++;

		$this->group         = $this->groups[$this->current_group];
		$this->group->fields = apply_filters( 'xprofile_group_fields', $this->group->fields, $this->group->id );
		$this->field_count   = count( $this->group->fields );

		return $this->group;
	}

	function rewind_groups() {
		$this->current_group = -1;
		if ( $this->group_count > 0 ) {
			$this->group = $this->groups[0];
		}
	}

	function profile_groups() {
		if ( $this->current_group + 1 < $this->group_count ) {
			return true;
		} elseif ( $this->current_group + 1 == $this->group_count ) {
			do_action('xprofile_template_loop_end');
			// Do some cleaning up after the loop
			$this->rewind_groups();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_profile_group() {
		global $group;

		$this->in_the_loop = true;
		$group = $this->next_group();

		if ( 0 == $this->current_group ) // loop has just started
			do_action('xprofile_template_loop_start');
	}

	/**** FIELDS ****/

	function next_field() {
		$this->current_field++;

		$this->field = $this->group->fields[$this->current_field];
		return $this->field;
	}

	function rewind_fields() {
		$this->current_field = -1;
		if ( $this->field_count > 0 ) {
			$this->field = $this->group->fields[0];
		}
	}

	function has_fields() {
		$has_data = false;

		for ( $i = 0, $count = count( $this->group->fields ); $i < $count; ++$i ) {
			$field = &$this->group->fields[$i];

			if ( !empty( $field->data ) && $field->data->value != null ) {
				$has_data = true;
			}
		}

		if ( $has_data )
			return true;

		return false;
	}

	function profile_fields() {
		if ( $this->current_field + 1 < $this->field_count ) {
			return true;
		} elseif ( $this->current_field + 1 == $this->field_count ) {
			// Do some cleaning up after the loop
			$this->rewind_fields();
		}

		return false;
	}

	function the_profile_field() {
		global $field;

		$field = $this->next_field();

		$value = !empty( $field->data ) && !empty( $field->data->value ) ? maybe_unserialize( $field->data->value ) : false;

		if ( !empty( $value ) ) {
			$this->field_has_data = true;
		} else {
			$this->field_has_data = false;
		}
	}
}

function xprofile_get_profile() {
	locate_template( array( 'profile/profile-loop.php'), true );
}

function trs_has_profile( $args = '' ) {
	global $trs, $profile_template;

	// Only show empty fields if we're on the Dashboard, or we're on a user's profile edit page,
	// or this is a registration page
	$hide_empty_fields_default = ( !is_network_admin() && !is_admin() && !trs_is_user_profile_edit() && !trs_is_register_page() );

	$defaults = array(
		'user_id'           => $trs->displayed_user->id,
		'profile_group_id'  => false,
		'hide_empty_groups' => true,
		'hide_empty_fields' => $hide_empty_fields_default,
		'fetch_fields'      => true,
		'fetch_field_data'  => true,
		'exclude_groups'    => false, // Comma-separated list of profile field group IDs to exclude
		'exclude_fields'    => false  // Comma-separated list of profile field IDs to exclude
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$profile_template = new TRS_XProfile_Data_Template( $user_id, $profile_group_id, $hide_empty_groups, $fetch_fields, $fetch_field_data, $exclude_groups, $exclude_fields, $hide_empty_fields );
	return apply_filters( 'trs_has_profile', $profile_template->has_groups(), $profile_template );
}

function trs_profile_groups() {
	global $profile_template;
	return $profile_template->profile_groups();
}

function trs_the_profile_group() {
	global $profile_template;
	return $profile_template->the_profile_group();
}

function trs_profile_group_has_fields() {
	global $profile_template;
	return $profile_template->has_fields();
}

function trs_field_css_class( $class = false ) {
	echo trs_get_field_css_class( $class );
}
	function trs_get_field_css_class( $class = false ) {
		global $profile_template;

		$css_classes = array();

		if ( $class )
			$css_classes[] = sanitize_title( esc_attr( $class ) );

		// Set a class with the field ID
		$css_classes[] = 'field_' . $profile_template->field->id;

		// Set a class with the field name (sanitized)
		$css_classes[] = 'field_' . sanitize_title( $profile_template->field->name );

		if ( $profile_template->current_field % 2 == 1 )
			$css_classes[] = 'alt';

		$css_classes = apply_filters_ref_array( 'trs_field_css_classes', array( &$css_classes ) );

		return apply_filters( 'trs_get_field_css_class', ' class="' . implode( ' ', $css_classes ) . '"' );
	}

function trs_field_has_data() {
	global $profile_template;
	return $profile_template->field_has_data;
}

function trs_field_has_public_data() {
	global $profile_template;

	if ( $profile_template->field_has_data )
		return true;

	return false;
}

function trs_the_profile_group_id() {
	echo trs_get_the_profile_group_id();
}
	function trs_get_the_profile_group_id() {
		global $group;
		return apply_filters( 'trs_get_the_profile_group_id', $group->id );
	}

function trs_the_profile_group_name() {
	echo trs_get_the_profile_group_name();
}
	function trs_get_the_profile_group_name() {
		global $group;
		return apply_filters( 'trs_get_the_profile_group_name', $group->name );
	}

function trs_the_profile_group_slug() {
	echo trs_get_the_profile_group_slug();
}
	function trs_get_the_profile_group_slug() {
		global $group;
		return apply_filters( 'trs_get_the_profile_group_slug', sanitize_title( $group->name ) );
	}

function trs_the_profile_group_description() {
	echo trs_get_the_profile_group_description();
}
	function trs_get_the_profile_group_description() {
		global $group;
		echo apply_filters( 'trs_get_the_profile_group_description', $group->description );
	}

function trs_the_profile_group_edit_form_action() {
	echo trs_get_the_profile_group_edit_form_action();
}
	function trs_get_the_profile_group_edit_form_action() {
		global $trs, $group;

		return apply_filters( 'trs_get_the_profile_group_edit_form_action', trailingslashit( $trs->displayed_user->domain . $trs->profile->slug . '/edit/group/' . $group->id ) );
	}

function trs_the_profile_group_field_ids() {
	echo trs_get_the_profile_group_field_ids();
}
	function trs_get_the_profile_group_field_ids() {
		global $group;

		$field_ids = '';
		foreach ( (array) $group->fields as $field )
			$field_ids .= $field->id . ',';

		return substr( $field_ids, 0, -1 );
	}

function trs_profile_fields() {
	global $profile_template;
	return $profile_template->profile_fields();
}

function trs_the_profile_field() {
	global $profile_template;
	return $profile_template->the_profile_field();
}

function trs_the_profile_field_id() {
	echo trs_get_the_profile_field_id();
}
	function trs_get_the_profile_field_id() {
		global $field;
		return apply_filters( 'trs_get_the_profile_field_id', $field->id );
	}

function trs_the_profile_field_name() {
	echo trs_get_the_profile_field_name();
}
	function trs_get_the_profile_field_name() {
		global $field;

		return apply_filters( 'trs_get_the_profile_field_name', $field->name );
	}

function trs_the_profile_field_value() {
	echo trs_get_the_profile_field_value();
}
	function trs_get_the_profile_field_value() {
		global $field;

		$field->data->value = trs_unserialize_profile_field( $field->data->value );

		return apply_filters( 'trs_get_the_profile_field_value', $field->data->value, $field->type, $field->id );
	}

function trs_the_profile_field_edit_value() {
	echo trs_get_the_profile_field_edit_value();
}
	function trs_get_the_profile_field_edit_value() {
		global $field;

		/**
		 * Check to see if the posted value is different, if it is re-display this
		 * value as long as it's not empty and a required field.
		 */
		if ( !isset( $field->data->value ) )
			$field->data->value = '';

		if ( isset( $_POST['field_' . $field->id] ) && $field->data->value != $_POST['field_' . $field->id] ) {
			if ( !empty( $_POST['field_' . $field->id] ) )
				$field->data->value = $_POST['field_' . $field->id];
			else
				$field->data->value = '';
		}

		$field_value = isset( $field->data->value ) ? trs_unserialize_profile_field( $field->data->value ) : '';

		return apply_filters( 'trs_get_the_profile_field_edit_value', $field_value, $field->type, $field->id );
	}

function trs_the_profile_field_type() {
	echo trs_get_the_profile_field_type();
}
	function trs_get_the_profile_field_type() {
		global $field;

		return apply_filters( 'trs_the_profile_field_type', $field->type );
	}

function trs_the_profile_field_description() {
	echo trs_get_the_profile_field_description();
}
	function trs_get_the_profile_field_description() {
		global $field;

		return apply_filters( 'trs_get_the_profile_field_description', $field->description );
	}

function trs_the_profile_field_input_name() {
	echo trs_get_the_profile_field_input_name();
}
	function trs_get_the_profile_field_input_name() {
		global $field;

		$array_box = false;
		if ( 'multiselectbox' == $field->type )
			$array_box = '[]';

		return apply_filters( 'trs_get_the_profile_field_input_name', 'field_' . $field->id . $array_box );
	}

/**
 * trs_the_profile_field_options()
 *
 * Displays field options HTML for field types of 'selectbox', 'multiselectbox',
 * 'radio', 'checkbox', and 'datebox'.
 *
 * @package trendr Xprofile
 * @since 1.1
 *
 * @uses trs_get_the_profile_field_options()
 *
 * @param array $args Specify type for datebox. Allowed 'day', 'month', 'year'.
 */
function trs_the_profile_field_options( $args = '' ) {
	echo trs_get_the_profile_field_options( $args );
}
	/**
	 * trs_get_the_profile_field_options()
	 *
	 * Retrieves field options HTML for field types of 'selectbox', 'multiselectbox',
	 * 'radio', 'checkbox', and 'datebox'.
	 *
	 * @package trendr Xprofile
	 * @since 1.1
	 *
	 * @uses TRS_XProfile_Field::get_children()
	 * @uses TRS_XProfile_ProfileData::get_value_byid()
	 *
	 * @param array $args Specify type for datebox. Allowed 'day', 'month', 'year'.
	 */
	function trs_get_the_profile_field_options( $args = '' ) {
		global $field;

		$defaults = array(
			'type' => false
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if ( !method_exists( $field, 'get_children' ) )
			$field = new TRS_XProfile_Field( $field->id );

		$options = $field->get_children();

		// Setup some defaults
		$html     = '';
		$selected = '';

		switch ( $field->type ) {
			case 'selectbox':
				if ( !$field->is_required )
					$html .= '<option value="">' . /* translators: no option picked in select box */ __( '----', 'trendr' ) . '</option>';

				$original_option_values = '';
				$original_option_values = maybe_unserialize( TRS_XProfile_ProfileData::get_value_byid( $field->id ) );

				if ( empty( $original_option_values ) && !empty( $_POST['field_' . $field->id] ) )
					$original_option_values = $_POST['field_' . $field->id];

				$option_values = (array) $original_option_values;

				for ( $k = 0, $count = count( $options ); $k < $count; ++$k ) {
					// Check for updated posted values, but errors preventing them from being saved first time
					foreach( $option_values as $i => $option_value ) {
						if ( isset( $_POST['field_' . $field->id] ) && $_POST['field_' . $field->id] != $option_value ) {
							if ( !empty( $_POST['field_' . $field->id] ) )
								$option_values[$i] = $_POST['field_' . $field->id];
						}
					}
					$selected = '';

					// Run the allowed option name through the before_save filter, so we'll be sure to get a match
					$allowed_options = xprofile_sanitize_data_value_before_save( $options[$k]->name, false, false );

					// First, check to see whether the user-entered value matches
					if ( in_array( $allowed_options, (array) $option_values ) )
						$selected = ' selected="selected"';

					// Then, if the user has not provided a value, check for defaults
					if ( !is_array( $original_option_values ) && empty( $option_values ) && $options[$k]->is_default_option )
						$selected = ' selected="selected"';

					$html .= apply_filters( 'trs_get_the_profile_field_options_select', '<option' . $selected . ' value="' . esc_attr( stripslashes( $options[$k]->name ) ) . '">' . esc_attr( stripslashes( $options[$k]->name ) ) . '</option>', $options[$k], $field->id, $selected, $k );
				}
				break;

			case 'multiselectbox':
				$original_option_values = '';
				$original_option_values = maybe_unserialize( TRS_XProfile_ProfileData::get_value_byid( $field->id ) );

				if ( empty( $original_option_values ) && !empty( $_POST['field_' . $field->id] ) )
					$original_option_values = $_POST['field_' . $field->id];

				$option_values = (array) $original_option_values;

				for ( $k = 0, $count = count( $options ); $k < $count; ++$k ) {
					// Check for updated posted values, but errors preventing them from being saved first time
					foreach( $option_values as $i => $option_value ) {
						if ( isset( $_POST['field_' . $field->id] ) && $_POST['field_' . $field->id][$i] != $option_value ) {
							if ( !empty( $_POST['field_' . $field->id][$i] ) )
								$option_values[] = $_POST['field_' . $field->id][$i];
						}
					}
					$selected = '';

					// Run the allowed option name through the before_save filter, so we'll be sure to get a match
					$allowed_options = xprofile_sanitize_data_value_before_save( $options[$k]->name, false, false );

					// First, check to see whether the user-entered value matches
					if ( in_array( $allowed_options, (array) $option_values ) )
						$selected = ' selected="selected"';

					// Then, if the user has not provided a value, check for defaults
					if ( !is_array( $original_option_values ) && empty( $option_values ) && $options[$k]->is_default_option )
						$selected = ' selected="selected"';

					$html .= apply_filters( 'trs_get_the_profile_field_options_multiselect', '<option' . $selected . ' value="' . esc_attr( stripslashes( $options[$k]->name ) ) . '">' . esc_attr( stripslashes( $options[$k]->name ) ) . '</option>', $options[$k], $field->id, $selected, $k );
				}
				break;

			case 'radio':
				$html .= '<div id="field_' . $field->id . '">';
				$option_value = TRS_XProfile_ProfileData::get_value_byid( $field->id );

				for ( $k = 0, $count = count( $options ); $k < $count; ++$k ) {
					// Check for updated posted values, but errors preventing them from being saved first time
					if ( isset( $_POST['field_' . $field->id] ) && $option_value != $_POST['field_' . $field->id] ) {
						if ( !empty( $_POST['field_' . $field->id] ) )
							$option_value = $_POST['field_' . $field->id];
					}

					// Run the allowed option name through the before_save
					// filter, so we'll be sure to get a match
					$allowed_options = xprofile_sanitize_data_value_before_save( $options[$k]->name, false, false );

					$selected = '';
					if ( $option_value == $allowed_options || !empty( $value ) && $value == $allowed_options || ( empty( $option_value ) && $options[$k]->is_default_option ) )
						$selected = ' checked="checked"';

					$html .= apply_filters( 'trs_get_the_profile_field_options_radio', '<label><input' . $selected . ' type="radio" name="field_' . $field->id . '" id="option_' . $options[$k]->id . '" value="' . esc_attr( stripslashes( $options[$k]->name ) ) . '"> ' . esc_attr( stripslashes( $options[$k]->name ) ) . '</label>', $options[$k], $field->id, $selected, $k );
				}

				$html .= '</div>';
				break;

			case 'checkbox':
				$option_values = TRS_XProfile_ProfileData::get_value_byid( $field->id );
				$option_values = maybe_unserialize( $option_values );

				// Check for updated posted values, but errors preventing them from being saved first time
				if ( isset( $_POST['field_' . $field->id] ) && $option_values != maybe_serialize( $_POST['field_' . $field->id] ) ) {
					if ( !empty( $_POST['field_' . $field->id] ) )
						$option_values = $_POST['field_' . $field->id];
				}

				for ( $k = 0, $count = count( $options ); $k < $count; ++$k ) {
					$selected = '';

					// First, check to see whether the user's saved values
					// match the option
					for ( $j = 0, $count_values = count( $option_values ); $j < $count_values; ++$j ) {

						// Run the allowed option name through the
						// before_save filter, so we'll be sure to get a match
						$allowed_options = xprofile_sanitize_data_value_before_save( $options[$k]->name, false, false );

						if ( $option_values[$j] == $allowed_options || @in_array( $allowed_options, $value ) ) {
							$selected = ' checked="checked"';
							break;
						}
					}

					// If the user has not yet supplied a value for this field,
					// check to see whether there is a default value available
					if ( !is_array( $option_values ) && empty( $option_values ) && !$selected && $options[$k]->is_default_option) {
						$selected = ' checked="checked"';
					}

					$html .= apply_filters( 'trs_get_the_profile_field_options_checkbox', '<label><input' . $selected . ' type="checkbox" name="field_' . $field->id . '[]" id="field_' . $options[$k]->id . '_' . $k . '" value="' . esc_attr( stripslashes( $options[$k]->name ) ) . '"> ' . esc_attr( stripslashes( $options[$k]->name ) ) . '</label>', $options[$k], $field->id, $selected, $k );
				}
				break;

			case 'datebox':
				$date = TRS_XProfile_ProfileData::get_value_byid( $field->id );

				// Set day, month, year defaults
				$day   = '';
				$month = '';
				$year  = '';

				if ( !empty( $date ) ) {
					// If Unix timestamp
					if ( is_numeric( $date ) ) {
						$day   = date( 'j', $date );
						$month = date( 'F', $date );
						$year  = date( 'Y', $date );

					// If MySQL timestamp
					} else {
						$day   = mysql2date( 'j', $date );
						$month = mysql2date( 'F', $date, false ); // Not localized, so that selected() works below
						$year  = mysql2date( 'Y', $date );
					}
				}

				// Check for updated posted values, but errors preventing them from being saved first time
				if ( !empty( $_POST['field_' . $field->id . '_day'] ) ) {
					if ( $day != $_POST['field_' . $field->id . '_day'] )
						$day = $_POST['field_' . $field->id . '_day'];
				}

				if ( !empty( $_POST['field_' . $field->id . '_month'] ) ) {
					if ( $month != $_POST['field_' . $field->id . '_month'] )
						$month = $_POST['field_' . $field->id . '_month'];
				}

				if ( !empty( $_POST['field_' . $field->id . '_year'] ) ) {
					if ( $year != date( "j", $_POST['field_' . $field->id . '_year'] ) )
						$year = $_POST['field_' . $field->id . '_year'];
				}

				switch ( $type ) {
					case 'day':
						$html .= '<option value=""' . selected( $day, '', false ) . '>--</option>';

						for ( $i = 1; $i < 32; ++$i ) {
							$html .= '<option value="' . $i .'"' . selected( $day, $i, false ) . '>' . $i . '</option>';
						}
						break;

					case 'month':
						$eng_months = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );

						$months = array(
							__( 'January', 'trendr' ),
							__( 'February', 'trendr' ),
							__( 'March', 'trendr' ),
							__( 'April', 'trendr' ),
							__( 'May', 'trendr' ),
							__( 'June', 'trendr' ),
							__( 'July', 'trendr' ),
							__( 'August', 'trendr' ),
							__( 'September', 'trendr' ),
							__( 'October', 'trendr' ),
							__( 'November', 'trendr' ),
							__( 'December', 'trendr' )
						);

						$html .= '<option value=""' . selected( $month, '', false ) . '>------</option>';

						for ( $i = 0; $i < 12; ++$i ) {
							$html .= '<option value="' . $eng_months[$i] . '"' . selected( $month, $eng_months[$i], false ) . '>' . $months[$i] . '</option>';
						}
						break;

					case 'year':
						$html .= '<option value=""' . selected( $year, '', false ) . '>----</option>';

						for ( $i = 2037; $i > 1901; $i-- ) {
							$html .= '<option value="' . $i .'"' . selected( $year, $i, false ) . '>' . $i . '</option>';
						}
						break;
				}

				$html = apply_filters( 'trs_get_the_profile_field_datebox', $html, $type, $day, $month, $year, $field->id, $date );

				break;
		}

		return $html;
	}

function trs_the_profile_field_is_required() {
	echo trs_get_the_profile_field_is_required();
}
	function trs_get_the_profile_field_is_required() {
		global $field;

		// Define locale variable(s)
		$retval = false;

		// Super admins can skip required check
		if ( is_super_admin() && !is_admin() )
			$retval = false;

		// All other users will use the field's setting
		elseif ( isset( $field->is_required ) )
			$retval = $field->is_required;

		return apply_filters( 'trs_get_the_profile_field_is_required', (bool) $retval );
	}

function trs_unserialize_profile_field( $value ) {
	if ( is_serialized($value) ) {
		$field_value = maybe_unserialize($value);
		$field_value = implode( ', ', $field_value );
		return $field_value;
	}

	return $value;
}

function trs_profile_field_data( $args = '' ) {
	echo trs_get_profile_field_data( $args );
}
	function trs_get_profile_field_data( $args = '' ) {
		global $trs;

		$defaults = array(
			'field'   => false, // Field name or ID.
			'user_id' => $trs->displayed_user->id
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		return apply_filters( 'trs_get_profile_field_data', xprofile_get_field_data( $field, $user_id ) );
	}

function trs_profile_group_tabs() {
	global $trs, $group_name;

	if ( !$groups = trm_cache_get( 'xprofile_groups_inc_empty', 'trs' ) ) {
		$groups = TRS_XProfile_Group::get( array( 'fetch_fields' => true ) );
		trm_cache_set( 'xprofile_groups_inc_empty', $groups, 'trs' );
	}

	if ( empty( $group_name ) )
		$group_name = trs_profile_group_name(false);

	$tabs = array();
	for ( $i = 0, $count = count( $groups ); $i < $count; ++$i ) {
		if ( $group_name == $groups[$i]->name )
			$selected = ' class="current"';
		else
			$selected = '';

		if ( !empty( $groups[$i]->fields ) ) {
			$link = $trs->displayed_user->domain . $trs->profile->slug . '/edit/group/' . $groups[$i]->id;
			$tabs[] = sprintf( '<li %1$s><a href="%2$s">%3$s</a></li>', $selected, $link, esc_html( $groups[$i]->name ) );
		}
	}

	$tabs = apply_filters( 'xprofile_filter_profile_group_tabs', $tabs, $groups, $group_name );
	foreach ( (array) $tabs as $tab )
		echo $tab;

	do_action( 'xprofile_profile_group_tabs' );
}

function trs_profile_group_name( $deprecated = true ) {
	if ( !$deprecated ) {
		return trs_get_profile_group_name();
	} else {
		echo trs_get_profile_group_name();
	}
}
	function trs_get_profile_group_name() {
		if ( !$group_id = trs_action_variable( 1 ) )
			$group_id = 1;

		if ( !is_numeric( $group_id ) )
			$group_id = 1;

		if ( !$group = trm_cache_get( 'xprofile_group_' . $group_id, 'trs' ) ) {
			$group = new TRS_XProfile_Group($group_id);
			trm_cache_set( 'xprofile_group_' . $group_id, $group, 'trs' );
		}

		return apply_filters( 'trs_get_profile_group_name', $group->name );
	}

function trs_portrait_upload_form() {
	global $trs;

	if ( !(int)$trs->site_options['trs-disable-portrait-uploads'] )
		trs_core_portrait_admin( null, $trs->loggedin_user->domain . $trs->profile->slug . '/change-portrait/', $trs->loggedin_user->domain . $trs->profile->slug . '/delete-portrait/' );
	else
		_e( 'Avatar uploads are currently disabled. Why not use a <a href="http://grportrait.com" target="_blank">grportrait</a> instead?', 'trendr' );
}

function trs_profile_last_updated() {
	global $trs;

	$last_updated = trs_get_profile_last_updated();

	if ( !$last_updated ) {
		_e( 'Profile not recently updated', 'trendr' ) . '.';
	} else {
		echo $last_updated;
	}
}
	function trs_get_profile_last_updated() {
		global $trs;

		$last_updated = trs_get_user_meta( $trs->displayed_user->id, 'profile_last_updated', true );

		if ( $last_updated )
			return apply_filters( 'trs_get_profile_last_updated', sprintf( __('Profile updated %s', 'trendr'), trs_core_time_since( strtotime( $last_updated ) ) ) );

		return false;
	}

function trs_current_profile_group_id() {
	echo trs_get_current_profile_group_id();
}
	function trs_get_current_profile_group_id() {
		if ( !$profile_group_id = trs_action_variable( 1 ) )
			$profile_group_id = 1;

		return apply_filters( 'trs_get_current_profile_group_id', $profile_group_id ); // admin/profile/edit/[group-id]
	}

function trs_portrait_delete_link() {
	echo trs_get_portrait_delete_link();
}
	function trs_get_portrait_delete_link() {
		global $trs;

		return apply_filters( 'trs_get_portrait_delete_link', trm_nonce_url( $trs->displayed_user->domain . $trs->profile->slug . '/change-portrait/delete-portrait/', 'trs_delete_portrait_link' ) );
	}

function trs_get_user_has_portrait() {
	global $trs;

	if ( !trs_core_fetch_portrait( array( 'item_id' => $trs->displayed_user->id, 'no_grav' => true ) ) )
		return false;

	return true;
}

function trs_edit_profile_button() {
	global $trs;

	trs_button( array (
		'id'                => 'edit_profile',
		'component'         => 'xprofile',
		'must_be_logged_in' => true,
		'block_self'        => true,
		'link_href'         => trailingslashit( $trs->displayed_user->domain . $trs->profile->slug . '/edit' ),
		'link_class'        => 'edit',
		'link_text'         => __( 'Edit Profile', 'trendr' ),
		'link_title'        => __( 'Edit Profile', 'trendr' ),
	) );
}
?>