<?php
/**
 * Plugin Name: Custom Permalink Editor
 * Plugin URI: https://kingscrestglobal.com/
 * Description: Set Permalinks on a per-post and  per-page basis. This is very easy to use and this will not effect your rewrite url rules.
 * Version: 1.0.3
 * Requires PHP: 5.4
 * Author: Team KCG
 * Author URI: https://kingscrestglobal.com/contact/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Text Domain: custom-permalink-editor
 * Domain Path: /languages/  
 *
 */



if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'CP_EDITOR_FILE' ) ) {
	define( 'CP_EDITOR_FILE', __FILE__ );
}

// Include the main Custom Permalink Editor class.
require_once plugin_dir_path( CP_EDITOR_FILE ) . 'includes/class-custom-permalink-editor.php';
