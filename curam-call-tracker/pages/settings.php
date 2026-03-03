<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function curam_ct_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }

    if ( isset( $_POST['curam_ct_save'] ) ) {
        check_admin_referer( 'curam_ct_settings_nonce' );
        update_option( 'curam_ct_api_url', sanitize_text_field( $_POST['curam_ct_api_url'] ) );
        update_option( 'curam_ct_api_key', sanitize_text_field( $_POST['curam_ct_api_key'] ) );
        echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
    }

    $api_url = get_option( 'curam_ct_api_url', '' );
    $api_key = get_option( 'curam_ct_api_key', '' );
    ?>
    <div class="wrap dpct-wrap">
        <div class="dpct-header">
            <h1>Settings</h1>
            <p class="dpct-subtitle">Configure your Railway API connection</p>
        </div>

        <form method="post" class="dpct-settings-form">
            <?php wp_nonce_field( 'curam_ct_settings_nonce' ); ?>
            <table class="form-table dpct-form-table">
                <tr>
                    <th><label for="curam_ct_api_url">Railway API URL</label></th>
                    <td>
                        <input type="url" id="curam_ct_api_url" name="curam_ct_api_url"
                               value="<?php echo esc_attr( $api_url ); ?>"
                               placeholder="https://your-app.up.railway.app"
                               class="regular-text" />
                        <p class="description">Your Railway app public URL. No trailing slash.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="curam_ct_api_key">API Secret Key</label></th>
                    <td>
                        <input type="password" id="curam_ct_api_key" name="curam_ct_api_key"
                               value="<?php echo esc_attr( $api_key ); ?>"
                               placeholder="Your API_SECRET_KEY from Railway"
                               class="regular-text" />
                        <p class="description">Must match <code>API_SECRET_KEY</code> in Railway Variables.</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" name="curam_ct_save" class="dpct-btn dpct-btn--primary">Save Settings</button>
            </p>
        </form>

        <?php if ( $api_url && $api_key ) :
            $test = curam_ct_fetch_calls( 1, 1 );
        ?>
        <div class="dpct-connection-test">
            <h3>Connection Status</h3>
            <?php if ( isset( $test['error'] ) ) : ?>
                <span class="dpct-badge dpct-badge--negative">&times; <?php echo esc_html( $test['error'] ); ?></span>
            <?php else : ?>
                <span class="dpct-badge dpct-badge--positive">&check; Connected successfully</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
}
