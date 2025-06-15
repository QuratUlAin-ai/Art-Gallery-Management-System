<?php
require_once 'base_api.php';

class TicketAPI extends BaseAPI {
    public function __construct() {
        parent::__construct('tickets');
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                $this->getTickets();
                break;
            case 'POST':
                $this->createTicket();
                break;
            case 'PUT':
                $this->updateTicket();
                break;
            case 'DELETE':
                $this->deleteTicket();
                break;
            default:
                $this->sendError("Method not allowed", 405);
        }
    }

    private function getTickets() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if ($id) {
            $query = "SELECT t.*, c.name as customer_name, e.name as exhibition_name 
                     FROM " . $this->table_name . " t 
                     LEFT JOIN customers c ON t.customer_id = c.id 
                     LEFT JOIN exhibitions e ON t.exhibition_id = e.id 
                     WHERE t.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
        } else {
            $query = "SELECT t.*, c.name as customer_name, e.name as exhibition_name 
                     FROM " . $this->table_name . " t 
                     LEFT JOIN customers c ON t.customer_id = c.id 
                     LEFT JOIN exhibitions e ON t.exhibition_id = e.id 
                     ORDER BY t.created_at DESC";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse($tickets);
    }

    private function createTicket() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $error = $this->validateRequired($data, ['customer_id', 'exhibition_id', 'purchase_date', 'price']);
        if ($error) {
            $this->sendError($error);
        }

        // Validate price
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            $this->sendError("Price must be a valid positive number");
        }

        $query = "INSERT INTO " . $this->table_name . " (customer_id, exhibition_id, purchase_date, price) 
                 VALUES (:customer_id, :exhibition_id, :purchase_date, :price)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':customer_id', $data['customer_id']);
        $stmt->bindParam(':exhibition_id', $data['exhibition_id']);
        $stmt->bindParam(':purchase_date', $data['purchase_date']);
        $stmt->bindParam(':price', $data['price']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Ticket created successfully", "id" => $this->conn->lastInsertId()), 201);
        } else {
            $this->sendError("Unable to create ticket", 500);
        }
    }

    private function updateTicket() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Ticket ID is required");
        }

        // Validate price if provided
        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) {
            $this->sendError("Price must be a valid positive number");
        }

        $query = "UPDATE " . $this->table_name . " 
                 SET customer_id = :customer_id, exhibition_id = :exhibition_id, 
                     purchase_date = :purchase_date, price = :price 
                 WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':customer_id', $data['customer_id']);
        $stmt->bindParam(':exhibition_id', $data['exhibition_id']);
        $stmt->bindParam(':purchase_date', $data['purchase_date']);
        $stmt->bindParam(':price', $data['price']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Ticket updated successfully"));
        } else {
            $this->sendError("Unable to update ticket", 500);
        }
    }

    private function deleteTicket() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Ticket ID is required");
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['id']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Ticket deleted successfully"));
        } else {
            $this->sendError("Unable to delete ticket", 500);
        }
    }
}

$api = new TicketAPI();
$api->handleRequest();
?>
