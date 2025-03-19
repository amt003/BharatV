<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user's ward
$user_id = $_SESSION['user_id'];
$ward_query = "SELECT ward_id FROM users WHERE id = ?";
$stmt = $conn->prepare($ward_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_ward = $result->fetch_assoc()['ward_id'];

// Get completed elections with published results for user's ward only
$query = "SELECT DISTINCT 
            e.election_id,
            e.Election_title,
            e.start_date,
            e.end_date,
            w.ward_name
          FROM elections e
          JOIN wards w ON w.ward_id = ?
          JOIN results r ON r.election_id = e.election_id
          WHERE e.end_date < NOW() 
          AND FIND_IN_SET(?, e.ward_ids)
          ORDER BY e.end_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_ward, $user_ward);
$stmt->execute();
$elections = $stmt->get_result();
?>

<div class="results-container">
    <h2>Election Results</h2>
    <?php if ($elections->num_rows > 0): ?>
        <?php while ($election = $elections->fetch_assoc()): ?>
            <div class="election-card">
                <div class="election-header">
                    <h3><?php echo htmlspecialchars($election['Election_title']); ?></h3>
                    <p class="ward-name">Ward: <?php echo htmlspecialchars($election['ward_name']); ?></p>
                    <p class="date-range">
                        <?php 
                        $start = date('M d, Y', strtotime($election['start_date']));
                        $end = date('M d, Y', strtotime($election['end_date']));
                        echo "Duration: $start - $end"; 
                        ?>
                    </p>
                </div>
                <button class="view-results-btn" 
                        onclick="loadResults(<?php echo $election['election_id']; ?>)">
                    View Results
                </button>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-results">
            <p>No published results available for your ward at this time.</p>
            <p class="ward-info">Your Ward: <?php 
                $ward_name_query = "SELECT ward_name FROM wards WHERE ward_id = ?";
                $ward_stmt = $conn->prepare($ward_name_query);
                $ward_stmt->bind_param("i", $user_ward);
                $ward_stmt->execute();
                $ward_result = $ward_stmt->get_result();
                echo htmlspecialchars($ward_result->fetch_assoc()['ward_name']);
            ?></p>
        </div>
    <?php endif; ?>
</div>

<style>
.results-container {
    padding: 20px;
    max-width: 800px;
    margin: 0 auto;
}

.results-container h2 {
    text-align: center;
    font-size: 2em;
    font-weight: bold;
    color: var(--primary-green);
    margin-bottom: 20px;
}

.election-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid var(--primary-green);
}

.election-header h3 {
    color: var(--dark-green);
    margin-bottom: 10px;
}

.ward-name {
    color: var(--primary-orange);
    font-weight: 500;
    margin-bottom: 5px;
}

.date-range {
    color: #666;
    font-size: 0.9em;
    margin-bottom: 15px;
}

.view-results-btn {
    background: var(--primary-green);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.view-results-btn:hover {
    background: var(--dark-green);
    transform: translateY(-2px);
}

.no-results {
    text-align: center;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 8px;
    color: #666;
}

/* Enhanced Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.7);
    overflow-y: auto;
    padding: 20px;
    box-sizing: border-box;
    backdrop-filter: blur(5px);
}

.modal-content {
    background: #fff;
    margin: 20px auto;
    padding: 30px;
    width: 95%;
    max-width: 1200px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    position: relative;
    animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-content h2 {
    color: #1B5E20;
    text-align: center;
    font-size: 2.2em;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 3px solid #4CAF50;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.ward-results {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 25px;
    margin: 25px 0;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
}

.ward-results h3 {
    color: #2E7D32;
    text-align: center;
    font-size: 1.8em;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ward-total-votes {
    text-align: center;
    color: #555;
    font-size: 1.2em;
    margin: 15px 0 25px 0;
    padding: 12px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 1px solid #e0e0e0;
}

.candidates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
    padding: 15px;
}

.candidate-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    border: 1px solid #e0e0e0;
}

.candidate-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.candidate-card.winner {
    border: 2px solid #4CAF50;
    background: linear-gradient(135deg, #ffffff 0%, #f0f8f0 100%);
}

.winner-badge {
    position: absolute;
    top: -12px;
    right: -12px;
    background: linear-gradient(45deg, #2E7D32, #4CAF50);
    color: white;
    padding: 12px 20px;
    border-radius: 25px;
    font-size: 1em;
    font-weight: bold;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
    transform: rotate(5deg);
    z-index: 1;
}

.candidate-details h4 {
    color: #333;
    font-size: 1.4em;
    margin-bottom: 8px;
    text-align: center;
    font-weight: 600;
}

.party-name {
    color: #666;
    text-align: center;
    font-style: italic;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
    font-size: 1.1em;
}

.votes-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
}

.votes-count, .vote-percentage {
    margin: 12px 0;
}

.number, .percentage {
    font-size: 2em;
    font-weight: bold;
    color: #2E7D32;
    display: block;
    line-height: 1.3;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.winner .number, .winner .percentage {
    color: #2E7D32;
}

.label {
    color: #666;
    font-size: 0.95em;
    margin-top: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.vote-bar {
    height: 10px;
    background: #e9ecef;
    border-radius: 5px;
    margin-top: 20px;
    overflow: hidden;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

.vote-bar-fill {
    height: 100%;
    background: linear-gradient(to right, #2E7D32, #4CAF50);
    border-radius: 5px;
    transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.winner .vote-bar-fill {
    background: linear-gradient(to right, #2E7D32, #4CAF50);
}

.zero-votes .number,
.zero-votes .percentage {
    color: #999;
}

.zero-votes .vote-bar-fill {
    background: #ccc;
}

.close {
    position: absolute;
    right: 25px;
    top: 20px;
    font-size: 32px;
    font-weight: bold;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #f8f9fa;
}

.close:hover {
    color: #333;
    background: #e9ecef;
    transform: rotate(90deg);
}

/* Responsive Design */
@media (max-width: 768px) {
    .modal-content {
        padding: 20px;
        margin: 10px auto;
    }

    .candidates-grid {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 10px;
    }

    .candidate-card {
        padding: 20px;
    }

    .modal-content h2 {
        font-size: 1.8em;
    }

    .ward-results h3 {
        font-size: 1.5em;
    }

    .number, .percentage {
        font-size: 1.6em;
    }
}
</style>


