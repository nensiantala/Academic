<?php
$servername = "bsjvqufvzqkzs5kqijg1-mysql.services.clever-cloud.com"; // usually localhost
$username = "u2dmuf8ob16mtxjc";        // your MySQL username
$password = "u2dmuf8ob16mtxjc";            // your MySQL password
$dbname = "bsjvqufvzqkzs5kqijg1";      // your academic database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
