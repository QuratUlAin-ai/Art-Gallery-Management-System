<?php
require_once 'base_api.php';

class ArtworkAPI extends BaseAPI {
    public function __construct() {
        parent::__construct('artworks');
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch($method) {
            case 'GET':
                $this->getArtworks();
                break;
            case 'POST':
                $this->createArtwork();
                break;
            case 'PUT':
                $this->updateArtwork();
                break;
            case 'DELETE':
                $this->deleteArtwork();
                break;
            default:
                $this->sendError("Method not allowed", 405);
        }
    }

    private function getArtworks() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if ($id) {
            $query = "SELECT a.*, ar.name as artist_name, r.name as room_name 
                     FROM " . $this->table_name . " a 
                     LEFT JOIN artists ar ON a.artist_id = ar.id 
                     LEFT JOIN rooms r ON a.room_id = r.id 
                     WHERE a.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
        } else {
            $query = "SELECT a.*, ar.name as artist_name, r.name as room_name 
                     FROM " . $this->table_name . " a 
                     LEFT JOIN artists ar ON a.artist_id = ar.id 
                     LEFT JOIN rooms r ON a.room_id = r.id 
                     ORDER BY a.created_at DESC";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        $artworks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse($artworks);
    }

    private function createArtwork() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $error = $this->validateRequired($data, ['title', 'year', 'medium', 'artist_id']);
        if ($error) {
            $this->sendError($error);
        }

        $query = "INSERT INTO " . $this->table_name . " (title, year, medium, status, artist_id, room_id, price) 
                 VALUES (:title, :year, :medium, :status, :artist_id, :room_id, :price)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':title', $this->sanitizeInput($data['title']));
        $stmt->bindParam(':year', $data['year']);
        $stmt->bindParam(':medium', $this->sanitizeInput($data['medium']));
        $stmt->bindParam(':status', $data['status'] ?? 'Available');
        $stmt->bindParam(':artist_id', $data['artist_id']);
        $stmt->bindParam(':room_id', $data['room_id'] ?? null);
        $stmt->bindParam(':price', $data['price'] ?? null);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Artwork created successfully", "id" => $this->conn->lastInsertId()), 201);
        } else {
            $this->sendError("Unable to create artwork", 500);
        }
    }

    private function updateArtwork() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Artwork ID is required");
        }

        $query = "UPDATE " . $this->table_name . " 
                 SET title = :title, year = :year, medium = :medium, status = :status, 
                     artist_id = :artist_id, room_id = :room_id, price = :price 
                 WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':title', $this->sanitizeInput($data['title']));
        $stmt->bindParam(':year', $data['year']);
        $stmt->bindParam(':medium', $this->sanitizeInput($data['medium']));
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':artist_id', $data['artist_id']);
        $stmt->bindParam(':room_id', $data['room_id']);
        $stmt->bindParam(':price', $data['price']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Artwork updated successfully"));
        } else {
            $this->sendError("Unable to update artwork", 500);
        }
    }

    private function deleteArtwork() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            $this->sendError("Artwork ID is required");
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['id']);

        if ($stmt->execute()) {
            $this->sendResponse(array("message" => "Artwork deleted successfully"));
        } else {
            $this->sendError("Unable to delete artwork", 500);
        }
    }
}

$api = new ArtworkAPI();
$api->handleRequest();
?>
