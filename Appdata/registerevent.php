<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "academic";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "status" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

// Read input (allow GET fallback for easier testing)
$event_id = trim($_POST['event_id'] ?? $_GET['event_id'] ?? '');
$name     = trim($_POST['name']     ?? $_GET['name']     ?? '');
$email    = trim($_POST['email']    ?? $_GET['email']    ?? '');
$phone    = trim($_POST['phone']    ?? $_GET['phone']    ?? '');
$remarks  = trim($_POST['remarks']  ?? $_GET['remarks']  ?? '');

// Basic validation
if ($event_id === '' || $name === '' || $email === '') {
    echo json_encode([
        "status" => false,
        "message" => "Event ID, name, and email are required"
    ]);
    $conn->close();
    exit;
}

// Validate event_id is numeric
$event_id = intval($event_id);
if ($event_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid event ID"
    ]);
    $conn->close();
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid email format"
    ]);
    $conn->close();
    exit;
}

// Check if event exists and is still accepting registrations
$checkStmt = $conn->prepare("SELECT id, title, end_date FROM events WHERE id = ? LIMIT 1");
$checkStmt->bind_param("i", $event_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => false,
        "message" => "Event not found"
    ]);
    $checkStmt->close();
    $conn->close();
    exit;
}

$event = $checkResult->fetch_assoc();
$checkStmt->close();

// Check if event has ended
$end_date = strtotime($event['end_date']);
$today = strtotime(date('Y-m-d'));

if ($end_date < $today) {
    echo json_encode([
        "status" => false,
        "message" => "Registration closed. This event has already ended."
    ]);
    $conn->close();
    exit;
}

// Check if user already registered for this event
$duplicateStmt = $conn->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND email = ? LIMIT 1");
$duplicateStmt->bind_param("is", $event_id, $email);
$duplicateStmt->execute();
$duplicateResult = $duplicateStmt->get_result();

if ($duplicateResult->num_rows > 0) {
    echo json_encode([
        "status" => false,
        "message" => "You have already registered for this event"
    ]);
    $duplicateStmt->close();
    $conn->close();
    exit;
}
$duplicateStmt->close();

// Insert registration
$stmt = $conn->prepare("INSERT INTO event_registrations (event_id, name, email, phone, remarks) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $event_id, $name, $email, $phone, $remarks);

if ($stmt->execute()) {
    $registration_id = $stmt->insert_id;
    
    echo json_encode([
        "status" => true,
        "message" => "Registration successful. We will contact you soon.",
        "registration" => [
            "id" => $registration_id,
            "event_id" => $event_id,
            "event_title" => $event['title'],
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "remarks" => $remarks
        ]
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Registration failed. Please try again."
    ]);
}

$stmt->close();
$conn->close();
?>

