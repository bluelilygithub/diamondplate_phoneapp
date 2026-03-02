<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function curam_ct_dashboard_page() {
    $page  = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
    $data  = curam_ct_fetch_calls( $page, 20 );
    $nonce = wp_create_nonce( 'curam_ct_nonce' );
    ?>
    <div class="wrap dpct-wrap">
        <div class="dpct-header">
            <h1>📞 Call Tracker</h1>
            <p class="dpct-subtitle">Inbound call recordings, transcripts &amp; sentiment</p>
        </div>

        <?php if ( isset( $data['error'] ) ) : ?>
            <div class="dpct-notice dpct-notice--error">
                <strong>Error:</strong> <?php echo esc_html( $data['error'] ); ?>
            </div>
        <?php else :
            $calls = $data['calls'] ?? [];
        ?>
            <?php if ( empty( $calls ) ) : ?>
                <div class="dpct-notice">No calls recorded yet.</div>
            <?php else : ?>
                <table class="dpct-table widefat">
                    <thead>
                        <tr>
                            <th>Caller</th>
                            <th>Date &amp; Time</th>
                            <th>Duration</th>
                            <th>Sentiment</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $calls as $call ) :
                            $id          = intval( $call['id'] );
                            $from        = esc_html( $call['from_number'] ?? '—' );
                            $date        = isset( $call['created_at'] ) ? date( 'd M Y, g:ia', strtotime( $call['created_at'] ) ) : '—';
                            $duration    = isset( $call['duration'] ) ? intval( $call['duration'] ) . 's' : '—';
                            $sentiment   = $call['sentiment'] ?? null;
                            $badge_class = 'dpct-badge dpct-badge--' . ( $sentiment ?? 'unknown' );
                        ?>
                        <tr class="dpct-row" data-id="<?php echo $id; ?>">
                            <td class="dpct-cell-number"><?php echo $from; ?></td>
                            <td><?php echo esc_html( $date ); ?></td>
                            <td><?php echo esc_html( $duration ); ?></td>
                            <td>
                                <span class="<?php echo esc_attr( $badge_class ); ?>">
                                    <?php echo $sentiment ? esc_html( ucfirst( $sentiment ) ) : 'Pending'; ?>
                                </span>
                            </td>
                            <td class="dpct-cell-toggle"><span class="dpct-toggle-icon">▼</span></td>
                        </tr>
                        <tr class="dpct-detail-row" id="dpct-detail-<?php echo $id; ?>" style="display:none;">
                            <td colspan="5">
                                <div class="dpct-detail-content" data-loaded="false">
                                    <div class="dpct-loading">Loading...</div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="dpct-pagination">
                    <?php if ( $page > 1 ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'paged', $page - 1 ) ); ?>" class="dpct-btn">← Previous</a>
                    <?php endif; ?>
                    <span>Page <?php echo esc_html( $page ); ?></span>
                    <?php if ( count( $calls ) === 20 ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'paged', $page + 1 ) ); ?>" class="dpct-btn">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
    var curamCtAjax = {
        url:    '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
        nonce:  '<?php echo esc_js( $nonce ); ?>',
        apiUrl: '<?php echo esc_js( trailingslashit( get_option( "curam_ct_api_url", "" ) ) ); ?>',
        apiKey: '<?php echo esc_js( get_option( "curam_ct_api_key", "" ) ); ?>'
    };
    </script>
    <?php
}
