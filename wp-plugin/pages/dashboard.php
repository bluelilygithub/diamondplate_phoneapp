<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function dpct_dashboard_page() {
    $page  = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
    $limit = 20;
    $data  = dpct_fetch_calls( $page, $limit );
    $nonce = wp_create_nonce( 'dpct_nonce' );
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
        <?php else : ?>

            <?php $calls = $data['calls'] ?? []; ?>

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
                            $id        = intval( $call['id'] );
                            $from      = esc_html( $call['from_number'] ?? '—' );
                            $date      = isset( $call['created_at'] )
                                         ? date( 'd M Y, g:ia', strtotime( $call['created_at'] ) )
                                         : '—';
                            $duration  = isset( $call['duration'] ) ? intval( $call['duration'] ) . 's' : '—';
                            $sentiment = $call['sentiment'] ?? null;
                            $badge_class = 'dpct-badge dpct-badge--' . ( $sentiment ?? 'unknown' );
                        ?>
                        <tr class="dpct-row" data-id="<?php echo $id; ?>">
                            <td class="dpct-cell-number"><?php echo $from; ?></td>
                            <td><?php echo esc_html( $date ); ?></td>
                            <td><?php echo esc_html( $duration ); ?></td>
                            <td>
                                <?php if ( $sentiment ) : ?>
                                    <span class="<?php echo esc_attr( $badge_class ); ?>">
                                        <?php echo esc_html( ucfirst( $sentiment ) ); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="dpct-badge dpct-badge--unknown">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="dpct-cell-toggle">
                                <span class="dpct-toggle-icon">▼</span>
                            </td>
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

                <?php // Pagination
                $total_pages = ceil( count( $calls ) / $limit );
                if ( $page > 1 || count( $calls ) === $limit ) : ?>
                <div class="dpct-pagination">
                    <?php if ( $page > 1 ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'paged', $page - 1 ) ); ?>" class="dpct-btn">← Previous</a>
                    <?php endif; ?>
                    <span>Page <?php echo $page; ?></span>
                    <?php if ( count( $calls ) === $limit ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'paged', $page + 1 ) ); ?>" class="dpct-btn">Next →</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
    var dpctAjax = {
        url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
        nonce: '<?php echo $nonce; ?>'
    };
    </script>
    <?php
}
