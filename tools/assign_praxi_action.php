<?php
require_once '../config.php';

$mysqlconnection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_query($mysqlconnection, "SET NAMES 'utf8'");
mysqli_query($mysqlconnection, "SET CHARACTER SET 'utf8'");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['ids'];
    $praxi_id = $_POST['praxi_id'];

    $queries = []; // Array to store queries for preview

    foreach ($ids as $id) {
        $query = "UPDATE ektaktoi SET praxi = $praxi_id WHERE id = $id";
        $queries[] = $query; // Add query to array
        mysqli_query($mysqlconnection, $query);
    }

    // Output the queries for preview
    echo json_encode(['success' => true, 'queries' => $queries]);
} else {
    error_log('Request method: ' . $_SERVER['REQUEST_METHOD']); // Log the request method
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 