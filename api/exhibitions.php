<?php
require_once 'base_api.php';

class ExhibitionAPI extends BaseAPI {
    public function __construct() {
        parent::__construct('exhibitions');
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                $this->getExhibitions();
                break;
            case 'POST':
                $this->createExhibition();
                break;
            case 'PUT':
                $this->updateExhibition();
                break;
            case 'DELETE':
                $this->deleteExhibition();
                break;
            default:
                $this->sendError("Method not allowed", 405);
        }
    }

    private function getExhibitions() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if ($id) {
            $query = "SELECT e.*, r.name as room_name, s.name as staff_name 
                     FROM " . $this->table_name . " e 
                     LEFT JOIN rooms r ON e.room_id = r.id 
                     LEFT JOIN staff s ON e.staff_id = s.id 
                     WHERE e.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
        } else {
            $query = "SELECT e.*, r.name as room_name, s.name as staff_name 
                     FROM " . $this->table_name . " e 
                     LEFT JOIN rooms r ON e.room_id = r.id 
                     LEFT JOIN staff s ON e.staff_id = s.id 
                     ORDER BY e.created_at DESC";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        $exhibitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse($exhibitions);
    }

    private function createExhibition() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $error = $this->validateRequired($data, ['name', 'start_date', 'end_date']);
        if ($error) {
            $this->sendError($error);
        }

        if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
            $this->sendError("End date must be after start date");
        }

        $query = "INSERT INTO " . $this->table_name . " (name, start_date, end_date, room_id, staff_id) 
                 VALUES (:name, :start_date, :end_date, :room_id, :staff_id)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->sanitizeInput($data['name']));
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':room_id', $data['room_id'] ?? null);
        $stmt->bindParam(':staff_id', $data['staff_id'] ?? null);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Exhibition created successfully", "id" => $this->conn->lastInsertId()), 201);
        } else {
            $this->sendError("Unable to create exhibition", 500);
        }
    }

    private function updateExhibition() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Exhibition ID is required");
        }

        if (isset($data['start_date']) && isset($data['end_date'])) {
            if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
                $this->sendError("End date must be after start date");
            }
        }

        $query = "UPDATE " . $this->table_name . " 
                 SET name = :name, start_date = :start_date, end_date = :end_date, 
                     room_id = :room_id, staff_id = :staff_id 
                 WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':name', $this->sanitizeInput($data['name']));
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':room_id', $data['room_id']);
        $stmt->bindParam(':staff_id', $data['staff_id']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Exhibition updated successfully"));
        } else {
            $this->sendError("Unable to update exhibition", 500);
        }
    }

    private function deleteExhibition() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Exhibition ID is required");
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['id']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Exhibition deleted successfully"));
        } else {
            $this->sendError("Unable to delete exhibition", 500);
        }
    }
}

$api = new ExhibitionAPI();
$api->handleRequest();
?>
