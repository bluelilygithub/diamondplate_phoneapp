const twilio = require("twilio");

module.exports = (req, res, next) => {
  const twilioSignature = req.headers["x-twilio-signature"];
  const url             = `${process.env.APP_URL}${req.originalUrl}`;
  const params          = req.body;

  const isValid = twilio.validateRequest(
    process.env.TWILIO_AUTH_TOKEN,
    twilioSignature,
    url,
    params
  );

  if (!isValid) {
    console.warn("Invalid Twilio signature from:", req.ip);
    return res.status(403).send("Forbidden");
  }

  next();
};
