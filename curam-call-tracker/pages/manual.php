<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function curam_ct_manual_page() {
    ?>
    <div class="wrap dpct-wrap">
        <div class="dpct-header">
            <h1>📖 Manual</h1>
            <p class="dpct-subtitle">How to use the Curam Call Tracker plugin</p>
        </div>

        <div class="dpct-manual">
            <div class="dpct-manual-section">
                <h2>Overview</h2>
                <p>Curam Call Tracker connects to your Railway-hosted API to display inbound call recordings, AI-generated transcripts, sentiment analysis, and call summaries within your WordPress admin.</p>
            </div>

            <div class="dpct-manual-section">
                <h2>Setup</h2>
                <ol>
                    <li>Go to <strong>Call Tracker → Settings</strong></li>
                    <li>Enter your <strong>Railway API URL</strong> (e.g. <code>https://your-app.up.railway.app</code>)</li>
                    <li>Enter your <strong>API Secret Key</strong> — must match <code>API_SECRET_KEY</code> in Railway Variables</li>
                    <li>Save — a connection status indicator will confirm the link is working</li>
                </ol>
            </div>

            <div class="dpct-manual-section">
                <h2>Dashboard</h2>
                <p>The Dashboard shows all inbound calls with:</p>
                <ul>
                    <li><strong>Caller</strong> — the phone number that called in</li>
                    <li><strong>Date &amp; Time</strong> — when the call occurred</li>
                    <li><strong>Duration</strong> — length of the call in seconds</li>
                    <li><strong>Sentiment</strong> — AI tone analysis (Positive / Neutral / Negative)</li>
                </ul>
                <p>Click any row to expand and view the full transcript and AI-generated summary.</p>
            </div>

            <div class="dpct-manual-section">
                <h2>Sentiment Badges</h2>
                <ul>
                    <li><span class="dpct-badge dpct-badge--positive">Positive</span> — caller expressed satisfaction</li>
                    <li><span class="dpct-badge dpct-badge--neutral">Neutral</span> — no strong sentiment detected</li>
                    <li><span class="dpct-badge dpct-badge--negative">Negative</span> — caller expressed frustration</li>
                    <li><span class="dpct-badge dpct-badge--unknown">Pending</span> — still being processed by Voice Intelligence</li>
                </ul>
            </div>

            <div class="dpct-manual-section">
                <h2>How Calls Are Processed</h2>
                <ol>
                    <li>Customer calls your Twilio number</li>
                    <li>Call is recorded automatically</li>
                    <li>Recording is submitted to Twilio Voice Intelligence</li>
                    <li>Voice Intelligence generates transcript, sentiment, and summary</li>
                    <li>Results appear here within 1–2 minutes of the call ending</li>
                </ol>
            </div>

            <div class="dpct-manual-section">
                <h2>Troubleshooting</h2>
                <ul>
                    <li><strong>Connection error on Settings page</strong> — verify Railway is running and your URL/key are correct</li>
                    <li><strong>Transcript is blank</strong> — call may still be processing. Wait 1–2 minutes and refresh</li>
                    <li><strong>No calls appearing</strong> — confirm your Twilio number Voice webhook points to your Railway app</li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}
