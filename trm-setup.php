<?php
/**
 * The base configurations of the Trnder.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, Trnder Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.trendr.org/Editing_trm-setup.php Editing
 * trm-setup.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the trm-setup.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "trm-setup.php" and fill in the values.
 *
 * @package Trnder
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for Trnder */
define('DB_NAME', 'trendr');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.trendr.org/secret-key/1.1/salt/ Trnder.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '*j*Powsg7K3%A:9v{!^MP|<ck^R44||7ckRB/835?#l-|bVWWS_sHY#DpZAJ7UUx');
define('SECURE_AUTH_KEY',  '8?<uFeuqC:ZmN;(NdC4?T2L%!{ )jLl&uAPN/n.[-K9IMqdamPIw=ZppAskXLTM=');
define('LOGGED_IN_KEY',    '3MBC4/?{H}b?6?J05:.Kr/,[0[dWs-wh/TNC7mJO-jvp%M5g+[fj:`=yN4f8w1KQ');
define('NONCE_KEY',        'XKyx,,_xAa]+v-x~JwpBfF]o2)pqf]0M0wjP4;{9~UO3o|D:r[cgj9Nb=3O&k%8g');
define('AUTH_SALT',        '`qmm]|;tAVmx3NM+M*%E}AmUxXBUVeWmH_H)ghFqE .PcZc$YJI&YkTn>X+3XT|d');
define('SECURE_AUTH_SALT', 'cPGZI^%m]z9;?Y:54sT$P7Bn9I5zg.G>II=9,y_ !gX4rYrM^A#:#8?h<i7nX:UO');
define('LOGGED_IN_SALT',   'g3/q@fO~9Yv7~]5l>sYr3ryMZLAAY~< =UXM}1J{^?zCvt)!fyE4nTNWD#_ ]TZt');
define('NONCE_SALT',       '$mTP83bc4,Hpx6r<K!V9yc3[At&I_gSgeHB|Kk ~HB&}OtRA<w]<zmsW}Oi/?EII');

/**#@-*/

/**
 * Trnder Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'trm_';

/**
 * Trnder Localized Language, defaults to English.
 *
 * Change this to localize Trnder. A corresponding MO file for the chosen
 * language must be installed to trm-src/languages. For example, install
 * de_DE.mo to trm-src/languages and set TRMLANG to 'de_DE' to enable German
 * language support.
 */
define('TRMLANG', '');

/**
 * For developers: Trnder debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use TRM_DEBUG
 * in their development environments.
 */
define('TRM_DEBUG', false);
@ini_set('log_errors','On');
@ini_set('display_errors','Off');
@ini_set('error_reporting', E_ALL );
define('TRM_DEBUG_LOG', true);
define('TRM_DEBUG_DISPLAY', false);
/* That's all, stop editing! Happy blogging. */

/** Absolute path to the Trnder directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up Trnder vars and included files. */
require_once(ABSPATH . 'trm-main.php');
