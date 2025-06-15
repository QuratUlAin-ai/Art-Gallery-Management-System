<?php
// Minimal API test - no database required
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Simple response
$response = array(
    "status" => "success",
    "message" => "Simple API test working",
    "timestamp" => date('Y-m-d H:i:s'),
    "method" => $_SERVER['REQUEST_METHOD'],
    "php_version" => phpversion()
);

echo json_encode($response);
exit();
?>
