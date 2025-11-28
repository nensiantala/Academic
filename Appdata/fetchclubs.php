<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow all origins (for testing)
header("Access-Control-Allow-Methods: GET");

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "academic"; // change as needed

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed",
        "data" => []
    ]);
    exit;
}


    $sql = "SELECT * FROM clubs ";


$result = $conn->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!isset($row['images_json']) || $row['images_json'] === '') {
            $row['images_json'] = "[]";
        }
        $data[] = $row;
    }
    echo json_encode([
        "status" => "success",
        "message" => "Data loaded",
        "data" => $data
    ], JSON_UNESCAPED_SLASHES);
} else {
    echo json_encode([
        "status" => "success",
        "message" => "No records found",
        "data" => []
    ], JSON_UNESCAPED_SLASHES);
}

$conn->close();
?>
