<?php
/**
 * Sets up the default filters and actions for Multisite.
 *
 * If you need to remove a default hook, this file will give you the priority
 * for which to use to remove the hook.
 *
 * Not all of the Multisite default hooks are found in ms-default-filters.php
 *
 * @package Trnder
 * @subpackage Multisite
 * @see default-filters.php
 * @since 3.0.0
 */

// Users
add_filter( 'trmmu_validate_user_signup', 'signup_nonce_check' );
add_action( 'init', 'maybe_add_existing_user_to_blog' );
add_action( 'trmmu_new_user', 'newuser_notify_siteadmin' );
add_action( 'trmmu_activate_user', 'add_new_user_to_blog', 10, 3 );
add_action( 'sanitize_user', 'strtolower' );

// Blogs
add_filter( 'trmmu_validate_blog_signup', 'signup_nonce_check' );
add_action( 'trmmu_new_blog', 'trmmu_log_new_registrations', 10, 2 );
add_action( 'trmmu_new_blog', 'newblog_notify_siteadmin', 10, 2 );

// Register Nonce
add_action( 'signup_hidden_fields', 'signup_nonce_fields' );

// Template
add_action( 'template_redirect', 'maybe_redirect_404' );
add_filter( 'allowed_redirect_hosts', 'redirect_this_site' );

// Administration
add_filter( 'term_id_filter', 'global_terms', 10, 2 );
add_action( 'publish_post', 'update_posts_count' );
add_action( 'delete_post', 'trmmu_update_blogs_date' );
add_action( 'private_to_published', 'trmmu_update_blogs_date' );
add_action( 'publish_phone', 'trmmu_update_blogs_date' );
add_action( 'publish_post', 'trmmu_update_blogs_date' );
add_action( 'admin_init', 'trm_schedule_update_network_counts');
add_action( 'update_network_counts', 'trm_update_network_counts');

// Files
add_filter( 'trm_upload_bits', 'upload_is_file_too_big' );
add_filter( 'import_upload_size_limit', 'fix_import_form_size' );
add_filter( 'upload_mimes', 'check_upload_mimes' );
add_filter( 'upload_size_limit', 'upload_size_limit_filter' );

// Mail
add_action( 'phpmailer_init', 'fix_phpmailer_messageid' );

// Disable somethings by default for multisite
add_filter( 'enable_update_services_configuration', '__return_false' );
if ( ! defined('POST_BY_EMAIL') || ! POST_BY_EMAIL ) // back compat constant.
	add_filter( 'enable_post_by_email_configuration', '__return_false' );
if ( ! defined('EDIT_ANY_USER') || ! EDIT_ANY_USER ) // back compat constant.
	add_filter( 'enable_edit_any_user_configuration', '__return_false' );
add_filter( 'force_filtered_html_on_import', '__return_true' );

// TRM_HOME and TRM_SITEURL should not have any effect in MS
remove_filter( 'option_siteurl', '_config_trm_siteurl' );
remove_filter( 'option_home',    '_config_trm_home'    );

?>
