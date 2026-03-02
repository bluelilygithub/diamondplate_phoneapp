const express                  = require("express");
const router                   = express.Router();
const IntelligenceController   = require("../controllers/intelligenceController");

// Verify the request is from our Twilio account
function verifyTwilioAccount(req, res, next) {
  const { account_sid } = req.body;
  if (!account_sid || account_sid !== process.env.TWILIO_ACCOUNT_SID) {
    console.warn("Intelligence webhook: account_sid mismatch");
    return res.status(403).send("Forbidden");
  }
  next();
}

router.post("/transcript-complete", verifyTwilioAccount, IntelligenceController.transcriptComplete);

module.exports = router;