import express from "express";
import bodyParser from "body-parser";
import axios from "axios";
import dotenv from "dotenv";
import cors from "cors";
import pool from "./lib/db.js";
import https from "https";
import fs from "fs";
import jwt from "jsonwebtoken";
dotenv.config();
const app = express();
app.use(bodyParser.json());
app.use(cors());

const PYTHON_API = process.env.PYTHON_API || "https://fixzo.in:3021";
let VERIFY_TOKEN;
let WHATSAPP_TOKEN;
let PHONE_NUMBER_ID;
let agent;

// -------------------------
// Database Functions
// -------------------------
async function getUserConfig(userId) {
  try {
    const [rows] = await pool.query(
      `SELECT * FROM users WHERE id = ? LIMIT 1`,
      [userId]
    );
    return rows.length > 0 ? rows[0] : null;
  } catch (error) {
    console.error("Error fetching user config:", error);
    return null;
  }
}


const SECRET_KEY = process.env.SECRET_KEY || "mercurysofttech_secret_key";


app.post("/login", (req, res) => {
  const { user_id, email } = req.body;
  if (!user_id || !email) {
    return res.status(400).json({ error: "user_id and email are required" });
  }

  const token = jwt.sign({ user_id, email }, SECRET_KEY, { expiresIn: "1h" });
  res.json({ token });
});

// -------------------------
// Initialize Configuration
// -------------------------
async function initializeConfiguration() {
  try {
    const userId = 2;
    const userConfig = await getUserConfig(userId);

    if (!userConfig) {
      console.error("❌ Cannot proceed — missing user configuration.");
      return false;
    }

    VERIFY_TOKEN = userConfig.webhook_token;
    WHATSAPP_TOKEN = userConfig.com_access_token;
    PHONE_NUMBER_ID = userConfig.wa_phone_id;
    agent = new https.Agent({ rejectUnauthorized: false });

    console.log("🛠 Config Loaded:");
    console.log("🔑 Webhook Token:", VERIFY_TOKEN);
    console.log("📞 WhatsApp Phone ID:", PHONE_NUMBER_ID);
    console.log(
      "🧾 Access Token:",
      WHATSAPP_TOKEN ? "✅ Loaded" : "❌ Missing"
    );

    return true;
  } catch (error) {
    console.error("Error initializing configuration:", error);
    return false;
  }
}

// -------------------------
// WhatsApp Send Message
// -------------------------
async function sendWhatsAppMessage(to, payload) {
  try {
    if (!WHATSAPP_TOKEN || !PHONE_NUMBER_ID) {
      console.error("❌ WhatsApp config not loaded. Cannot send message.");
      return;
    }

    let body;
    if (typeof payload === "string") {
      // Simple text
      body = {
        messaging_product: "whatsapp",
        to,
        type: "text",
        text: { body: payload },
      };
    } else {
      // Already interactive JSON
      body = {
        messaging_product: "whatsapp",
        to,
        ...payload,
      };
    }

    const res = await axios.post(
      `https://graph.facebook.com/v19.0/${PHONE_NUMBER_ID}/messages`,
      body,
      {
        headers: {
          Authorization: `Bearer ${WHATSAPP_TOKEN}`,
          "Content-Type": "application/json",
        },
        httpsAgent: agent,
        timeout: 30000,
      }
    );

    console.log("✅ WhatsApp message sent:", res.data);
    return res.data;
  } catch (err) {
    console.error("❌ WhatsApp API Error:", err.response?.data || err.message);
    return null;
  }
}

// -------------------------
// Build WhatsApp List Payload
// -------------------------
function buildWhatsAppList(title, rows, buttonText = "Select") {
  return {
    type: "interactive",
    interactive: {
      type: "list",
      header: { type: "text", text: title },
      body: { text: "Please choose one option 👇" },
      footer: { text: "Powered by Mercury Softech" },
      action: {
        button: buttonText,
        sections: [
          {
            title: title.length > 24 ? title.slice(0, 21) + "..." : title, // truncate section title
            rows: rows.map((r) => {
              let rowTitle = r.name || r.title || "Unnamed";
              if (rowTitle.length > 24) {
                rowTitle = rowTitle.slice(0, 21) + "..."; // truncate row title
              }
              return {
                id: String(r.id),
                title: rowTitle,
                description: r.price
                  ? `₹${r.price}${r.weight ? ` – ${r.weight}` : ""}`
                  : "",
              };
            }),
          },
        ],
      },
    },
  };
}


// -------------------------
// Webhook Verification
// -------------------------
app.get("/webhook", (req, res) => {
  const mode = req.query["hub.mode"];
  const token = req.query["hub.verify_token"];
  const challenge = req.query["hub.challenge"];

  if (mode && token) {
    if (mode === "subscribe" && token === VERIFY_TOKEN) {
      console.log("✅ WEBHOOK_VERIFIED");
      res.status(200).send(challenge);
    } else {
      console.log("❌ VERIFICATION_FAILED");
      res.sendStatus(403);
    }
  } else {
    res.sendStatus(400);
  }
});

// -------------------------
// Handle Incoming Messages
// -------------------------
app.post("/webhook", async (req, res) => {
  try {
    if (!WHATSAPP_TOKEN || !PHONE_NUMBER_ID) {
      console.error("❌ WhatsApp config not loaded. Cannot process webhook.");
      return res.sendStatus(500);
    }

    res.sendStatus(200); // Acknowledge FB

    const entry = req.body.entry?.[0];
    const changes = entry?.changes?.[0];
    const messages = changes?.value?.messages;
    if (!messages || messages.length === 0) return;

    const message = messages[0];
    const from = message.from;
    let userMessage = "";
    let orderData = null;

    console.log(`📩 Message from ${from}, type: ${message.type}`);

    // Handle list reply (category/product selection)
    if (
      message.type === "interactive" &&
      message.interactive?.type === "list_reply"
    ) {
      const replyId = message.interactive.list_reply.id;
      const replyTitle = message.interactive.list_reply.title;
      userMessage = replyId; // send category/product id to Flask
      console.log(`📋 User selected from list: ${replyTitle} (${replyId})`);
    } else if (message.text?.body) {
      userMessage = message.text.body.toLowerCase().trim();
    } else if (message.type === "order") {
      orderData = message.order;
      userMessage = "order_selected";
    }

    // Forward to Flask AI
    try {
      const aiRes = await axios.post(
        `${PYTHON_API}/ai-reply`,
        { from, userMessage, order: orderData },
        {
          headers: { "Content-Type": "application/json" },
          httpsAgent: agent,
        }
      );

      if (aiRes.data) {
        if (aiRes.data.categories) {
          console.log("📂 Sending categories as WhatsApp list...");
          const listPayload = buildWhatsAppList(
            "📂 Product Categories",
            aiRes.data.categories,
            "Select Category"
          );
          await sendWhatsAppMessage(from, listPayload);
        } else if (aiRes.data.products) {
          console.log("🛍 Sending products as WhatsApp list...");
          const listPayload = buildWhatsAppList(
            "🛍 Products",
            aiRes.data.products,
            "Select Product"
          );
          await sendWhatsAppMessage(from, listPayload);
        } else if (aiRes.data.reply) {
          console.log(`💬 Sending text reply: ${aiRes.data.reply}`);
          await sendWhatsAppMessage(from, aiRes.data.reply);
        }
      }
    } catch (error) {
      console.error("❌ Error calling Flask API:", error.message);
      await sendWhatsAppMessage(
        from,
        "⚠️ Sorry, I'm experiencing technical difficulties. Please try again later."
      );
    }
  } catch (err) {
    console.error("❌ Webhook Error:", err.message);
  }
});

// -------------------------
// Razorpay Webhook
// -------------------------
app.post("/razorpay/webhook", async (req, res) => {
  try {
    console.log("💳 Razorpay webhook received:", JSON.stringify(req.body));
    await axios.post(`${PYTHON_API}/payment-webhook`, req.body, {
      timeout: 30000,
      headers: { "Content-Type": "application/json" },
    });
    res.sendStatus(200);
  } catch (err) {
    console.error("❌ Razorpay Webhook Error:", err.message);
    res.sendStatus(500);
  }
});

// -------------------------
// Health Check
// -------------------------
app.get("/health", (req, res) => {
  res.json({
    status: "OK",
    message: "Node.js server is running",
    timestamp: new Date().toISOString(),
    python_api: PYTHON_API,
    whatsapp_configured: !!(WHATSAPP_TOKEN && PHONE_NUMBER_ID),
    webhook_token_configured: !!VERIFY_TOKEN,
  });
});

// -------------------------
// Start Server
// -------------------------
async function startServer() {
  console.log("🔄 Initializing configuration...");
  const configLoaded = await initializeConfiguration();
  if (!configLoaded) {
    process.exit(1);
  }

  const sslOptions = {
    key: fs.readFileSync("ssl/private.key"),
    cert: fs.readFileSync("ssl/certificate.crt"),
  };

  const PORT = process.env.PORT || 3020;
  https.createServer(sslOptions, app).listen(PORT, () => {
    console.log(`🚀 HTTPS server running on port ${PORT}`);
    console.log(`📞 Webhook URL: https://fixzo.in:${PORT}/webhook`);
  });



}

startServer().catch((err) => {
  console.error("❌ Failed to start server:", err);
  process.exit(1);
});


