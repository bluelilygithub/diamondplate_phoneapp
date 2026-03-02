# Curam Call Tracker — Project Documentation

## Overview
A system to record, transcribe, and analyse inbound phone calls using Twilio Voice Intelligence. Call data is stored in a PostgreSQL database on Railway, exposed via a secured REST API, and displayed in a WordPress admin dashboard with audio playback.

---

## Architecture

```
Inbound Call
     │
     ▼
Twilio Phone Number
     │
     ▼ POST /webhook/incoming (Twilio signature validated)
Railway App (Node.js)
     │  - Creates call record in DB (status: in-progress)
     │  - Returns TwiML to greet caller and start recording
     │
     ▼ POST /webhook/recording (Twilio signature validated)
Railway App
     │  - Updates call record with recording URL and duration
     │  - Submits recording SID to Twilio Voice Intelligence
     │  - Saves transcript SID to DB
     │
     ▼ POST /webhook/intelligence/transcript-complete (account_sid verified)
Railway App
     │  - Fetches speaker-labeled transcript (up to 1000 sentences)
     │  - Fetches sentiment and summary in parallel via Promise.all()
     │  - Updates call record with transcript, sentiment, summary
     │
     ▼
PostgreSQL Database (Railway)
     │
     ▼ GET /api/calls          (x-api-key authenticated, 60s WP transient cache)
     ▼ GET /api/calls/:id      (x-api-key authenticated)
     ▼ GET /api/calls/:id/audio (x-api-key authenticated, proxies Twilio recording)
WordPress Plugin (WP Admin Dashboard)
```

---

## Infrastructure

| Component | Platform | Notes |
|---|---|---|
| App hosting | Railway | Node.js, auto-deploys from GitHub |
| Database | Railway PostgreSQL | Connected via DATABASE_URL |
| Source control | GitHub | Push to main triggers deploy |
| Phone number | Twilio | Voice + SMS capable |
| Transcription | Twilio Voice Intelligence | Sentiment + Summary operators enabled |
| WP Plugin | SiteGround | PHP, no dependencies |

---

## Repository Structure

```
curam-ai-phonesync/
  index.js                              ← App entry point, Express + CORS setup
  package.json                          ← Dependencies and start script
  package-lock.json
  .gitignore
  .env.example
  README.md
  src/
    config/
      db.js                             ← PostgreSQL connection pool
    middleware/
      auth.js                           ← Timing-safe API key auth (header or query param)
      twilioAuth.js                     ← Twilio webhook signature validation
    models/
      callModel.js                      ← All database queries (capped limit at 100)
    controllers/
      webhookController.js              ← Inbound call + recording webhooks
      intelligenceController.js         ← Voice Intelligence completion webhook
      callsController.js                ← API list and detail endpoints
      audioController.js                ← Twilio recording proxy/stream
    routes/
      webhook.js                        ← POST /webhook/incoming, /webhook/recording
      intelligence.js                   ← POST /webhook/intelligence/transcript-complete
      calls.js                          ← GET /api/calls, /api/calls/:id, /api/calls/:id/audio
  wp-plugin/
    curam-call-tracker.php              ← Main plugin file
    pages/
      dashboard.php                     ← Call list table with inline expand
      settings.php                      ← API URL + key config with connection test
      manual.php                        ← Usage documentation
    assets/
      style.css                         ← Admin UI styles
      script.js                         ← Inline row expand + audio player
```

---

## Database Schema

```sql
CREATE TABLE calls (
  id SERIAL PRIMARY KEY,
  call_sid VARCHAR(64) UNIQUE NOT NULL,   -- Twilio Call SID (CA...)
  transcript_sid VARCHAR(64),              -- Voice Intelligence SID (GT...)
  from_number VARCHAR(20),                 -- Caller's number
  to_number VARCHAR(20),                   -- Your Twilio number
  duration INT,                            -- Call duration in seconds
  status VARCHAR(20),                      -- in-progress / completed
  sentiment VARCHAR(20),                   -- neutral / positive / negative
  summary TEXT,                            -- AI-generated call summary
  transcript TEXT,                         -- Speaker-labeled transcript
  recording_url TEXT,                      -- Twilio recording URL (no auth)
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Performance index
CREATE INDEX idx_calls_created_at ON calls (created_at DESC);
```

---

## Environment Variables

Set in Railway dashboard under app service → Variables.

| Variable | Description |
|---|---|
| `DATABASE_URL` | Auto-provided by Railway Postgres service |
| `TWILIO_ACCOUNT_SID` | Twilio account identifier |
| `TWILIO_AUTH_TOKEN` | Twilio authentication token |
| `TWILIO_VOICE_INTELLIGENCE_SID` | Voice Intelligence service SID (GA...) |
| `API_SECRET_KEY` | Secret key used by WP plugin to authenticate API requests |
| `APP_URL` | Public Railway app URL (e.g. https://your-app.up.railway.app) |
| `ALLOWED_ORIGINS` | Comma-separated list of allowed CORS origins (e.g. https://yoursite.com.au) |

---

## API Endpoints

All `/api/*` endpoints require either:
- Header: `x-api-key: your_API_SECRET_KEY`
- Query param: `?api_key=your_API_SECRET_KEY` (audio streaming only)

### GET /api/calls
Returns a paginated list of calls. Limit capped at 100.

Query params: `page` (default 1), `limit` (default 20)

### GET /api/calls/:id
Returns a single call including full transcript.

### GET /api/calls/:id/audio
Proxies the Twilio MP3 recording back to the browser. Twilio credentials never leave Railway.

---

## Twilio Configuration

### Phone Number
- Voice webhook: `POST https://your-app.up.railway.app/webhook/incoming`

### Voice Intelligence Service
- Auto transcribe: **enabled**
- Operators: **Conversation Summary**, **Sentiment Analysis**
- Webhook: `POST https://your-app.up.railway.app/webhook/intelligence/transcript-complete`
- Event: `voice_intelligence_transcript_available`

---

## WordPress Plugin

Installed at: `wp-content/plugins/curam-call-tracker/`

**Dashboard** displays all inbound calls with:
- Caller number
- Date & time
- Duration
- Sentiment badge (Positive / Neutral / Negative / Pending)
- Click to expand: Summary, Sentiment, Audio player, Full transcript

**Settings** stores Railway API URL and secret key in WP options with live connection test.

**Manual** documents setup, usage, and troubleshooting.

---

## Security

| Measure | Implementation |
|---|---|
| Twilio webhook validation | `twilioAuth.js` validates `x-twilio-signature` on all Twilio webhooks |
| Voice Intelligence webhook | `account_sid` verified against environment variable |
| API authentication | Timing-safe key comparison via `crypto.timingSafeEqual()` |
| CORS | Locked to `ALLOWED_ORIGINS` environment variable |
| Error messages | Generic — no internal details exposed to clients |
| Sensitive logging | Removed — only non-PII identifiers logged |
| Limit cap | API limit capped at 100 rows per request |
| WP AJAX | Nonce + `manage_options` capability check |

---

## What's Working

- ✅ Inbound calls received and recorded
- ✅ Call records created in database on answer
- ✅ Recording URL and duration saved on completion
- ✅ Recording submitted to Twilio Voice Intelligence
- ✅ Full speaker-labeled transcript saved (up to 1000 sentences)
- ✅ Sentiment analysis saved (neutral / positive / negative)
- ✅ AI-generated call summary saved
- ✅ Audio playback via Railway proxy
- ✅ REST API secured and optimised
- ✅ WordPress admin dashboard with inline expand
- ✅ 60-second transient caching on WP dashboard
- ✅ Database index on created_at for query performance

---

## What's Next

### 1. Search and Filtering
- Filter by date range, sentiment, caller number
- Full-text search across transcripts (PostgreSQL `tsvector`)
- Add to both API and WP dashboard

### 2. Caller Identification
- Cross-reference `from_number` against a contacts table
- Display caller name alongside number in dashboard

### 3. Speaker Label Improvement
- Remap channel 0/1 to meaningful labels (e.g. `[Customer]` / `[Staff]`)
- Currently defaults to `[Caller]` / `[Agent]`

### 4. Error Handling and Reliability
- Retry logic if Voice Intelligence submission fails
- Handle calls that drop before recording starts
- Alert if recording callback never fires

### 5. Recording Storage
- Optionally download recordings to S3 for long-term storage
- Reduces dependency on Twilio's storage retention policy

### 6. Notifications
- Email or Slack alert on negative sentiment calls
- Daily/weekly call summary digest

---

## Deployment Workflow

```
Local changes
     │
     ▼
git add .
git commit -m "description"
git push origin main
     │
     ▼
Railway auto-deploys (monitor under Deployments → View Logs)
     │
     ▼ (WP plugin changes only)
Upload changed files to SiteGround
wp-content/plugins/curam-call-tracker/
```
