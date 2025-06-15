<?php
require_once 'base_api.php';

class RoomAPI extends BaseAPI {
    public function __construct() {
        parent::__construct('rooms');
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                $this->getRooms();
                break;
            case 'POST':
                $this->createRoom();
                break;
            case 'PUT':
                $this->updateRoom();
                break;
            case 'DELETE':
                $this->deleteRoom();
                break;
            default:
                $this->sendError("Method not allowed", 405);
        }
    }

    private function getRooms() {
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
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse($rooms);
    }

    private function createRoom() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $error = $this->validateRequired($data, ['name', 'capacity']);
        if ($error) {
            $this->sendError($error);
        }

        $query = "INSERT INTO " . $this->table_name . " (name, capacity) VALUES (:name, :capacity)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->sanitizeInput($data['name']));
        $stmt->bindParam(':capacity', $data['capacity']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Room created successfully", "id" => $this->conn->lastInsertId()), 201);
        } else {
            $this->sendError("Unable to create room", 500);
        }
    }

    private function updateRoom() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Room ID is required");
        }

        $query = "UPDATE " . $this->table_name . " SET name = :name, capacity = :capacity WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':name', $this->sanitizeInput($data['name']));
        $stmt->bindParam(':capacity', $data['capacity']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Room updated successfully"));
        } else {
            $this->sendError("Unable to update room", 500);
        }
    }

    private function deleteRoom() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Room ID is required");
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['id']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Room deleted successfully"));
        } else {
            $this->sendError("Unable to delete room", 500);
        }
    }
}

$api = new RoomAPI();
$api->handleRequest();
?>
