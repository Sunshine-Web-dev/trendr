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
define('DB_NAME', 'demotest');

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
define('AUTH_KEY',         '8Xy]o4fr3fYB9wM:6s2]}VCA8@1T+I;+PkiFkU-`<!0o<tT7+>`|4f~L[Gpq~E,_');
define('SECURE_AUTH_KEY',  'Suz:P)Z38cQFBGGt9Cj!sG-`q#grFG;K Oj0R(/>N86(Rly/Vm]~Uc.dZ!XLLkJV');
define('LOGGED_IN_KEY',    'eEEXF;MuVb+rMz(^u86xTp?Ovx;.o&TPbq{0,ZI_I6Eb-EzN;<Mr?Ldy0uX+v0b8');
define('NONCE_KEY',        '4M8P^CWsXV,qI4.UC?untO/eI{0Nn/971v2AfRv&6Q%USdFw#/W0e1L|] ]%{/vc');
define('AUTH_SALT',        '^<ctpCjVBS(0!Z_w&b*F_2xr&UGw0p!YEx`U|sn6*3&8/9[;Hml&du;O4CWA-CqW');
define('SECURE_AUTH_SALT', ',kgdjJ*lu2w:6c8*F}QH3)wl#X{(N@zDcyxHoz/hoZl;~AE)|!d)m@ZK<75mPGhC');
define('LOGGED_IN_SALT',   'iH!Wh[zn^]~[6Qv)6TV]^py IGXvUU9rDteYFdgWN{<QZ|t?FedTc%4[hu,Uu..M');
define('NONCE_SALT',       '*iS2A+7o`u5$Dmr%`scZl?rl3&XCtSNlk Vrr06J:h;/iD6pax:Z@{o+/wDo9uUM');

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
