<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_trm-setup.php Editing
 * trm-setup.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the trm-setup.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "trm-setup.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'trendr');
ini_set('display_errors','off');
/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'Jsforever26');

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
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'tb4A=(00b+gzgQZU_}J4w|SnaB}dTl+,~%7o>6E~[j1M=55#}UwXTxXE:> e=^fB');
define('SECURE_AUTH_KEY',  '!Ez, `Z0VLjDbPJWq}Ew9Q>_P3$v48UzXqGvRpN0e1,7[pb|H.XW_$UX!8~:#Q.]');
define('LOGGED_IN_KEY',    '~bga 3CqtNw&A q7ePKEGz&n*VuoyEE9dn`d^^ff~ZBckB<BQ+ar8d9n[/HteX[C');
define('NONCE_KEY',        'X gvb5]@,o/C!WODVR}`E[crTBQiHc&TO1(vk7KYA0P6hN?`}QpG(V/u~^qbQR.U');
define('AUTH_SALT',        'OzPV^,NSg@:qucu,(dSA;P@pI|0fQikE}W(1ze+vus@o@ek`D<-XADsUAuu30Ifg');
define('SECURE_AUTH_SALT', 'z|+_P?3%#evuzJ_h~)*h(Ps.Ndn*k:#5$g17wV=ySvBy}#_XiQGUEB1yPPU+`c-Y');
define('LOGGED_IN_SALT',   'D#{4X$5 1giwa>gLB4y*0aP~Wi{T}/78ZiPC{D:4Mn>H}t,h^TNUJa8-Ci6$R`}|');
define('NONCE_SALT',       '|~HNcc3<]ES/X8TY9TY{6i{0.4(uz)3s/s4GFQ.?$214v[^J`Aq)DZ~kgcE6+1??');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'trm_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to trm-src/languages. For example, install
 * de_DE.mo to trm-src/languages and set TRMLANG to 'de_DE' to enable German
 * language support.
 */
define('TRMLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use TRM_DEBUG
 * in their development environments.
 */
define('TRM_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'trm-main.php');
