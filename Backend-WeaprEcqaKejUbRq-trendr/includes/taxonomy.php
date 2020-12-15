<?php
/**
 * Trnder Taxonomy Administration API.
 *
 * @package Trnder
 * @subpackage Administration
 */

//
// Category
//

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.0.0
 *
 * @param unknown_type $cat_name
 * @return unknown
 */
function category_exists($cat_name, $parent = 0) {
	$id = term_exists($cat_name, 'category', $parent);
	if ( is_array($id) )
		$id = $id['term_id'];
	return $id;
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.0.0
 *
 * @param unknown_type $id
 * @return unknown
 */
function get_category_to_edit( $id ) {
	$category = get_category( $id, OBJECT, 'edit' );
	return $category;
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.0.0
 *
 * @param unknown_type $cat_name
 * @param unknown_type $parent
 * @return unknown
 */
function trm_create_category( $cat_name, $parent = 0 ) {
	if ( $id = category_exists($cat_name, $parent) )
		return $id;

	return trm_insert_category( array('cat_name' => $cat_name, 'category_parent' => $parent) );
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.0.0
 *
 * @param unknown_type $categories
 * @param unknown_type $post_id
 * @return unknown
 */
function trm_create_categories($categories, $post_id = '') {
	$cat_ids = array ();
	foreach ($categories as $category) {
		if ($id = category_exists($category))
			$cat_ids[] = $id;
		else
			if ($id = trm_create_category($category))
				$cat_ids[] = $id;
	}

	if ( $post_id )
		trm_set_post_categories($post_id, $cat_ids);

	return $cat_ids;
}

/**
 * Updates an existing Category or creates a new Category.
 *
 * @since 2.0.0
 *
 * @param mixed $catarr See defaults below. Set 'cat_ID' to a non-zero value to update an existing category. The 'taxonomy' key was added in 3.0.0.
 * @param bool $trm_error Optional, since 2.5.0. Set this to true if the caller handles TRM_Error return values.
 * @return int|object The ID number of the new or updated Category on success.  Zero or a TRM_Error on failure, depending on param $trm_error.
 */
function trm_insert_category($catarr, $trm_error = false) {
	$cat_defaults = array('cat_ID' => 0, 'taxonomy' => 'category', 'cat_name' => '', 'category_description' => '', 'category_nicename' => '', 'category_parent' => '');
	$catarr = trm_parse_args($catarr, $cat_defaults);
	extract($catarr, EXTR_SKIP);

	if ( trim( $cat_name ) == '' ) {
		if ( ! $trm_error )
			return 0;
		else
			return new TRM_Error( 'cat_name', __('You did not enter a category name.') );
	}

	$cat_ID = (int) $cat_ID;

	// Are we updating or creating?
	if ( !empty ($cat_ID) )
		$update = true;
	else
		$update = false;

	$name = $cat_name;
	$description = $category_description;
	$slug = $category_nicename;
	$parent = $category_parent;

	$parent = (int) $parent;
	if ( $parent < 0 )
		$parent = 0;

	if ( empty($parent) || !category_exists( $parent ) || ($cat_ID && cat_is_ancestor_of($cat_ID, $parent) ) )
		$parent = 0;

	$args = compact('name', 'slug', 'parent', 'description');

	if ( $update )
		$cat_ID = trm_update_term($cat_ID, $taxonomy, $args);
	else
		$cat_ID = trm_insert_term($cat_name, $taxonomy, $args);

	if ( is_trm_error($cat_ID) ) {
		if ( $trm_error )
			return $cat_ID;
		else
			return 0;
	}

	return $cat_ID['term_id'];
}

/**
 * Aliases trm_insert_category() with minimal args.
 *
 * If you want to update only some fields of an existing category, call this
 * function with only the new values set inside $catarr.
 *
 * @since 2.0.0
 *
 * @param array $catarr The 'cat_ID' value is required.  All other keys are optional.
 * @return int|bool The ID number of the new or updated Category on success. Zero or FALSE on failure.
 */
function trm_update_category($catarr) {
	$cat_ID = (int) $catarr['cat_ID'];

	if ( isset($catarr['category_parent']) && ($cat_ID == $catarr['category_parent']) )
		return false;

	// First, get all of the original fields
	$category = get_category($cat_ID, ARRAY_A);

	// Escape data pulled from DB.
	$category = add_magic_quotes($category);

	// Merge old and new fields with new fields overwriting old ones.
	$catarr = array_merge($category, $catarr);

	return trm_insert_category($catarr);
}

//
// Tags
//

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.3.0
 *
 * @param unknown_type $tag_name
 * @return unknown
 */
function tag_exists($tag_name) {
	return term_exists($tag_name, 'post_tag');
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.3.0
 *
 * @param unknown_type $tag_name
 * @return unknown
 */
function trm_create_tag($tag_name) {
	return trm_create_term( $tag_name, 'post_tag');
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.3.0
 *
 * @param unknown_type $post_id
 * @return unknown
 */
function get_tags_to_edit( $post_id, $taxonomy = 'post_tag' ) {
	return get_terms_to_edit( $post_id, $taxonomy);
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.8.0
 *
 * @param unknown_type $post_id
 * @return unknown
 */
function get_terms_to_edit( $post_id, $taxonomy = 'post_tag' ) {
	$post_id = (int) $post_id;
	if ( !$post_id )
		return false;

	$tags = trm_get_post_terms($post_id, $taxonomy, array());

	if ( !$tags )
		return false;

	if ( is_trm_error($tags) )
		return $tags;

	foreach ( $tags as $tag )
		$tag_names[] = $tag->name;
	$tags_to_edit = join( ',', $tag_names );
	$tags_to_edit = esc_attr( $tags_to_edit );
	$tags_to_edit = apply_filters( 'terms_to_edit', $tags_to_edit, $taxonomy );

	return $tags_to_edit;
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.8.0
 *
 * @param unknown_type $tag_name
 * @return unknown
 */
function trm_create_term($tag_name, $taxonomy = 'post_tag') {
	if ( $id = term_exists($tag_name, $taxonomy) )
		return $id;

	return trm_insert_term($tag_name, $taxonomy);
}
