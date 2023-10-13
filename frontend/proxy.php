<?php
// A proxy script to skip the need to 
// Set the target URL of the non-HTTPS server
require_once 'params.php';
$targetUrl = $apiEndpointRoot."tools/request.php?school_code=".$_GET['school_code'];

// Get the HTTP request method (GET, POST, etc.) from the client request
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Create a cURL session to send the request to the non-HTTPS server
$ch = curl_init($targetUrl);

// Forward the HTTP method used by the client request
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestMethod);

$headers = ["Authorization: Bearer $apiToken"];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
// Set additional cURL options as needed (headers, request data, etc.)
// For example, you can add headers like this:
// curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//     "Content-Type: application/json"
// ));

// If the client request is a POST request, forward the request body
if ($requestMethod === "POST") {
    $requestBody = file_get_contents("php://input");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
}

// Other cURL options can be set here as needed

// Execute the cURL request and capture the response
$response = curl_exec($ch);

// Check for cURL errors and handle them as needed
if (curl_errno($ch)) {
    header("HTTP/1.1 500 Internal Server Error");
    exit("Error: " . curl_error($ch));
}

// Get the HTTP status code from the response
$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Set the same HTTP status code in the response to the client
http_response_code($httpStatusCode);

// Forward response headers (including content type) to the client
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$responseHeaders = substr($response, 0, $headerSize);
$headers = explode("\r\n", $responseHeaders);
foreach ($headers as $header) {
    if (!empty($header)) {
        header($header);
    }
}

// Forward the response body to the client
$responseBody = substr($response, $headerSize);
echo $responseBody;

// Close the cURL session
curl_close($ch);
?>