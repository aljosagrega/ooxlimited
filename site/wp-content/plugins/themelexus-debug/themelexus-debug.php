<?php
/**
 * ThemeLexus Debug
 *
 * @package      themelexus-debug
 * @author        THEMELEXUS
 * @version       1.3.4
 *
 * @wordpress-plugin
 * Plugin Name:   ThemeLexus Debug
 * Plugin URI:    https://themelexus.com/themelexus-debug
 * Description:   **ThemeLexus Debug** A WordPress plugin that automatically fixes errors, updates essential features, and resolves conflicts in Lexus Theme designs..
 * Version:       1.3.4
 * Author:        THEMELEXUS
 * Author URI:    https://themelexus.com
 * License:       GPLv2 or later
 * License URI:   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:  themelexus-debug
 * Domain Path:   /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Plugin name
define( 'LXDB_NAME', 'ThemeLexus Debug' );
define( 'LXDB_TEXTDOMAIN', 'themelexus-debug' );

// Plugin version
define( 'LXDB_VERSION', '1.3.4' );

// Plugin Root File
define( 'LXDB_PLUGIN_FILE', __FILE__ );

// Plugin base
define( 'LXDB_PLUGIN_BASE', plugin_basename( LXDB_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'LXDB_PLUGIN_DIR',  plugin_dir_path( LXDB_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'LXDB_PLUGIN_URL',  plugin_dir_url( LXDB_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once LXDB_PLUGIN_DIR . 'includes/class-themelexus-debug.php';

function plugin_update() {
    if (file_exists(LXDB_PLUGIN_DIR . 'plugin-updates/plugin-update-checker.php')) {
        require LXDB_PLUGIN_DIR . 'plugin-updates/plugin-update-checker.php';
        Puc_v4_Factory::buildUpdateChecker(
            'http://source.wpopal.com/plugin_update/themelexus-debug.json',
            __FILE__,
            'themelexus-debug'
        );
    }
}
plugin_update();

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  LexusTeam
 * @since   1.3.4
 * @return  object|LXDB_Start_Instance
 */
function lxdb() {
    return LXDB_Start_Instance::instance();
}
lxdb();
