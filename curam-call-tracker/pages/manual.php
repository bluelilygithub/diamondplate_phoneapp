<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function curam_ct_manual_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }
    ?>
    <div class="wrap curam-manual">
    <style>
    .curam-manual { max-width: 900px; margin: 20px auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif; }
    .curam-manual h1 { font-size: 28px; font-weight: 600; color: #1d2327; margin-bottom: 8px; }
    .curam-manual .manual-version { color: #646970; font-size: 13px; margin-bottom: 30px; }
    .curam-manual .manual-toc { background: #f0f6fc; border-left: 4px solid #2271b1; padding: 20px 24px; margin-bottom: 32px; border-radius: 0 8px 8px 0; }
    .curam-manual .manual-toc h3 { margin: 0 0 12px; font-size: 15px; color: #1d2327; }
    .curam-manual .manual-toc ul { margin: 0; padding: 0; list-style: none; columns: 2; column-gap: 24px; }
    .curam-manual .manual-toc li { margin-bottom: 6px; }
    .curam-manual .manual-toc a { color: #2271b1; text-decoration: none; font-size: 14px; }
    .curam-manual .manual-toc a:hover { text-decoration: underline; }
    .curam-manual .manual-section { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 28px 32px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .curam-manual .manual-section h2 { font-size: 20px; font-weight: 600; color: #1d2327; margin: 0 0 16px; padding-bottom: 12px; border-bottom: 2px solid #f0f0f1; }
    .curam-manual .manual-section h3 { font-size: 16px; font-weight: 600; color: #2c3338; margin: 20px 0 10px; }
    .curam-manual .manual-section p, .curam-manual .manual-section li { font-size: 14px; line-height: 1.7; color: #3c434a; }
    .curam-manual .manual-section ul, .curam-manual .manual-section ol { padding-left: 20px; }
    .curam-manual .manual-section li { margin-bottom: 6px; }
    .curam-manual .manual-section code { background: #f0f0f1; padding: 2px 8px; border-radius: 4px; font-size: 13px; color: #1d2327; }
    .curam-manual .manual-section .info-box { background: #f0f6fc; border-left: 3px solid #2271b1; padding: 12px 16px; margin: 16px 0; border-radius: 0 4px 4px 0; font-size: 13px; color: #2c3338; }
    .curam-manual .manual-section .warning-box { background: #fcf0f1; border-left: 3px solid #d63638; padding: 12px 16px; margin: 16px 0; border-radius: 0 4px 4px 0; font-size: 13px; color: #2c3338; }
    .curam-manual .manual-section .success-box { background: #edfaef; border-left: 3px solid #00a32a; padding: 12px 16px; margin: 16px 0; border-radius: 0 4px 4px 0; font-size: 13px; color: #2c3338; }
    .curam-manual .manual-section table { width: 100%; border-collapse: collapse; margin: 16px 0; }
    .curam-manual .manual-section th { background: #f6f7f7; text-align: left; padding: 10px 14px; font-size: 13px; font-weight: 600; border: 1px solid #e0e0e0; }
    .curam-manual .manual-section td { padding: 10px 14px; font-size: 13px; border: 1px solid #e0e0e0; }
    @media (max-width: 782px) {
        .curam-manual .manual-toc ul { columns: 1; }
        .curam-manual .manual-section { padding: 20px; }
    }
    </style>

    <h1>Call Tracker &mdash; Admin Manual</h1>
    <p class="manual-version">Plugin Version: <?php echo esc_html( CURAM_CT_VERSION ); ?> &bull; Last Updated: March 2026</p>

    <div class="manual-toc">
        <h3>Contents</h3>
        <ul>
            <li><a href="#overview">Overview</a></li>
            <li><a href="#setup">Setup &amp; Configuration</a></li>
            <li><a href="#dashboard">Dashboard</a></li>
            <li><a href="#call-details">Call Details</a></li>
            <li><a href="#sentiment">Sentiment Analysis</a></li>
            <li><a href="#processing">How Calls Are Processed</a></li>
            <li><a href="#security">Security</a></li>
            <li><a href="#troubleshooting">Troubleshooting</a></li>
        </ul>
    </div>

    <div class="manual-section" id="overview">
        <h2>Overview</h2>
        <p>Curam Call Tracker connects to your Railway-hosted API to display inbound call recordings, AI-generated transcripts, sentiment analysis, and call summaries within your WordPress admin.</p>
        <p>Key capabilities:</p>
        <ul>
            <li>View all inbound calls with caller number, date, duration, and sentiment</li>
            <li>Expand any call to view the full transcript and AI-generated summary</li>
            <li>Listen to call recordings directly in the browser</li>
            <li>Sentiment badges show AI tone analysis at a glance</li>
        </ul>
        <div class="info-box">
            <strong>Note:</strong> This plugin requires a running Railway-hosted call tracking API. Call data is fetched in real-time and cached for 60 seconds to reduce API load.
        </div>
    </div>

    <div class="manual-section" id="setup">
        <h2>Setup &amp; Configuration</h2>
        <p>Navigate to <strong>Call Tracker &rarr; Settings</strong> to configure the API connection:</p>
        <table>
            <thead>
                <tr>
                    <th>Setting</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Railway API URL</strong></td>
                    <td>Your Railway app public URL (e.g. <code>https://your-app.up.railway.app</code>). No trailing slash.</td>
                </tr>
                <tr>
                    <td><strong>API Secret Key</strong></td>
                    <td>Must match <code>API_SECRET_KEY</code> in your Railway environment variables.</td>
                </tr>
            </tbody>
        </table>
        <div class="success-box">
            <strong>Connection test:</strong> After saving, a status indicator confirms whether the API link is working. A green badge means connected; red indicates an error.
        </div>
    </div>

    <div class="manual-section" id="dashboard">
        <h2>Dashboard</h2>
        <p>The Dashboard lists all inbound calls in a paginated table with the following columns:</p>
        <table>
            <thead>
                <tr>
                    <th>Column</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Caller</strong></td>
                    <td>The phone number that called in</td>
                </tr>
                <tr>
                    <td><strong>Date &amp; Time</strong></td>
                    <td>When the call occurred</td>
                </tr>
                <tr>
                    <td><strong>Duration</strong></td>
                    <td>Length of the call in seconds</td>
                </tr>
                <tr>
                    <td><strong>Sentiment</strong></td>
                    <td>AI tone analysis badge (Positive / Neutral / Negative / Pending)</td>
                </tr>
            </tbody>
        </table>
        <p>Click any row to expand and view the full call details including transcript, summary, and audio recording.</p>
    </div>

    <div class="manual-section" id="call-details">
        <h2>Call Details</h2>
        <p>Expanding a call row reveals:</p>
        <ul>
            <li><strong>Summary</strong> &mdash; AI-generated overview of the call content</li>
            <li><strong>Sentiment</strong> &mdash; detailed sentiment badge with classification</li>
            <li><strong>Recording</strong> &mdash; audio player to listen to the call directly in the browser</li>
            <li><strong>Transcript</strong> &mdash; full text transcript generated by Twilio Voice Intelligence</li>
        </ul>
        <div class="info-box">
            <strong>Note:</strong> Audio is streamed through a secure server-side proxy. The API key is never exposed to the browser.
        </div>
    </div>

    <div class="manual-section" id="sentiment">
        <h2>Sentiment Analysis</h2>
        <p>Each call is automatically analysed for tone by Twilio Voice Intelligence:</p>
        <table>
            <thead>
                <tr>
                    <th>Badge</th>
                    <th>Meaning</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Positive</strong></td>
                    <td>Caller expressed satisfaction or positive intent</td>
                </tr>
                <tr>
                    <td><strong>Neutral</strong></td>
                    <td>No strong sentiment detected</td>
                </tr>
                <tr>
                    <td><strong>Negative</strong></td>
                    <td>Caller expressed frustration or dissatisfaction</td>
                </tr>
                <tr>
                    <td><strong>Pending</strong></td>
                    <td>Still being processed by Voice Intelligence (typically 1&ndash;2 minutes)</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="manual-section" id="processing">
        <h2>How Calls Are Processed</h2>
        <ol>
            <li>Customer calls your Twilio number</li>
            <li>Call is recorded automatically</li>
            <li>Recording is submitted to Twilio Voice Intelligence</li>
            <li>Voice Intelligence generates transcript, sentiment, and summary</li>
            <li>Results appear in the dashboard within 1&ndash;2 minutes of the call ending</li>
        </ol>
        <div class="info-box">
            <strong>Caching:</strong> Call list data is cached for 60 seconds via WordPress transients. Individual call details are always fetched fresh to ensure transcripts and summaries are up to date.
        </div>
    </div>

    <div class="manual-section" id="security">
        <h2>Security</h2>
        <ul>
            <li>All API requests use the <code>x-api-key</code> header &mdash; the key is never exposed in client-side code</li>
            <li>Audio playback is proxied through a server-side AJAX endpoint with nonce and capability checks</li>
            <li>All admin pages require the <code>curam_manage_enquiries</code> capability</li>
            <li>AJAX handlers verify nonces and user capabilities before processing</li>
        </ul>
    </div>

    <div class="manual-section" id="troubleshooting">
        <h2>Troubleshooting</h2>
        <table>
            <thead>
                <tr>
                    <th>Issue</th>
                    <th>Solution</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Connection error on Settings page</td>
                    <td>Verify Railway is running and your URL / key are correct</td>
                </tr>
                <tr>
                    <td>Transcript is blank</td>
                    <td>Call may still be processing. Wait 1&ndash;2 minutes and refresh</td>
                </tr>
                <tr>
                    <td>No calls appearing</td>
                    <td>Confirm your Twilio number's Voice webhook points to your Railway app</td>
                </tr>
                <tr>
                    <td>Audio not playing</td>
                    <td>Check that the Railway API is returning audio data and the endpoint is accessible</td>
                </tr>
            </tbody>
        </table>
        <div class="warning-box">
            <strong>Important:</strong> If you change the <code>API_SECRET_KEY</code> in Railway, you must also update it in the plugin Settings page.
        </div>
    </div>

    </div>
    <?php
}
