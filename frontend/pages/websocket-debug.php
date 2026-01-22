<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITRS WebSocket Debug Console</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background-color: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        h1 {
            color: #4ec9b0;
            margin-bottom: 20px;
        }
        
        .section {
            background-color: #252526;
            border: 1px solid #3e3e42;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .status.connecting {
            background-color: #f5a623;
            color: #000;
        }
        
        .status.connected {
            background-color: #00d65f;
            color: #000;
        }
        
        .status.disconnected {
            background-color: #ff3d3d;
            color: #fff;
        }
        
        .console-output {
            background-color: #1e1e1e;
            border: 1px solid #3e3e42;
            border-radius: 3px;
            padding: 10px;
            height: 300px;
            overflow-y: auto;
            margin: 10px 0;
            font-size: 12px;
            line-height: 1.5;
        }
        
        .console-log {
            color: #d4d4d4;
            margin: 2px 0;
        }
        
        .console-info {
            color: #4ec9b0;
            margin: 2px 0;
        }
        
        .console-error {
            color: #f48771;
            margin: 2px 0;
        }
        
        .console-success {
            color: #00d65f;
            margin: 2px 0;
        }
        
        .buttons {
            margin: 10px 0;
        }
        
        button {
            background-color: #0e639c;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 5px;
            font-size: 12px;
        }
        
        button:hover {
            background-color: #1177bb;
        }
        
        button:disabled {
            background-color: #666;
            cursor: not-allowed;
        }
        
        input {
            background-color: #3e3e42;
            color: #d4d4d4;
            border: 1px solid #555;
            padding: 5px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        
        label {
            display: inline-block;
            margin-right: 10px;
            min-width: 100px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔌 ITRS WebSocket Debug Console</h1>
        
        <div class="section">
            <h2>Connection Status</h2>
            <div>
                <span class="status disconnected" id="connectionStatus">DISCONNECTED</span>
                <span id="userInfo">User: Not connected</span>
            </div>
            <div style="margin-top: 10px;">
                <label>Test User ID:</label>
                <input type="number" id="userIdInput" value="1" min="1" style="width: 100px;">
                <button onclick="connectWebSocket()">Connect</button>
                <button onclick="disconnectWebSocket()">Disconnect</button>
            </div>
        </div>
        
        <div class="grid">
            <div class="section">
                <h2>Server Health Check</h2>
                <button onclick="checkServerHealth()" style="width: 100%;">Check Server Status</button>
                <div class="console-output" id="healthOutput"></div>
            </div>
            
            <div class="section">
                <h2>WebSocket Events</h2>
                <div class="buttons">
                    <button onclick="simulateRequestCreated()" title="Emit a request created event">Request Created</button>
                    <button onclick="simulateRequestAccepted()" title="Emit a request accepted event">Request Accepted</button>
                    <button onclick="simulateRequestFinished()" title="Emit a request finished event">Request Finished</button>
                    <button onclick="simulateRequestCancelled()" title="Emit a request cancelled event">Request Cancelled</button>
                </div>
                <div class="console-output" id="eventOutput"></div>
            </div>
        </div>
        
        <div class="section">
            <h2>Real-time Events Log</h2>
            <button onclick="clearLogs()" style="margin-bottom: 10px;">Clear Logs</button>
            <div class="console-output" id="consoleOutput"></div>
        </div>
    </div>

    <script src="https://cdn.socket.io/4.8.3/socket.io.min.js"></script>
    <script src="../frontend/assets/js/realtime.js"></script>
    
    <script>
        const consoleOutput = document.getElementById('consoleOutput');
        const healthOutput = document.getElementById('healthOutput');
        const eventOutput = document.getElementById('eventOutput');
        const connectionStatus = document.getElementById('connectionStatus');
        const userInfo = document.getElementById('userInfo');

        function log(type, message) {
            const timestamp = new Date().toLocaleTimeString();
            const logEntry = document.createElement('div');
            logEntry.className = `console-${type}`;
            logEntry.textContent = `[${timestamp}] ${message}`;
            consoleOutput.appendChild(logEntry);
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
        }

        function connectWebSocket() {
            const userId = parseInt(document.getElementById('userIdInput').value);
            if (!userId || userId < 1) {
                alert('Please enter a valid user ID');
                return;
            }

            connectionStatus.textContent = 'CONNECTING...';
            connectionStatus.className = 'status connecting';

            if (realtimeClient.init(userId)) {
                // Wait a moment for connection
                setTimeout(() => {
                    if (realtimeClient.socket && realtimeClient.socket.connected) {
                        connectionStatus.textContent = 'CONNECTED';
                        connectionStatus.className = 'status connected';
                        userInfo.textContent = `User: ${userId}`;
                        log('success', `Connected to WebSocket server as user ${userId}`);
                    } else {
                        connectionStatus.textContent = 'WAITING...';
                        connectionStatus.className = 'status connecting';
                        log('info', `Attempting to connect as user ${userId}...`);
                    }
                }, 500);

                // Listen for connection success
                realtimeClient.socket.on('connect', () => {
                    connectionStatus.textContent = 'CONNECTED';
                    connectionStatus.className = 'status connected';
                    log('success', 'WebSocket connected');
                });

                realtimeClient.socket.on('disconnect', () => {
                    connectionStatus.textContent = 'DISCONNECTED';
                    connectionStatus.className = 'status disconnected';
                    log('error', 'WebSocket disconnected');
                });

                // Listen for all events
                document.addEventListener('request-updated', (e) => {
                    log('info', `✓ request-updated: ${JSON.stringify(e.detail)}`);
                });

                document.addEventListener('request-accepted', (e) => {
                    log('success', `✓ request-accepted: ${JSON.stringify(e.detail)}`);
                });

                document.addEventListener('request-finished', (e) => {
                    log('success', `✓ request-finished: ${JSON.stringify(e.detail)}`);
                });

                document.addEventListener('request-cancelled', (e) => {
                    log('error', `✓ request-cancelled: ${JSON.stringify(e.detail)}`);
                });

                document.addEventListener('access-granted', (e) => {
                    log('success', `✓ access-granted: ${JSON.stringify(e.detail)}`);
                });
            }
        }

        function disconnectWebSocket() {
            if (realtimeClient.socket) {
                realtimeClient.disconnect();
                connectionStatus.textContent = 'DISCONNECTED';
                connectionStatus.className = 'status disconnected';
                userInfo.textContent = 'User: Not connected';
                log('info', 'WebSocket disconnected');
            }
        }

        function checkServerHealth() {
            const timestamp = new Date().toLocaleTimeString();
            fetch('http://localhost:3001/health')
                .then(res => res.json())
                .then(data => {
                    const output = `
[${timestamp}] ✓ Server Health Check
Status: ${data.status}
Server: ${data.server}
Port: ${data.port}
Connected Users: ${data.connectedUsers}
                    `;
                    healthOutput.innerHTML = `<div class="console-success">${output.trim()}</div>`;
                    log('success', `Server is healthy - ${data.connectedUsers} connected users`);
                })
                .catch(error => {
                    const output = `
[${timestamp}] ✗ Server Connection Failed
Error: ${error.message}
Make sure the Node.js server is running on port 3001!
                    `;
                    healthOutput.innerHTML = `<div class="console-error">${output.trim()}</div>`;
                    log('error', `Server health check failed: ${error.message}`);
                });
        }

        function simulateRequestCreated() {
            const request_id = Math.floor(Math.random() * 1000);
            const data = {
                request_id,
                request_code: `IT-2026-${String(request_id).padStart(4, '0')}`,
                created_by: 1,
                office: 'Test Office',
                issue: 'Test Issue'
            };

            fetch('http://localhost:3001/emit/request_created', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(() => {
                log('success', `Simulated: request_created - ${data.request_code}`);
            })
            .catch(error => log('error', `Failed to simulate event: ${error.message}`));
        }

        function simulateRequestAccepted() {
            const data = {
                request_id: Math.floor(Math.random() * 1000),
                assigned_to: 2,
                created_by: 1
            };

            fetch('http://localhost:3001/emit/request_accepted', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(() => {
                log('success', `Simulated: request_accepted - ID: ${data.request_id}`);
            })
            .catch(error => log('error', `Failed to simulate event: ${error.message}`));
        }

        function simulateRequestFinished() {
            const data = {
                request_id: Math.floor(Math.random() * 1000),
                created_by: 1
            };

            fetch('http://localhost:3001/emit/request_finished', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(() => {
                log('success', `Simulated: request_finished - ID: ${data.request_id}`);
            })
            .catch(error => log('error', `Failed to simulate event: ${error.message}`));
        }

        function simulateRequestCancelled() {
            const data = {
                request_id: Math.floor(Math.random() * 1000),
                created_by: 1
            };

            fetch('http://localhost:3001/emit/request_cancelled', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(() => {
                log('success', `Simulated: request_cancelled - ID: ${data.request_id}`);
            })
            .catch(error => log('error', `Failed to simulate event: ${error.message}`));
        }

        function clearLogs() {
            consoleOutput.innerHTML = '';
        }

        // Auto-check server health on page load
        window.addEventListener('load', () => {
            log('info', 'Debug console loaded');
            setTimeout(checkServerHealth, 500);
        });
    </script>
</body>
</html>
