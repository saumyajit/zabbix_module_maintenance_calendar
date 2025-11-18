<?php
namespace Modules;

use CController;
use CControllerResponseData;

class MaintenanceCalendar extends CController {
    public function init() {
        $this->disableCsrfValidation();
    }

    protected function checkPermissions() {
        return true; // Adjust permission checks as needed
    }

    protected function doAction() {
        $data = $this->fetchMaintenanceData();
        $response = new CControllerResponseData($data);
        $this->setResponse($response);
    }

    private function fetchMaintenanceData() {
        // Build the API URL dynamically based on current server
        $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $apiUrl = "$scheme://$host/api_jsonrpc.php";

        // Get Zabbix session auth token from PHP session
        session_start();
        if (!isset($_SESSION['zbx_session'])) {
            return ['error' => 'Authentication token not found. Please login to Zabbix.'];
        }
        $authToken = $_SESSION['zbx_session'];

        $request = json_encode([
            'jsonrpc' => '2.0',
            'method' => 'maintenance.get',
            'params' => [
                'output' => 'extend',
                'selectTimeperiods' => 'extend',
                'selectHosts' => ['hostid', 'host']
            ],
            'auth' => $authToken,
            'id' => 1
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => "Curl error: $error"];
        }
        curl_close($ch);

        $response = json_decode($result, true);

        if (isset($response['result'])) {
            return $response['result'];
        } elseif (isset($response['error'])) {
            return ['error' => $response['error']['data'] ?? 'Unknown API error'];
        } else {
            return ['error' => 'Unknown error fetching maintenance data'];
        }
    }
}
