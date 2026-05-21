<?php
/**
 * Plugin Name: Custom Code
 * Description: Custom shortcodes and snippets for the site.
 * Version: 1.0.0
 * Author: Tebiko
 * Author URI: https://tebiko.com
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CC_VERSION', '1.0.0');
define('CC_PATH', plugin_dir_path(__FILE__));
define('CC_URL', plugin_dir_url(__FILE__));

/**
 * Autoload all PHP files inside the registered folders.
 */
function cc_autoload_files() {
    $folders = ['shortcodes', 'snippets'];

    foreach ($folders as $folder) {
        foreach (glob(CC_PATH . $folder . '/*.php') as $file) {
            require_once $file;
        }
    }
}
cc_autoload_files();