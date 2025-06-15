<?php
require_once 'base_api.php';

class PurchaseAPI extends BaseAPI {
    public function __construct() {
        parent::__construct('purchases');
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                $this->getPurchases();
                break;
            case 'POST':
                $this->createPurchase();
                break;
            case 'PUT':
                $this->updatePurchase();
                break;
            case 'DELETE':
                $this->deletePurchase();
                break;
            default:
                $this->sendError("Method not allowed", 405);
        }
    }

    private function getPurchases() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if ($id) {
            $query = "SELECT p.*, c.name as customer_name, a.title as artwork_title 
                     FROM " . $this->table_name . " p 
                     LEFT JOIN customers c ON p.customer_id = c.id 
                     LEFT JOIN artworks a ON p.artwork_id = a.id 
                     WHERE p.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
        } else {
            $query = "SELECT p.*, c.name as customer_name, a.title as artwork_title 
                     FROM " . $this->table_name . " p 
                     LEFT JOIN customers c ON p.customer_id = c.id 
                     LEFT JOIN artworks a ON p.artwork_id = a.id 
                     ORDER BY p.created_at DESC";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse($purchases);
    }

    private function createPurchase() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $error = $this->validateRequired($data, ['customer_id', 'artwork_id', 'purchase_date', 'price']);
        if ($error) {
            $this->sendError($error);
        }

        if (!is_numeric($data['price']) || $data['price'] < 0) {
            $this->sendError("Price must be a valid positive number");
        }

        // Check if artwork is available
        $checkQuery = "SELECT status FROM artworks WHERE id = :artwork_id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':artwork_id', $data['artwork_id']);
        $checkStmt->execute();
        $artwork = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$artwork) {
            $this->sendError("Artwork not found");
        }

        if ($artwork['status'] === 'Sold') {
            $this->sendError("Artwork is already sold");
        }

        // Begin transaction
        $this->conn->beginTransaction();

        try {
            // Create purchase record
            $query = "INSERT INTO " . $this->table_name . " (customer_id, artwork_id, purchase_date, price) 
                     VALUES (:customer_id, :artwork_id, :purchase_date, :price)";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':customer_id', $data['customer_id']);
            $stmt->bindParam(':artwork_id', $data['artwork_id']);
            $stmt->bindParam(':purchase_date', $data['purchase_date']);
            $stmt->bindParam(':price', $data['price']);

            $stmt->execute();
            $purchaseId = $this->conn->lastInsertId();

            // Update artwork status to sold
            $updateQuery = "UPDATE artworks SET status = 'Sold' WHERE id = :artwork_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':artwork_id', $data['artwork_id']);
            $updateStmt->execute();

            $this->conn->commit();
            $this->sendResponse(array("message" => "Purchase created successfully", "id" => $purchaseId), 201);

        } catch (Exception $e) {
            $this->conn->rollback();
            $this->sendError("Unable to create purchase: " . $e->getMessage(), 500);
        }
    }

    private function updatePurchase() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Purchase ID is required");
        }

        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) {
            $this->sendError("Price must be a valid positive number");
        }

        $query = "UPDATE " . $this->table_name . " 
                 SET customer_id = :customer_id, artwork_id = :artwork_id, 
                     purchase_date = :purchase_date, price = :price 
                 WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':customer_id', $data['customer_id']);
        $stmt->bindParam(':artwork_id', $data['artwork_id']);
        $stmt->bindParam(':purchase_date', $data['purchase_date']);
        $stmt->bindParam(':price', $data['price']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Purchase updated successfully"));
        } else {
            $this->sendError("Unable to update purchase", 500);
        }
    }

    private function deletePurchase() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Purchase ID is required");
        }

        // Begin transaction
        $this->conn->beginTransaction();

        try {
            // Get artwork ID before deleting purchase
            $getQuery = "SELECT artwork_id FROM " . $this->table_name . " WHERE id = :id";
            $getStmt = $this->conn->prepare($getQuery);
            $getStmt->bindParam(':id', $data['id']);
            $getStmt->execute();
            $purchase = $getStmt->fetch(PDO::FETCH_ASSOC);

            if (!$purchase) {
                $this->sendError("Purchase not found");
            }

            // Delete purchase
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $data['id']);
            $stmt->execute();

            // Update artwork status back to available
            $updateQuery = "UPDATE artworks SET status = 'Available' WHERE id = :artwork_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':artwork_id', $purchase['artwork_id']);
            $updateStmt->execute();

            $this->conn->commit();
            $this->sendResponse(array("message" => "Purchase deleted successfully"));

        } catch (Exception $e) {
            $this->conn->rollback();
            $this->sendError("Unable to delete purchase: " . $e->getMessage(), 500);
        }
    }
}

$api = new PurchaseAPI();
$api->handleRequest();
?>
