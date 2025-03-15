<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php'; // This file should establish $conn to your new database

$message = "";

// Initialize login attempts and lockout time if not already set
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['lockout_time'])) {
    $_SESSION['lockout_time'] = 0;
}

$max_attempts = 3;
$lockout_duration = 60; // Lockout period in seconds

// Check if the user is currently locked out
if ($_SESSION['login_attempts'] >= $max_attempts) {
    $elapsed = time() - $_SESSION['lockout_time'];
    if ($elapsed < $lockout_duration) {
        $remaining = ceil($lockout_duration - $elapsed);
        $message = "You have exceeded the maximum number of login attempts. Please try again in {$remaining} seconds.";
    } else {
        // Lockout period has expired, reset attempts and lockout time
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_time'] = 0;
    }
}

// Process form submission if not locked out
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['login_attempts'] < $max_attempts) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $message = "Both username and password are required.";
    } else {
        // Use the new database's table name `user`
        $stmt = $conn->prepare("SELECT user_id, username, password, role_id FROM `user` WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                // If the stored password is empty, update it with the provided password
                if (empty($user['password'])) {
                    $updateStmt = $conn->prepare("UPDATE `user` SET password = ? WHERE user_id = ?");
                    $updateStmt->bind_param("si", $password, $user['user_id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                    
                    // Reset login attempts and log the user in
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role_id'] = $user['role_id'];
                    header("Location: index.php");
                    exit;
                } else {
                    // Compare the plain text password (consider hashing for production)
                    if ($password === $user['password']) {
                        $_SESSION['login_attempts'] = 0; // reset on success
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role_id'] = $user['role_id'];
                        header("Location: index.php");
                        exit;
                    } else {
                        // Increment login attempts on failed login
                        $_SESSION['login_attempts']++;
                        if ($_SESSION['login_attempts'] >= $max_attempts) {
                            // Set the lockout time when max attempts are reached
                            $_SESSION['lockout_time'] = time();
                            $message = "You have exceeded the maximum number of login attempts. Please try again in {$lockout_duration} seconds.";
                        } else {
                            $message = "Invalid username or password.";
                        }
                    }
                }
            } else {
                // Increment login attempts if username not found
                $_SESSION['login_attempts']++;
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
  <style>
    /* Additional styles for the login page */
    .login-container {
      display: flex;
      align-items: center;
      justify-content: center;
      height: calc(100vh - var(--header-height));
      background-color: var(--color-light);
      margin-top: var(--header-height);
    }
    .login-box {
      background: #fff;
      padding: 30px;
      border-radius: 5px;
      box-shadow: 0 4px 8px var(--color-box-shadow);
      width: 100%;
      max-width: 400px;
    }
    .login-box h1 {
      text-align: center;
      margin-bottom: 20px;
      color: var(--color-primary);
    }
    .form-group {
      margin-bottom: 15px;
    }
    .form-group label {
      display: block;
      font-weight: bold;
      margin-bottom: 5px;
      color: var(--color-text);
    }
    .form-group input {
      width: 100%;
      padding: 8px;
      border: 1px solid var(--color-border);
      border-radius: 3px;
      font-size: 1em;
    }
    .login-button {
      width: 100%;
      padding: 10px;
      background-color: var(--color-primary);
      color: #fff;
      border: none;
      border-radius: 3px;
      font-size: 1em;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .login-button:hover {
      background-color: #0069d9;
    }
    .login-message {
      text-align: center;
      margin-bottom: 15px;
      color: #e83838;
    }
    /* Styles for the banner image above the login form */
    .login-banner {
      text-align: center;
      margin-bottom: 20px;
    }
    .login-banner img {
      max-width: 100%;
      height: auto;
      border-radius: 5px;
    }
  </style>
</head>
<body>
  <!-- RHU MacArthur Header -->
  <div class="header">
    <div class="logo">
      <img src="health.png" alt="RHU MacArthur Logo" style="height:50px;">
    </div>
    <div class="header-title">RHU MacArthur</div>
  </div>
  
  <div class="login-container">
    <div class="login-box">
      <!-- Banner Image on Top -->
      <div class="login-banner">
        <img src="login-banner.png" alt="Login Banner">
      </div>
      <h1>Login</h1>
      <?php if (!empty($message)): ?>
        <div class="login-message"><?php echo $message; ?></div>
      <?php endif; ?>
      <form method="post" action="login.php">
        <div class="form-group">
          <label for="username">Username:</label>
          <input type="text" name="username" id="username" required autofocus>
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <!-- This field is used for both normal login and to set a new password if none is set -->
          <input type="password" name="password" id="password" required>
        </div>
        <button type="submit" class="login-button">Login</button>
      </form>
    </div>
  </div>
</body>
</html>
