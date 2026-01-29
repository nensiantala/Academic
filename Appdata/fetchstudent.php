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

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}

$sql = "SELECT id, name, email, phone, password, created_at FROM users ORDER BY id DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    echo json_encode([
        "status" => "success",
        "data" => $students
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No students found"
    ]);
}

$conn->close();
?>




