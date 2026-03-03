const twilio    = require("twilio");
const CallModel = require("../models/callModel");

const client = twilio(process.env.TWILIO_ACCOUNT_SID, process.env.TWILIO_AUTH_TOKEN);

const WebhookController = {
  async incoming(req, res) {
    const { CallSid, From, To } = req.body;
    const mode        = process.env.CALL_MODE || "monitor";
    const agentNumber = process.env.AGENT_PHONE_NUMBER;
    const agentName   = process.env.AGENT_NAME || "Agent";

    try {
      await CallModel.create(CallSid, From, To, mode, agentName, agentNumber);
    } catch (err) {
      console.error("Failed to create call record:", err.message);
    }

    const twiml = new twilio.twiml.VoiceResponse();

    if (mode === "voice_record" && agentNumber) {
      // Two-party mode — greet caller then forward to agent with recording
      twiml.say("Thank you for calling. Please hold while we connect you.");
      const dial = twiml.dial({
        record: "record-from-ringing",
        recordingStatusCallback: `${process.env.APP_URL}/webhook/recording`,
        recordingStatusCallbackMethod: "POST",
      });
      dial.number(agentNumber);
    } else {
      // Monitor mode — record only, no forwarding
      twiml.say("Thank you for calling. This call will be recorded.");
      twiml.record({
        transcribe: false,
        recordingStatusCallback: `${process.env.APP_URL}/webhook/recording`,
        recordingStatusCallbackMethod: "POST",
      });
    }

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
