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
define('DB_NAME', 'experiment');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'jsforever26');

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
define('AUTH_KEY',         'V)[1thVLY*JTF=ls}k.cX8EF4daV)z~t)XsImw|ixjea,8,-KQRQHPL@Nt`UIcWU');
define('SECURE_AUTH_KEY',  'm.5O=D^?1IQ/pMm]zBcOhZKU_(E_L:RJ03V<EyDj#`_>e8jJ,1#b`_m%]O|q5~hr');
define('LOGGED_IN_KEY',    '%V@yd3e$dSu6n4w/|!7C09.VP{Mh.&m0HaP1x/0 }`]8*<BJ|4*)>bZ5|S$B?-&9');
define('NONCE_KEY',        'sUdlK|D[tGlUp$>>mm8-QUOaZY$z$ 91P2iqnI>oG]fO$,1 &d!?..jH}s5EYhI5');
define('AUTH_SALT',        'a!`t,PEUdEVWc<DFwH4}|`H[ h{nUWK%YmucSL=:2rxr;SWzxSt1?]l8usKsPXVB');
define('SECURE_AUTH_SALT', 'i|l:.K)}5>bKxS$w4>8DRU=/~UWYr(1Kk(a|s1v=|SME`k$;.Djm~>)6*_Uw6dWL');
define('LOGGED_IN_SALT',   'D <+kXISL aq^^+9]{]DcKNo&m#bn /F5ed&#M2]ze2%|Wjfx*NRP@`w;l->+y t');
define('NONCE_SALT',       'p#F#O-?2*G4-C@IW1NjkF>!V^e=5~Ri|1]@57<Qd!(#=HD070&$iC(<HR0:X,:Y@');

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

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the Trnder directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up Trnder vars and included files. */
require_once(ABSPATH . 'trm-main.php');
