<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'] ) || $_SESSION['role'] != 'candidate') {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Modified query to use the results table for votes_received
$query = "SELECT 
    e.election_id as election_id,
    e.Election_title as election_title,
    e.start_date,
    e.end_date,
    w.ward_name as ward_name,
    cc.application_type,
    CASE 
        WHEN cc.application_type = 'party' THEN p.party_name
        ELSE cc.independent_party_name
    END as party_name,
    r.votes_received,
    r.is_winner,
    (SELECT SUM(votes_received) FROM results WHERE election_id = e.election_id) as total_votes
FROM users u
JOIN contesting_candidates cc ON cc.id = u.id
JOIN elections e ON cc.election_id = e.election_id
JOIN wards w ON cc.ward_id = w.ward_id
LEFT JOIN parties p ON cc.party_id = p.party_id
LEFT JOIN results r ON r.contesting_id = cc.contesting_id AND r.election_id = e.election_id
WHERE u.id = ? 
    AND e.status = 'completed'
    AND e.result_status = 'published'
GROUP BY e.election_id, e.Election_title, e.start_date, w.ward_name, cc.application_type, party_name, r.votes_received, r.is_winner
ORDER BY e.start_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="election-reports-container">
    <h2>My Election Reports</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="reports-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="report-card">
                    <?php if ($row['is_winner']): ?>
                        <div class="winner-badge">Winner</div>
                    <?php endif; ?>
                    
                    <h3><?php echo htmlspecialchars($row['election_title']); ?></h3>
                    <p>Ward: <?php echo htmlspecialchars($row['ward_name']); ?></p>
                    <p>Election Date: <?php echo date('d M Y', strtotime($row['start_date'])); ?>
                    <?php if (isset($row['end_date']) && !empty($row['end_date'])): ?> 
                        - <?php echo date('d M Y', strtotime($row['end_date'])); ?>
                    <?php endif; ?></p>
                    <p>Contested As: <?php echo htmlspecialchars($row['application_type'] === 'party' ? 'Party Candidate' : 'Independent Candidate'); ?></p>
                    <p>Party: <?php echo htmlspecialchars($row['party_name']); ?></p>
                    <p>Votes Received: <?php echo number_format($row['votes_received']); ?></p>
                    <?php if ($row['total_votes'] > 0): ?>
                        <p>Vote Percentage: <?php echo round(($row['votes_received'] / $row['total_votes']) * 100, 2); ?>%</p>
                    <?php else: ?>
                        <p>Vote Percentage: 0%</p>
                    <?php endif; ?>
                    
                    <div class="report-actions">
                        <button onclick="generateElectionReport(<?php echo $row['election_id']; ?>)" class="view-report-btn">
                            View Detailed Report
                        </button>
                        <button onclick="downloadReport(<?php echo $row['election_id']; ?>)" class="download-report-btn">
                            Download Report
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-reports">
            <p>No election reports available. Reports will appear here after your contested elections are completed and results are published.</p>
        </div>
    <?php endif; ?>
</div>

<style>
.election-reports-container {
    padding: 20px;
}
.election-reports-container h2{
    text-align: center;
    font-size: 2em;
    font-weight: bold;
    color: var(--primary-green);
    margin-bottom: 20px;
}   

.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.report-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    border-top: 4px solid var(--primary-green);
    position: relative;
}

.report-card:hover {
    transform: translateY(-5px);
}

.report-card h3 {
    color: var(--primary-green);
    margin-bottom: 15px;
    font-size: 1.2em;
}

.report-card p {
    margin: 8px 0;
    color: #444;
    font-size: 0.95em;
    line-height: 1.4;
}

.report-card p:nth-child(2) {
    color: var(--primary-orange);
    font-weight: 500;
}

.report-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.view-report-btn, .download-report-btn {
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    font-weight: 500;
    flex: 1;
    text-align: center;
}

.view-report-btn {
    background: var(--primary-green);
    color: white;
}

.download-report-btn {
    background: var(--primary-orange);
    color: white;
}

.view-report-btn:hover, .download-report-btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.no-reports {
    text-align: center;
    padding: 40px;
    background: #f5f5f5;
    border-radius: 10px;
    margin-top: 20px;
    color: #666;
}

/* Winner badge styling */
.winner-badge {
    background: var(--primary-orange);
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8em;
    display: inline-block;
    position: absolute;
    top: -10px;
    right: 20px;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style> 