<?php
require_once 'base_api.php';

class DashboardAPI extends BaseAPI {
    public function __construct() {
        parent::__construct('');
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'GET') {
            $this->getDashboardData();
        } else {
            $this->sendError("Method not allowed", 405);
        }
    }

    private function getDashboardData() {
    $data = array();

    try {
        // Check if database connection exists
        if (!$this->conn) {
            throw new Exception("No database connection");
        }

        // Initialize with default values
        $data = array(
            'total_customers' => 0,
            'active_exhibitions' => 0,
            'total_artworks' => 0,
            'monthly_ticket_revenue' => 0,
            'monthly_purchase_revenue' => 0,
            'total_monthly_revenue' => 0,
            'recent_transactions' => array()
        );

        // Try to get actual data, but don't fail if tables don't exist
        try {
            // Total customers
            $query = "SELECT COUNT(*) as total FROM customers";
            $stmt = $this->conn->prepare($query);
            if ($stmt && $stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $data['total_customers'] = $result['total'] ?? 0;
            }
        } catch (Exception $e) {
            error_log("Error getting customers count: " . $e->getMessage());
        }

        try {
            // Active exhibitions
            $query = "SELECT COUNT(*) as total FROM exhibitions WHERE end_date >= CURDATE()";
            $stmt = $this->conn->prepare($query);
            if ($stmt && $stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $data['active_exhibitions'] = $result['total'] ?? 0;
            }
        } catch (Exception $e) {
            error_log("Error getting exhibitions count: " . $e->getMessage());
        }

        try {
            // Total artworks
            $query = "SELECT COUNT(*) as total FROM artworks";
            $stmt = $this->conn->prepare($query);
            if ($stmt && $stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $data['total_artworks'] = $result['total'] ?? 0;
            }
        } catch (Exception $e) {
            error_log("Error getting artworks count: " . $e->getMessage());
        }

        // Add debug info
        $data['debug_info'] = array(
            'database_connected' => $this->conn ? true : false,
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => phpversion()
        );

    } catch (Exception $e) {
        error_log("Dashboard API Error: " . $e->getMessage());
        // Return default data with error info
        $data = array(
            'total_customers' => 0,
            'active_exhibitions' => 0,
            'total_artworks' => 0,
            'monthly_ticket_revenue' => 0,
            'monthly_purchase_revenue' => 0,
            'total_monthly_revenue' => 0,
            'recent_transactions' => array(),
            'error' => $e->getMessage(),
            'debug_info' => array(
                'database_connected' => false,
                'timestamp' => date('Y-m-d H:i:s'),
                'error_details' => $e->getMessage()
            )
        );
    }

    $this->sendResponse($data);
}
}

$api = new DashboardAPI();
$api->handleRequest();
?>
