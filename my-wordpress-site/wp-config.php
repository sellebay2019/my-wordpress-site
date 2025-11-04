<?php
define( 'WP_CACHE', true );

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u397939103_3LCZN' );

/** Database username */
define( 'DB_USER', 'u397939103_KwIHl' );

/** Database password */
define( 'DB_PASSWORD', '9PyOdE7znY' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'MO$]3Hb77Ot<R@SE`1j)zM[+Z-KN*;AV]Hp>rZ5z*kkzAKIJ*~uCbr{UxF.m.t6-' );
define( 'SECURE_AUTH_KEY',   '#{)4,9KkA$DxL-KX;l_KZvsFn/$xbX0AZvZ/p8{v7EU$`p=y;*V)f*L5E9LM|CW`' );
define( 'LOGGED_IN_KEY',     'MgE]:/]61g|X4{C-dWPY(~t6m8wmo/(C5An]IHM3nL$6S#,ER?4 EN~?YI]z9#DC' );
define( 'NONCE_KEY',         '*h0fjeMj_TrT4&$E4?qnrh=b[Q^ >P%DCS[kmKKaS_@is)}7Tj~B1]+K<5`k`Nbz' );
define( 'AUTH_SALT',         '&/|M{Uc,J<r0C@@T}?s3@bWd8[~59/Z_!T}]uBUUeCJS]f9#Pg/x[<P3|>`[0!O[' );
define( 'SECURE_AUTH_SALT',  '9P_hkUzg<`f)J$wz3S%QI],8fN#:@n=Z9MwrZ]H;t>-)gukebU;41Q,:LoirtOcW' );
define( 'LOGGED_IN_SALT',    ':@{SgJ@EmW*PNi+2gAM)6UjqW,[`kGp0`#MCzM?j]f`*T#D7;QK)_?V@Yv=Ru$w3' );
define( 'NONCE_SALT',        'N?]C=-`|QYwE34m[9Ggut:ECM~]JiuTMh]|9-3!>a8TLxBUeGYj6N^~=g$Kg^ZGc' );
define( 'WP_CACHE_KEY_SALT', 'XHkAmmdj?N&@//*S2|cyJeB2+oU8(s|R+oU6v1!}0a.u$@k#<%AT8M!hf`g{*&[e' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '40859fb647c100f1e38378d707fad4c7' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
