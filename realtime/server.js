const express = require("express");
const http = require("http");
const cors = require("cors");
const { Server } = require("socket.io");

const app = express();
app.use(cors());
app.use(express.json());

const server = http.createServer(app);

const io = new Server(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
  },
});

// Store connected clients and their user IDs
const connectedUsers = new Map();

io.on("connection", (socket) => {
  console.log("Client connected:", socket.id);

  // Register user when they connect
  socket.on("register_user", (data) => {
    const userId = data.user_id;
    connectedUsers.set(socket.id, userId);
    socket.join(`user_${userId}`);
    console.log(`User ${userId} registered with socket ${socket.id}`);
  });

  // Listen for request status updates
  socket.on("request_created", (data) => {
    console.log("Request created:", data);
    io.emit("request_update", {
      event: "created",
      request_id: data.request_id,
      request_code: data.request_code,
      created_by: data.created_by,
      office: data.office,
      issue: data.issue,
      timestamp: new Date(),
    });
  });

  socket.on("request_accepted", (data) => {
    console.log("Request accepted:", data);
    io.emit("request_update", {
      event: "accepted",
      request_id: data.request_id,
      assigned_to: data.assigned_to,
      status: "IN_PROGRESS",
      timestamp: new Date(),
    });
    // Notify specific user
    io.to(`user_${data.created_by}`).emit("my_request_accepted", {
      request_id: data.request_id,
      assigned_to: data.assigned_to,
    });
  });

  socket.on("request_finished", (data) => {
    console.log("Request finished:", data);
    io.emit("request_update", {
      event: "finished",
      request_id: data.request_id,
      status: "DONE",
      timestamp: new Date(),
    });
    io.to(`user_${data.created_by}`).emit("my_request_finished", {
      request_id: data.request_id,
    });
  });

  socket.on("request_cancelled", (data) => {
    console.log("Request cancelled:", data);
    io.emit("request_update", {
      event: "cancelled",
      request_id: data.request_id,
      status: "CANCELLED",
      timestamp: new Date(),
    });
    io.to(`user_${data.created_by}`).emit("my_request_cancelled", {
      request_id: data.request_id,
    });
  });

  socket.on("shared_access_granted", (data) => {
    console.log("Shared access granted:", data);
    io.to(`user_${data.user_id}`).emit("access_granted", {
      request_code: data.request_code,
      granted_by: data.granted_by,
    });
  });

  socket.on("disconnect", () => {
    const userId = connectedUsers.get(socket.id);
    connectedUsers.delete(socket.id);
    console.log(`Client disconnected: ${socket.id} (User: ${userId})`);
  });
});

// API endpoint to broadcast request updates (called from PHP backend)
app.post("/emit/request_created", (req, res) => {
  const { request_id, request_code, created_by, office, issue } = req.body;
  io.emit("request_update", {
    event: "created",
    request_id,
    request_code,
    created_by,
    office,
    issue,
    timestamp: new Date(),
  });
  res.json({ status: "success" });
});

app.post("/emit/request_accepted", (req, res) => {
  const { request_id, assigned_to, created_by } = req.body;
  io.emit("request_update", {
    event: "accepted",
    request_id,
    assigned_to,
    status: "IN_PROGRESS",
    timestamp: new Date(),
  });
  io.to(`user_${created_by}`).emit("my_request_accepted", {
    request_id,
    assigned_to,
  });
  res.json({ status: "success" });
});

app.post("/emit/request_finished", (req, res) => {
  const { request_id, created_by } = req.body;
  io.emit("request_update", {
    event: "finished",
    request_id,
    status: "DONE",
    timestamp: new Date(),
  });
  io.to(`user_${created_by}`).emit("my_request_finished", {
    request_id,
  });
  res.json({ status: "success" });
});

app.post("/emit/request_cancelled", (req, res) => {
  const { request_id, created_by } = req.body;
  io.emit("request_update", {
    event: "cancelled",
    request_id,
    status: "CANCELLED",
    timestamp: new Date(),
  });
  io.to(`user_${created_by}`).emit("my_request_cancelled", {
    request_id,
  });
  res.json({ status: "success" });
});

app.post("/emit/shared_access_granted", (req, res) => {
  const { user_id, request_code, granted_by } = req.body;
  io.to(`user_${user_id}`).emit("access_granted", {
    request_code,
    granted_by,
  });
  res.json({ status: "success" });
});

app.get("/", (req, res) => {
  res.json({
    status: "ok",
    message: "Socket Server Running",
    timestamp: new Date(),
  });
});

app.get("/health", (req, res) => {
  res.json({
    status: "healthy",
    server: "ITRS Realtime",
    port: 3001,
    timestamp: new Date(),
    connectedUsers: connectedUsers.size,
  });
});

server.listen(3001, () => {
  console.log("Realtime server running on port 3001");
  console.log("Health check: http://localhost:3001/health");
});
