
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <!-- Responsive meta tag -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pharmacy Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Global Header -->
  <header class="header">
    <div class="logo">
    <div class="logo">RHU MacArthur</div>
    </div>
    <div class="header-title">RHU MacArthur</div>
  </header>
  
  <div class="global-container">
    <!-- Fixed Sidebar -->
    <aside class="global-sidebar">
  <div class="profile">
    <div class="profile-logo">
      <img src="logo.png" alt="Logo" class="responsive-logo">
    </div>
    <div class="profile-name"><?php echo htmlspecialchars($profile_name); ?></div>
  </div>
  <div class="nav">
    <?php
      // Ensure the user is logged in and role_id is set.
      if (!isset($_SESSION['role_id'])) {
          header("Location: login.php");
          exit;
      }
      
      // Look up the current role name using the $roles array fetched from SQL.
      $current_role = '';
      foreach ($roles as $r) {
          if ($r['role_id'] == $_SESSION['role_id']) {
              $current_role = $r['role_name'];
              break;
          }
      }
      
      // Define the navigation items for each role.
      $navItems = [
        "Admin" => [
              ["Dashboard", "index.php"],
              ["Manage Inventory", "manage_inventory.php"],
              ["Dispense Medicine", "dispense_medicine.php"],
              ["Manage Accounts", "manage_accounts.php"],
              ["View Transactions", "transaction_history.php"],
              ["About", "about.php"]
            ],
            "Municipal Health Officer" => [
              ["Dashboard", "index.php"],
              ["Dispense Medicine", "dispense_medicine.php"],
              ["Add Prescription", "make_prescription.php"],
              ["View Transactions", "transaction_history.php"],
              ["About", "about.php"]
            ],
            "Health Officer" => [
              ["Dashboard", "index.php"],
              ["Manage Inventory", "manage_inventory.php"],
              ["Dispense Medicine", "dispense_medicine.php"],
              ["View Transactions", "transaction_history.php"],
              ["About", "about.php"]
            ],
            "Public Health Nurse" => [
              ["Dashboard", "index.php"],
              ["Dispense Medicine", "dispense_medicine.php"],
              ["View Transactions", "transaction_history.php"],
              ["About", "about.php"]
            ],
            "Pharmacist" => [
              ["Dashboard", "index.php"],
              ["Manage Inventory", "manage_inventory.php"],
              ["Dispense Medicine", "dispense_medicine.php"],
              ["View Transactions", "transaction_history.php"],
              ["About", "about.php"]
            ]
      ];
      
      // Output the navigation buttons based on the current role.
      if (array_key_exists($current_role, $navItems)) {
          foreach ($navItems[$current_role] as $item) {
              echo '<button class="nav-button" onclick="window.location.href=\'' . $item[1] . '\'">' . $item[0] . '</button>';
          }
      } else {
          echo '<button class="nav-button" onclick="window.location.href=\'index.php\'">Dashboard</button>';
          echo '<button class="nav-button" onclick="window.location.href=\'about.php\'">About</button>';
      }
    ?>
  </div>
  <!-- Logout Link in the Sidebar -->
  <div class="logout-sidebar">
    <a href="logout.php">Logout</a>
  </div>
</aside>

    
    <!-- Main Content Area -->
    <main class="page-content">
      <!-- Quick Stats Section -->
      <section class="top-row">
        <div class="box">
          <h4>Daily Stock Monitoring</h4>
          <table class="summary-table">
            <thead>
              <tr>
                <th>Parameter</th>
                <th>Value</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Current Stock Status</td>
                <td><?php echo $currentStockStatus; ?></td>
              </tr>
              <tr>
                <td>Stock Dispensed Today</td>
                <td><?php echo $stockDispensedToday; ?></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="box">
          <h4>Daily Prescription Monitoring</h4>
          <table class="summary-table">
            <thead>
              <tr>
                <th>Parameter</th>
                <th>Value</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Dispensed Prescriptions</td>
                <td><?php echo $dispensedPrescriptions; ?></td>
              </tr>
              <tr>
                <td>Undispensed Prescriptions</td>
                <td><?php echo $undispensedPrescriptions; ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
      
      <!-- Recent Prescriptions Section -->
      <section class="section">
        <h4>Recent Prescriptions</h4>
        <table class="data-table">
          <thead>
            <tr>
              <th>Prescription ID</th>
              <th>Patient</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentPrescriptions as $rx): ?>
              <tr>
                <td><?php echo $rx['id']; ?></td>
                <td><?php echo $rx['patient']; ?></td>
                <td><?php echo $rx['status']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
      
      <!-- Inventory Warning Section -->
      <section class="section">
        <h4>Inventory Warning</h4>
        <table class="data-table">
          <thead>
            <tr>
              <th>Item no.</th>
              <th>Medicine</th>
              <th>Quantity</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($inventoryWarnings as $item): ?>
              <tr>
                <td><?php echo $item['item_no']; ?></td>
                <td><?php echo $item['medicine']; ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo $item['status']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div><!-- end global-container -->
</body>
</html>
