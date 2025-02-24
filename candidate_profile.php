<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is a candidate
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'candidate') {
    header('Location: login.php');
    exit();
}

// Get candidate details
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT 
        u.id,
        u.name,
        u.dob,
        u.email,
        u.phone,
        u.address,
        u.aadhaar_number,
        u.ward_id,
        u.approved_by_admin,
        w.ward_name
    FROM users u
    LEFT JOIN wards w ON u.ward_id = w.ward_id
    WHERE u.id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get election participation history (as candidate)
// $stmt = $conn->prepare("
//     SELECT e.election_id, e.election_name, e.election_date, ce.status as candidate_status, 
//            COALESCE(v.vote_count, 0) as vote_count,
//            (SELECT COUNT(*) FROM votes WHERE voter_id = ? AND election_id = e.election_id) as has_voted
//     FROM candidate_elections ce
//     JOIN elections e ON ce.election_id = e.election_id
//     LEFT JOIN (
//         SELECT candidate_id, election_id, COUNT(*) as vote_count 
//         FROM votes 
//         GROUP BY candidate_id, election_id
//     ) v ON v.candidate_id = ce.candidate_id AND v.election_id = e.election_id
//     WHERE ce.candidate_id = ?
//     ORDER BY e.election_date DESC
// ");
// $stmt->bind_param("ii", $userId, $userId);
// $stmt->execute();
// $electionHistory = $stmt->get_result();

// // Get voting history (as voter)
// $stmt = $conn->prepare("
//     SELECT v.voted_at, e.election_name, c.name as candidate_name,
//            (SELECT COUNT(*) FROM candidate_elections 
//             WHERE candidate_id = ? AND election_id = e.election_id) as is_candidate
//     FROM votes v
//     JOIN elections e ON v.election_id = e.election_id
//     JOIN users c ON v.candidate_id = c.id
//     WHERE v.voter_id = ?
//     ORDER BY v.voted_at DESC
// ");
// $stmt->bind_param("ii", $userId, $userId);
// $stmt->execute();
// $votingHistory = $stmt->get_result();
// ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Profile</title>
    <style>
        /* Previous styles remain the same */
        :root {
            --primary-green: #2E7D32;
            --light-green: #4CAF50;
            --dark-green: #1B5E20;
            --primary-orange: #F57C00;
            --light-orange: #FF9800;
            --dark-orange: #E65100;
        }

        .profile-container {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .profile-section {
            flex: 1;
            min-width: 300px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.3s ease;
            border-top: 4px solid var(--primary-green);
        }

        .profile-section:hover {
            transform: translateY(-5px);
        }

        .section-header {
            background: var(--primary-green);
            padding: 1.25rem;
            color: white;
        }

        .section-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--light-orange);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .section-body {
            padding: 1.5rem;
        }

        .profile-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .profile-table th,
        .profile-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }

        .profile-table th {
            color: var(--primary-green);
            font-weight: 600;
            width: 35%;
        }

        .profile-table td {
            color: #4b5563;
        }

        .profile-table tr:hover {
            background-color: rgba(76, 175, 80, 0.05);
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .status-approved {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--light-green) 100%);
            color: white;
        }

        .status-pending {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--light-orange) 100%);
            color: white;
        }

        .status-ineligible {
            background: #dc3545;
            color: white;
        }

        .section-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .tab-button {
            padding: 0.5rem 1rem;
            border: none;
            background: none;
            color: #4b5563;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-button.active {
            color: var(--primary-green);
            border-bottom-color: var(--primary-green);
        }

        .text-muted {
            color: #6c757d;
            font-style: italic;
            text-align: center;
            padding: 2rem;
        }

        @media screen and (max-width: 768px) {
            .profile-container {
                flex-direction: column;
            }
            
            .profile-section {
                width: 100%;
            }
            
            .profile-table th,
            .profile-table td {
                padding: 0.75rem;
            }
            
            .section-header h3 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>

<div class="profile-container">
    <!-- Candidate Information -->
    <div class="profile-section">
        <div class="section-header">
            <h3>Candidate Information</h3>
        </div>
        <div class="section-body">
            <table class="profile-table">
                <!-- Previous candidate information table content remains the same -->
                <tr>
                    <th>Name:</th>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                </tr>
                <tr>
                    <th>Date of Birth:</th>
                    <td><?php echo date('d-m-Y', strtotime($user['dob'])); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                </tr>
                <tr>
                    <th>Address:</th>
                    <td><?php echo htmlspecialchars($user['address']); ?></td>
                </tr>
                <tr>
                    <th>Ward:</th>
                    <td><?php echo htmlspecialchars($user['ward_name']); ?></td>
                </tr>
                <tr>
                    <th>Aadhaar Number:</th>
                    <td><?php echo htmlspecialchars($user['aadhaar_number']); ?></td>
                </tr>
                <tr>
                    <th>Account Status:</th>
                    <td>
                        <?php if($user['approved_by_admin']): ?>
                            <span class="status-badge status-approved">Approved</span>
                        <?php else: ?>
                            <span class="status-badge status-pending">Pending Approval</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Combined Election & Voting History -->
    <div class="profile-section">
        <div class="section-header">
            <h3>Election & Voting History</h3>
        </div>
        <div class="section-body">
            <div class="section-tabs">
                <button class="tab-button active" onclick="showTab('candidate-history')">Candidate History</button>
                <button class="tab-button" onclick="showTab('voting-history')">Voting History</button>
            </div>

            <!-- Candidate History Tab -->
            <div id="candidate-history" class="history-tab">
                <!-- <?php if($electionHistory->num_rows > 0): ?> -->
                    <table class="profile-table">
                        <thead>
                            <tr>
                                <th>Election</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Votes Received</th>
                                <th>Voting Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- <?php while($election = $electionHistory->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($election['election_name']); ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($election['election_date'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $election['candidate_status'] === 'Approved' ? 'status-approved' : 'status-pending'; ?>">
                                            <?php echo htmlspecialchars($election['candidate_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($election['vote_count']); ?></td>
                                    <td>
                                        <span class="status-badge status-ineligible">Cannot Vote</span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No election participation history available.</p>
                <?php endif; ?>
            </div> -->

            <!-- Voting History Tab -->
            <div id="voting-history" class="history-tab" style="display: none;">
                <!-- <?php if($votingHistory->num_rows > 0): ?>
                    <table class="profile-table">
                        <thead>
                            <tr>
                                <th>Election</th>
                                <th>Voted For</th>
                                <th>Voted On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($vote = $votingHistory->fetch_assoc()): ?>
                                <?php if(!$vote['is_candidate']): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($vote['election_name']); ?></td>
                                        <td><?php echo htmlspecialchars($vote['candidate_name']); ?></td>
                                        <td><?php echo date('d-m-Y H:i', strtotime($vote['voted_at'])); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?> -->
                    <p class="text-muted">No voting history available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabId) {
    // Hide all tabs
    document.querySelectorAll('.history-tab').forEach(tab => {
        tab.style.display = 'none';
    });
    
    // Show selected tab
    document.getElementById(tabId).style.display = 'block';
    
    // Update tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    event.target.classList.add('active');
}
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>