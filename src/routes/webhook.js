const express           = require("express");
const router            = express.Router();
const twilioAuth        = require("../middleware/twilioAuth");
const WebhookController = require("../controllers/webhookController");

router.post("/incoming",  twilioAuth, WebhookController.incoming);
router.post("/recording", twilioAuth, WebhookController.recording);

module.exports = router;
