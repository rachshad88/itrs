# Real-time Updates with WebSockets

This guide explains how to use the WebSocket system for real-time updates in the ITRS application.

## Overview

The real-time system uses Socket.IO to push updates to all connected clients immediately when requests are created, accepted, completed, or cancelled. This replaces the need for constant page reloads or polling.

## Server Setup

### 1. Install Node.js Server

The WebSocket server runs on Node.js on port 3001.

```bash
cd realtime
npm install
node server.js
```

The server should now be running at `http://localhost:3001`.

## Frontend Integration

### 1. Include Socket.IO Client Library

Add this to your HTML file (in the `<head>` or before closing `</body>`):

```html
<script src="https://cdn.socket.io/4.8.3/socket.io.min.js"></script>
<script src="../../frontend/assets/js/realtime.js"></script>
```

### 2. Initialize the Realtime Client

After the user logs in, initialize the client with their user ID:

```javascript
// In your dashboard or main page after login
if (typeof realtimeClient !== 'undefined') {
    realtimeClient.init(<?php echo $_SESSION['user_id']; ?>);
    realtimeClient.requestNotificationPermission();
}
```

### 3. Listen for Real-time Events

The system automatically emits custom events that you can listen to:

```javascript
// Listen for request updates
document.addEventListener("request-updated", function (event) {
  const data = event.detail;
  console.log("Request updated:", data);

  if (data.event === "created") {
    // Handle new request
    console.log("New request:", data.request_code);
  } else if (data.event === "accepted") {
    // Handle accepted request
    console.log("Request accepted:", data.request_id);
  }
});

// Listen for your specific request accepted
document.addEventListener("request-accepted", function (event) {
  const data = event.detail;
  console.log("Your request was accepted by:", data.assigned_to);
  // Refresh dashboard or show notification
  location.reload();
});

// Listen for your specific request finished
document.addEventListener("request-finished", function (event) {
  const data = event.detail;
  console.log("Your request is finished:", data.request_id);
  location.reload();
});

// Listen for your specific request cancelled
document.addEventListener("request-cancelled", function (event) {
  const data = event.detail;
  console.log("Your request was cancelled:", data.request_id);
  location.reload();
});

// Listen for access granted
document.addEventListener("access-granted", function (event) {
  const data = event.detail;
  console.log("Access granted for:", data.request_code);
});
```

## Backend Integration

### 1. Use the WebSocket Emitter

PHP files automatically emit events after database operations. The WebSocket helper is already included in:

- `send_request.php` - Emits when a new request is created
- `accept_request.php` - Emits when a request is accepted
- `request_finish.php` - Emits when a request is completed
- `cancel_request.php` - Emits when a request is cancelled
- `shared_access.php` - Can emit when access is granted

### 2. Example: Manually Emit Events

If you need to emit events from other PHP files:

```php
<?php
require __DIR__ . "/../config/websocket.php";

// After creating/updating a request in database
$wsEmitter->requestCreated($requestId, $requestCode, $createdBy, $office, $issue);

// After accepting a request
$wsEmitter->requestAccepted($requestId, $assignedTo, $createdBy);

// After finishing a request
$wsEmitter->requestFinished($requestId, $createdBy);

// After cancelling a request
$wsEmitter->requestCancelled($requestId, $createdBy);

// When sharing access
$wsEmitter->sharedAccessGranted($userId, $requestCode, $grantedBy);
?>
```

## Event Types

### Global Events (Broadcast to All Users)

- `request_update` - Any request status change
  - `event`: 'created' | 'accepted' | 'finished' | 'cancelled'
  - `request_id`: ID of the request
  - `request_code`: Request ticket code
  - `status`: Current status
  - `timestamp`: When the event occurred

### User-Specific Events

- `my_request_accepted` - User's request was accepted
  - `request_id`: ID of the request
  - `assigned_to`: ID of the technician

- `my_request_finished` - User's request was completed
  - `request_id`: ID of the request

- `my_request_cancelled` - User's request was cancelled
  - `request_id`: ID of the request

- `access_granted` - User was granted access to a request
  - `request_code`: Request ticket code
  - `granted_by`: ID of the user who granted access

## Troubleshooting

### WebSocket Connection Issues

1. **Make sure Node.js server is running**

   ```bash
   node realtime/server.js
   ```

2. **Check CORS settings** in `realtime/server.js` if connecting from different domain

3. **Verify port 3001 is not blocked** by firewall

### Events Not Triggering

1. Verify the Socket.IO script is loaded (check browser console)
2. Call `realtimeClient.init(userId)` with the correct user ID
3. Check browser console for connection errors
4. Verify PHP files are using the websocket emitter correctly

### Notifications Not Showing

1. Check browser notification permissions
2. Call `realtimeClient.requestNotificationPermission()`
3. Ensure browser notifications are enabled in OS settings

## Example: Dashboard Integration

```html
<!-- dashboard.php -->
<?php session_start(); ?>
<!DOCTYPE html>
<html>
  <head>
    <script src="https://cdn.socket.io/4.8.3/socket.io.min.js"></script>
  </head>
  <body>
    <!-- Dashboard content -->

    <script src="../../frontend/assets/js/realtime.js"></script>
    <script>
      // Initialize realtime updates when page loads
      window.addEventListener('load', function() {
          realtimeClient.init(<?php echo $_SESSION['user_id']; ?>);
          realtimeClient.requestNotificationPermission();
      });

      // Listen for request updates
      document.addEventListener('request-accepted', function(event) {
          // Refresh dashboard to show updated status
          location.reload();
      });
    </script>
  </body>
</html>
```

## Performance Notes

- WebSockets maintain an open connection, so fewer HTTP requests
- Updates are pushed instantly to all connected clients
- Browser notifications provide instant user feedback
- Fallback to HTTP polling if WebSocket unavailable (automatic with Socket.IO)
