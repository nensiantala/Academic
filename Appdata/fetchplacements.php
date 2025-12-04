<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Database connection
$servername = "bsjvqufvzqkzs5kqijg1-mysql.services.clever-cloud.com"; // usually localhost
$username = "u2dmuf8ob16mtxjc";        // your MySQL username
$password = "KnPCyASMwE220OGeIci4";            // your MySQL password
$dbname = "bsjvqufvzqkzs5kqijg1"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

$sql = "SELECT * FROM placements ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $data]);
} else {
    echo json_encode(["status" => "error", "message" => "No records found"]);
}

$conn->close();
?>


