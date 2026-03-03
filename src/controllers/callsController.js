const CallModel = require("../models/callModel");

const CallsController = {
  async getAll(req, res) {
    const page     = parseInt(req.query.page)      || 1;
    const limit    = parseInt(req.query.limit)     || 20;
    const callType = req.query.type || null;

    try {
      const calls = callType
        ? await CallModel.getByType(callType, page, limit)
        : await CallModel.getAll(page, limit);
      res.json({ calls, page, limit });
    } catch (err) {
      res.status(500).json({ error: "Failed to fetch calls" });
    }
  },

  async getById(req, res) {
    try {
      const call = await CallModel.getById(req.params.id);
      if (!call) return res.status(404).json({ error: "Call not found" });
      res.json(call);
    } catch (err) {
      res.status(500).json({ error: "Failed to fetch call" });
    }
  },

  async updateDisposition(req, res) {
    const { disposition } = req.body;
    if (disposition === undefined) {
      return res.status(400).json({ error: "disposition is required" });
    }
    try {
      const call = await CallModel.updateDisposition(req.params.id, disposition);
      if (!call) return res.status(404).json({ error: "Call not found" });
      res.json(call);
    } catch (err) {
      res.status(500).json({ error: "Failed to update disposition" });
    }
  },
};

module.exports = CallsController;
