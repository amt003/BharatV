<?php
session_start();
require_once 'db.php'; // Ensure database connection

// Add cache control headers to prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Check if the user is logged in and is a returning officer
if (!isset($_SESSION['ro_id']) || $_SESSION['role'] !== 'returning_officer') {
    header('Location: login.php');
    exit();
}

// Handle form submission for approving or rejecting applications
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'];
    $action = $_POST['action']; // 'approve' or 'reject'
    
    
    // Start transaction
    $conn->begin_transaction();

    try {
        // First update the application status
        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE candidate_applications SET application_ro_approval = 'approved' WHERE application_id = ?");
            $stmt->bind_param("i", $application_id);
        } else {
            // Include rejection reason when rejecting
            $stmt = $conn->prepare("UPDATE candidate_applications SET application_ro_approval = 'rejected' WHERE application_id = ?");
            $stmt->bind_param("i", $application_id);
        }
        $stmt->execute();

        // If approving, insert into contesting_candidates
        if ($action === 'approve') {
            // Get candidate details from application
            $getDetailsQuery = $conn->prepare("
                SELECT ca.id, ca.election_id, ca.ward_id, ca.party_id, ca.application_type,
                       ca.independent_party_name, ca.independent_party_symbol 
                FROM candidate_applications ca 
                WHERE ca.application_id = ?
            ");
            $getDetailsQuery->bind_param("i", $application_id);
            $getDetailsQuery->execute();
            $details = $getDetailsQuery->get_result()->fetch_assoc();

            // For independent candidates, explicitly set party_id to NULL
            if ($details['application_type'] === 'independent') {
                $details['party_id'] = null;
            }

            // Insert into contesting_candidates with all fields
            $insertQuery = $conn->prepare("
                INSERT INTO contesting_candidates 
                (id, Election_id, ward_id, party_id, application_type, 
                independent_party_name, independent_party_symbol) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                ward_id = VALUES(ward_id),
                party_id = VALUES(party_id),
                application_type = VALUES(application_type),
                independent_party_name = VALUES(independent_party_name),
                independent_party_symbol = VALUES(independent_party_symbol)
            ");
            
            $insertQuery->bind_param("iiissss", 
                $details['id'], 
                $details['election_id'], 
                $details['ward_id'], 
                $details['party_id'],
                $details['application_type'],
                $details['independent_party_name'],
                $details['independent_party_symbol']
            );
            $insertQuery->execute();
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['message'] = "Application successfully " . ($action === 'approve' ? "approved" : "rejected") . ".";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['message'] = "Error processing application: " . $e->getMessage();
    }

    header("Location: officer.php");
    exit();
}

// Fetch all scheduled elections
$electionsQuery = $conn->prepare("
    SELECT election_id, Election_title, start_date, end_date, status 
    FROM elections 
    WHERE status = 'scheduled'
");
$electionsQuery->execute();
$electionsResult = $electionsQuery->get_result();

// At the top of your officer.php file, check for the session message
if (isset($_SESSION['message'])): ?>
    <div class="message"><?php echo htmlspecialchars($_SESSION['message']); ?></div>
    <?php unset($_SESSION['message']); // Clear the session message after displaying it ?>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve/Reject Applications</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --danger-color: #dc3545;
            --success-color: #28a745;
            --warning-color: #ffc107;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            min-height: 100vh;
        }
        

.candidate-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px;
}

.detail-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.detail-section h3 {
    margin-bottom: 15px;
    color: #333;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 5px;
}

.detail-row {
    margin-bottom: 10px;
}

.detail-label {
    font-weight: bold;
    color: #495057;
    display: inline-block;
    width: 150px;
}

.detail-value {
    color: #212529;
}

.document-link {
    color: #007bff;
    text-decoration: none;
}

.document-link:hover {
    text-decoration: underline;
}

.error {
    color: #dc3545;
    padding: 10px;
    background: #f8d7da;
    border-radius: 4px;
}


        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .party-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .party-details h1 {
            font-size: 24px;
            color: #333;
        }

        .party-details p {
            color: #666;
            font-size: 14px;
        }

        .application-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .message {
            margin-bottom: 20px;
            color: #28a745; /* Success color */
        }

        .election-section {
            margin-bottom: 30px;
        }

        .election-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .wards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .ward-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .candidate-list {
            list-style: none;
            padding: 0;
        }

        .candidate-item {
            background: #ffffff; /* White background for candidate items */
            border: 1px solid #dee2e6; /* Border for candidate items */
            border-radius: 5px; /* Rounded corners */
            padding: 15px; /* Padding inside the candidate item */
            margin-bottom: 10px; /* Space below each candidate item */
            transition: box-shadow 0.3s; /* Smooth transition for hover effect */
        }

        .candidate-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Shadow effect on hover */
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .action-btn {
            padding: 8px 12px; /* Padding for buttons */
            border: none; /* No border */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            font-size: 0.9em; /* Font size for buttons */
            transition: background-color 0.3s; /* Smooth transition for background color */
        }

        .approve-btn {
            background-color: #28a745; /* Green for approve */
            color: white; /* White text */
        }

        .approve-btn:hover {
            background-color: #218838; /* Darker green on hover */
        }

        .reject-btn {
            background-color: #dc3545; /* Red for reject */
            color: white; /* White text */
        }

        .reject-btn:hover {
            background-color: #c82333; /* Darker red on hover */
        }

        .view-btn {
            background-color: #007bff; /* Blue for view */
            color: white; /* White text */
        }

        .view-btn:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        .status-badge {
            display: inline-block; /* Inline block for badges */
            padding: 5px 10px; /* Padding for badges */
            border-radius: 12px; /* Rounded corners for badges */
            font-size: 0.9em; /* Font size for badges */
            color: #fff; /* White text color */
        }

        .status-approved {
            background-color: #28a745; /* Green for approved */
        }

        .status-rejected {
            background-color: #dc3545; /* Red for rejected */
        }

        .status-pending {
            background-color: #ffc107; /* Yellow for pending */
        }

        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }

        .modal-content {
            background-color: #fefefe; /* White background */
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px; /* Padding inside the modal */
            border: 1px solid #888; /* Border for modal */
            width: 80%; /* Could be more or less, depending on screen size */
        }

        .close-btn {
            color: #aaa; /* Gray color for close button */
            float: right; /* Right aligned */
            font-size: 28px; /* Font size for close button */
            font-weight: bold; /* Bold text */
        }

        .close-btn:hover,
        .close-btn:focus {
            color: black; /* Black on hover */
            text-decoration: none; /* No underline */
            cursor: pointer; /* Pointer cursor */
        }

        /* General Styles */
        .election-info {
            background-color: #f8f9fa; /* Light background for election info */
            border: 1px solid #dee2e6; /* Border for election info */
            border-radius: 5px; /* Rounded corners */
            padding: 15px; /* Padding inside the box */
            margin-bottom: 20px; /* Space below the election info */
        }

        .election-info strong {
            color: #333; /* Dark color for labels */
        }

        /* Candidate Item Styles */
        .candidate-item {
            background: #ffffff; /* White background for candidate items */
            border: 1px solid #dee2e6; /* Border for candidate items */
            border-radius: 5px; /* Rounded corners */
            padding: 15px; /* Padding inside the candidate item */
            margin-bottom: 10px; /* Space below each candidate item */
            transition: box-shadow 0.3s; /* Smooth transition for hover effect */
        }

        .candidate-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Shadow effect on hover */
        }

        /* Status Badge Styles */
        .status-badge {
            display: inline-block; /* Inline block for badges */
            padding: 5px 10px; /* Padding for badges */
            border-radius: 12px; /* Rounded corners for badges */
            font-size: 0.9em; /* Font size for badges */
            color: #fff; /* White text color */
        }

        .status-approved {
            background-color: #28a745; /* Green for approved */
        }

        .status-rejected {
            background-color: #dc3545; /* Red for rejected */
        }

        .status-pending {
            background-color: #ffc107; /* Yellow for pending */
        }

        /* Action Button Styles */
        .action-btn {
            padding: 8px 12px; /* Padding for buttons */
            border: none; /* No border */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            font-size: 0.9em; /* Font size for buttons */
            transition: background-color 0.3s; /* Smooth transition for background color */
        }

        .approve-btn {
            background-color: #28a745; /* Green for approve */
            color: white; /* White text */
        }

        .approve-btn:hover {
            background-color: #218838; /* Darker green on hover */
        }

        .reject-btn {
            background-color: #dc3545; /* Red for reject */
            color: white; /* White text */
        }

        .reject-btn:hover {
            background-color: #c82333; /* Darker red on hover */
        }

        .view-btn {
            background-color:orangered ; 
            color: white; 
        }

        .view-btn:hover {
            background-color: orange; 
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: white;
            margin: 50px auto;
            padding: 20px;
            width: 90%;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .close-btn {
            color: #aaa; /* Gray color for close button */
            float: right; /* Right aligned */
            font-size: 28px; /* Font size for close button */
            font-weight: bold; /* Bold text */
        }

        .close-btn:hover,
        .close-btn:focus {
            color: black; /* Black on hover */
            text-decoration: none; 
            cursor: pointer; 
        }
        .logout-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            background: var(--primary-color);
            color: white;
            font-size: 0.9em;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }   

        .logout-btn:hover {
            background:rgb(15, 203, 56);
        }

        /* Custom scrollbar styles */
::-webkit-scrollbar {
  width: 10px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #4caf50;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #45a049;
}

/* Hide the scrollbar arrows */
::-webkit-scrollbar-button {
  display: none;
}

.no-nominations-message {
    text-align: center;
    padding: 40px;
    background: #f8f9fa;
    border-radius: 10px;
    margin: 20px 0;
    border: 2px dashed #dee2e6;
}

.no-nominations-message i {
    font-size: 48px;
    color: #6c757d;
    margin-bottom: 15px;
}

.no-nominations-message h3 {
    color: #495057;
    margin-bottom: 10px;
}

.no-nominations-message p {
    color: #6c757d;
    font-size: 1.1em;
}

.no-wards-message {
    text-align: center;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 10px;
    margin: 20px 0;
    border: 2px dashed #dee2e6;
}

.no-wards-message i {
    font-size: 36px;
    color: #6c757d;
    margin-bottom: 10px;
}

.no-wards-message h4 {
    color: #495057;
    margin-bottom: 8px;
}

.no-wards-message p {
    color: #6c757d;
}

.no-candidates-message {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    margin: 10px 0;
    border: 1px dashed #dee2e6;
    list-style: none;
}

.no-candidates-message i {
    font-size: 24px;
    color: #6c757d;
    margin-bottom: 8px;
}

.no-candidates-message p {
    color: #6c757d;
    margin: 0;
}

    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="header">
        <div class="party-info">
            <div class="party-details">
                <h1>Returning Officer Dashboard</h1>
                <p>Manage Candidate Nominations</p>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="application-container">
        <h2 style="text-align:center;margin-bottom:10px;">Nominations</h2>
        <?php 
        if ($electionsResult->num_rows === 0): ?>
            <div class="no-nominations-message">
                <i class="fas fa-calendar-times"></i>
                <h3>No Scheduled Elections</h3>
                <p>There are currently no elections scheduled for nominations.</p>
            </div>
        <?php else: 
            while ($election = $electionsResult->fetch_assoc()): ?>
                <div class="election-section">
                    <h3><?php echo htmlspecialchars($election['Election_title']); ?></h3>
                    <div class="election-info">
                        <strong>Status:</strong> <?php echo htmlspecialchars($election['status']); ?><br>
                        <strong>Start Date:</strong> <?php echo htmlspecialchars($election['start_date']); ?><br>
                        <strong>End Date:</strong> <?php echo htmlspecialchars($election['end_date']); ?>
                    </div>
                    
                    <div class="wards-container">
                        <?php
                        // Fetch only wards associated with this election
                        $wardsQuery = $conn->prepare("
                            SELECT DISTINCT w.ward_id, w.ward_name 
                            FROM wards w 
                            JOIN elections ew ON w.ward_id = ew.ward_ids 
                            WHERE ew.election_id = ?
                        ");
                        $wardsQuery->bind_param("i", $election['election_id']);
                        $wardsQuery->execute();
                        $wardsResult = $wardsQuery->get_result();

                        if ($wardsResult->num_rows === 0): ?>
                            <div class="no-wards-message">
                                <i class="fas fa-map-marker-slash"></i>
                                <h4>No Wards Assigned</h4>
                                <p>This election has no wards assigned yet.</p>
                            </div>
                        <?php else:
                            while ($ward = $wardsResult->fetch_assoc()): 
                                // Get candidates for this ward and election
                                $candidatesQuery = $conn->prepare("
                                    SELECT 
                                        ca.application_id,
                                        ca.application_ro_approval,
                                        ca.application_party_approval,
                                        ca.application_type,
                                        ca.election_id,
                                        ca.ward_id,
                                        u.name,
                                        u.phone,
                                        u.email,
                                        p.party_name,
                                        p.party_id,
                                        ca.independent_party_name,
                                        ca.independent_party_symbol
                                    FROM candidate_applications ca 
                                    JOIN users u ON ca.id = u.id 
                                    LEFT JOIN parties p ON ca.party_id = p.party_id 
                                    WHERE ca.ward_id = ? 
                                    AND ca.election_id = ?
                                    AND (
                                        (ca.application_type = 'independent')
                                        OR 
                                        (ca.application_type = 'party' AND ca.application_party_approval = 'approved')
                                    )
                                    ORDER BY ca.application_id DESC
                                ");
                                $candidatesQuery->bind_param("ii", $ward['ward_id'], $election['election_id']);
                                $candidatesQuery->execute();
                                $candidatesResult = $candidatesQuery->get_result();
                            ?>
                                <div class="ward-card">
                                    <h4><?php echo htmlspecialchars($ward['ward_name']); ?> Ward</h4>
                                    <ul class="candidate-list">
                                        <?php if ($candidatesResult->num_rows > 0): ?>
                                            <?php while ($candidate = $candidatesResult->fetch_assoc()): ?>
                                                <li class="candidate-item">
                                                    <div class="candidate-info">
                                                        <div class="candidate-name">
                                                            <strong>Name:</strong> 
                                                            <strong><?php echo htmlspecialchars($candidate['name']); ?></strong>
                                                        </div>
                                                        <div class="candidate-details">
                                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($candidate['phone']); ?>
                                                            <br>
                                                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($candidate['email']); ?>
                                                        </div>
                                                        <div>
                                                            <strong>Party:</strong> 
                                                            <?php echo htmlspecialchars($candidate['party_name'] ?? 'Independent Candidate'); ?>
                                                            <br>
                                                            <?php if ($candidate['application_type'] === 'party'): ?>
                                                                <div>
                                                                    <strong>Party Approval:</strong>
                                                                    <span class="status-badge status-<?php echo strtolower($candidate['application_party_approval'] ?? 'pending'); ?>">
                                                                        <?php echo ucfirst($candidate['application_party_approval'] ?? 'Pending'); ?>
                                                                    </span>
                                                                </div>
                                                            <?php endif; ?>
                                                            <strong>RO Approval:</strong>
                                                            <span class="status-badge status-<?php echo strtolower($candidate['application_ro_approval'] ?? 'pending'); ?>">
                                                                <?php echo ucfirst($candidate['application_ro_approval'] ?? 'Pending'); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="action-buttons">
                                                        <?php if (!in_array($candidate['application_ro_approval'], ['approved', 'rejected'])): ?>
                                                            <form method="post" action="" class="action-form">
                                                                <input type="hidden" name="application_id" value="<?php echo $candidate['application_id']; ?>">
                                                                <button type="submit" name="action" value="approve" class="action-btn approve-btn">
                                                                    <i class="fas fa-check-circle"></i>
                                                                    <span>Approve</span>
                                                                </button>
                                                                <button type="submit" name="action" value="reject" class="action-btn reject-btn">
                                                                    <i class="fas fa-times-circle"></i>
                                                                    <span>Reject</span>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <button type="button" class="action-btn view-btn" 
                                                                onclick="viewCandidate(<?php echo $candidate['application_id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                            <span>View Details</span>
                                                        </button>
                                                    </div>
                                                </li>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <li class="no-candidates-message">
                                                <i class="fas fa-user-slash"></i>
                                                <p>No nominations received for this ward yet.</p>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endwhile;
                        endif; ?>
                    </div>
                </div>
            <?php endwhile;
        endif; ?>
    </div>
</div>

<!-- Candidate Details Modal -->
<div id="candidateModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Candidate Details</h2>
        <div id="candidateDetails" class="candidate-details">
            <!-- Details will be loaded here -->
        </div>
    </div>
</div>

<script>
function viewCandidate(applicationId) {
    const modal = document.getElementById('candidateModal');
    const detailsContainer = document.getElementById('candidateDetails');
    
    // Show loading state
    detailsContainer.innerHTML = '<p>Loading...</p>';
    modal.style.display = 'block';
    
    // Fetch candidate details
    fetch(`get_ro_candidate_details.php?application_id=${applicationId}`)
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                const data = response.data;
                const applicationForm = data.application_form;
                
                let html = `
                    <div class="candidate-detail-grid">
                        <div class="detail-section">
                            <h3>Basic Information</h3>
                            <div class="detail-row">
                                <span class="detail-label">Name:</span>
                                <span class="detail-value">${data.name}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Phone:</span>
                                <span class="detail-value">${data.phone}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value">${data.email}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Age:</span>
                                <span class="detail-value">${applicationForm.age}</span>
                            </div>
                        </div>

                        <div class="detail-section">
                            <h3>Election Details</h3>
                            <div class="detail-row">
                                <span class="detail-label">Election:</span>
                                <span class="detail-value">${data.election_title}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Ward:</span>
                                <span class="detail-value">${data.ward_name}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Application Type:</span>
                                <span class="detail-value">${data.application_type}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Party:</span>
                                <span class="detail-value">${data.application_type === 'independent' ? 
                                    data.independent_party_name + ' (Independent)' : data.party_name}</span>
                            </div>
                        </div>

                        <div class="detail-section">
                            <h3>Additional Information</h3>
                            <div class="detail-row">
                                <span class="detail-label">Education:</span>
                                <span class="detail-value">${applicationForm.education}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Occupation:</span>
                                <span class="detail-value">${applicationForm.occupation}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Political Experience:</span>
                                <span class="detail-value">${applicationForm.political_experience} years</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Address:</span>
                                <span class="detail-value">${applicationForm.address}</span>
                            </div>
                        </div>

                        <div class="detail-section">
                            <h3>Documents</h3>
                            <div class="detail-row">
                                <span class="detail-label">Profile Photo:</span>
                                <img src="uploads/profile_photos/${applicationForm.profile_photo}" 
                                     alt="Profile Photo" 
                                     style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Aadhar Proof:</span>
                                <a href="uploads/aadhar_proofs/${applicationForm.aadhar_proof}" 
                                   target="_blank" 
                                   class="document-link">View Document</a>
                            </div>
                            ${data.application_type === 'independent' ? `
                                <div class="detail-row">
                                    <span class="detail-label">Party Symbol:</span>
                                    <img src="uploads/party_symbols/${data.independent_party_symbol}" 
                                         alt="Party Symbol" 
                                         style="width: 100px; height: 100px; object-fit: contain;">
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
                
                detailsContainer.innerHTML = html;
            } else {
                detailsContainer.innerHTML = '<p class="error">Failed to load candidate details.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            detailsContainer.innerHTML = '<p class="error">Failed to load candidate details.</p>';
        });
}

// Close modal
document.querySelector('.close-btn').onclick = function() {
    document.getElementById('candidateModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('candidateModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

   

</script>

</body>
</html>