<?php

/**
 * trs_like_install()
 *
 * Installs or upgrades the database content
 *
 */
function trs_like_install() {

		$default_text_strings = array(
			'like' => array(
				'default'	=> __('Like', 'trendr-like'), 
				'custom'	=> __('Like', 'trendr-like')
			),
			'unlike' => array(
				'default'	=> __('Unlike', 'trendr-like'),
				'custom'	=> __('Unlike', 'trendr-like')
			),
			'like_this_item' => array(
				'default'	=> __('Like this item', 'trendr-like'),
				'custom'	=> __('Like this item', 'trendr-like')
			),
			'unlike_this_item' => array(
				'default'	=> __('Unlike this item', 'trendr-like'),
				'custom'	=> __('Unlike this item', 'trendr-like')
			)
		);



	update_site_option( 'trs_like_db_version', TRS_LIKE_DB_VERSION );
	//update_site_option( 'trs_like_settings', $settings );

}