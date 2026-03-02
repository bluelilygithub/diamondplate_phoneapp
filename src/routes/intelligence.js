const express = require("express");
const router = express.Router();
const IntelligenceController = require("../controllers/intelligenceController");

router.post("/transcript-complete", IntelligenceController.transcriptComplete);

module.exports = router;
