const express                  = require("express");
const router                   = express.Router();
const twilioAuth               = require("../middleware/twilioAuth");
const IntelligenceController   = require("../controllers/intelligenceController");

router.post("/transcript-complete", twilioAuth, IntelligenceController.transcriptComplete);

module.exports = router;
