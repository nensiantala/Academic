
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "academic";

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

