<?php
/**
 * WebSocket Helper for Real-time Updates
 * Emits events to the Node.js Socket.IO server
 */

class WebSocketEmitter {
    private $websocketUrl = "http://localhost:3001";
    private $timeout = 2;
    private $logFile = __DIR__ . "/../../logs/websocket.log";

    public function __construct() {
        // Create logs directory if it doesn't exist
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
    }

    /**
     * Log messages for debugging
     */
    private function log($message) {
        $timestamp = date("Y-m-d H:i:s");
        $logMessage = "[$timestamp] $message\n";
        error_log($logMessage, 3, $this->logFile);
    }

    /**
     * Emit request created event
     */
    public function requestCreated($requestId, $requestCode, $createdBy, $office, $issue) {
        $this->log("Emitting: requestCreated - ID: $requestId, Code: $requestCode");
        return $this->emit("/emit/request_created", [
            "request_id" => $requestId,
            "request_code" => $requestCode,
            "created_by" => $createdBy,
            "office" => $office,
            "issue" => $issue
        ]);
    }

    /**
     * Emit request accepted event
     */
    public function requestAccepted($requestId, $assignedTo, $createdBy) {
        $this->log("Emitting: requestAccepted - ID: $requestId, Assigned to: $assignedTo");
        return $this->emit("/emit/request_accepted", [
            "request_id" => $requestId,
            "assigned_to" => $assignedTo,
            "created_by" => $createdBy
        ]);
    }

    /**
     * Emit request finished event
     */
    public function requestFinished($requestId, $createdBy) {
        $this->log("Emitting: requestFinished - ID: $requestId");
        return $this->emit("/emit/request_finished", [
            "request_id" => $requestId,
            "created_by" => $createdBy
        ]);
    }

    /**
     * Emit request cancelled event
     */
    public function requestCancelled($requestId, $createdBy) {
        $this->log("Emitting: requestCancelled - ID: $requestId");
        return $this->emit("/emit/request_cancelled", [
            "request_id" => $requestId,
            "created_by" => $createdBy
        ]);
    }

    /**
     * Emit shared access granted event
     */
    public function sharedAccessGranted($userId, $requestCode, $grantedBy) {
        $this->log("Emitting: sharedAccessGranted - User: $userId, Code: $requestCode");
        return $this->emit("/emit/shared_access_granted", [
            "user_id" => $userId,
            "request_code" => $requestCode,
            "granted_by" => $grantedBy
        ]);
    }

    /**
     * Generic emit function with error handling
     */
    private function emit($endpoint, $data) {
        try {
            $url = $this->websocketUrl . $endpoint;
            $payload = json_encode($data);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_CONNECTTIMEOUT => $this->timeout,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
                CURLOPT_POSTFIELDS => $payload
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                $this->log("CURL Error: $curlError - Endpoint: $endpoint");
                return false;
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                $this->log("Success: $endpoint - HTTP $httpCode");
                return true;
            } else {
                $this->log("HTTP Error: $httpCode - Endpoint: $endpoint - Response: $response");
                return false;
            }

        } catch (Exception $e) {
            $this->log("Exception: " . $e->getMessage());
            return false;
        }
    }
}

// Create global instance
$wsEmitter = new WebSocketEmitter();
?>
