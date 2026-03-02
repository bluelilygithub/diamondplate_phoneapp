const CallModel = require("../models/callModel");

const AudioController = {
  async stream(req, res) {
    try {
      const call = await CallModel.getById(req.params.id);

      if (!call) {
        return res.status(404).json({ error: "Call not found" });
      }

      if (!call.recording_url) {
        return res.status(404).json({ error: "No recording available for this call" });
      }

      // Twilio requires Basic Auth to access recordings
      const authHeader = "Basic " + Buffer.from(
        `${process.env.TWILIO_ACCOUNT_SID}:${process.env.TWILIO_AUTH_TOKEN}`
      ).toString("base64");

      // Fetch the recording from Twilio as MP3
      const recordingUrl = call.recording_url + ".mp3";

      const twilioResponse = await fetch(recordingUrl, {
        headers: { Authorization: authHeader },
      });

      if (!twilioResponse.ok) {
        console.error("Failed to fetch recording from Twilio:", twilioResponse.status);
        return res.status(502).json({ error: "Failed to fetch recording" });
      }

      // Stream audio back to browser
      res.setHeader("Content-Type", "audio/mpeg");
      res.setHeader("Accept-Ranges", "bytes");
      res.setHeader("Cache-Control", "private, max-age=3600");

      twilioResponse.body.pipeTo(
        new WritableStream({
          write(chunk) { res.write(chunk); },
          close()      { res.end(); },
          abort(err)   { console.error("Stream error:", err.message); res.end(); },
        })
      );
    } catch (err) {
      console.error("Audio stream error:", err.message);
      res.status(500).json({ error: "Failed to stream audio" });
    }
  },
};

module.exports = AudioController;
