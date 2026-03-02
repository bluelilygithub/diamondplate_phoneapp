const CallModel = require("../models/callModel");

const CallsController = {
  // Returns paginated list of calls
  async getAll(req, res) {
    const page = parseInt(req.query.page) || 1;
    const limit = parseInt(req.query.limit) || 20;

    try {
      const calls = await CallModel.getAll(page, limit);
      res.json({ calls, page, limit });
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  },

  // Returns a single call with full transcript
  async getById(req, res) {
    try {
      const call = await CallModel.getById(req.params.id);
      if (!call) {
        return res.status(404).json({ error: "Call not found" });
      }
      res.json(call);
    } catch (err) {
      res.status(500).json({ error: err.message });
    }
  },
};

module.exports = CallsController;
