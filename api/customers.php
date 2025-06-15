<?php
require_once 'base_api.php';

class CustomerAPI extends BaseAPI {
    public function __construct() {
        parent::__construct('customers');
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                $this->getCustomers();
                break;
            case 'POST':
                $this->createCustomer();
                break;
            case 'PUT':
                $this->updateCustomer();
                break;
            case 'DELETE':
                $this->deleteCustomer();
                break;
            default:
                $this->sendError("Method not allowed", 405);
        }
    }

    private function getCustomers() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if ($id) {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
        } else {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse($customers);
    }

    private function createCustomer() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $error = $this->validateRequired($data, ['name', 'email', 'phone', 'address']);
        if ($error) {
            $this->sendError($error);
        }

        $query = "INSERT INTO " . $this->table_name . " (name, email, phone, address) VALUES (:name, :email, :phone, :address)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->sanitizeInput($data['name']));
        $stmt->bindParam(':email', $this->sanitizeInput($data['email']));
        $stmt->bindParam(':phone', $this->sanitizeInput($data['phone']));
        $stmt->bindParam(':address', $this->sanitizeInput($data['address']));

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Customer created successfully", "id" => $this->conn->lastInsertId()), 201);
        } else {
            $this->sendError("Unable to create customer", 500);
        }
    }

    private function updateCustomer() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Customer ID is required");
        }

        $query = "UPDATE " . $this->table_name . " SET name = :name, email = :email, phone = :phone, address = :address WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':name', $this->sanitizeInput($data['name']));
        $stmt->bindParam(':email', $this->sanitizeInput($data['email']));
        $stmt->bindParam(':phone', $this->sanitizeInput($data['phone']));
        $stmt->bindParam(':address', $this->sanitizeInput($data['address']));

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Customer updated successfully"));
        } else {
            $this->sendError("Unable to update customer", 500);
        }
    }

    private function deleteCustomer() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Customer ID is required");
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['id']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Customer deleted successfully"));
        } else {
            $this->sendError("Unable to delete customer", 500);
        }
    }
}

$api = new CustomerAPI();
$api->handleRequest();
?>
