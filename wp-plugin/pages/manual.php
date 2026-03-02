<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function dpct_manual_page() {
    ?>
    <div class="wrap dpct-wrap">
        <div class="dpct-header">
            <h1>📖 Manual</h1>
            <p class="dpct-subtitle">How to use the Call Tracker plugin</p>
        </div>

        <div class="dpct-manual">

            <div class="dpct-manual-section">
                <h2>Overview</h2>
                <p>Call Tracker connects to your Railway-hosted API to display inbound call recordings, AI-generated transcripts, sentiment analysis, and call summaries — all within your WordPress admin.</p>
            </div>

            <div class="dpct-manual-section">
                <h2>Setup</h2>
                <ol>
                    <li>Go to <strong>Call Tracker → Settings</strong></li>
                    <li>Enter your <strong>Railway API URL</strong> (e.g. <code>https://your-app.up.railway.app</code>)</li>
                    <li>Enter your <strong>API Secret Key</strong> — this must match the <code>API_SECRET_KEY</code> variable set in your Railway project</li>
                    <li>Save — the page will show a connection status indicator</li>
                </ol>
            </div>

            <div class="dpct-manual-section">
                <h2>Dashboard</h2>
                <p>The Dashboard displays all inbound calls in a table, showing:</p>
                <ul>
                    <li><strong>Caller</strong> — the phone number that called in</li>
                    <li><strong>Date &amp; Time</strong> — when the call occurred</li>
                    <li><strong>Duration</strong> — length of the call in seconds</li>
                    <li><strong>Sentiment</strong> — AI analysis of the call tone (Positive / Neutral / Negative)</li>
                </ul>
                <p>Click any row to expand it and view the full transcript and AI-generated summary.</p>
            </div>

            <div class="dpct-manual-section">
                <h2>Sentiment Badges</h2>
                <ul>
                    <li><span class="dpct-badge dpct-badge--positive">Positive</span> — caller expressed satisfaction or positive tone</li>
                    <li><span class="dpct-badge dpct-badge--neutral">Neutral</span> — conversational, no strong sentiment detected</li>
                    <li><span class="dpct-badge dpct-badge--negative">Negative</span> — caller expressed frustration or negative tone</li>
                    <li><span class="dpct-badge dpct-badge--unknown">Pending</span> — call is still being processed by Voice Intelligence</li>
                </ul>
            </div>

            <div class="dpct-manual-section">
                <h2>How Calls Are Processed</h2>
                <ol>
                    <li>Customer calls your Twilio number</li>
                    <li>Call is recorded automatically</li>
                    <li>Recording is submitted to Twilio Voice Intelligence</li>
                    <li>Voice Intelligence generates a transcript with speaker labels, sentiment score, and call summary</li>
                    <li>Results are saved to the database and appear here within 1–2 minutes of the call ending</li>
                </ol>
            </div>

            <div class="dpct-manual-section">
                <h2>Troubleshooting</h2>
                <ul>
                    <li><strong>Settings page shows connection error</strong> — verify your Railway app is running and the API URL and key are correct</li>
                    <li><strong>Calls appear but transcript is blank</strong> — the call may still be processing. Wait 1–2 minutes and refresh</li>
                    <li><strong>No calls appearing</strong> — confirm your Twilio number Voice webhook is pointing to your Railway app</li>
                </ul>
            </div>

        </div>
    </div>
    <?php
}
