const twilio   = require("twilio");
const CallModel = require("../models/callModel");

const client = twilio(process.env.TWILIO_ACCOUNT_SID, process.env.TWILIO_AUTH_TOKEN);

const WebhookController = {
  async incoming(req, res) {
    const { CallSid, From, To } = req.body;

    try {
      await CallModel.create(CallSid, From, To);
    } catch (err) {
      console.error("Failed to create call record:", err.message);
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

  async recording(req, res) {
    const { CallSid, RecordingSid, RecordingUrl, RecordingDuration } = req.body;

    try {
      await CallModel.updateRecording(CallSid, RecordingUrl, RecordingDuration);

      const transcript = await client.intelligence.v2.transcripts.create({
        serviceSid: process.env.TWILIO_VOICE_INTELLIGENCE_SID,
        channel: {
          media_properties: {
            source_sid: RecordingSid,
          },
        },
      });

      await CallModel.updateTranscriptSid(CallSid, transcript.sid);
      console.log(`Voice Intelligence job created for call: ${CallSid}`);
    } catch (err) {
      console.error("Failed to process recording:", err.message);
    }

    res.sendStatus(200);
  },
};

module.exports = WebhookController;
