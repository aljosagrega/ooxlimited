<?php
/**
 * Local-only WordPress config for the ooxlimited.com copy.
 * The original from the Hostinger host is at ../_hostinger-original/wp-config.php
 */

// --- Database: the `db` service in docker-compose.yml ---
define( 'DB_NAME',     'ooxlimited' );
define( 'DB_USER',     'wp' );
define( 'DB_PASSWORD', 'wp' );
define( 'DB_HOST',     'db' );
define( 'DB_CHARSET',  'utf8' );
define( 'DB_COLLATE',  '' );

$table_prefix = 'wp_';

// --- Local URLs (override whatever is in the database) ---
define( 'WP_HOME',    'http://localhost:8080' );
define( 'WP_SITEURL', 'http://localhost:8080' );

// --- Keep this copy inert: no outbound anything, no self-updates ---
define( 'WP_ENVIRONMENT_TYPE', 'local' );
define( 'WP_CACHE',            false );
define( 'DISABLE_WP_CRON',     true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'WP_AUTO_UPDATE_CORE', false );
define( 'DISALLOW_FILE_EDIT',  false );
define( 'FS_METHOD',           'direct' );

define( 'WP_DEBUG',         true );
define( 'WP_DEBUG_LOG',     true );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

// --- Fresh salts: local sessions only, prod salts deliberately not reused ---
define( 'AUTH_KEY', 'DBouwoUP24(HJ2ry(#f#6aDJOq3a03G&CHBKBc^p^wbzNXHAwwcUxkwRss#oKwWW' );
define( 'SECURE_AUTH_KEY', 'M1I)EDSs_CO55N@DYgCE4G@2f)p=#ZPpYi@Z_1s3KXL^Ye5)4_C%JTHWgV-xGV0&' );
define( 'LOGGED_IN_KEY', 'YUXglBCfKR3Nu5ajNT%btZvvgq&sqlKd&jehJUWq#j7QZXzuUtoLVqnkimZS(3@%' );
define( 'NONCE_KEY', 'uRs5-JAEGu7VqE_k7DKivSgT#YqL73%(HiIo2dXIzLTeQFqFQS!axKtE_i)LUH4^' );
define( 'AUTH_SALT', '_k33CXO%!wf0q*+vttMf73^CMm)2D)dJK#3aKQ)lgqYR%+dxrcVq%_epfL-yZ2Ck' );
define( 'SECURE_AUTH_SALT', 'WdiAM!yf!-=Pn+enFM8q0+Xs+zLuR_Pjlsc=w+-0^Aw-PFQT=8VzqT01bAd-e_xC' );
define( 'LOGGED_IN_SALT', 'A&c5=z2HSztz_8LVAXlOrXE-Q1&gjeqf40MsC5x)2K)iLG8x88M2BaU%+(nGW=c8' );
define( 'NONCE_SALT', 'U0r5MyqLx0SSYw)S(t2)cAlTpR5pkbNLjSFx=3kdW&k40uS^lNdvF%pc4Gx77-4^' );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';
