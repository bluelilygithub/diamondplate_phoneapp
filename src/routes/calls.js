const express         = require("express");
const router          = express.Router();
const auth            = require("../middleware/auth");
const CallsController = require("../controllers/callsController");

router.use(auth);

router.get("/",    CallsController.getAll);
router.get("/:id", CallsController.getById);

module.exports = router;
