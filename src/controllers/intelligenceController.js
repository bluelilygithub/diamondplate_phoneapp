const twilio    = require("twilio");
const CallModel = require("../models/callModel");

const client = twilio(process.env.TWILIO_ACCOUNT_SID, process.env.TWILIO_AUTH_TOKEN);

const IntelligenceController = {
  async transcriptComplete(req, res) {
    const { transcript_sid, event_type } = req.body;

    if (event_type !== "voice_intelligence_transcript_available") {
      return res.sendStatus(200);
    }

    try {
      // Fetch sentences and operator results in parallel — faster than sequential
      const [sentences, operatorResults] = await Promise.all([
        client.intelligence.v2.transcripts(transcript_sid).sentences.list({ limit: 1000 }),
        client.intelligence.v2.transcripts(transcript_sid).operatorResults.list(),
      ]);

      // Build speaker-labeled transcript
      const transcript = sentences
        .map((s) => `[${s.mediaChannel === 0 ? "Caller" : "Agent"}] ${s.transcript}`)
        .join("\n");

      // Extract sentiment and summary from operator results
      let sentiment = null;
      let summary   = null;

      operatorResults.forEach((result) => {
        if (result.name === "Sentiment Analysis") {
          sentiment = result.predictedLabel || null;
        }
        if (result.name === "Conversation Summary") {
          summary = result.textGenerationResults?.result || null;
        }
      });

      await CallModel.updateTranscript(transcript_sid, transcript, sentiment, summary);
      console.log(`Transcript processed: ${transcript_sid}`);
    } catch (err) {
      console.error("Failed to process transcript:", err.message);
    }

    res.sendStatus(200);
  },
};

module.exports = IntelligenceController;