const express = require("express");
const router = express.Router();
const WebhookController = require("../controllers/webhookController");

router.post("/incoming", WebhookController.incoming);
router.post("/recording", WebhookController.recording);

module.exports = router;
