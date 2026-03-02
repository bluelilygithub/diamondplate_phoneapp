require("dotenv").config();
const express = require("express");
const app = express();

app.use(express.json());
app.use(express.urlencoded({ extended: false }));

// Health check — Railway uses this to confirm app is running
app.get("/", (req, res) => {
  res.json({ status: "ok" });
});

// Routes (to be built out)
// app.use("/webhook", require("./src/webhook"));
// app.use("/api/calls", require("./src/calls"));

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Call tracker running on port ${PORT}`);
});
