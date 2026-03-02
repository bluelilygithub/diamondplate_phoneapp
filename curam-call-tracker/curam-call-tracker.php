<?php
/**
 * Plugin Name: Curam Call Tracker
 * Description: Displays inbound call recordings, transcripts and sentiment from the Railway call tracking API.
 * Version: 1.0.0
 * Author: Curam
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'CURAM_CT_VERSION',    '1.0.0' );
define( 'CURAM_CT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CURAM_CT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CURAM_CT_PLUGIN_DIR . 'pages/settings.php';
require_once CURAM_CT_PLUGIN_DIR . 'pages/dashboard.php';
require_once CURAM_CT_PLUGIN_DIR . 'pages/manual.php';

// Register admin menu
add_action( 'admin_menu', 'curam_ct_register_menu' );
function curam_ct_register_menu() {
    add_menu_page(
        'Call Tracker',
        'Call Tracker',
        'manage_options',
        'curam-ct-dashboard',
        'curam_ct_dashboard_page',
        'dashicons-phone',
        30
    );
    add_submenu_page( 'curam-ct-dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'curam-ct-dashboard',  'curam_ct_dashboard_page' );
    add_submenu_page( 'curam-ct-dashboard', 'Settings',  'Settings',  'manage_options', 'curam-ct-settings',   'curam_ct_settings_page' );
    add_submenu_page( 'curam-ct-dashboard', 'Manual',    'Manual',    'manage_options', 'curam-ct-manual',     'curam_ct_manual_page' );
}

// Enqueue assets only on plugin pages
add_action( 'admin_enqueue_scripts', 'curam_ct_enqueue_assets' );
function curam_ct_enqueue_assets( $hook ) {
    if ( strpos( $hook, 'curam-ct' ) === false ) return;

    wp_enqueue_style(
        'curam-ct-styles',
        CURAM_CT_PLUGIN_URL . 'assets/style.css',
        [],
        CURAM_CT_VERSION
    );
    wp_enqueue_script(
        'curam-ct-scripts',
        CURAM_CT_PLUGIN_URL . 'assets/script.js',
        [ 'jquery' ],
        CURAM_CT_VERSION,
        true
    );
}

// Helper: get API config
function curam_ct_get_api_config() {
    return [
        'url' => get_option( 'curam_ct_api_url', '' ),
        'key' => get_option( 'curam_ct_api_key', '' ),
    ];
}

// Helper: fetch calls from Railway API with transient caching
function curam_ct_fetch_calls( $page = 1, $limit = 20 ) {
    $config = curam_ct_get_api_config();
    if ( empty( $config['url'] ) || empty( $config['key'] ) ) {
        return [ 'error' => 'API not configured. Please update Settings.' ];
    }

    $cache_key = 'curam_ct_calls_' . $page . '_' . $limit;
    $cached    = get_transient( $cache_key );
    if ( $cached !== false ) return $cached;

    $url = trailingslashit( $config['url'] ) . 'api/calls?page=' . intval( $page ) . '&limit=' . intval( $limit );

    $response = wp_remote_get( $url, [
        'headers' => [ 'x-api-key' => $config['key'] ],
        'timeout' => 15,
    ]);

    if ( is_wp_error( $response ) ) {
        return [ 'error' => $response->get_error_message() ];
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $data ) ) return [ 'error' => 'Invalid response from API.' ];

    // Cache for 60 seconds
    set_transient( $cache_key, $data, 60 );

    return $data;
}

// Helper: fetch single call (no cache — always fresh for detail view)
function curam_ct_fetch_call( $id ) {
    $config = curam_ct_get_api_config();
    if ( empty( $config['url'] ) || empty( $config['key'] ) ) {
        return [ 'error' => 'API not configured.' ];
    }

    $url = trailingslashit( $config['url'] ) . 'api/calls/' . intval( $id );

    $response = wp_remote_get( $url, [
        'headers' => [ 'x-api-key' => $config['key'] ],
        'timeout' => 15,
    ]);

    if ( is_wp_error( $response ) ) {
        return [ 'error' => $response->get_error_message() ];
    }

    return json_decode( wp_remote_retrieve_body( $response ), true );
}

// AJAX: fetch single call detail for inline expand
add_action( 'wp_ajax_curam_ct_get_call', 'curam_ct_ajax_get_call' );
function curam_ct_ajax_get_call() {
    check_ajax_referer( 'curam_ct_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

    $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
    if ( ! $id ) wp_send_json_error( 'Invalid ID' );

    $call = curam_ct_fetch_call( $id );
    wp_send_json_success( $call );
}
