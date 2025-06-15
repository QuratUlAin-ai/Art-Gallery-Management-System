<?php
// Simple API router
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/api/', '', $path);

switch($path) {
    case 'customers':
    case 'customers.php':
        require_once 'customers.php';
        break;
    case 'artists':
    case 'artists.php':
        require_once 'artists.php';
        break;
    case 'rooms':
    case 'rooms.php':
        require_once 'rooms.php';
        break;
    case 'staff':
    case 'staff.php':
        require_once 'staff.php';
        break;
    case 'exhibitions':
    case 'exhibitions.php':
        require_once 'exhibitions.php';
        break;
    case 'artworks':
    case 'artworks.php':
        require_once 'artworks.php';
        break;
    case 'tickets':
    case 'tickets.php':
        require_once 'tickets.php';
        break;
    case 'purchases':
    case 'purchases.php':
        require_once 'purchases.php';
        break;
    case 'dashboard':
    case 'dashboard.php':
        require_once 'dashboard.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(array("error" => "Endpoint not found"));
        break;
}
?>
