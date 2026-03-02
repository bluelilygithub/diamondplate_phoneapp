<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function dpct_settings_page() {
    if ( isset( $_POST['dpct_save_settings'] ) ) {
        check_admin_referer( 'dpct_settings_nonce' );
        update_option( 'dpct_api_url', sanitize_text_field( $_POST['dpct_api_url'] ) );
        update_option( 'dpct_api_key', sanitize_text_field( $_POST['dpct_api_key'] ) );
        echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
    }

    $api_url = get_option( 'dpct_api_url', '' );
    $api_key = get_option( 'dpct_api_key', '' );
    ?>
    <div class="wrap dpct-wrap">
        <div class="dpct-header">
            <h1>⚙️ Settings</h1>
            <p class="dpct-subtitle">Configure your Railway API connection</p>
        </div>

        <form method="post" class="dpct-settings-form">
            <?php wp_nonce_field( 'dpct_settings_nonce' ); ?>

            <table class="form-table dpct-form-table">
                <tr>
                    <th><label for="dpct_api_url">Railway API URL</label></th>
                    <td>
                        <input
                            type="url"
                            id="dpct_api_url"
                            name="dpct_api_url"
                            value="<?php echo esc_attr( $api_url ); ?>"
                            placeholder="https://your-app.up.railway.app"
                            class="regular-text"
                        />
                        <p class="description">Your Railway app's public URL. No trailing slash.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="dpct_api_key">API Secret Key</label></th>
                    <td>
                        <input
                            type="password"
                            id="dpct_api_key"
                            name="dpct_api_key"
                            value="<?php echo esc_attr( $api_key ); ?>"
                            placeholder="Your API_SECRET_KEY from Railway"
                            class="regular-text"
                        />
                        <p class="description">Must match the <code>API_SECRET_KEY</code> variable set in Railway.</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="dpct_save_settings" class="dpct-btn dpct-btn--primary">
                    Save Settings
                </button>
            </p>
        </form>

        <?php if ( $api_url && $api_key ) : ?>
        <div class="dpct-connection-test">
            <h3>Connection Status</h3>
            <?php
            $test = dpct_fetch_calls( 1, 1 );
            if ( isset( $test['error'] ) ) {
                echo '<span class="dpct-badge dpct-badge--negative">✗ ' . esc_html( $test['error'] ) . '</span>';
            } else {
                echo '<span class="dpct-badge dpct-badge--positive">✓ Connected successfully</span>';
            }
            ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
}
