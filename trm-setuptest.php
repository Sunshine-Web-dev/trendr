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
define('DB_NAME', 'ttt');

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
define('AUTH_KEY',         '944^K=MDU .?,aXHb$tRl<?]{9Uw2%/zftGJN8Z8xIu,!l07~ L{,;@a#=]OrkQx');
define('SECURE_AUTH_KEY',  'R_B=F>zyv2PkVTb%+q-*R;s;SpfA3r3 2-zo:n%$e`]baJ#!~I=3:~9v3?1Ph?B,');
define('LOGGED_IN_KEY',    '%l>A[*DBq6:g+]%.,A]-cMW#L[vTN$xNlch<h$>d0>=MavmHK}ea%)vzC%a`OB3Z');
define('NONCE_KEY',        'wJNL $8zc*<UO>-]&]:SHRixyI?=4GZL/[|nTkwvXfO$92DV:+_oDx#$Q*:4M8JB');
define('AUTH_SALT',        'SIIYjZ`I1N$7l/0fyuEHV[{zxqBkqZ9|AY.)ZFDRl+XoV&kL+YVO:)nfGv!N7LsZ');
define('SECURE_AUTH_SALT', '$j%i9p3:ja)txXALp?[-kNA6W4]XzqOolT@Hrjy%x_Z<q*rb)]s%-]gyWe!a6/L?');
define('LOGGED_IN_SALT',   'Snuy0DO}./ltDV&Ova5N2EYg?KB`-U5Y4Kcpa=L(XoPp=+7yB}}HD)-BJ9,s~:rR');
define('NONCE_SALT',       'JA@-hu+rg){Y74j}]m>$FU_~plV}r{6Wx`ltMl[v^${G$TNsQQ%| &,6gG;vPsp)');

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
