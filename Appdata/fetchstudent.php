<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Database connection
$conn = new mysqli(
    "bsjvqufvzqkzs5kqijg1-mysql.services.clever-cloud.com",
    "u2dmuf8ob16mtxjc",
    "KnPCyASMwE220OGeIci4",
    "bsjvqufvzqkzs5kqijg1"
);

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}

/* 🔴 IMPORTANT: get ID from URL */
if (!isset($_GET['id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "User ID missing"
    ]);
    exit;
}

$id = intval($_GET['id']); // sanitize

$sql = "SELECT id, name, email, phone, created_at 
        FROM users 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    echo json_encode([
        "status" => "success",
        "data" => $result->fetch_assoc()
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "User not found"
    ]);
}

$stmt->close();
$conn->close();
?>
