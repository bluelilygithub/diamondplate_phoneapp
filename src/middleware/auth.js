const crypto = require("crypto");

module.exports = (req, res, next) => {
  const key = req.headers["x-api-key"];

  if (!key || !process.env.API_SECRET_KEY) {
    return res.status(401).json({ error: "Unauthorized" });
  }

  // Timing-safe comparison to prevent timing attacks
  try {
    const provided = Buffer.from(key);
    const expected = Buffer.from(process.env.API_SECRET_KEY);

    if (
      provided.length !== expected.length ||
      !crypto.timingSafeEqual(provided, expected)
    ) {
      return res.status(401).json({ error: "Unauthorized" });
    }
  } catch {
    return res.status(401).json({ error: "Unauthorized" });
  }

  next();
};
