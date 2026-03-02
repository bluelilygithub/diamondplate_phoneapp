const db = require("../config/db");

const CallModel = {
  async create(callSid, from, to) {
    const result = await db.query(
      `INSERT INTO calls (call_sid, from_number, to_number, status)
       VALUES ($1, $2, $3, 'in-progress')
       ON CONFLICT (call_sid) DO NOTHING
       RETURNING *`,
      [callSid, from, to]
    );
    return result.rows[0];
  },

  async updateRecording(callSid, recordingUrl, duration) {
    const result = await db.query(
      `UPDATE calls
       SET recording_url = $1, duration = $2, status = 'completed'
       WHERE call_sid = $3
       RETURNING *`,
      [recordingUrl, duration, callSid]
    );
    return result.rows[0];
  },

  async updateTranscriptSid(callSid, transcriptSid) {
    const result = await db.query(
      `UPDATE calls SET transcript_sid = $1 WHERE call_sid = $2 RETURNING *`,
      [transcriptSid, callSid]
    );
    return result.rows[0];
  },

  async updateTranscript(transcriptSid, transcript, sentiment, summary) {
    const result = await db.query(
      `UPDATE calls
       SET transcript = $1, sentiment = $2, summary = $3
       WHERE transcript_sid = $4
       RETURNING *`,
      [transcript, sentiment, summary, transcriptSid]
    );
    return result.rows[0];
  },

  async getAll(page = 1, limit = 20) {
    // Cap limit to prevent large dumps
    const safLimit  = Math.min(parseInt(limit) || 20, 100);
    const offset    = (page - 1) * safLimit;

    const result = await db.query(
      `SELECT id, call_sid, from_number, to_number, duration, status, sentiment, summary, created_at
       FROM calls
       ORDER BY created_at DESC
       LIMIT $1 OFFSET $2`,
      [safLimit, offset]
    );
    return result.rows;
  },

  async getById(id) {
    const result = await db.query(
      `SELECT * FROM calls WHERE id = $1`,
      [id]
    );
    return result.rows[0];
  },
};

module.exports = CallModel;
