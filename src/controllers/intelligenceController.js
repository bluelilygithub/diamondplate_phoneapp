const twilio = require("twilio");
const CallModel = require("../models/callModel");

const client = twilio(process.env.TWILIO_ACCOUNT_SID, process.env.TWILIO_AUTH_TOKEN);

const IntelligenceController = {
  // Voice Intelligence calls this when transcription is complete
  async transcriptComplete(req, res) {
    console.log("VI webhook received:", JSON.stringify(req.body));

    const { TranscriptSid, Status } = req.body;

    console.log("TranscriptSid:", TranscriptSid, "Status:", Status);

    if (Status !== "completed") {
      console.log("Ignoring non-completed status:", Status);
      return res.sendStatus(200);
    }

    try {
      // Fetch the full transcript with sentences
      const sentences = await client.intelligence.v2
        .transcripts(TranscriptSid)
        .sentences.list();

      // Build speaker-labeled transcript
      const transcript = sentences
        .map((s) => `[${s.mediaChannel === 0 ? "Caller" : "Agent"}] ${s.transcript}`)
        .join("\n");

      // Fetch operator results (sentiment, summary)
      const operatorResults = await client.intelligence.v2
        .transcripts(TranscriptSid)
        .operatorResults.list();

      let sentiment = null;
      let summary = null;

      operatorResults.forEach((result) => {
        if (result.name === "Sentiment Analysis") {
          sentiment = result.extractedResults?.sentiment || null;
        }
        if (result.name === "Conversation Summary") {
          summary = result.extractedResults?.summary || null;
        }
      });

      // Update the call record
      await CallModel.updateTranscript(TranscriptSid, transcript, sentiment, summary);

      console.log(`Transcript saved for: ${TranscriptSid}`);
    } catch (err) {
      console.error("Failed to process transcript:", err);
    }

    res.sendStatus(200);
  },
};

module.exports = IntelligenceController;
