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

add_action( 'admin_menu', 'curam_ct_register_menu' );
function curam_ct_register_menu() {
    $cap = 'manage_options';

    add_menu_page(
        'Call Tracker',
        'Call Tracker',
        $cap,
        'curam-ct-dashboard',
        'curam_ct_dashboard_page',
        'dashicons-phone',
        74
    );

    add_submenu_page(
        'curam-ct-dashboard',
        '',
        '<span class="curam-reports-heading">Calls</span>',
        $cap,
        'curam-ct-calls-heading',
        '__return_false'
    );

    add_submenu_page( 'curam-ct-dashboard', 'Dashboard', 'Dashboard', $cap, 'curam-ct-dashboard',  'curam_ct_dashboard_page' );
    add_submenu_page( 'curam-ct-dashboard', 'Settings',  'Settings',  $cap, 'curam-ct-settings',   'curam_ct_settings_page' );
    add_submenu_page( 'curam-ct-dashboard', 'Manual',    'Manual',    $cap, 'curam-ct-manual',     'curam_ct_manual_page' );
}

add_action( 'admin_head', 'curam_ct_menu_heading_style' );
function curam_ct_menu_heading_style() {
    ?>
    <style>
    #adminmenu .wp-submenu a[href*="curam-ct-calls-heading"] {
        cursor: default !important;
        pointer-events: none !important;
        font-weight: bold !important;
        border-top: 1px solid rgba(255,255,255,0.15);
        margin-top: 8px;
        padding-top: 10px !important;
        padding-bottom: 6px !important;
    }
    #adminmenu .wp-submenu a[href*="curam-ct-calls-heading"]:hover {
        background: transparent !important;
    }
    #toplevel_page_curam-ct-dashboard .wp-menu-image::before {
        color: #4caf50 !important;
    }
    .curam-reports-heading {
        color: #ff9800 !important;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
    }
    </style>
    <?php
}

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

function curam_ct_get_api_config() {
    return [
        'url' => get_option( 'curam_ct_api_url', '' ),
        'key' => get_option( 'curam_ct_api_key', '' ),
    ];
}

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

    set_transient( $cache_key, $data, 60 );

    return $data;
}

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

add_action( 'wp_ajax_curam_ct_get_call', 'curam_ct_ajax_get_call' );
function curam_ct_ajax_get_call() {
    check_ajax_referer( 'curam_ct_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

    $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
    if ( ! $id ) wp_send_json_error( 'Invalid ID' );

    $call = curam_ct_fetch_call( $id );
    if ( isset( $call['error'] ) ) {
        wp_send_json_error( $call['error'] );
    }
    wp_send_json_success( $call );
}

add_action( 'wp_ajax_curam_ct_proxy_audio', 'curam_ct_proxy_audio' );
function curam_ct_proxy_audio() {
    check_ajax_referer( 'curam_ct_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );

    $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
    $config = curam_ct_get_api_config();

    if ( ! $id || empty( $config['url'] ) || empty( $config['key'] ) ) {
        wp_die( 'Invalid request' );
    }

    $url = trailingslashit( $config['url'] ) . 'api/calls/' . $id . '/audio';

    $response = wp_remote_get( $url, [
        'headers' => [ 'x-api-key' => $config['key'] ],
        'timeout' => 30,
    ]);

    if ( is_wp_error( $response ) ) {
        wp_die( 'Audio unavailable' );
    }

    $content_type = wp_remote_retrieve_header( $response, 'content-type' );
    if ( ! $content_type ) {
        $content_type = 'audio/mpeg';
    }

    header( 'Content-Type: ' . $content_type );
    header( 'Content-Length: ' . strlen( wp_remote_retrieve_body( $response ) ) );
    echo wp_remote_retrieve_body( $response );
    exit;
}
