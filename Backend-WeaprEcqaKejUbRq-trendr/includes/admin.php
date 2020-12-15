<?php
/**
 * Includes all of the Trnder Administration API files.
 *
 * @package Trnder
 * @subpackage Administration
 */

/** Trnder Bookmark Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/bookmark.php');

/** Trnder Comment Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/comment.php');

/** Trnder Administration File API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/file.php');

/** Trnder Image Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/image.php');

/** Trnder Media Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/media.php');

/** Trnder Import Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/import.php');

/** Trnder Misc Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/misc.php');

/** Trnder Plugin Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/plugin.php');

/** Trnder Post Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/post.php');

/** Trnder Taxonomy Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/taxonomy.php');

/** Trnder Template Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/template.php');

/** Trnder List Table Administration API and base class */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/class-trm-list-table.php');
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/list-table.php');

/** Trnder Theme Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/theme.php');

/** Trnder User Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/user.php');

/** Trnder Update Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/update.php');

/** Trnder Deprecated Administration API */
require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/deprecated.php');

/** Trnder Multi-Site support API */
if ( is_multisite() ) {
	require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/ms.php');
	require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/ms-deprecated.php');
}

?>
