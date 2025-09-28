<?php
include 'db.php'; // connect to DB

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['Name']);
    $email = $conn->real_escape_string($_POST['Email']);
    $message = $conn->real_escape_string($_POST['Message']);

    $sql = "INSERT INTO messages (name, email, message) VALUES ('$name', '$email', '$message')";

    if ($conn->query($sql) === TRUE) {
        echo "success"; // for AJAX in script.js
    } else {
        echo "error";
    }
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn->close();
?>
