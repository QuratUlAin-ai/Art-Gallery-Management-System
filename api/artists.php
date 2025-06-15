<?php
require_once 'base_api.php';

class ArtistAPI extends BaseAPI {
    public function __construct() {
        parent::__construct('artists');
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                $this->getArtists();
                break;
            case 'POST':
                $this->createArtist();
                break;
            case 'PUT':
                $this->updateArtist();
                break;
            case 'DELETE':
                $this->deleteArtist();
                break;
            default:
                $this->sendError("Method not allowed", 405);
        }
    }

    private function getArtists() {
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
        $artists = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse($artists);
    }

    private function createArtist() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $error = $this->validateRequired($data, ['name', 'nationality', 'birthdate']);
        if ($error) {
            $this->sendError($error);
        }

        $query = "INSERT INTO " . $this->table_name . " (name, nationality, birthdate) VALUES (:name, :nationality, :birthdate)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->sanitizeInput($data['name']));
        $stmt->bindParam(':nationality', $this->sanitizeInput($data['nationality']));
        $stmt->bindParam(':birthdate', $data['birthdate']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Artist created successfully", "id" => $this->conn->lastInsertId()), 201);
        } else {
            $this->sendError("Unable to create artist", 500);
        }
    }

    private function updateArtist() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Artist ID is required");
        }

        $query = "UPDATE " . $this->table_name . " SET name = :name, nationality = :nationality, birthdate = :birthdate WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':name', $this->sanitizeInput($data['name']));
        $stmt->bindParam(':nationality', $this->sanitizeInput($data['nationality']));
        $stmt->bindParam(':birthdate', $data['birthdate']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Artist updated successfully"));
        } else {
            $this->sendError("Unable to update artist", 500);
        }
    }

    private function deleteArtist() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Artist ID is required");
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['id']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Artist deleted successfully"));
        } else {
            $this->sendError("Unable to delete artist", 500);
        }
    }
}

$api = new ArtistAPI();
$api->handleRequest();
?>
