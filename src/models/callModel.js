const db = require("../config/db");

const CallModel = {
  // Insert a new call record when a call arrives
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

  // Update call with recording details when recording is ready
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

  // Update call with transcript and intelligence data
  async updateTranscript(callSid, transcript, sentiment, summary) {
    const result = await db.query(
      `UPDATE calls
       SET transcript = $1, sentiment = $2, summary = $3
       WHERE call_sid = $4
       RETURNING *`,
      [transcript, sentiment, summary, callSid]
    );
    return result.rows[0];
  },

  // Get paginated list of calls
  async getAll(page = 1, limit = 20) {
    const offset = (page - 1) * limit;
    const result = await db.query(
      `SELECT id, call_sid, from_number, to_number, duration, status, sentiment, summary, created_at
       FROM calls
       ORDER BY created_at DESC
       LIMIT $1 OFFSET $2`,
      [limit, offset]
    );
    return result.rows;
  },

  // Get a single call by ID including full transcript
  async getById(id) {
    const result = await db.query(
      `SELECT * FROM calls WHERE id = $1`,
      [id]
    );
    return result.rows[0];
  },
};

module.exports = CallModel;
