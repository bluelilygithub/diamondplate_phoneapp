require("dotenv").config();
const express = require("express");
const app = express();

app.use(express.json());
app.use(express.urlencoded({ extended: false }));

// Health check
app.get("/", (req, res) => {
  res.json({ status: "ok" });
});

// Routes
app.use("/webhook", require("./src/routes/webhook"));
app.use("/webhook/intelligence", require("./src/routes/intelligence"));
app.use("/api/calls", require("./src/routes/calls"));

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Call tracker running on port ${PORT}`);
});
