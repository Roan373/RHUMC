<?php
// db.php
$host = 'localhost';
$db   = 'macpharmacy_db'; // Ensure this matches the database name in phpMyAdmin
$user = 'root';
$pass = ''; // Replace with your MySQL password if you have one

// Create connection
$mysqli = new mysqli($host, $user, $pass, $db);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>