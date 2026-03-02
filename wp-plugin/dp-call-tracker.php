<?php
/**
 * Plugin Name: DP Call Tracker
 * Description: Displays inbound call recordings, transcripts and sentiment from the Railway call tracking API.
 * Version: 1.0.0
 * Author: Diamond Plate
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'DPCT_VERSION', '1.0.0' );
define( 'DPCT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DPCT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load pages
require_once DPCT_PLUGIN_DIR . 'pages/settings.php';
require_once DPCT_PLUGIN_DIR . 'pages/dashboard.php';
require_once DPCT_PLUGIN_DIR . 'pages/manual.php';

// Register admin menu
add_action( 'admin_menu', 'dpct_register_menu' );
function dpct_register_menu() {
    add_menu_page(
        'Call Tracker',
        'Call Tracker',
        'manage_options',
        'dpct-dashboard',
        'dpct_dashboard_page',
        'dashicons-phone',
        30
    );

    add_submenu_page(
        'dpct-dashboard',
        'Dashboard',
        'Dashboard',
        'manage_options',
        'dpct-dashboard',
        'dpct_dashboard_page'
    );

    add_submenu_page(
        'dpct-dashboard',
        'Settings',
        'Settings',
        'manage_options',
        'dpct-settings',
        'dpct_settings_page'
    );

    add_submenu_page(
        'dpct-dashboard',
        'Manual',
        'Manual',
        'manage_options',
        'dpct-manual',
        'dpct_manual_page'
    );
}

// Enqueue admin styles and scripts
add_action( 'admin_enqueue_scripts', 'dpct_enqueue_assets' );
function dpct_enqueue_assets( $hook ) {
    if ( strpos( $hook, 'dpct' ) === false ) return;

    wp_enqueue_style(
        'dpct-styles',
        DPCT_PLUGIN_URL . 'assets/style.css',
        [],
        DPCT_VERSION
    );

    wp_enqueue_script(
        'dpct-scripts',
        DPCT_PLUGIN_URL . 'assets/script.js',
        [ 'jquery' ],
        DPCT_VERSION,
        true
    );
}

// Helper: get API config
function dpct_get_api_config() {
    return [
        'url' => get_option( 'dpct_api_url', '' ),
        'key' => get_option( 'dpct_api_key', '' ),
    ];
}

// Helper: fetch calls from Railway API
function dpct_fetch_calls( $page = 1, $limit = 20 ) {
    $config = dpct_get_api_config();
    if ( empty( $config['url'] ) || empty( $config['key'] ) ) {
        return [ 'error' => 'API not configured. Please update Settings.' ];
    }

    $url = trailingslashit( $config['url'] ) . 'api/calls?page=' . $page . '&limit=' . $limit;

    $response = wp_remote_get( $url, [
        'headers' => [
            'x-api-key' => $config['key'],
        ],
        'timeout' => 15,
    ]);

    if ( is_wp_error( $response ) ) {
        return [ 'error' => $response->get_error_message() ];
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( empty( $data ) ) {
        return [ 'error' => 'Invalid response from API.' ];
    }

    return $data;
}

// Helper: fetch single call from Railway API
function dpct_fetch_call( $id ) {
    $config = dpct_get_api_config();
    if ( empty( $config['url'] ) || empty( $config['key'] ) ) {
        return [ 'error' => 'API not configured.' ];
    }

    $url = trailingslashit( $config['url'] ) . 'api/calls/' . intval( $id );

    $response = wp_remote_get( $url, [
        'headers' => [
            'x-api-key' => $config['key'],
        ],
        'timeout' => 15,
    ]);

    if ( is_wp_error( $response ) ) {
        return [ 'error' => $response->get_error_message() ];
    }

    $body = wp_remote_retrieve_body( $response );
    return json_decode( $body, true );
}

// AJAX: fetch single call detail for inline expand
add_action( 'wp_ajax_dpct_get_call', 'dpct_ajax_get_call' );
function dpct_ajax_get_call() {
    check_ajax_referer( 'dpct_nonce', 'nonce' );

    $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
    if ( ! $id ) wp_send_json_error( 'Invalid ID' );

    $call = dpct_fetch_call( $id );
    wp_send_json_success( $call );
}
