
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
