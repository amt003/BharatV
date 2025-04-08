<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is a party admin
if (!isset($_SESSION['party_id']) and !isset($_SESSION['party_admin'])) {
    header("Location: login.php");
    exit();
}

// Get party details
$party_id = $_SESSION['party_id'];
$stmt = $conn->prepare("SELECT * FROM parties WHERE party_id = ?");
$stmt->bind_param("i", $party_id);
$stmt->execute();
$party_result = $stmt->get_result();
$party_data = $party_result->fetch_assoc();

//get all active elections
$elections_query = "SELECT election_id, Election_title, start_date, end_date, status, ward_ids 
                   FROM elections 
                   WHERE status = 'scheduled' 
                   ORDER BY start_date DESC";
$elections_result = $conn->query($elections_query);

// Get published election results
$publishedResultsQuery = "SELECT e.election_id, e.Election_title, e.start_date, e.end_date, e.status, e.ward_ids 
                         FROM elections e
                         WHERE e.status = 'completed' AND e.result_status = 'published'
                         ORDER BY e.end_date DESC";
$publishedResults = $conn->query($publishedResultsQuery);

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
           
            color: #666;
        }

        .close-btn:hover,
        .close-btn:focus {
            color: black; /* Black on hover */
            text-decoration: none; 
            cursor: pointer; 
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
        .election-section {
            margin-bottom: 40px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .election-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .election-title {
            font-size: 22px;
            color: #333;
        }

        .election-meta {
            display: flex;
            gap: 20px;
            color: #666;
            font-size: 14px;
        }

        .election-date {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .election-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-scheduled {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-ongoing {
            background: #e8f5e9;
            color: #2e7d32;
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

        /* Results Section Styles */
        .results-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .results-title {
            font-size: 22px;
            color: #333;
            margin: 0;
        }

        .results-meta {
            color: #666;
        }

        .results-dates {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .wards-results-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }

        .ward-results-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .ward-name {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
        }

        .results-table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }

        .results-table th,
        .results-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .results-table th {
            background-color: #f0f0f0;
            font-weight: 600;
            color: #333;
        }

        .winner-row {
            background-color: rgba(40, 167, 69, 0.1);
        }

        .party-candidate-row {
            background-color: rgba(76, 175, 80, 0.2);
            font-weight: bold;
        }

        .winner-badge {
            display: inline-block;
            padding: 4px 8px;
            background-color: #28a745;
            color: white;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .party-symbol-small {
            width: 24px;
            height: 24px;
            object-fit: contain;
            vertical-align: middle;
            margin-left: 5px;
        }

        .no-results {
            padding: 15px;
            text-align: center;
            color: #666;
            font-style: italic;
        }

        .no-results-message {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }

        .no-results-message i {
            font-size: 48px;
            color: #adb5bd;
            margin-bottom: 20px;
        }

        .no-results-message p {
            color: #6c757d;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .results-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .results-title {
                margin-bottom: 10px;
            }
            
            .wards-results-container {
                grid-template-columns: 1fr;
            }
            
            .ward-results-card {
                padding: 15px;
            }
            
            .results-table th,
            .results-table td {
                padding: 8px;
            }
        }

        /* Add these new styles for rejection functionality */
        .rejection-reason {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #dc3545;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        /* Modal styles if not already defined */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 80%;
            max-width: 600px;
        }

        .close-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-btn:hover,
        .close-btn:focus {
            color: black;
            text-decoration: none;
        }

        .no-applications-message {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
            border: 2px dashed #dee2e6;
            list-style: none;
        }

        .no-applications-message i {
            font-size: 36px;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .no-applications-message h4 {
            color: #495057;
            margin-bottom: 10px;
            font-size: 1.2em;
        }

        .no-applications-message p {
            color: #6c757d;
            font-size: 1.1em;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="party-info">
                
                <div class="party-details">
                <?php if($party_data['party_symbol']): ?>
                    <img src="./uploads/party_symbols/<?php echo htmlspecialchars($party_data['party_symbol']); ?>" alt="Party symbol Photo" style="width: 100px; height: 100px; border-radius: 50%;">
                <?php endif; ?>
                    <h1><?php echo htmlspecialchars($party_data['party_name']); ?></h1>
                    <p>Party Dashboard</p>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <?php while($election = $elections_result->fetch_assoc()): ?>
            <div class="election-section">
                <div class="election-header">
            <h2>Requests</h2>
            <div class="election-title">
              <strong>
                  <?php echo htmlspecialchars($election['Election_title']); ?>
              </strong>
            </div>
            <div class="election-meta">
                <div class="election-date">
                    <i class="fas fa-calendar"></i>
                    <?php echo date('M d, Y', strtotime($election['start_date'])); ?> - 
                    <?php echo date('M d, Y', strtotime($election['end_date'])); ?>
                </div>
                <div class="election-status status-<?php echo $election['status']; ?>">
                    <?php echo ucfirst($election['status']); ?>
                </div>
            </div>
        </div>

        <div class="wards-container">
            <?php
            if (isset($election['ward_ids']) && !empty($election['ward_ids'])) {
                $ward_ids = array_map('intval', explode(',', $election['ward_ids']));
                $ward_ids = array_filter($ward_ids);
                
                if (!empty($ward_ids)) {
                    $ward_ids_string = implode(',', $ward_ids);
                    $wards_query = "SELECT * FROM wards WHERE ward_id IN ($ward_ids_string) ORDER BY ward_id";
                    $wards_result = $conn->query($wards_query);
                    
                    if ($wards_result && $wards_result->num_rows > 0) {
                        while($ward = $wards_result->fetch_assoc()) {
                            // Get candidates for this ward
                            $stmt = $conn->prepare("
                                SELECT c.*, u.name, u.phone, u.email 
                                FROM candidate_applications c 
                                JOIN users u ON c.id = u.id  
                                WHERE c.ward_id = ? 
                                AND c.party_id = ? 
                                AND c.election_id = ?
                                ORDER BY c.created_at DESC
                            ");
                            
                            $stmt->bind_param("iii", $ward['ward_id'], $party_id, $election['election_id']);
                            $stmt->execute();
                            $candidates = $stmt->get_result();
                            ?>
                            
                            <div class="ward-card">
                                <div class="ward-header">
                                    <h2>Ward <?php echo $ward['ward_id']; ?> - <?php echo htmlspecialchars($ward['ward_name']); ?></h2>
                                </div>

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
                                                    <span class="status-badge status-<?php echo $candidate['application_party_approval']; ?>">
                                                        <?php echo ucfirst($candidate['application_party_approval']); ?>
                                                    </span>
                                                </div>
                                                <div class="action-buttons">
                                                    <button class="action-btn view-btn" onclick="viewCandidate(<?php echo $candidate['application_id']; ?>)">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </button>
                                                    <?php if($candidate['application_party_approval'] === 'pending'): ?>
                                                        <button class="action-btn approve-btn" onclick="updateStatus(<?php echo $candidate['application_id']; ?>, 'approved')">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                        <button class="action-btn reject-btn" onclick="updateStatus(<?php echo $candidate['application_id']; ?>, 'reject')">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </li>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <li class="no-applications-message">
                                            <i class="fas fa-file-alt"></i>
                                            <h4>No Applications Received</h4>
                                            <p>No candidate applications have been submitted for this ward yet.</p>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<div class='no-candidates'>No wards found for this election</div>";
                    }
                } else {
                    echo "<div class='no-candidates'>Invalid ward IDs for this election</div>";
                }
            } else {
                echo "<div class='no-candidates'>No wards assigned to this election</div>";
            }
            ?>
        </div>
    </div>
<?php endwhile; ?>

<!-- Published Election Results Section -->
<div class="election-section">
    <h2 style="text-align:center;margin-bottom:20px;color:#333;">Published Election Results</h2>
    
    <?php if ($publishedResults && $publishedResults->num_rows > 0): ?>
        <?php while ($election = $publishedResults->fetch_assoc()): ?>
            <div class="results-card">
                <div class="results-header">
                    <h3 class="results-title"><?php echo htmlspecialchars($election['Election_title']); ?></h3>
                    <div class="results-meta">
                        <div class="results-dates">
                            <i class="fas fa-calendar"></i>
                            <?php echo date('M d, Y', strtotime($election['start_date'])); ?> - 
                            <?php echo date('M d, Y', strtotime($election['end_date'])); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Results by ward -->
                <div class="wards-results-container">
                    <?php
                    // Get wards for this election
                    if (isset($election['ward_ids']) && !empty($election['ward_ids'])) {
                        $ward_ids = array_map('intval', explode(',', $election['ward_ids']));
                        $ward_ids = array_filter($ward_ids);
                        
                        if (!empty($ward_ids)) {
                            $ward_ids_string = implode(',', $ward_ids);
                            $wards_query = "SELECT * FROM wards WHERE ward_id IN ($ward_ids_string) ORDER BY ward_id";
                            $wards_result = $conn->query($wards_query);
                            
                            if ($wards_result && $wards_result->num_rows > 0) {
                                while($ward = $wards_result->fetch_assoc()) {
                                    // Get results for this ward
                                    $resultsQuery = $conn->prepare("
                                        SELECT cc.id, u.name, p.party_name, p.party_symbol, 
                                               r.votes_received, r.is_winner,
                                               cc.application_type, cc.independent_party_name, cc.independent_party_symbol,
                                               cc.party_id, cc.contesting_id
                                        FROM contesting_candidates cc
                                        JOIN users u ON cc.id = u.id
                                        LEFT JOIN parties p ON cc.party_id = p.party_id
                                        JOIN results r ON cc.contesting_id = r.contesting_id
                                        WHERE r.election_id = ? AND r.ward_id = ?
                                        ORDER BY r.votes_received DESC
                                    ");
                                    
                                    $resultsQuery->bind_param("ii", $election['election_id'], $ward['ward_id']);
                                    $resultsQuery->execute();
                                    $resultsData = $resultsQuery->get_result();
                                    
                                    // Calculate total votes
                                    $totalVotesQuery = $conn->prepare("
                                        SELECT SUM(votes_received) as total_votes
                                        FROM results
                                        WHERE election_id = ? AND ward_id = ?
                                    ");
                                    $totalVotesQuery->bind_param("ii", $election['election_id'], $ward['ward_id']);
                                    $totalVotesQuery->execute();
                                    $totalVotesData = $totalVotesQuery->get_result()->fetch_assoc();
                                    $totalVotes = $totalVotesData['total_votes'] ?? 0;
                                    
                                    // Check if this party has any candidates in this ward
                                    $hasPartyCandidates = false;
                                    $tempResults = [];
                                    if ($resultsData && $resultsData->num_rows > 0) {
                                        while ($result = $resultsData->fetch_assoc()) {
                                            $tempResults[] = $result;
                                            if (($result['party_id'] == $party_id) || 
                                                ($result['application_type'] === 'independent' && $party_id == 1)) {
                                                $hasPartyCandidates = true;
                                            }
                                        }
                                    }
                                    
                                    ?>
                                    <div class="ward-results-card">
                                        <h4 class="ward-name"><?php echo htmlspecialchars($ward['ward_name']); ?> Results</h4>
                                        
                                        <?php if (!empty($tempResults)): ?>
                                            <div class="results-table-responsive">
                                                <table class="results-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Candidate</th>
                                                            <th>Party</th>
                                                            <th>Votes</th>
                                                            <th>Percentage</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($tempResults as $result): 
                                                            $votePercentage = $totalVotes > 0 ? ($result['votes_received'] / $totalVotes) * 100 : 0;
                                                            
                                                            // Highlight this party's candidates
                                                            $isPartyCandidate = ($result['party_id'] == $party_id) || 
                                                                ($result['application_type'] === 'independent' && $party_id == 1);
                                                            
                                                            $rowClass = [];
                                                            if ($result['is_winner']) $rowClass[] = 'winner-row';
                                                            if ($isPartyCandidate) $rowClass[] = 'party-candidate-row';
                                                        ?>
                                                            <tr class="<?php echo implode(' ', $rowClass); ?>">
                                                                <td><?php echo htmlspecialchars($result['name']); ?></td>
                                                                <td>
                                                                    <?php if ($result['application_type'] === 'independent'): ?>
                                                                        <?php echo htmlspecialchars($result['independent_party_name']); ?> 
                                                                        <?php if (!empty($result['independent_party_symbol'])): ?>
                                                                            <img src="uploads/party_symbols/<?php echo htmlspecialchars($result['independent_party_symbol']); ?>" 
                                                                                alt="Symbol" class="party-symbol-small">
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        <?php echo htmlspecialchars($result['party_name']); ?>
                                                                        <?php if (!empty($result['party_symbol'])): ?>
                                                                            <img src="uploads/party_symbols/<?php echo htmlspecialchars($result['party_symbol']); ?>" 
                                                                                alt="Symbol" class="party-symbol-small">
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?php echo number_format($result['votes_received']); ?></td>
                                                                <td><?php echo number_format($votePercentage, 2); ?>%</td>
                                                                <td>
                                                                    <?php if ($result['is_winner']): ?>
                                                                        <span class="winner-badge">Winner</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <p class="no-results">No results available for this ward</p>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                }
                            } else {
                                echo "<p class='no-results'>No wards found for this election</p>";
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-results-message">
            <i class="fas fa-info-circle"></i>
            <p>No published election results are available at this time.</p>
        </div>
    <?php endif; ?>
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

    <!-- Add this modal for rejection reason -->
    <div id="rejectionModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeRejectionModal">&times;</span>
            <h2>Rejection Reason</h2>
            <form id="rejectionForm">
                <input type="hidden" id="rejection_application_id" name="application_id">
                
                <div class="form-group">
                    <label for="rejection_reason">Please provide a reason for rejecting this application:</label>
                    <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="4" required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="action-button" id="confirmRejection">
                        <i class="fas fa-times-circle"></i> Confirm Rejection
                    </button>
                    <button type="button" class="action-button" id="cancelRejection">
                        Cancel
                    </button>
                </div>
            </form>
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
    fetch(`get_candidate_details.php?application_id=${applicationId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
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
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${data.email}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Education:</span>
                        <span class="detail-value">${data.education}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Political Experience:</span>
                        <span class="detail-value">${data.experience} years</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value">${data.address}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Profile Photo:</span>
                        <img src="${data.profile_photo}" alt="Profile Photo" style="width: 100px; height: 100px; border-radius: 10px;">
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Aadhar Proof:</span>
                        <a href="${data.aadhar_proof}" target="_blank">Click to view Aadhar Proof</a>
                    </div>
                `;
            } else {
                detailsContainer.innerHTML = '<p class="error">Failed to load candidate details.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            detailsContainer.innerHTML = '<p class="error">Failed to load candidate details.</p>';
        });
}

 // Update application status
 function updateStatus(applicationId, status) {
    if (status === 'reject') {
        // Show rejection modal
        const rejectionModal = document.getElementById('rejectionModal');
        document.getElementById('rejection_application_id').value = applicationId;
        rejectionModal.style.display = 'block';
        return;
    }
    
    // For approval, continue with the existing flow
    if(confirm(`Are you sure you want to ${status} this application?`)) {
        processStatusUpdate(applicationId, status);
    }
}

// New function to process the status update
function processStatusUpdate(applicationId, status, rejectionReason = null) {
    const requestData = {
        application_id: applicationId,
        status: status
    };
    
    // Add rejection reason if provided
    if (rejectionReason) {
        requestData.rejection_reason = rejectionReason;
    }
    
    fetch('update_application_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert('Error updating status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing request');
    });
}

// Add event listeners for the rejection modal
document.addEventListener('DOMContentLoaded', function() {
    const rejectionModal = document.getElementById('rejectionModal');
    const closeRejectionModal = document.getElementById('closeRejectionModal');
    const cancelRejection = document.getElementById('cancelRejection');
    const confirmRejection = document.getElementById('confirmRejection');
    
    // Close modal when clicking the close button
    closeRejectionModal.addEventListener('click', function() {
        rejectionModal.style.display = 'none';
    });
    
    // Close modal when clicking the cancel button
    cancelRejection.addEventListener('click', function() {
        rejectionModal.style.display = 'none';
    });
    
    // Handle confirmation of rejection
    confirmRejection.addEventListener('click', function() {
        const applicationId = document.getElementById('rejection_application_id').value;
        const rejectionReason = document.getElementById('rejection_reason').value;
        
        if (!rejectionReason.trim()) {
            alert('Please provide a reason for rejection');
            return;
        }
        
        processStatusUpdate(applicationId, 'reject', rejectionReason);
        rejectionModal.style.display = 'none';
    });
    
    // Close modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target === rejectionModal) {
            rejectionModal.style.display = 'none';
        }
        
        // Keep the existing modal close functionality
        const candidateModal = document.getElementById('candidateModal');
        if (event.target == candidateModal) {
            candidateModal.style.display = 'none';
        }
    };
});
    </script>
</body>
</html>
