<?php
/*
Plugin Name: Comments Export/Import Manager
Description: Export and import WordPress comments with filtering options
Author: TechReader
Author URI: https://techreader.com
Version: 1.7.7
Text Domain: ./tr-comments-import-export
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
GitHub URI: https://github.com/lso2/tr-comments-import-export
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TR_CEI_VERSION', '1.7.7');
define('TR_CEI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TR_CEI_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the main class
require_once TR_CEI_PLUGIN_DIR . 'includes/class-comments-export-import.php';

// Initialize the plugin
new CommentsExportImport();
