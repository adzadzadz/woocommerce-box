<?php

/**
 * Plugin Name: WooCommerce Box Plugin
 * Plugin URI: http://mycustomsoftware.com/
 * Description: An extension to WooCommerce that displays how full a box is based on the size and weight of the items in the box.
 * Version: 1.0.0
 * Author: MCS
 * Author URI: http://mycustomsoftware.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 3.0.0
 * WC tested up to: 5.9.0
 * Text Domain: wc-box
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Simple php autoloader
spl_autoload_register(function ($class) {
    $prefix = 'WCB\\';
    $base_dir = __DIR__ . '/includes/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

define('MCS_WCB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MCS_WCB_PLUGIN_URL', plugin_dir_url(__FILE__));


// Initialize the plugin
new WCB\Init(require_once MCS_WCB_PLUGIN_DIR . 'config.php');
