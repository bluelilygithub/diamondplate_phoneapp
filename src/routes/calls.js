const express         = require("express");
const router          = express.Router();
const auth            = require("../middleware/auth");
const CallsController = require("../controllers/callsController");
const AudioController = require("../controllers/audioController");

router.use(auth);

router.get("/",           CallsController.getAll);
router.get("/:id",        CallsController.getById);
router.get("/:id/audio",  AudioController.stream);

module.exports = router;
