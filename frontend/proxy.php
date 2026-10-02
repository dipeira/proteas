<?php
// A proxy script to skip the need to 
// Set the target URL of the non-HTTPS server
require_once 'params.php';

$schoolCode = isset($_GET['school_code']) ? $_GET['school_code'] : '';
$targetUrl = $apiEndpointRoot . "tools/request.php?school_code=" . urlencode($schoolCode);

// Get the HTTP request method (GET, POST, etc.) from the client request
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Create a cURL session to send the request to the non-HTTPS server
$ch = curl_init($targetUrl);

// Forward the HTTP method used by the client request
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestMethod);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$headers = ["Authorization: Bearer $apiToken"];

// If the client request is a POST request, forward the request body and Content-Type
if ($requestMethod === "POST") {
    $requestBody = file_get_contents("php://input");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
    $headers[] = "Content-Type: application/json";
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Execute the cURL request and capture the response
$response = curl_exec($ch);

// Check for cURL errors and handle them as needed
if (curl_errno($ch)) {
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode(["error" => curl_error($ch)]);
    curl_close($ch);
    exit;
}

// Get the HTTP status code and content type from the response
$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

// Set the same HTTP status code in the response to the client
http_response_code($httpStatusCode);
header("Content-Type: " . ($contentType ?: "application/json"));

// Close the cURL session
curl_close($ch);

// Forward the response body to the client
echo $response;
?>