
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Make Prescription</title>
  <link rel="stylesheet" href="style.css">
  <script>
    // Pass inventory data to JavaScript for client-side validation
    var inventory = <?php echo json_encode($inventory); ?>;
    
    // Add a new medicine row dynamically with a <select> for medicine
    function addRow() {
      var container = document.getElementById("medicineRows");
      var row = document.createElement("div");
      row.className = "medicine-row";
      row.innerHTML = `
        <select name="medicines[]" required>
          <option value="">-- Select Medicine --</option>
          <?php foreach ($medicine_names as $med): ?>
            <option value="<?php echo htmlspecialchars($med); ?>">
              <?php echo htmlspecialchars($med); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="number" name="quantities[]" placeholder="Quantity" required>
        <button type="button" class="delete-row" onclick="deleteRow(this)">Delete</button>
      `;
      container.appendChild(row);
    }
    
    // Delete a medicine row
    function deleteRow(btn) {
      var row = btn.parentNode;
      row.parentNode.removeChild(row);
    }
    
    // Validate the form on submit: warn if any selected medicine exceeds available quantity
    window.addEventListener('DOMContentLoaded', function() {
      var form = document.getElementById('prescriptionForm');
      form.addEventListener('submit', function(e) {
        var warnings = [];
        var rows = document.querySelectorAll('.medicine-row');
        rows.forEach(function(row) {
          var select = row.querySelector('select[name="medicines[]"]');
          var qtyInput = row.querySelector('input[name="quantities[]"]');
          if (select && qtyInput) {
            var med = select.value.trim();
            var qty = parseInt(qtyInput.value);
            if (inventory.hasOwnProperty(med)) {
              var available = inventory[med];
              if (available <= 0) {
                warnings.push(med + " is out of stock.");
              } else if (qty > available) {
                warnings.push("Only " + available + " available for " + med + ". You entered " + qty + ".");
              }
            } else if (med !== "") {
              warnings.push("Medicine '" + med + "' not found in inventory.");
            }
          }
        });
        if (warnings.length > 0) {
          var msg = "The following issues were found:\n" + warnings.join("\n") + "\n\nDo you want to continue anyway?";
          if (!confirm(msg)) {
            e.preventDefault();
          }
        }
      });
    });
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
</div>

    </div>
    
    <!-- Main Content -->
    <div class="page-content">
      <div class="page-header">
        <h1>Make Prescription</h1>
      </div>
      
      <?php if (!empty($message)): ?>
      <!-- Message Modal -->
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Notice</h2>
            <a href="make_prescription.php" class="close">&times;</a>
          </div>
          <div class="modal-body">
            <p><?php echo $message; ?></p>
          </div>
        </div>
      </div>
      <?php endif; ?>
      
      <!-- Prescription Creation Form -->
      <div class="inventory-container">
        <main class="inventory-list">
          <h2>Create New Prescription</h2>
          <form method="POST" action="make_prescription.php" id="prescriptionForm">
            <input type="hidden" name="action" value="submit_prescription">
            
            <!-- Patient Name -->
            <div class="form-group">
              <label for="patient_name">Patient Name:</label>
              <input type="text" name="patient_name" id="patient_name" placeholder="Enter patient's full name" required>
            </div>
            
            <!-- Instructions -->
            <div class="form-group">
              <label for="instructions">Instructions:</label>
              <textarea name="instructions" id="instructions" placeholder="Enter any prescription instructions" rows="3"></textarea>
            </div>
            
            <!-- Medicine Rows Container -->
            <div id="medicineRows">
              <div class="medicine-row">
                <select name="medicines[]" required>
                  <option value="">-- Select Medicine --</option>
                  <?php foreach ($medicine_names as $med): ?>
                    <option value="<?php echo htmlspecialchars($med); ?>">
                      <?php echo htmlspecialchars($med); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <input type="number" name="quantities[]" placeholder="Quantity" required>
                <button type="button" class="delete-row" onclick="deleteRow(this)">Delete</button>
              </div>
            </div>
            
            <!-- Button to add more medicine rows -->
            <div class="form-group">
              <button type="button"  onclick="addRow()" class="nav-button">Add Row</button>
            </div>
            
            <button type="submit" class="modal-submit">Add Prescription</button>
          </form>
        </main>
      </div>
      
      <!-- Pending Prescriptions List -->
      <div class="transaction-history" style="max-height:200px; overflow-y:auto; margin-top:20px;">
        <h2>Pending Prescriptions</h2>
        <?php if (!empty($pending_prescriptions)): ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Prescription ID</th>
                <th>Patient</th>
                <th>Medicines</th>
                <th>Instructions</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pending_prescriptions as $presc): ?>
                <tr>
                  <td><?php echo htmlspecialchars($presc["prescription_code"]); ?></td>
                  <td><?php echo htmlspecialchars($presc["patient_name"]); ?></td>
                  <td><?php echo htmlspecialchars($presc["med_list"] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($presc["instructions"]); ?></td>
                  <td><?php echo date("Y-m-d", strtotime($presc["created_at"])); ?></td>
                  <td>
                    <!-- Remove Prescription -->
                    <form method="POST" action="make_prescription.php" style="display:inline;">
                      <input type="hidden" name="action" value="remove_prescription">
                      <input type="hidden" name="prescription_id" value="<?php echo htmlspecialchars($presc["prescription_code"]); ?>">
                      <button type="submit" class="overview-btn">Remove</button>
                    </form>
                    <!-- Force Complete Prescription -->
                    <form method="POST" action="make_prescription.php" style="display:inline;">
                      <input type="hidden" name="action" value="force_complete_prescription">
                      <input type="hidden" name="prescription_id" value="<?php echo htmlspecialchars($presc["prescription_code"]); ?>">
                      <button type="submit" class="overview-btn">Complete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No pending prescriptions.</p>
        <?php endif; ?>
      </div>
      
      <!-- Prescription History (Completed) -->
      <div class="transaction-history" style="max-height:200px; overflow-y:auto; margin-top:20px;">
        <h2>Prescription History</h2>
        <?php if (!empty($prescription_history)): ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Prescription ID</th>
                <th>Patient</th>
                <th>Medicines</th>
                <th>Instructions</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($prescription_history as $presc): ?>
                <tr>
                  <td><?php echo htmlspecialchars($presc["prescription_code"]); ?></td>
                  <td><?php echo htmlspecialchars($presc["patient_name"]); ?></td>
                  <td><?php echo htmlspecialchars($presc["med_list"] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($presc["instructions"]); ?></td>
                  <td><?php echo date("Y-m-d", strtotime($presc["created_at"])); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No completed prescriptions.</p>
        <?php endif; ?>
      </div>
      
    </div><!-- end page-content -->
  </div><!-- end global-container -->
  
  <!-- Modal Popup for Processing Internal Dispense -->
  <?php if (isset($_GET['action'])):
    $action = $_GET['action'];
    if ($action == "process" && isset($_GET['id'])):
      $presc_code = $conn->real_escape_string($_GET['id']);
      $stmt = $conn->prepare("SELECT p.*, pt.full_name as patient_name 
                              FROM prescriptions p 
                              JOIN patients pt ON p.patient_id = pt.patient_id 
                              WHERE p.prescription_code = ? AND p.status = 'Pending' LIMIT 1");
      if (!$stmt) {
          die("Prepare failed (process modal): " . $conn->error);
      }
      $stmt->bind_param("s", $presc_code);
      $stmt->execute();
      $result = $stmt->get_result();
      $process_prescription = $result ? $result->fetch_assoc() : null;
      $stmt->close();
      if ($process_prescription):
        $instructions = $process_prescription['instructions'];
  ?>
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Process Dispense for Prescription <?php echo htmlspecialchars($process_prescription['prescription_code']); ?></h2>
            <a href="dispense_medicine.php" class="close">&times;</a>
          </div>
          <div class="modal-body">
            <form method="POST" action="dispense_medicine.php?action=process&id=<?php echo htmlspecialchars($process_prescription['prescription_code']); ?>">
              <input type="hidden" name="prescription_id" value="<?php echo htmlspecialchars($process_prescription['prescription_code']); ?>">
              <input type="hidden" name="patient" value="<?php echo htmlspecialchars($process_prescription['patient_name']); ?>">
              <input type="hidden" name="instructions" value="<?php echo htmlspecialchars($instructions); ?>">
              <div class="form-group">
                <label>Patient:</label>
                <span><?php echo htmlspecialchars($process_prescription['patient_name']); ?></span>
              </div>
              <div class="form-group">
                <label>Instructions:</label>
                <span><?php echo htmlspecialchars($instructions); ?></span>
              </div>
              <div class="form-group">
                <label for="dispense-type">Dispense Type:</label>
                <select name="dispense_type" id="dispense-type" required>
                  <option value="inhouse">Inhouse</option>
                  <option value="e-prescription">E-Prescription</option>
                </select>
              </div>
              <div id="e-prescription-options" style="display:none;">
                <div class="form-group">
                  <label for="email">Email:</label>
                  <input type="email" name="email" id="email" placeholder="Enter email for e-prescription">
                </div>
                <div class="form-group">
                  <button type="button" id="printPrescription">Print Prescription</button>
                </div>
              </div>
              <button type="submit" name="process_dispense" class="modal-submit">Process Dispense</button>
            </form>
          </div>
        </div>
      </div>
      <script>
        document.getElementById('dispense-type').addEventListener('change', function() {
          if (this.value === 'e-prescription') {
            document.getElementById('e-prescription-options').style.display = 'block';
          } else {
            document.getElementById('e-prescription-options').style.display = 'none';
          }
        });
        document.getElementById('printPrescription').addEventListener('click', function() {
          window.print();
        });
      </script>
  <?php 
      endif;
    endif;
  endif;
  ?>
  
</body>
</html>
