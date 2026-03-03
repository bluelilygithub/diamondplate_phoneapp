const db = require("../config/db");

const CallModel = {
  async create(callSid, from, to, callType = "monitor", agentName = null, agentNumber = null) {
    const result = await db.query(
      `INSERT INTO calls (call_sid, from_number, to_number, status, call_type, agent_name, agent_number)
       VALUES ($1, $2, $3, 'in-progress', $4, $5, $6)
       ON CONFLICT (call_sid) DO NOTHING
       RETURNING *`,
      [callSid, from, to, callType, agentName, agentNumber]
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

  async updateDisposition(id, disposition) {
    const result = await db.query(
      `UPDATE calls SET disposition = $1 WHERE id = $2 RETURNING *`,
      [disposition, id]
    );
    return result.rows[0];
  },

  async getAll(page = 1, limit = 20) {
    const safeLimit = Math.min(parseInt(limit) || 20, 100);
    const offset    = (page - 1) * safeLimit;
    const result = await db.query(
      `SELECT id, call_sid, from_number, to_number, duration, status, sentiment, summary, agent_name, agent_number, disposition, call_type, created_at
       FROM calls
       ORDER BY created_at DESC
       LIMIT $1 OFFSET $2`,
      [safeLimit, offset]
    );
    return result.rows;
  },

  async getByType(callType, page = 1, limit = 20) {
    const safeLimit = Math.min(parseInt(limit) || 20, 100);
    const offset    = (page - 1) * safeLimit;
    const result = await db.query(
      `SELECT id, call_sid, from_number, to_number, duration, status, sentiment, summary, agent_name, agent_number, disposition, call_type, created_at
       FROM calls
       WHERE call_type = $1
       ORDER BY created_at DESC
       LIMIT $2 OFFSET $3`,
      [callType, safeLimit, offset]
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
