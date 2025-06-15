<?php
require_once 'base_api.php';

class StaffAPI extends BaseAPI {
    public function __construct() {
        parent::__construct('staff');
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                $this->getStaff();
                break;
            case 'POST':
                $this->createStaff();
                break;
            case 'PUT':
                $this->updateStaff();
                break;
            case 'DELETE':
                $this->deleteStaff();
                break;
            default:
                $this->sendError("Method not allowed", 405);
        }
    }

    private function getStaff() {
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
        $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse($staff);
    }

    private function createStaff() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $error = $this->validateRequired($data, ['name', 'role', 'email', 'phone']);
        if ($error) {
            $this->sendError($error);
        }

        // Validate role
        $validRoles = ['Manager', 'Curator', 'Security', 'Guide', 'Administrator'];
        if (!in_array($data['role'], $validRoles)) {
            $this->sendError("Invalid role. Must be one of: " . implode(', ', $validRoles));
        }

        $query = "INSERT INTO " . $this->table_name . " (name, role, email, phone) VALUES (:name, :role, :email, :phone)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->sanitizeInput($data['name']));
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':email', $this->sanitizeInput($data['email']));
        $stmt->bindParam(':phone', $this->sanitizeInput($data['phone']));

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Staff member created successfully", "id" => $this->conn->lastInsertId()), 201);
        } else {
            $this->sendError("Unable to create staff member", 500);
        }
    }

    private function updateStaff() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Staff ID is required");
        }

        // Validate role if provided
        if (isset($data['role'])) {
            $validRoles = ['Manager', 'Curator', 'Security', 'Guide', 'Administrator'];
            if (!in_array($data['role'], $validRoles)) {
                $this->sendError("Invalid role. Must be one of: " . implode(', ', $validRoles));
            }
        }

        $query = "UPDATE " . $this->table_name . " SET name = :name, role = :role, email = :email, phone = :phone WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':name', $this->sanitizeInput($data['name']));
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':email', $this->sanitizeInput($data['email']));
        $stmt->bindParam(':phone', $this->sanitizeInput($data['phone']));

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Staff member updated successfully"));
        } else {
            $this->sendError("Unable to update staff member", 500);
        }
    }

    private function deleteStaff() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Staff ID is required");
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['id']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Staff member deleted successfully"));
        } else {
            $this->sendError("Unable to delete staff member", 500);
        }
    }
}

$api = new StaffAPI();
$api->handleRequest();
?>
