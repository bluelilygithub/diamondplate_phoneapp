const twilio = require("twilio");
const CallModel = require("../models/callModel");

const WebhookController = {
  // Handles inbound call — returns TwiML and creates call record
  async incoming(req, res) {
    const { CallSid, From, To } = req.body;

    try {
      await CallModel.create(CallSid, From, To);
    } catch (err) {
      console.error("Failed to create call record:", err);
    }

    const twiml = new twilio.twiml.VoiceResponse();
    twiml.say("Thank you for calling. This call will be recorded.");
    twiml.record({
      transcribe: false,
      recordingStatusCallback: `${process.env.APP_URL}/webhook/recording`,
      recordingStatusCallbackMethod: "POST",
    });

    res.type("text/xml");
    res.send(twiml.toString());
  },

  // Handles recording callback — updates call with recording URL
  async recording(req, res) {
    const { CallSid, RecordingUrl, RecordingDuration } = req.body;

    try {
      await CallModel.updateRecording(CallSid, RecordingUrl, RecordingDuration);
    } catch (err) {
      console.error("Failed to update recording:", err);
    }

    res.sendStatus(200);
  },
};

module.exports = WebhookController;
