<?php
session_start();
include 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user details from database with additional status information
$user_id = $_SESSION['user_id'];
$sql = "SELECT u.*, w.ward_name 
        FROM users u 
        LEFT JOIN wards w ON u.ward_id = w.ward_id 
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Redirect to appropriate dashboard if already approved
if ($user['approved_by_admin'] === 1) {
    $dashboard = ($user['role'] === 'candidate') ? 'candidate.php' : 'voter.php';
    header("Location: " . $dashboard);
    exit();
}

// Function to get status details
function getStatusDetails($status, $role, $created_at, $rejection_reason = '') {
    if ($status === 1) {
        return [
            'badge_color' => '#28a745',
            'status_text' => 'Approved',
            'message' => "Your registration has been approved. You can now access the " . 
                        ucfirst($role) . " dashboard."
        ];
    } else if ($status === 0) {
        $waiting_time = ceil((time() - strtotime($created_at)) / (60 * 60 * 24));
        return [
            'badge_color' => '#ffd700',
            'status_text' => 'Pending Approval',
            'message' => "Your registration is under review (Day $waiting_time). Average processing time is 2-3 business days."
        ];
    } else {
        return [
            'badge_color' => '#dc3545',
            'status_text' => 'Rejected',
            'message' => "Your registration has been rejected." . 
                      (!empty($rejection_reason) ? " Reason: " . $rejection_reason : "")
        ];
    }
}

$status_details = getStatusDetails($user['approved_by_admin'], $user['role'], $user['created_at'], $user['rejection_reason']);

// Define timeline classes based on status
$registrationClass = 'completed';
$documentClass = $user['approved_by_admin'] === 0 ? 'current' : ($user['approved_by_admin'] === 1 ? 'completed' : '');
$approvalClass = $user['approved_by_admin'] === 1 ? 'completed' : ($user['approved_by_admin'] === -1 ? 'rejected' : '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BharatV - Registration Status</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color:white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: linear-gradient(135deg, #f5f7fa 100%);
            padding: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .header img {
            max-height: 100px;
            max-width: 250px;
        }

        .status-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .status-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .status-header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .status-content {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .status-info {
            margin-bottom: 15px;
        }

        .status-info p {
            margin: 10px 0;
            font-size: 16px;
            color: #555;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            background-color: <?php echo $status_details['badge_color']; ?>;
            color: <?php echo $status_details['badge_color'] === '#ffd700' ? '#333' : '#fff'; ?>;
        }

        .status-timeline {
            margin: 30px 0;
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }

        .timeline-item:before {
            content: '';
            position: absolute;
            left: -30px;
            top: 0;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background-color: #ddd;
        }

        .timeline-item.completed:before {
            background-color: #28a745;
        }

        .timeline-item.current:before {
            background-color: #007bff;
        }

        .timeline-item.rejected:before {
            background-color: #dc3545;
        }

        .timeline-item:after {
            content: '';
            position: absolute;
            left: -23px;
            top: 15px;
            width: 2px;
            height: calc(100% - 15px);
            background-color: #ddd;
        }

        .timeline-item:last-child:after {
            display: none;
        }

        .timeline-content {
            margin-bottom: 10px;
        }

        .timeline-date {
            font-size: 12px;
            color: #666;
        }

        .document-status {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 4px;
        }

        .status-message {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background-color: <?php echo $user['approved_by_admin'] === -1 ? '#dc3545' : 'orange'; ?>;
            border: 1px solid #ffeeba;
            border-radius: 4px;
            color: white;
        }

        .rejection-reason {
            margin-top: 15px;
            padding: 15px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            color: #721c24;
            display: <?php echo ($user['approved_by_admin'] === -1 && !empty($user['rejection_reason'])) ? 'block' : 'none'; ?>;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: green;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            margin-top: 20px;
        }

        .logout-btn:hover {
            background-color: lightgreen;
        }

        .refresh-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color:green;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            margin-bottom: 10px;
        }

        .refresh-btn:hover {
            background-color: lightgreen;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="assets/logo.jpg" alt="Company Logo">
    </div>

    <div class="status-container">
        <div class="status-header">
            <h1>Registration Status</h1>
        </div>

        <div class="status-content">
            <div class="status-info">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Role:</strong> <?php echo ucfirst(htmlspecialchars($user['role'])); ?></p>
                <p><strong>Ward:</strong> <?php echo htmlspecialchars($user['ward_name']); ?></p>
                <p><strong>Status:</strong> <span class="status-badge"><?php echo $status_details['status_text']; ?></span></p>
            </div>

            <div class="status-timeline">
                <div class="timeline-item <?php echo $registrationClass; ?>">
                    <div class="timeline-content">
                        <strong>Registration Submitted</strong>
                        <div class="timeline-date">
                            <?php echo date('F j, Y g:i A', strtotime($user['created_at'])); ?>
                        </div>
                    </div>
                </div>

                <div class="timeline-item <?php echo $documentClass; ?>">
                    <div class="timeline-content">
                        <strong>Document Verification</strong>
                        <div class="document-status">
                            <p>✓ Aadhaar Card Uploaded</p>
                            <p><?php echo $user['approved_by_admin'] === -1 ? '✘ Verification Failed' : '✓ Information Verification in Progress'; ?></p>
                        </div>
                    </div>
                </div>

                <div class="timeline-item <?php echo $approvalClass; ?>">
                    <div class="timeline-content">
                        <strong><?php echo $user['approved_by_admin'] === -1 ? 'Application Rejected' : 'Final Approval'; ?></strong>
                        <div class="timeline-date">
                            <?php 
                            if ($user['approved_by_admin'] === 1) {
                                echo date('F j, Y', strtotime($user['updated_at']));
                            } elseif ($user['approved_by_admin'] === -1) {
                                echo date('F j, Y', strtotime($user['updated_at']));
                            } else {
                                echo 'Pending';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="status-message">
                <?php echo $status_details['message']; ?>
            </div>

            <?php if ($user['approved_by_admin'] === -1 && !empty($user['rejection_reason'])): ?>
            <div class="rejection-reason">
                <strong>Rejection Details:</strong>
                <p><?php echo htmlspecialchars($user['rejection_reason']); ?></p>
                <p>If you believe this is an error or you would like to provide additional information, please contact our support team.</p>
            </div>
            <?php endif; ?>
        </div>

        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="refresh-btn">Refresh Status</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>