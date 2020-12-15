<?php
/**
 * Bootstrap file for setting the ABSPATH constant
 * and loading the trm-setup.php file. The trm-setup.php
 * file will then load the trm-main.php file, which
 * will then set up the Trnder environment.
 *
 * If the trm-setup.php file is not found then an error
 * will be displayed asking the visitor to set up the
 * trm-setup.php file.
 *
 * Will also search for trm-setup.php in Trnder' parent
 * directory to allow the Trnder directory to remain
 * untouched.
 *
 * @internal This file must be parsable by PHP4.
 *
 * @package Trnder
 */

/** Define ABSPATH as this files directory */
define( 'ABSPATH', dirname(__FILE__) . '/' );

error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

if ( file_exists( ABSPATH . 'trm-setup.php') ) {

	/** The config file resides in ABSPATH */
	require_once( ABSPATH . 'trm-setup.php' );

} elseif ( file_exists( dirname(ABSPATH) . '/trm-setup.php' ) && ! file_exists( dirname(ABSPATH) . '/trm-main.php' ) ) {

	/** The config file resides one level above ABSPATH but is not part of another install*/
	require_once( dirname(ABSPATH) . '/trm-setup.php' );

} else {

	// A config file doesn't exist

	// Set a path for the link to the installer
	if ( strpos($_SERVER['PHP_SELF'], 'Backend-WeaprEcqaKejUbRq-trendr') !== false )
		$path = '';
	else
		$path = 'Backend-WeaprEcqaKejUbRq-trendr/';

	require_once( ABSPATH . '/Source-zACHAvU6As28quwr-trendr/load.php' );
	require_once( ABSPATH . '/Source-zACHAvU6As28quwr-trendr/version.php' );
	define( 'TRM_CONTENT_DIR', ABSPATH . 'trm-src' );
	trm_check_php_mysql_versions();

	// Die with an error message
	require_once( ABSPATH . '/Source-zACHAvU6As28quwr-trendr/class-trm-error.php' );
	require_once( ABSPATH . '/Source-zACHAvU6As28quwr-trendr/functions.php' );
	require_once( ABSPATH . '/Source-zACHAvU6As28quwr-trendr/plugin.php' );
	$text_direction = /*TRM_I18N_TEXT_DIRECTION*/'ltr'/*/TRM_I18N_TEXT_DIRECTION*/;
	trm_die(sprintf(/*TRM_I18N_NO_CONFIG*/"There doesn't seem to be a <code>trm-setup.php</code> file. I need this before we can get started. Need more help? <a href='http://codex.trendr.org/Editing_trm-setup.php'>We got it</a>. You can create a <code>trm-setup.php</code> file through a web interface, but this doesn't work for all server setups. The safest way is to manually create the file.</p><p><a href='%ssetup-config.php' class='button'>Create a Configuration File</a>"/*/TRM_I18N_NO_CONFIG*/, $path), /*TRM_I18N_ERROR_TITLE*/'Trnder &rsaquo; Error'/*/TRM_I18N_ERROR_TITLE*/, array('text_direction' => $text_direction));

}

?>