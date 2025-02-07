<?php
session_start();
include 'db.php';

// Debug logging for session and initial page load
error_log("Debug: Session started");
error_log("Debug: User session name: " . ($_SESSION['name'] ?? 'not set'));

// Session check
if (!isset($_SESSION['name'])) {
    error_log("Debug: No active session, redirecting to login");
    header("Location: login.php");
    exit();
}

// Handle approval/rejection actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    error_log("Debug: Processing action '$action' for user_id: $user_id");
    
    if ($action === 'approve' || $action === 'reject') {
        $status = ($action === 'approve') ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE users SET approved_by_admin = ? WHERE user_id = ?");
        if (!$stmt) {
            error_log("Prepare failed in approval process: " . $conn->error);
            $_SESSION['error_message'] = "Error preparing statement";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        
        $stmt->bind_param("ii", $status, $user_id);
        
        if ($stmt->execute()) {
            error_log("Successfully updated user_id: $user_id with status: $status");
            $_SESSION['success_message'] = "User successfully " . ($status ? "approved" : "rejected");
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            error_log("Error updating user_id: $user_id - " . $stmt->error);
            $_SESSION['error_message'] = "Error processing request";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        $stmt->close();
    }
}

// Function to check database connection
function checkDatabaseConnection() {
    global $conn;
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return false;
    }
    error_log("Database connection successful");
    return true;
}

// Enhanced getPendingApprovals function with error handling
function getPendingApprovals($role) {
    global $conn;
    
    if (!checkDatabaseConnection()) {
        return false;
    }
    
    $sql = "SELECT u.*, w.ward_name 
            FROM users u 
            LEFT JOIN wards w ON u.ward_id = w.ward_id 
            WHERE u.role = ? AND u.approved_by_admin = 0 
            ORDER BY u.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed in getPendingApprovals: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("s", $role);
    if (!$stmt->execute()) {
        error_log("Execute failed in getPendingApprovals: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result();
    error_log("Found " . $result->num_rows . " pending approvals for role: " . $role);
    
    return $result;
}

// Enhanced getApprovedUsers function with error handling
function getApprovedUsers($role) {
    global $conn;
    
    if (!checkDatabaseConnection()) {
        return false;
    }
    
    $sql = "SELECT u.*, w.ward_name 
            FROM users u 
            LEFT JOIN wards w ON u.ward_id = w.ward_id 
            WHERE u.role = ? AND u.approved_by_admin = 1 
            ORDER BY u.name ASC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed in getApprovedUsers: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("s", $role);
    if (!$stmt->execute()) {
        error_log("Execute failed in getApprovedUsers: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result();
    error_log("Found " . $result->num_rows . " approved users for role: " . $role);
    
    return $result;
}

// Get all users
$pendingVoters = getPendingApprovals('voter');
$pendingCandidates = getPendingApprovals('candidate');
$approvedVoters = getApprovedUsers('voter');
$approvedCandidates = getApprovedUsers('candidate');

// Debug logging for result counts
error_log("Debug: Pending voters: " . ($pendingVoters ? $pendingVoters->num_rows : 'query failed'));
error_log("Debug: Pending candidates: " . ($pendingCandidates ? $pendingCandidates->num_rows : 'query failed'));
error_log("Debug: Approved voters: " . ($approvedVoters ? $approvedVoters->num_rows : 'query failed'));
error_log("Debug: Approved candidates: " . ($approvedCandidates ? $approvedCandidates->num_rows : 'query failed'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Approvals - Admin Panel</title>
    <style>
        /* Your existing CSS styles here */
        .approval-section {
            margin: 20px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .approval-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .approval-table th,
        .approval-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .approval-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .approval-table tr:hover {
            background-color: #f9f9f9;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 5px;
        }

        .btn-approve {
            background-color: #4CAF50;
            color: white;
        }

        .btn-reject {
            background-color: #f44336;
            color: white;
        }

        .alert {
            padding: 15px;
            margin: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #dff0d8;
            border-color: #d6e9c6;
            color: #3c763d;
        }

        .alert-error {
            background-color: #f2dede;
            border-color: #ebccd1;
            color: #a94442;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .debug-info {
            background-color: #f8f9fa;
            padding: 10px;
            margin: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-family: monospace;
        }

        .company-logo {
  top: 2px;
  left: 10px;
  display: flex;
  align-items: center;
}

.company-logo img {
  width: 140px;
  height: auto;
}
    </style>
</head>
<body>


<div class="company-logo">
           <a href="admin.php"><img src="assets/logo.jpg" alt="Company logo"></a>
        </div>
    <!-- Debug Information Section (only visible to admins) -->
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
    <div class="debug-info">
        <h3>Debug Information</h3>
        <p>Pending Voters: <?php echo $pendingVoters ? $pendingVoters->num_rows : 'Query Failed'; ?></p>
        <p>Pending Candidates: <?php echo $pendingCandidates ? $pendingCandidates->num_rows : 'Query Failed'; ?></p>
        <p>Approved Voters: <?php echo $approvedVoters ? $approvedVoters->num_rows : 'Query Failed'; ?></p>
        <p>Approved Candidates: <?php echo $approvedCandidates ? $approvedCandidates->num_rows : 'Query Failed'; ?></p>
    </div>
    <?php endif; ?>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Pending Candidates Section -->
    <div class="approval-section">
        <h2>Pending Candidate Approvals</h2>
        <?php if ($pendingCandidates && $pendingCandidates->num_rows > 0): ?>
            <table class="approval-table">
            <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Ward</th>
                            <th>Aadhaar Number</th>
                            <th>Documents</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($candidate = $pendingCandidates->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                                <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                                <td><?php echo htmlspecialchars($candidate['phone']); ?></td>
                                <td><?php echo htmlspecialchars($candidate['ward_name']); ?></td>
                                <td><?php echo htmlspecialchars($candidate['aadhaar_number']); ?></td>
                                <td>
                                    <a href="<?php echo htmlspecialchars($candidate['aadhaar_file']); ?>" 
                                       class="view-doc" target="_blank">View Aadhaar</a>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" 
                                               value="<?php echo $candidate['user_id']; ?>">
                                        <button type="submit" name="action" value="approve" 
                                                class="btn btn-approve">Approve</button>
                                        <button type="submit" name="action" value="reject" 
                                                class="btn btn-reject">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
            </table>
        <?php else: ?>
            <p>No pending candidate approvals<?php echo $pendingCandidates === false ? ' (Error loading data)' : ''; ?>.</p>
        <?php endif; ?>
    </div>

    <!-- Pending Voters Section -->
    <div class="approval-section">
        <h2>Pending Voter Approvals</h2>
        <?php if ($pendingVoters && $pendingVoters->num_rows > 0): ?>
            <table class="approval-table">
            <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Ward</th>
                            <th>Aadhar Number</th>
                            <th>Documents</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($voter = $pendingVoters->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($voter['name']); ?></td>
                                <td><?php echo htmlspecialchars($voter['email']); ?></td>
                                <td><?php echo htmlspecialchars($voter['phone']); ?></td>
                                <td><?php echo htmlspecialchars($voter['ward_name']); ?></td>
                                <td><?php echo htmlspecialchars($voter['aadhaar_number']); ?></td>

                                <td>
                                    <a href="<?php echo htmlspecialchars($voter['aadhaar_file']); ?>" 
                                       class="view-doc" target="_blank">View Aadhaar</a>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" 
                                               value="<?php echo $voter['user_id']; ?>">
                                        <button type="submit" name="action" value="approve" 
                                                class="btn btn-approve">Approve</button>
                                        <button type="submit" name="action" value="reject" 
                                                class="btn btn-reject">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
            </table>
        <?php else: ?>
            <p>No pending voter approvals<?php echo $pendingVoters === false ? ' (Error loading data)' : ''; ?>.</p>
        <?php endif; ?>
    </div>

    <!-- Approved Candidates Section -->
    <div class="approval-section">
        <h2>Approved Candidates</h2>
        <?php if ($approvedCandidates && $approvedCandidates->num_rows > 0): ?>
            <table class="approval-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Ward</th>
                    <th>Aadhaar Number</th>
                    <th>Documents</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($candidate = $approvedCandidates->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                        <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                        <td><?php echo htmlspecialchars($candidate['phone']); ?></td>
                        <td><?php echo htmlspecialchars($candidate['ward_name']); ?></td>
                        <td><?php echo htmlspecialchars($candidate['aadhaar_number']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars($candidate['aadhaar_file']); ?>" 
                               class="view-doc" target="_blank">View Aadhaar</a>
                        </td>
                        <td>
                            <span class="badge badge-success">Approved</span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            </table>
        <?php else: ?>
            <p>No approved candidates found<?php echo $approvedCandidates === false ? ' (Error loading data)' : ''; ?>.</p>
        <?php endif; ?>
    </div>

    <!-- Approved Voters Section -->
    <div class="approval-section">
        <h2>Approved Voters</h2>
        <?php if ($approvedVoters && $approvedVoters->num_rows > 0): ?>
            <table class="approval-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Ward</th>
                    <th>Aadhaar Number</th>
                    <th>Documents</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($voter = $approvedVoters->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($voter['name']); ?></td>
                        <td><?php echo htmlspecialchars($voter['email']); ?></td>
                        <td><?php echo htmlspecialchars($voter['phone']); ?></td>
                        <td><?php echo htmlspecialchars($voter['ward_name']); ?></td>
                        <td><?php echo htmlspecialchars($voter['aadhaar_number']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars($voter['aadhaar_file']); ?>" 
                               class="view-doc" target="_blank">View Aadhaar</a>
                        </td>
                        <td>
                            <span class="badge badge-success">Approved</span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            </table>
        <?php else: ?>
            <p>No approved voters found<?php echo $approvedVoters === false ? ' (Error loading data)' : ''; ?>.</p>
        <?php endif; ?>
    </div>

    <script>
        // Confirm before rejecting
        document.querySelectorAll('.btn-reject').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to reject this user?')) {
                    e.preventDefault();
                }
            });
        });

        // Add error logging to form submissions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                console.log('Form submitted:', {
                    action: this.querySelector('[name="action"]').value,
                    userId: this.querySelector('[name="user_id"]').value
                });
            });
        });
    </script>
</body>
</html>