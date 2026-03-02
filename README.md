# Call Tracker

Inbound call tracking using Twilio Voice Intelligence. Exposes a REST API consumed by a WordPress plugin.

## Stack
- Node.js + Express
- PostgreSQL (Railway)
- Twilio Voice Intelligence

## Environment Variables
See `.env.example` for required variables. Set these in Railway dashboard under Variables.

## Routes (planned)
- `POST /webhook/incoming` — Twilio calls this when a call arrives
- `POST /webhook/callback` — Twilio calls this when transcription is ready
- `GET  /api/calls` — Returns call list (used by WP plugin)
- `GET  /api/calls/:id` — Returns single call + transcript

## Local Development
```bash
npm install
cp .env.example .env   # fill in your values
npm run dev
```
