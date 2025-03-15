<?php
// registration.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db.php'; // establishes $conn

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and trim inputs
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Basic validations
    if (empty($username) || empty($password) || empty($confirm) || empty($email)) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT user_id FROM `user` WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Username already taken.";
        } else {
            // Insert new user.
            // In production, use password_hash() instead of plain text.
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO `user` (username, password, role_id) VALUES (?, ?, ?)");
            // Assuming default role_id 2 for regular users, adjust as needed.
            $default_role = 2;
            $stmt->bind_param("ssi", $username, $password, $default_role);
            if ($stmt->execute()) {
                $message = "Registration successful! You can now log in.";
            } else {
                $message = "Registration error: " . $conn->error;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Registration - Pharmacy Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Register</h1>
  <?php if (!empty($message)): ?>
      <p><?php echo $message; ?></p>
  <?php endif; ?>
  <form method="post" action="registration.php">
    <label for="username">Username:</label>
    <input type="text" name="username" id="username" required autofocus>
    <br>
    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required>
    <br>
    <label for="password">Password (min. 8 characters):</label>
    <input type="password" name="password" id="password" required>
    <br>
    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" id="confirm_password" required>
    <br>
    <button type="submit">Register</button>
  </form>
  <p><a href="login.php">Already have an account? Login here.</a></p>
</body>
</html>
