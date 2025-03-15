<?php
// login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db.php';

// Include auto-logout (see auto_logout.php below) on each protected page if needed
// include('auto_logout.php');

$message = "";

// Initialize session variables for login attempts.
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['lockout_time'])) {
    $_SESSION['lockout_time'] = 0;
}

$max_attempts = 3;
$lockout_duration = 60; // seconds

// Check for lockout.
if ($_SESSION['login_attempts'] >= $max_attempts) {
    $elapsed = time() - $_SESSION['lockout_time'];
    if ($elapsed < $lockout_duration) {
        $remaining = ceil($lockout_duration - $elapsed);
        $message = "You have exceeded the maximum number of login attempts. Please try again in {$remaining} seconds.";
    } else {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_time'] = 0;
    }
}

// Function to insert log entries.
function insertLog($conn, $user_id, $action, $description) {
    $stmt = $conn->prepare("INSERT INTO userlogs (user_id, action, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $action, $description);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['login_attempts'] < $max_attempts) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $message = "Both username and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, username, password, role_id FROM `user` WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                // If no password is set, update it with the provided password.
                if (empty($user['password'])) {
                    $updateStmt = $conn->prepare("UPDATE `user` SET password = ? WHERE user_id = ?");
                    $updateStmt->bind_param("si", $password, $user['user_id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                    
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role_id'] = $user['role_id'];
                    insertLog($conn, $user['user_id'], 'NEW_PASSWORD_SET', 'User set a new password during login.');
                    header("Location: index.php");
                    exit;
                } else {
                    if ($password === $user['password']) {
                        $_SESSION['login_attempts'] = 0;
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role_id'] = $user['role_id'];
                        insertLog($conn, $user['user_id'], 'LOGIN_SUCCESS', 'User logged in successfully.');
                        header("Location: index.php");
                        exit;
                    } else {
                        $_SESSION['login_attempts']++;
                        insertLog($conn, $user['user_id'], 'LOGIN_FAIL', 'Incorrect password entered.');
                        if ($_SESSION['login_attempts'] >= $max_attempts) {
                            $_SESSION['lockout_time'] = time();
                            $message = "You have exceeded the maximum number of login attempts. Please try again in {$lockout_duration} seconds.";
                        } else {
                            $message = "Invalid username or password.";
                        }
                    }
                }
            } else {
                // Log failed attempt even if username not found.
                $_SESSION['login_attempts']++;
                insertLog($conn, 0, 'LOGIN_FAIL', 'Username not found: ' . $username);
                if ($_SESSION['login_attempts'] >= $max_attempts) {
                    $_SESSION['lockout_time'] = time();
                    $message = "You have exceeded the maximum number of login attempts. Please try again in {$lockout_duration} seconds.";
                } else {
                    $message = "Invalid username or password.";
                }
            }
            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Pharmacy Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <script>
    // Disable password field on initial load.
    window.addEventListener("DOMContentLoaded", function() {
      const usernameInput = document.getElementById("username");
      const passwordInput = document.getElementById("password");
      passwordInput.disabled = true;
      // Enable password field when username field loses focus and is not empty.
      usernameInput.addEventListener("blur", function() {
        if (usernameInput.value.trim() !== "") {
          passwordInput.disabled = false;
        } else {
          passwordInput.disabled = true;
        }
      });
    });
  </script>
</head>
<body>
  <!-- Global header remains unchanged -->
  <div class="header">
    <div class="logo">Pharmacy</div>
    <div class="header-title">User Login</div>
  </div>
  
  <div class="login-container">
    <div class="login-box">
      <h1>Login</h1>
      <?php if (!empty($message)): ?>
        <p class="login-message"><?php echo $message; ?></p>
      <?php endif; ?>
      <form method="post" action="login.php">
        <div class="form-group">
          <label for="username">Username:</label>
          <input type="text" name="username" id="username" required autofocus>
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" name="password" id="password" required>
        </div>
        <button type="submit" class="login-button">Login</button>
      </form>
      <p><a href="forgot_password.php">Forgot Password?</a></p>
      <p><a href="registration.php">Register</a></p>
    </div>
  </div>
</body>
</html>