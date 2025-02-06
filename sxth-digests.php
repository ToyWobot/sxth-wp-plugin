<?php
/**
 * Plugin Name: SXTH Digests
 * Description: Create and display digest posts via API
 * Version: 1.0.0
 * Author: sxth.ai
 * License: GPL-2.0+
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SXTH_DIGESTS_VERSION', '1.0.0');
define('SXTH_DIGESTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SXTH_DIGESTS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load core classes
require_once SXTH_DIGESTS_PLUGIN_DIR . 'includes/class-sxth-digests-core.php';
require_once SXTH_DIGESTS_PLUGIN_DIR . 'includes/class-sxth-digests-auth.php';
require_once SXTH_DIGESTS_PLUGIN_DIR . 'includes/class-sxth-digests-api.php';
require_once SXTH_DIGESTS_PLUGIN_DIR . 'includes/class-sxth-digests-admin.php';
require_once SXTH_DIGESTS_PLUGIN_DIR . 'includes/class-sxth-digests-public.php';

// Initialize the plugin
register_activation_hook(__FILE__, array('SXTH_Digests_Core', 'activate'));
register_deactivation_hook(__FILE__, array('SXTH_Digests_Core', 'deactivate'));


// allow cross origiin
function add_cors_headers()
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, X-API-Key"); // Both cases
    header("Access-Control-Allow-Credentials: true");
    header("Vary: Origin");

    // Handle OPTIONS preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        status_header(200);
        exit();
    }
}
add_filter('rest_pre_dispatch', function ($result, $server, $request) {
    $route = $request->get_route();
    if (strpos($route, '/sxth-digests/') === 0) {
        add_cors_headers();
    }
    return $result;
}, 10, 3);

function sxth_digests_init()
{
    SXTH_Digests_Auth::get_instance();
    SXTH_Digests_Core::get_instance();
    SXTH_Digests_API::get_instance();
    SXTH_Digests_Admin::get_instance();
    SXTH_Digests_Public::get_instance();
}
add_action('plugins_loaded', 'sxth_digests_init');