<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

$conn = new mysqli(
    "bsjvqufvzqkzs5kqijg1-mysql.services.clever-cloud.com",
    "u2dmuf8ob16mtxjc",
    "KnPCyASMwE220OGeIci4",
    "bsjvqufvzqkzs5kqijg1"
);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

// 🔴 IMPORTANT
if (!isset($_GET['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User ID missing"]);
    exit;
}

$user_id = intval($_GET['user_id']);

$sql = "SELECT id, name, email, phone, created_at 
        FROM users 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {
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

$conn->close();
?>
