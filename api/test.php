<?php
require_once 'base_api.php';

// Simple test endpoint
$response = array(
    "status" => "success",
    "message" => "API is working correctly",
    "timestamp" => date('Y-m-d H:i:s'),
    "php_version" => phpversion(),
    "server_info" => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
);

sendJsonResponse($response);
?>
