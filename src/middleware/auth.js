const crypto = require("crypto");

module.exports = (req, res, next) => {
  // Accept key from header (API calls) or query string (audio src URLs)
  const key = req.headers["x-api-key"] || req.query.api_key;

  if (!key || !process.env.API_SECRET_KEY) {
    return res.status(401).json({ error: "Unauthorized" });
  }

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
