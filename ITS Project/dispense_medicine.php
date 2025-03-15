
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dispense Medicine</title>
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
    <!-- Global Sidebar with Role-Based Navigation -->
    <div class="global-sidebar">
  <div class="profile">
    <div class="profile-logo">
      <img src="logo.png" alt="Logo" class="responsive-logo">
    </div>
    <div class="profile-name"><?php echo htmlspecialchars($profile_name); ?></div>
  </div>
  <div class="nav">
        <?php
          if (!isset($_SESSION['role_id'])) {
              header("Location: login.php");
              exit;
          }
          
          // Look up the current role name using the $roles array.
          $current_role = '';
          foreach ($roles as $r) {
              if ($r['role_id'] == $_SESSION['role_id']) {
                  $current_role = $r['role_name'];
                  break;
              }
          }
          
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
              ["Add Prescription", "make_prescription.php"],
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
      <div class="logout-sidebar">
    <a href="logout.php">Logout</a>
  </div>
    </div>
    
    <!-- Page Content -->
    <div class="page-content">
      <!-- Top Bar for Dispense Medicine Page -->
      <div class="top-bar">
        <div class="search-area">
          <input type="text" placeholder="Search Prescriptions">
          <button class="search-button">Search</button>
        </div>
        <div class="action-buttons">
          <a href="dispense_medicine.php?action=manual" class="add-item">Dispense Medicine</a>
          <a href="dispense_medicine.php?action=reports" class="reports">Item Reports</a>
        </div>
      </div>
      
      <!-- Success Message Modal -->
      <?php if (!empty($dispense_success)): ?>
        <div class="modal">
          <div class="modal-content">
            <div class="modal-header">
              <h2>Success</h2>
              <a href="dispense_medicine.php" class="close">&times;</a>
            </div>
            <div class="modal-body">
              <p><?php echo $dispense_success; ?></p>
            </div>
          </div>
        </div>
      <?php endif; ?>
      
      <!-- Main Inventory Container -->
      <div class="inventory-container">
        <!-- Left Sidebar: Prescription Filters -->
        <aside class="filter-sidebar">
          <h3>Prescription Filters</h3>
          <form method="GET" action="dispense_medicine.php" class="filter-form">
            <div class="filter-block">
              <label for="patient">Patient Name:</label>
              <input type="text" name="patient" id="patient" placeholder="Enter patient name" value="<?php echo isset($_GET['patient']) ? htmlspecialchars($_GET['patient']) : ''; ?>">
            </div>
            <div class="filter-block">
              <label for="date">Prescription Date:</label>
              <input type="date" name="date" id="date" value="<?php echo isset($_GET['date']) ? $_GET['date'] : ''; ?>">
            </div>
            <button type="submit" class="apply-filter">Apply Filter</button>
          </form>
          
          <div class="inventory-stats">
            <h4>Prescription Stats</h4>
            <p><strong>Pending Prescriptions:</strong> <?php echo count($pending_prescriptions); ?></p>
          </div>
        </aside>
        
        <!-- Right Content Area: Pending Prescriptions -->
        <main class="inventory-list">
          <h2>Pending Prescriptions</h2>
          <?php if (count($pending_prescriptions) > 0): ?>
            <?php foreach ($pending_prescriptions as $prescription): ?>
              <div class="inventory-item">
                <div class="item-info">
                  <h3>Prescription <?php echo $prescription['prescription_code']; ?></h3>
                  <span class="category">Patient: <?php echo $prescription['patient_name']; ?></span>
                  <p class="quantity">
                    Instructions: <?php echo $prescription['instructions']; ?>
                  </p>
                </div>
                <div class="date-info">
                  <div class="date-issued">
                    <label>Date:</label>
                    <span><?php echo date("Y-m-d", strtotime($prescription['created_at'])); ?></span>
                  </div>
                </div>
                <div class="item-options">
                  <button class="overview-btn" onclick="window.location.href='dispense_medicine.php?action=process&id=<?php echo $prescription['prescription_code']; ?>'">
                    Process Dispense
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No pending prescriptions.</p>
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
                <th>Medicines</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transaction_history as $trans): ?>
                <tr>
                  <td><?php echo htmlspecialchars($trans["transaction_code"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["prescription_id"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["patient_name"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["details"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["med_details"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["transaction_date"]); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No transactions yet.</p>
        <?php endif; ?>
      </div>
      
    </div>
  </div>
  
  <!-- Modal Popup for Internal Dispense -->
  <?php if (isset($_GET['action'])):
    $action = $_GET['action'];
    if ($action == "process" && isset($_GET['id'])):
      $presc_code = $conn->real_escape_string($_GET['id']);
      $stmt = $conn->prepare("SELECT p.*, pat.full_name as patient_name 
                              FROM prescriptions p 
                              JOIN patients pat ON p.patient_id = pat.patient_id 
                              WHERE p.prescription_code = ? AND p.status = 'pending' LIMIT 1");
      if (!$stmt) {
          die("Prepare failed (process modal): " . $conn->error);
      }
      $stmt->bind_param("s", $presc_code);
      $stmt->execute();
      $result = $stmt->get_result();
      $process_prescription = $result ? $result->fetch_assoc() : null;
      $stmt->close();
      if ($process_prescription):
          // Fetch list of medicines for this prescription.
          $stmt2 = $conn->prepare("SELECT pm.*, m.medicine_name FROM prescription_medicines pm JOIN medicine m ON pm.medicine_id = m.medicine_id WHERE pm.prescription_id = ?");
          $stmt2->bind_param("i", $process_prescription['prescription_id']);
          $stmt2->execute();
          $result2 = $stmt2->get_result();
          $medicine_list = [];
          while ($row = $result2->fetch_assoc()) {
              $medicine_list[] = $row;
          }
          $stmt2->close();
  ?>
<div class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Process Dispense for Prescription <?php echo $process_prescription['prescription_code']; ?></h2>
      <a href="dispense_medicine.php" class="close">&times;</a>
    </div>
    <div class="modal-body">
      <form method="POST" action="dispense_medicine.php?action=process&id=<?php echo $process_prescription['prescription_code']; ?>">
        <input type="hidden" name="prescription_id" value="<?php echo $process_prescription['prescription_code']; ?>">
        <input type="hidden" name="patient" value="<?php echo $process_prescription['patient_name']; ?>">
        <div class="form-group">
          <label>Patient:</label>
          <span><?php echo $process_prescription['patient_name']; ?></span>
        </div>
        <div class="form-group">
          <label>Medicines:</label>
          <ul>
            <?php foreach ($medicine_list as $med): ?>
              <li><?php echo htmlspecialchars($med['medicine_name'] . " (Qty: " . $med['quantity'] . ")"); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <button type="submit" name="process_dispense" class="modal-submit">Process Dispense</button>
      </form>
    </div>
  </div>
</div>
<?php 
      endif;
    endif;
  endif;
?>
  
<!-- Modal Popup for Manual Dispense -->
<?php if (isset($_GET['action']) && $_GET['action'] == "manual"): ?>
<div class="modal">
  <div class="modal-content">
    <div class="modal-header">
       <h2>Manual Medicine Dispense</h2>
       <a href="dispense_medicine.php" class="close">&times;</a>
    </div>
    <div class="modal-body">
       <form method="POST" action="dispense_medicine.php?action=manual">
          <input type="hidden" name="process_manual_dispense" value="1">
          <div class="form-group">
             <label for="manual-patient">Patient Name:</label>
             <input type="text" name="patient" id="manual-patient" required>
          </div>
          <div class="form-group" id="medicine-rows">
             <label>Medicines:</label>
             <div class="medicine-row">
                <select name="medicines[]" required>
                   <option value="">-- Select Medicine --</option>
                   <?php echo $medicine_options; ?>
                </select>
                <input type="number" name="quantities[]" placeholder="Quantity" required>
             </div>
          </div>
          <button type="button" onclick="addMedicineRow()">Add Another Medicine</button>
          <button type="submit" class="modal-submit">Process Manual Dispense</button>
       </form>
    </div>
  </div>
</div>
<script>
    function addMedicineRow() {
       var container = document.getElementById("medicine-rows");
       var row = document.createElement("div");
       row.className = "medicine-row";
       row.innerHTML = '<select name="medicines[]" required>' +
                       '<option value="">-- Select Medicine --</option>' +
                       '<?php echo $medicine_options; ?>' +
                       '</select>' +
                       '<input type="number" name="quantities[]" placeholder="Quantity" required>' +
                       '<button type="button" onclick="this.parentNode.remove()">Remove</button>';
       container.appendChild(row);
    }
</script>
<?php endif; ?>

<!-- Modal Popup for External Dispense -->
<?php if (isset($_GET['action']) && $_GET['action'] == "external"): ?>
<div class="modal">
  <div class="modal-content">
    <div class="modal-header">
       <h2>External Prescription Dispense</h2>
       <a href="dispense_medicine.php" class="close">&times;</a>
    </div>
    <div class="modal-body">
       <form method="POST" action="dispense_medicine.php?action=external">
          <input type="hidden" name="process_external_dispense" value="1">
          <div class="form-group">
             <label for="external-code">External Prescription Code:</label>
             <input type="text" name="external_code" id="external-code" required>
          </div>
          <div class="form-group">
             <label for="external-patient">Patient Name:</label>
             <input type="text" name="patient" id="external-patient" required>
          </div>
          <div class="form-group">
             <label for="external-source">Source:</label>
             <input type="text" name="source" id="external-source" required placeholder="Enter source">
          </div>
          <div class="form-group" id="external-medicine-rows">
             <label>Medicines:</label>
             <div class="medicine-row">
                <select name="medicines[]" required>
                   <option value="">-- Select Medicine --</option>
                   <?php echo $medicine_options; ?>
                </select>
                <input type="number" name="quantities[]" placeholder="Quantity" required>
             </div>
          </div>
          <button type="button" onclick="addExternalMedicineRow()">Add Another Medicine</button>
          <button type="submit" class="modal-submit">Process External Dispense</button>
       </form>
    </div>
  </div>
</div>
<script>
    function addExternalMedicineRow() {
       var container = document.getElementById("external-medicine-rows");
       var row = document.createElement("div");
       row.className = "medicine-row";
       row.innerHTML = '<select name="medicines[]" required>' +
                       '<option value="">-- Select Medicine --</option>' +
                       '<?php echo $medicine_options; ?>' +
                       '</select>' +
                       '<input type="number" name="quantities[]" placeholder="Quantity" required>' +
                       '<button type="button" onclick="this.parentNode.remove()">Remove</button>';
       container.appendChild(row);
    }
</script>
<?php endif; ?>

</body>
</html>
