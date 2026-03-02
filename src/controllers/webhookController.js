const twilio = require("twilio");
const CallModel = require("../models/callModel");

const client = twilio(process.env.TWILIO_ACCOUNT_SID, process.env.TWILIO_AUTH_TOKEN);

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

  // Handles recording callback — updates call and submits to Voice Intelligence
  async recording(req, res) {
    const { CallSid, RecordingSid, RecordingUrl, RecordingDuration } = req.body;

    console.log("Recording callback received:", { CallSid, RecordingSid, RecordingUrl, RecordingDuration });

    try {
      await CallModel.updateRecording(CallSid, RecordingUrl, RecordingDuration);

      console.log("Submitting to Voice Intelligence with RecordingSid:", RecordingSid);

      // Submit recording to Voice Intelligence using the Recording SID
      const transcript = await client.intelligence.v2.transcripts.create({
        serviceSid: process.env.TWILIO_VOICE_INTELLIGENCE_SID,
        channel: {
          media_properties: {
            source_sid: RecordingSid,
          },
        },
      });

      // Save the transcript SID so we can match the VI webhook later
      await CallModel.updateTranscriptSid(CallSid, transcript.sid);

      console.log(`Voice Intelligence transcript created: ${transcript.sid}`);
    } catch (err) {
      console.error("Failed to process recording:", err);
    }

    res.sendStatus(200);
  },
};

module.exports = WebhookController;
