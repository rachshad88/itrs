<?php
/**
 * ITRS Real-time System Validation Page
 * Check if all components are properly configured
 */

$status = [
    'checks' => [],
    'errors' => [],
    'warnings' => []
];

// Check 1: WebSocket Server
echo "<h2>ITRS Real-time System Validation</h2>";
echo "<p>Checking system components...</p>";

// Try to reach WebSocket server
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "http://localhost:3001/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 2,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strpos($response, 'status') !== false) {
    $status['checks']['websocket_server'] = '✅ WebSocket Server Running';
} else {
    $status['errors']['websocket_server'] = '❌ WebSocket Server Not Responding - Start: node realtime/server.js';
}

// Check 2: PHP curl extension
if (extension_loaded('curl')) {
    $status['checks']['php_curl'] = '✅ PHP cURL Extension Enabled';
} else {
    $status['errors']['php_curl'] = '❌ PHP cURL Extension Not Enabled - Enable in php.ini';
}

// Check 3: WebSocket config file
if (file_exists(__DIR__ . '/backend/config/websocket.php')) {
    $status['checks']['websocket_config'] = '✅ WebSocket Config File Exists';
} else {
    $status['errors']['websocket_config'] = '❌ WebSocket Config File Missing';
}

// Check 4: Realtime JS file
if (file_exists(__DIR__ . '/frontend/assets/js/realtime.js')) {
    $status['checks']['realtime_js'] = '✅ Realtime JS Client Exists';
} else {
    $status['errors']['realtime_js'] = '❌ Realtime JS Client Missing';
}

// Check 5: Server running processes
$processes = @file_get_contents('/proc/loadavg', false, null, 0, 20);
if (!$processes) {
    // Windows system - check differently
    $status['warnings']['process_check'] = '⚠️ Cannot verify running processes on this system';
}

// Check 6: Logs directory
$logDir = __DIR__ . '/logs';
if (is_dir($logDir) && is_writable($logDir)) {
    $status['checks']['logs_dir'] = '✅ Logs Directory Exists and Writable';
} else {
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
        $status['warnings']['logs_dir'] = '⚠️ Created logs directory';
    } else {
        $status['warnings']['logs_dir'] = '⚠️ Logs directory not writable';
    }
}

// Check 7: Database connection
try {
    require __DIR__ . '/backend/config/db.php';
    $pdo->query("SELECT 1");
    $status['checks']['database'] = '✅ Database Connected';
} catch (Exception $e) {
    $status['errors']['database'] = '❌ Database Connection Failed: ' . $e->getMessage();
}

// Output results
?>
<!DOCTYPE html>
<html>
<head>
    <title>ITRS Real-time System Status</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .check { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .box { border: 1px solid #ccc; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔍 ITRS Real-time System Status Check</h1>
    
    <div class="box">
        <h2>✅ Passed Checks</h2>
        <?php foreach ($status['checks'] as $check): ?>
            <p><span class="check"><?php echo $check; ?></span></p>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($status['errors'])): ?>
    <div class="box">
        <h2>❌ Errors</h2>
        <?php foreach ($status['errors'] as $error): ?>
            <p><span class="error"><?php echo $error; ?></span></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($status['warnings'])): ?>
    <div class="box">
        <h2>⚠️ Warnings</h2>
        <?php foreach ($status['warnings'] as $warning): ?>
            <p><span class="warning"><?php echo $warning; ?></span></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="box">
        <h2>🧪 Test the System</h2>
        <p><a href="/itrs/frontend/pages/websocket-debug.php">Go to Debug Console</a></p>
    </div>

    <div class="box">
        <h2>📚 Documentation</h2>
        <ul>
            <li><a href="/itrs/QUICK_REFERENCE.md">Quick Reference Card</a></li>
            <li><a href="/itrs/REALTIME_SYSTEM_COMPLETE.md">Complete Setup Guide</a></li>
            <li><a href="/itrs/WEBSOCKET_DEBUGGING.md">Debugging Guide</a></li>
        </ul>
    </div>
</body>
</html>
