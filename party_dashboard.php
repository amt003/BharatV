<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is a party admin
if (!isset($_SESSION['party_id'])) {
    header("Location: login.php");
    exit();
}

// Get party details
$party_id = $_SESSION['party_id'];
$stmt = $conn->prepare("SELECT party_name FROM parties WHERE party_id = ?");
$stmt->bind_param("i", $party_id);
$stmt->execute();
$party_result = $stmt->get_result();
$party_data = $party_result->fetch_assoc();

// Get all wards
$wards_query = "SELECT * FROM wards ORDER BY ward_id";
$wards_result = $conn->query($wards_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Party Dashboard - <?php echo htmlspecialchars($party_data['party_name']); ?></title>
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

        .party-logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }

        .party-details h1 {
            font-size: 24px;
            color: #333;
        }

        .party-details p {
            color: #666;
            font-size: 14px;
        }

        .wards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .ward-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .ward-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .ward-header h3 {
            color: #333;
            font-size: 18px;
        }

        .candidate-list {
            list-style: none;
            padding: 0;
        }

        .candidate-item {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #4CAF50;
        }

        .candidate-info {
            margin-bottom: 10px;
        }

        .candidate-name {
            font-weight: 600;
            color: #333;
        }

        .candidate-details {
            color: #666;
            font-size: 0.9em;
            margin: 5px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .view-btn {
            background: #4CAF50;
            color: white;
        }

        .approve-btn {
            background: #28a745;
            color: white;
        }

        .reject-btn {
            background: #dc3545;
            color: white;
        }

        .no-candidates {
            color: #666;
            text-align: center;
            padding: 20px;
            font-style: italic;
        }

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
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .close-btn {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .candidate-details {
            margin-top: 20px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .detail-label {
            width: 150px;
            font-weight: 500;
            color: #666;
        }

        .detail-value {
            flex: 1;
            color: #333;
        }

        @media (max-width: 768px) {
            .wards-container {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                text-align: center;
            }

            .party-info {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="party-info">
                <img src="<?php echo $party_id; ?>.jpg" alt="Party Logo" class="party-logo">
                <div class="party-details">
                    <h1><?php echo htmlspecialchars($party_data['party_name']); ?></h1>
                    <p>Candidate Applications Dashboard</p>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="wards-container">
            <?php while($ward = $wards_result->fetch_assoc()): ?>
                <div class="ward-card">
                    <div class="ward-header">
                        <h2>Ward <?php echo $ward['ward_id']; ?> - <?php echo htmlspecialchars($ward['ward_name']); ?></h2>
                    </div>

                    <?php
                    // Get candidates for this ward and party
                    $stmt = $conn->prepare("
                        SELECT c.*, u.name, u.phone, u.email 
                        FROM candidate_applications c 
                        JOIN users u ON c.user_id = u.user_id 
                        WHERE c.ward_id = ? AND c.party_id = ?
                        ORDER BY c.application_date DESC
                    ");
                    $stmt->bind_param("ii", $ward['ward_id'], $party_id);
                    $stmt->execute();
                    $candidates = $stmt->get_result();
                    ?>

                    <ul class="candidate-list">
                        <?php if($candidates->num_rows > 0): ?>
                            <?php while($candidate = $candidates->fetch_assoc()): ?>
                                <li class="candidate-item">
                                    <div class="candidate-info">
                                        <div class="candidate-name">
                                            <?php echo htmlspecialchars($candidate['name']); ?>
                                        </div>
                                        <div class="candidate-details">
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($candidate['phone']); ?>
                                            <br>
                                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($candidate['email']); ?>
                                        </div>
                                        <span class="status-badge status-<?php echo $candidate['status']; ?>">
                                            <?php echo ucfirst($candidate['status']); ?>
                                        </span>
                                    </div>
                                    <div class="action-buttons">
                                        <button class="action-btn view-btn" onclick="viewCandidate(<?php echo $candidate['application_id']; ?>)">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                        <?php if($candidate['status'] === 'pending'): ?>
                                            <button class="action-btn approve-btn" onclick="updateStatus(<?php echo $candidate['application_id']; ?>, 'approved')">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button class="action-btn reject-btn" onclick="updateStatus(<?php echo $candidate['application_id']; ?>, 'rejected')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="no-candidates">No applications for this ward</li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endwhile; ?>
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
        // View candidate details
        function viewCandidate(applicationId) {
            const modal = document.getElementById('candidateModal');
            const detailsContainer = document.getElementById('candidateDetails');
            
            // Fetch candidate details
            fetch(`get_candidate_details.php?application_id=${applicationId}`)
                .then(response => response.json())
                .then(data => {
                    detailsContainer.innerHTML = `
                        <div class="detail-row">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value">${data.name}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Age:</span>
                            <span class="detail-value">${data.age}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value">${data.phone}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Education:</span>
                            <span class="detail-value">${data.education}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Experience:</span>
                            <span class="detail-value">${data.experience} years</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Address:</span>
                            <span class="detail-value">${data.address}</span>
                        </div>
                    `;
                    modal.style.display = 'block';
                })
                .catch(error => console.error('Error:', error));
        }

        // Update application status
        function updateStatus(applicationId, status) {
            if(confirm(`Are you sure you want to ${status} this application?`)) {
                fetch('update_application_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        application_id: applicationId,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert('Error updating status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error processing request');
                });
            }
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
