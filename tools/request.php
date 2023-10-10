<?php
header("Content-Type:application/json");
require_once "../config.php";
require_once "../include/functions.php";

// Check the API token from the request
$headers = getallheaders();

if (!isset($headers['Authorization']) ) {
    header("HTTP/1.1 401 Unauthorized");
    exit("Unauthorized");
}

$authorizationHeader = $headers['Authorization'];
$token = null;

// Check if the Authorization header starts with "Bearer "
if (strpos($authorizationHeader, 'Bearer') === 0) {
  // Extract the token (remove "Bearer " prefix)
  $token = substr($authorizationHeader, 7);
} else {
  // Invalid Authorization header format
  http_response_code(401); // Unauthorized
  echo json_encode(array('message' => 'Authentication Error: Invalid Authorization header format.'));
  exit;
}

// Now, you have the Bearer Token in the $token variable
// You can use it for authentication or authorization as needed
// Check if the token is valid
if ($token !== $api_token) {
  header("HTTP/1.1 401 Unauthorized");
  exit("Unauthorized");
}

// Connect to your MySQL database
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$schoolCode = isset($_GET['school_code']) ? $_GET['school_code'] : '';
if (!$action || !$schoolCode) {
  header("HTTP/1.1 400 Bad Request");
  exit("Bad Request");
}
$schId = getSchoolFromCode($schoolCode, $conn);
// Handle different actions
switch ($action) {
    case 'list':
        listRequests($conn, $schId);
        break;
    case 'submit':
        submitRequest($conn, $schId);
        break;
    default:
        header("HTTP/1.1 400 Bad Request");
        exit("Bad Request");
}

// Function to list requests
function listRequests($conn, $schId) {
    // Retrieve requests from the 'school_requests' table
    $sql = "SELECT request, comment, done, submitted, handled FROM school_requests WHERE school = $schId ORDER BY submitted DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        header('Content-Type: application/json');
        echo json_encode($requests);
    } else {
        header("HTTP/1.1 404 Not Found");
        exit("No requests found");
    }
}

// Function to submit a new request
function submitRequest($conn, $schCode) {
    // Parse the JSON request body
    $requestBody = file_get_contents('php://input');
    $requestData = json_decode($requestBody, true);

    if (isset($requestData['request']) && isset($requestData['school_code'])) {
        $request = $requestData['request'];
        $sch_code = $requestData['school_code'];
        $sch_id = getSchoolFromCode($sch_code, $conn);
        // Insert the new request into the 'school_requests' table
        $sql = "INSERT INTO school_requests (request, school) VALUES ('$request', '$sch_id')";

        if ($conn->query($sql) === TRUE) {
            header("HTTP/1.1 201 Created");
            exit("Request submitted successfully");
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            exit("Error: " . $conn->error);
        }
    } else {
        header("HTTP/1.1 400 Bad Request");
        exit("Bad Request");
    }
}

// Close the database connection
$conn->close();
?>
