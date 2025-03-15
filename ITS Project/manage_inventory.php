
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Medicine Inventory</title>
  <link rel="stylesheet" href="style.css">
  <script>
  </script>
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
    <!-- Global Sidebar -->
    <div class="global-sidebar">
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
    // Format: "Role Name" => [ [Label, URL], ... ]
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
        // Fallback for unknown roles.
        echo '<button class="nav-button" onclick="window.location.href=\'index.php\'">Dashboard</button>';
        echo '<button class="nav-button" onclick="window.location.href=\'about.php\'">About</button>';
    }
  ?>
  <!-- Logout Link in the Sidebar -->
  <div class="logout-sidebar">
    <a href="logout.php">Logout</a>
  </div>
</div>


    </div>
    
    <!-- Page Content -->
    <div class="page-content">
      <!-- Top Bar -->
      <div class="top-bar">
        <div class="search-area">
          <form method="GET" action="manage_inventory.php">
            <input type="text" name="filter_name" placeholder="Search Inventory" value="<?php echo isset($_GET['filter_name']) ? htmlspecialchars($_GET['filter_name']) : ''; ?>">
            <button type="submit" class="search-button">Search</button>
          </form>
        </div>
        <div class="action-buttons">
          <form method="GET" action="manage_inventory.php" style="display:inline;">
            <input type="hidden" name="action" value="add">
            <button type="submit" class="add-item">Add Inventory Item</button>
          </form>
          <form method="GET" action="manage_inventory.php" style="display:inline;">
            <input type="hidden" name="action" value="reports">
            <button type="submit" class="reports">Item Reports</button>
          </form>
        </div>
      </div>
      
      <!-- Inventory Container -->
      <div class="inventory-container">
        <!-- Filter Sidebar -->
        <aside class="filter-sidebar">
          <h3>Inventory Filters</h3>
          <form method="GET" action="manage_inventory.php" class="filter-form">
            <!-- Name Filter -->
            <div class="form-group">
              <label for="filter_name">Name:</label>
              <input type="text" name="filter_name" id="filter_name" placeholder="Enter medicine name" 
                     value="<?php echo isset($_GET['filter_name']) ? htmlspecialchars($_GET['filter_name']) : ''; ?>">
            </div>
            <!-- Category Filter -->
            <div class="form-group">
              <label for="filter_category">Category:</label>
              <select name="filter_category" id="filter_category">
                <option value="all">-- All Categories --</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo htmlspecialchars($cat); ?>"
                    <?php echo (isset($_GET['filter_category']) && $_GET['filter_category'] === $cat) ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($cat); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <!-- Stock Type Filter -->
            <div class="form-group">
              <label for="filter_stock_type">Stock Type:</label>
              <select name="filter_stock_type" id="filter_stock_type">
                <option value="">-- All Stock Types --</option>
                <option value="new" <?php echo (isset($_GET['filter_stock_type']) && $_GET['filter_stock_type'] === 'new') ? "selected" : ""; ?>>
                  New Stock (Not Expired)
                </option>
                <option value="expired" <?php echo (isset($_GET['filter_stock_type']) && $_GET['filter_stock_type'] === 'expired') ? "selected" : ""; ?>>
                  Expired Stock
                </option>
              </select>
            </div>
            <!-- Stock Status Filter -->
            <div class="form-group">
              <label for="filter_stock_status">Stock Status:</label>
              <select name="filter_stock_status" id="filter_stock_status">
                <option value="">-- All Statuses --</option>
                <option value="In Stock" <?php echo (isset($_GET['filter_stock_status']) && $_GET['filter_stock_status'] === 'In Stock') ? "selected" : ""; ?>>
                  In Stock
                </option>
                <option value="Low Stock" <?php echo (isset($_GET['filter_stock_status']) && $_GET['filter_stock_status'] === 'Low Stock') ? "selected" : ""; ?>>
                  Low Stock
                </option>
                <option value="Out of Stock" <?php echo (isset($_GET['filter_stock_status']) && $_GET['filter_stock_status'] === 'Out of Stock') ? "selected" : ""; ?>>
                  Out of Stock
                </option>
              </select>
            </div>
            <button type="submit" class="apply-filter">Apply Filter</button>
          </form>
          
          <div class="inventory-stats">
            <h4>Inventory Statistics</h4>
            <p><strong>Items in Stock:</strong> <?php echo $totalStock; ?></p>
          </div>
        </aside>
        
        <!-- Inventory List -->
        <main class="inventory-list">
          <h2>Inventory List</h2>
          <?php if (count($filtered_inventory) > 0): ?>
            <?php foreach ($filtered_inventory as $item): ?>
              <?php $computedStatus = compute_status($item['quantity']); ?>
              <div class="inventory-item">
                <!-- Basic Info -->
                <div class="item-info">
                  <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                  <span class="category"><?php echo htmlspecialchars($item['category']); ?></span>
                  <p class="quantity">Quantity: <?php echo $item['quantity']; ?></p>
                  <p class="status <?php echo strtolower(str_replace(' ', '-', $computedStatus)); ?>">
                    <?php echo $computedStatus; ?>
                  </p>
                </div>
                <!-- Date Info -->
                <div class="date-info">
                  <div class="date-issued">
                    <label>Date Issued:</label>
                    <span><?php echo htmlspecialchars($item['date_issued']); ?></span>
                  </div>
                  <div class="expiry">
                    <label>Expiry Date:</label>
                    <span><?php echo htmlspecialchars($item['expiry_date']); ?></span>
                  </div>
                </div>
                <!-- Item Actions -->
                <div class="item-options">
                  <form method="GET" action="manage_inventory.php" style="display:inline;">
                    <input type="hidden" name="action" value="overview">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($item['name']); ?>">
                    <button type="submit" class="overview-btn">Quantity Overview</button>
                  </form>
                  <form method="GET" action="manage_inventory.php" style="display:inline;">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($item['name']); ?>">
                    <button type="submit" class="options-btn">Edit</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No inventory items found.</p>
          <?php endif; ?>
        </main>
      </div>
      
      <!-- Transaction History Section -->
      <div class="transaction-history" style="max-height:200px; overflow-y:auto; margin-top:20px;">
        <h2>Transaction History</h2>
        <?php if (count($transaction_history) > 0): ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Transaction ID</th>
                <th>Prescription ID</th>
                <th>Patient</th>
                <th>Details</th>
                <th></th>
                <th>Date</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transaction_history as $trans): ?>
                <tr>
                  <td><?php echo htmlspecialchars($trans["transaction_code"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["prescription_id"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["patient_name"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["details"]); ?></td>
                  <td><?php echo isset($trans["email"]) ? htmlspecialchars($trans["email"]) : ''; ?></td>
                  <td><?php echo htmlspecialchars($trans["transaction_date"]); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No transactions yet.</p>
        <?php endif; ?>
      </div>
      
    </div><!-- end page-content -->
  </div><!-- end global-container -->
  
  <!-- PHP-Driven Modal Popups -->
  <?php if (isset($_GET['action'])):
    $action = $_GET['action'];
    // Add Inventory Item Modal
    if ($action == "add"):
  ?>
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Add Inventory Item</h2>
            <form method="GET" action="manage_inventory.php" style="display:inline;">
              <button type="submit" class="close">&times;</button>
            </form>
          </div>
          <div class="modal-body">
            <form method="POST" action="manage_inventory.php">
              <div class="form-group">
                <label for="add-name">Name:</label>
                <input type="text" name="name" id="add-name" required>
              </div>
              <div class="form-group">
                <label for="add-category">Category:</label>
                <input type="text" name="category" id="add-category" list="existingCategories" placeholder="Enter or select category" required>
                <datalist id="existingCategories">
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>">
                  <?php endforeach; ?>
                </datalist>
              </div>
              <div class="form-group">
                <label for="add-quantity">Quantity:</label>
                <input type="number" name="quantity" id="add-quantity" required>
              </div>
              <div class="form-group">
                <label for="add-dateIssued">Date Issued:</label>
                <input type="date" name="dateIssued" id="add-dateIssued" required>
              </div>
              <div class="form-group">
                <label for="add-expiryDate">Expiry Date:</label>
                <input type="date" name="expiryDate" id="add-expiryDate" required>
              </div>
              <button type="submit" name="add_meds" class="modal-submit">Add Item</button>
            </form>
          </div>
        </div>
      </div>
  <?php
    // Report Modal: Form to generate a report
    elseif ($action == "reports"):
  ?>
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Item Report</h2>
            <form method="GET" action="manage_inventory.php" style="display:inline;">
              <button type="submit" class="close">&times;</button>
            </form>
          </div>
          <div class="modal-body">
            <form method="POST" action="manage_inventory.php">
              <div class="form-group">
                <label for="report-category">Category:</label>
                <select name="report_category" id="report-category">
                  <option value="all">-- All Categories --</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>">
                      <?php echo htmlspecialchars($cat); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="report-stock-type">Stock Type:</label>
                <select name="report_stock_type" id="report-stock-type">
                  <option value="">-- All Stock Types --</option>
                  <option value="new">New Stock (Not Expired)</option>
                  <option value="expired">Expired Stock</option>
                </select>
              </div>
              <div class="form-group">
                <label for="report-stock-status">Stock Status:</label>
                <select name="report_stock_status" id="report-stock-status">
                  <option value="">-- All Statuses --</option>
                  <option value="In Stock">In Stock</option>
                  <option value="Low Stock">Low Stock</option>
                  <option value="Out of Stock">Out of Stock</option>
                </select>
              </div>
              <button type="submit" name="generate_report" class="modal-submit">Generate Report &amp; Print</button>
            </form>
          </div>
        </div>
      </div>
  <?php
    // Edit Inventory Item Modal
    elseif ($action == "edit" && isset($_GET['name'])):
      $item = get_item_by_name($conn, $_GET['name']);
      if ($item):
  ?>
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Edit Inventory Item</h2>
            <form method="GET" action="manage_inventory.php" style="display:inline;">
              <button type="submit" class="close">&times;</button>
            </form>
          </div>
          <div class="modal-body">
            <form method="POST" action="manage_inventory.php">
              <div class="form-group">
                <label>Item Name:</label>
                <span><?php echo htmlspecialchars($item['name']); ?></span>
              </div>
              <div class="form-group">
                <label for="new_quantity">New Quantity:</label>
                <input type="number" name="new_quantity" id="new_quantity" value="<?php echo $item['quantity']; ?>" required>
              </div>
              <input type="hidden" name="action" value="edit">
              <input type="hidden" name="name" value="<?php echo htmlspecialchars($item['name']); ?>">
              <button type="submit" class="modal-submit">Update Quantity</button>
            </form>
            <form method="POST" action="manage_inventory.php" onsubmit="return confirm('Are you sure you want to delete this item?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="name" value="<?php echo htmlspecialchars($item['name']); ?>">
              <button type="submit" class="modal-submit delete-btn">Delete Item</button>
            </form>
          </div>
        </div>
      </div>
  <?php
      else:
        echo "<p>Item not found.</p>";
      endif;
    // Overview Modal
    elseif ($action == "overview" && isset($_GET['name'])):
      $item = get_item_by_name($conn, $_GET['name']);
      if ($item):
  ?>
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Quantity Overview</h2>
            <form method="GET" action="manage_inventory.php" style="display:inline;">
              <button type="submit" class="close">&times;</button>
            </form>
          </div>
          <div class="modal-body">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($item['name']); ?></p>
            <p><strong>Current Quantity:</strong> <?php echo $item['quantity']; ?></p>
          </div>
        </div>
      </div>
  <?php
      else:
        echo "<p>Item not found.</p>";
      endif;
    endif;
  endif;
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($message) && !isset($_POST['generate_report'])):
  ?>
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Action Completed</h2>
            <form method="GET" action="manage_inventory.php" style="display:inline;">
              <button type="submit" class="close">&times;</button>
            </form>
          </div>
          <div class="modal-body">
            <p><?php echo $message; ?></p>
            <form method="GET" action="manage_inventory.php">
              <button type="submit" class="modal-submit">Return to Inventory</button>
            </form>
          </div>
        </div>
      </div>
  <?php
  endif;
  
  if ($reportGenerated === true):
  ?>
      <div class="modal" id="reportModal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Inventory Report Summary</h2>
            <form method="GET" action="manage_inventory.php" style="display:inline;">
              <button type="submit" class="close">&times;</button>
            </form>
          </div>
          <div class="modal-body">
            <?php if (!empty($reportError)): ?>
              <p style="color:red;"><?php echo htmlspecialchars($reportError); ?></p>
            <?php else: ?>
              <p><strong>Category:</strong> <?php echo htmlspecialchars($reportSummary["Category"]); ?></p>
              <p><strong>Total Items Found:</strong> <?php echo $reportSummary["Total Items"]; ?></p>
              <p><strong>Total Quantity:</strong> <?php echo $reportSummary["Total Quantity"]; ?></p>
              <h3>Status Breakdown</h3>
              <table>
                <thead>
                  <tr>
                    <th>Status</th>
                    <th>Count</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($reportSummary["Status Breakdown"] as $status => $count): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($status); ?></td>
                      <td><?php echo $count; ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <h3>Detailed Items</h3>
              <table>
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Date Issued</th>
                    <th>Expiry Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($reportItems as $item): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($item['name']); ?></td>
                      <td><?php echo $item['quantity']; ?></td>
                      <td><?php echo htmlspecialchars($item['status']); ?></td>
                      <td><?php echo htmlspecialchars($item['date_issued']); ?></td>
                      <td><?php echo htmlspecialchars($item['expiry_date']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
          <div class="modal-footer">
            <button onclick="window.print()" class="modal-submit">Print Report</button>
          </div>
          <script>
            setTimeout(function() {
              window.print();
            }, 500);
          </script>
        </div>
      </div>
  <?php
  endif;
  ?>
  
</body>
</html>
