<?php
die("NEW VERSION LOADED");
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");

// Database connection
$servername = "bsjvqufvzqkzs5kqijg1-mysql.services.clever-cloud.com";
$username   = "u2dmuf8ob16mtxjc";
$password   = "KnPCyASMwE220OGeIci4";
$dbname     = "bsjvqufvzqkzs5kqijg1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => false, "message" => "Database connection failed"]);
    exit;
}

// Read input
$name     = trim($_POST['name']     ?? $_GET['name']     ?? '');
$email    = trim($_POST['email']    ?? $_GET['email']    ?? '');
$phone    = trim($_POST['phone']    ?? $_GET['phone']    ?? '');
$password = trim($_POST['password'] ?? $_GET['password'] ?? '');

// Basic validation
if ($name === '' || $email === '' || $password === '') {
    echo json_encode(["status" => false, "message" => "Name, email, and password are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => false, "message" => "Invalid email format"]);
    exit;
}

/* 🔒 DOMAIN RESTRICTION */
$allowedDomains = [
    'marwadiuniversity.ac.in',
    'marwadieducation.edu.in'
];

$emailDomain = substr(strrchr($email, "@"), 1);

if (!in_array($emailDomain, $allowedDomains)) {
    echo json_encode([
        "status" => false,
        "message" => "Only Marwadi University or Marwadi Education email IDs are allowed"
    ]);
    exit;
}

// Check if user already exists
$checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo json_encode(["status" => false, "message" => "Email already registered"]);
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// Hash password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert user
$stmt = $conn->prepare(
    "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $name, $email, $phone, $hashedPassword);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "message" => "Registration successful",
        "user" => [
            "id"    => $stmt->insert_id,
            "name"  => $name,
            "email" => $email,
            "phone" => $phone
        ]
    ]);
} else {
    echo json_encode(["status" => false, "message" => "Registration failed"]);
}

$stmt->close();
$conn->close();
?>

