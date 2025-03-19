<?php
    session_start();
    include 'db.php'; // Ensure you have a database connection file

    // Check if the user is logged in
    if (!isset($_SESSION['user_id']) ||  !in_array($_SESSION['role'], ['candidate', 'voter'])) {
        header('Location: login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Fetch user's ward ID
    $query = "SELECT ward_id FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $ward_id = $user['ward_id'];

    // Update the status of elections based on dates with explicit date formatting
    $updateStatusQuery = "UPDATE elections 
                     SET status = 
                         CASE 
                             WHEN DATE(CURDATE()) > DATE(end_date) THEN 'completed'
                             WHEN DATE(CURDATE()) BETWEEN DATE(start_date) AND DATE(end_date) THEN 'ongoing'
                             WHEN DATE(CURDATE()) < DATE(start_date) THEN 'scheduled'
                             ELSE status 
                         END 
                     WHERE status != 'completed'";

    if (!$conn->query($updateStatusQuery)) {
        error_log("Error updating election statuses: " . $conn->error);
    }

    // Fetch elections for the user's ward with proper date comparison
    $query = "SELECT e.*, 
            CASE 
                WHEN DATE(CURDATE()) BETWEEN DATE(e.start_date) AND DATE(e.end_date) THEN 'ongoing' 
                WHEN DATE(CURDATE()) > DATE(e.end_date) THEN 'completed' 
                WHEN DATE(CURDATE()) < DATE(e.start_date) THEN 'scheduled'
                ELSE e.status 
            END AS dynamic_status 
            FROM elections e
            WHERE FIND_IN_SET(?, e.ward_ids)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ward_id);
    $stmt->execute();
    $result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Updates</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f8f9fa;
    margin: 0;
    padding: 0;
}

h2 {
    text-align: center;
    color: #2E7D32;
    font-size: 2rem;
    margin-top: 20px;
}

.election-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    padding: 20px;
}

.election-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    padding: 20px;
    width: 320px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-top: 5px solid #2E7D32;
    position: relative;
    overflow: hidden;
}

.election-title {
    font-size: 1.6rem;
    text-align: center;
    font-weight: bold;
    color: #2E7D32;
    margin-bottom: 12px;
    text-transform: capitalize;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: bold;
    font-size: 0.9rem;
    color: white;
}

.status-ongoing {
    background: linear-gradient(45deg, #4CAF50, #1B5E20);
}

.status-scheduled {
    background: linear-gradient(45deg, #FF9800, #E65100);
}

.status-completed {
    background: linear-gradient(45deg, #6c757d, #495057);
}

.view-candidates-button, .vote-button {
    display: inline-block;
    padding: 12px 18px;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    text-align: center;
    transition: background 0.3s ease, transform 0.2s ease;
    border: none;
    cursor: pointer;
    width: 100%;
}

.view-candidates-button {
    background: #FF9800;
}

.view-candidates-button:hover {
    background: #E65100;
    transform: scale(1.05);
}

.vote-button {
    background: #4CAF50;
    text-align: center;
}

.vote-button:hover {
    background: #1B5E20;
    transform: scale(1.05);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    overflow-y: auto;
    padding: 20px;
}

.modal-content {
    background: #fff;
    margin: 20px auto;
    padding: 25px;
    width: 90%;
    max-width: 1200px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    position: relative;
}

.close {
    position: absolute;
    right: 25px;
    top: 15px;
    font-size: 28px;
    font-weight: bold;
    color: #666;
    cursor: pointer;
    transition: color 0.3s ease;
    z-index: 1;
}

.close:hover {
    color: #000;
}

.ward-results {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 25px;
    margin: 20px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.ward-results h3 {
    color: #2E7D32;
    text-align: center;
    font-size: 1.5em;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e0e0e0;
}

.candidates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    padding: 10px;
}

.candidate-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    overflow: hidden;
}

.candidate-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
}

.candidate-card.winner {
    border: 2px solid #4CAF50;
    background: linear-gradient(to bottom right, #ffffff, #f0f8f0);
}

.winner-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background: #4CAF50;
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: bold;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transform: rotate(5deg);
}

.candidate-details h4 {
    color: #333;
    font-size: 1.3em;
    margin-bottom: 5px;
    text-align: center;
}

.party-name {
    color: #666;
    text-align: center;
    font-style: italic;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.votes-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
}

.votes-count, .vote-percentage {
    margin: 10px 0;
}

.number, .percentage {
    font-size: 1.8em;
    font-weight: bold;
    color: #4CAF50;
    display: block;
    line-height: 1.2;
}

.winner .number, .winner .percentage {
    color: #4CAF50;
}

.label {
    color: #666;
    font-size: 0.9em;
    margin-top: 5px;
}

.vote-bar {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    margin-top: 15px;
    overflow: hidden;
}

.vote-bar-fill {
    height: 100%;
    background: linear-gradient(to right, #4CAF50, #81C784);
    border-radius: 4px;
    transition: width 1s ease-out;
}

.winner .vote-bar-fill {
    background: linear-gradient(to right, #4CAF50, #81C784);
}

.zero-votes .number,
.zero-votes .percentage {
    color: #999;
}

.zero-votes .vote-bar-fill {
    background: #ccc;
}

/* Modal title styling */
.modal-content h2 {
    color: #2E7D32;
    text-align: center;
    font-size: 2em;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 3px solid #4CAF50;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        padding: 15px;
    }
    
    .candidates-grid {
        grid-template-columns: 1fr;
    }
    
    .number, .percentage {
        font-size: 1.5em;
    }
}

.debug-info {
    margin: 20px; 
    padding: 15px; 
    background: #f8f9fa; 
    border: 1px solid #ddd;
}

/* Add this new style for no elections message */
.no-elections-message {
    text-align: center;
    padding: 40px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin: 20px auto;
    max-width: 600px;
}

.no-elections-icon {
    font-size: 48px;
    color: #6c757d;
    margin-bottom: 20px;
}

.no-elections-text {
    font-size: 1.2rem;
    color: #495057;
    margin-bottom: 10px;
}

.no-elections-subtext {
    color: #6c757d;
    font-size: 0.9rem;
}

.ward-info {
    background: #e9ecef;
    padding: 10px;
    border-radius: 8px;
    margin: 10px 0;
    display: inline-block;
}

.view-results-button {
    display: inline-block;
    padding: 12px 18px;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    text-align: center;
    transition: background 0.3s ease, transform 0.2s ease;
    border: none;
    cursor: pointer;
    width: 100%;
    background: #4CAF50;
}

.view-results-button:hover {
    background: #1B5E20;
    transform: scale(1.05);
}
</style> 
</head>
<body>
    <h2 style="text-align: center;">Elections</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="election-container">
            <?php while ($election = $result->fetch_assoc()): ?>
                <div class="election-card">
                    <div class="election-title">
                        <?= htmlspecialchars($election['Election_title']); ?>
                    </div>
                    <div class="election-description">
                        <?= htmlspecialchars($election['Description']); ?>
                    </div>
                    <div class="election-dates">
                        Start: <?= htmlspecialchars($election['start_date']); ?> | End: <?= htmlspecialchars($election['end_date']); ?>
                    </div>
                    <div>
                        <span class="status-badge status-<?= strtolower($election['dynamic_status']); ?>">
                        Status: <?= htmlspecialchars($election['dynamic_status']); ?>
                        </span>
                    </div>
                    
                    <?php if (strtolower($election['dynamic_status']) == 'ongoing'): ?>
                        <div style="margin-top: 15px;">
                            <form action="vote.php" method="GET">
                                <input type="hidden" name="election_id" value="<?= $election['election_id']; ?>">
                                <button class="vote-button">Vote Now</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if (strtolower($election['dynamic_status']) == 'completed'): ?>
                        <div style="margin-top: 15px;">
                            <button class="view-results-button" onclick="loadResults(<?= $election['election_id']; ?>)">
                                View Results
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- View Candidates Button -->
                    <div style="margin-top: 10px;">
                        <button class="view-candidates-button" data-election-id="<?= $election['election_id']; ?>">
                            View Candidates List
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-elections-message">
            <div class="no-elections-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <div class="no-elections-text">
                No Elections Available
            </div>
            <div class="ward-info">
                Your Ward: <?php 
                    $ward_query = "SELECT ward_name FROM wards WHERE ward_id = ?";
                    $ward_stmt = $conn->prepare($ward_query);
                    $ward_stmt->bind_param("i", $ward_id);
                    $ward_stmt->execute();
                    $ward_result = $ward_stmt->get_result();
                    $ward_name = $ward_result->fetch_assoc()['ward_name'];
                    echo htmlspecialchars($ward_name);
                ?>
            </div>
            <div class="no-elections-subtext">
                There are currently no scheduled, ongoing, or upcoming elections for your ward.
                <br>
                Please check back later for updates.
            </div>
        </div>
    <?php endif; ?>

   
</body>
</html>