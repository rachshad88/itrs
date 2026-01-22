/**
 * WebSocket Client for Real-time Updates
 * Connects to Node.js Socket.IO server and listens for updates
 */

class RealtimeClient {
  constructor() {
    this.socket = null;
    this.userId = null;
    this.initialized = false;
    this.reconnectAttempts = 0;
  }

  /**
   * Initialize connection to websocket server
   */
  init(userId) {
    this.userId = userId;
    console.log(`[RealtimeClient] Initializing with user ID: ${userId}`);

    // Check if Socket.IO client is available
    if (typeof io === "undefined") {
      console.error(
        '[RealtimeClient] Socket.IO client not loaded. Add: <script src="https://cdn.socket.io/4.8.3/socket.io.min.js"></script>',
      );
      return false;
    }

    try {
      this.socket = io("http://localhost:3001", {
        reconnection: true,
        reconnectionDelay: 1000,
        reconnectionDelayMax: 5000,
        reconnectionAttempts: 10,
        transports: ["websocket", "polling"],
      });

      this.setupListeners();
      this.initialized = true;
      console.log("[RealtimeClient] Client initialized successfully");
      return true;
    } catch (error) {
      console.error("[RealtimeClient] Failed to initialize:", error);
      return false;
    }
  }

  /**
   * Register current user
   */
  registerUser() {
    if (this.socket && this.userId) {
      this.socket.emit("register_user", { user_id: this.userId });
      console.log(`[RealtimeClient] User ${this.userId} registered`);
    }
  }

  /**
   * Setup event listeners
   */
  setupListeners() {
    // Connection events
    this.socket.on("connect", () => {
      console.log("[RealtimeClient] Connected to server");
      this.reconnectAttempts = 0;
      this.registerUser();
    });

    this.socket.on("connect_error", (error) => {
      console.error("[RealtimeClient] Connection error:", error);
    });

    this.socket.on("disconnect", () => {
      console.warn("[RealtimeClient] Disconnected from server");
    });

    this.socket.on("reconnect_attempt", () => {
      this.reconnectAttempts++;
      console.log(
        `[RealtimeClient] Reconnect attempt ${this.reconnectAttempts}`,
      );
    });

    // Request updates
    this.socket.on("request_update", (data) => {
      console.log("[RealtimeClient] Received request_update event:", data);
      this.handleRequestUpdate(data);
    });

    // User-specific events
    this.socket.on("my_request_accepted", (data) => {
      console.log("[RealtimeClient] Received my_request_accepted event:", data);
      this.handleRequestAccepted(data);
    });

    this.socket.on("my_request_finished", (data) => {
      console.log("[RealtimeClient] Received my_request_finished event:", data);
      this.handleRequestFinished(data);
    });

    this.socket.on("my_request_cancelled", (data) => {
      console.log(
        "[RealtimeClient] Received my_request_cancelled event:",
        data,
      );
      this.handleRequestCancelled(data);
    });

    // Access events
    this.socket.on("access_granted", (data) => {
      console.log("[RealtimeClient] Received access_granted event:", data);
      this.handleAccessGranted(data);
    });
  }

  /**
   * Handle general request updates
   */
  handleRequestUpdate(data) {
    console.log("[RealtimeClient] Dispatching request-updated custom event");
    const event = new CustomEvent("request-updated", { detail: data });
    document.dispatchEvent(event);
  }

  /**
   * Handle request accepted
   */
  handleRequestAccepted(data) {
    console.log("[RealtimeClient] Dispatching request-accepted custom event");
    const event = new CustomEvent("request-accepted", { detail: data });
    document.dispatchEvent(event);
    this.showNotification("Your request has been accepted!");
  }

  /**
   * Handle request finished
   */
  handleRequestFinished(data) {
    console.log("[RealtimeClient] Dispatching request-finished custom event");
    const event = new CustomEvent("request-finished", { detail: data });
    document.dispatchEvent(event);
    this.showNotification("Your request has been completed!");
  }

  /**
   * Handle request cancelled
   */
  handleRequestCancelled(data) {
    console.log("[RealtimeClient] Dispatching request-cancelled custom event");
    const event = new CustomEvent("request-cancelled", { detail: data });
    document.dispatchEvent(event);
    this.showNotification("Your request has been cancelled.");
  }

  /**
   * Handle access granted
   */
  handleAccessGranted(data) {
    console.log("[RealtimeClient] Dispatching access-granted custom event");
    const event = new CustomEvent("access-granted", { detail: data });
    document.dispatchEvent(event);
    this.showNotification("Access granted for request: " + data.request_code);
  }

  /**
   * Show browser notification
   */
  showNotification(message) {
    if ("Notification" in window && Notification.permission === "granted") {
      console.log("[RealtimeClient] Showing browser notification:", message);
      new Notification("ITRS Update", { body: message });
    }
  }

  /**
   * Request browser notification permission
   */
  requestNotificationPermission() {
    if ("Notification" in window && Notification.permission === "default") {
      Notification.requestPermission().then((permission) => {
        console.log("[RealtimeClient] Notification permission:", permission);
      });
    }
  }

  /**
   * Disconnect from server
   */
  disconnect() {
    if (this.socket) {
      this.socket.disconnect();
      this.initialized = false;
      console.log("[RealtimeClient] Disconnected");
    }
  }
}

// Global instance
const realtimeClient = new RealtimeClient();
