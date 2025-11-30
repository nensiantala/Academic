<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// DB connection
$servername = "bsjvqufvzqkzs5kqijg1-mysql.services.clever-cloud.com";
$username   = "u2dmuf8ob16mtxjc";
$password   = "KnPCyASMwE220OGeIci4";
$dbname     = "bsjvqufvzqkzs5kqijg1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => false, "message" => "Database connection failed"]);
    exit;
}

// Read GET + POST
$email    = $_POST['email']    ?? $_GET['email']    ?? '';
$password = $_POST['password'] ?? $_GET['password'] ?? '';

$email    = trim($email);
$password = trim($password);

// Validation
if ($email === '' || $password === '') {
    echo json_encode(["status" => false, "message" => "Email and password required"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, name, email, phone, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

    // Verify password hash (fallback to plain text for legacy rows)
    $passwordMatches = password_verify($password, $user['password']) || $password === $user['password'];

    if ($passwordMatches) {

        echo json_encode([
            "status" => true,
            "message" => "Login successful",
            "user" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email'],
                "phone" => $user['phone']
            ]
        ]);

    } else {
        echo json_encode(["status" => false, "message" => "Incorrect password"]);
    }

} else {
    echo json_encode(["status" => false, "message" => "User not found"]);
}

$stmt->close();
$conn->close();
?>
