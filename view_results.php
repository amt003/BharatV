<?php
        session_start();
       include 'db.php';
        
  

       if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: loginadmin.php");
        exit();
    }

    // Handle AJAX requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];
        $election_id = intval($_POST['election_id'] ?? 0);

        switch ($action) {
            case 'publish_results':
                // First, check if results already exist
                $check_query = "SELECT COUNT(*) as count FROM results WHERE election_id = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("i", $election_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                $count = $result->fetch_assoc()['count'];

                if ($count > 0) {
                    // Results already exist
                    echo json_encode(['success' => false, 'message' => 'Results have already been published for this election']);
                    exit();
                }

                // Begin transaction
                $conn->begin_transaction();

                try {
                    // Calculate and insert results
                    $calculate_results = "INSERT INTO results (election_id, ward_id, contesting_id, votes_received, is_winner)
                        SELECT 
                            v.election_id,
                            v.ward_id,
                            v.contesting_id,
                            COUNT(*) as votes_received,
                            CASE 
                                WHEN COUNT(*) = (
                                    SELECT COUNT(*) as vote_count
                                    FROM votes v2 
                                    WHERE v2.election_id = v.election_id 
                                    AND v2.ward_id = v.ward_id
                                    GROUP BY v2.contesting_id
                                    ORDER BY vote_count DESC
                                    LIMIT 1
                                ) THEN TRUE
                                ELSE FALSE
                            END as is_winner
                        FROM votes v
                        WHERE v.election_id = ?
                        GROUP BY v.election_id, v.ward_id, v.contesting_id";
                        
                    $stmt = $conn->prepare($calculate_results);
                    $stmt->bind_param("i", $election_id);
                    
                    if ($stmt->execute()) {
                        // Update election result_status to published
                        $update_status = "UPDATE elections SET result_status = 'published' WHERE id = ?";
                        $status_stmt = $conn->prepare($update_status);
                        $status_stmt->bind_param("i", $election_id);
                        
                        if ($status_stmt->execute()) {
                            $conn->commit();
                            echo json_encode(['success' => true]);
                        } else {
                            throw new Exception("Failed to update election status");
                        }
                    } else {
                        throw new Exception($conn->error);
                    }
                } catch (Exception $e) {
                    $conn->rollback();
                    echo json_encode(['success' => false, 'message' => 'Error publishing results: ' . $e->getMessage()]);
                }
                exit();

            case 'get_results':
                try {
                    // First check if results exist
                    $check_query = "SELECT COUNT(*) as count FROM results WHERE election_id = ?";
                    $check_stmt = $conn->prepare($check_query);
                    $check_stmt->bind_param("i", $election_id);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    $count = $check_result->fetch_assoc()['count'];

                    if ($count == 0) {
                        echo json_encode(['error' => true, 'message' => 'No results found for this election']);
                        exit();
                    }

                    // Updated query to include all candidates including independent ones
                    $results_query = "SELECT 
                        w.ward_name,
                        u.id,
                        u.name,
                        COALESCE(p.party_name, 'Independent') as party_name,
                        COALESCE(p.party_symbol, 'independent.png') as party_symbol,
                        COALESCE(r.votes_received, 0) as votes_received,
                        COALESCE(r.is_winner, 0) as is_winner,
                        e.Election_title,
                        CASE WHEN p.party_id IS NULL THEN 1 ELSE 0 END as is_independent
                        FROM contesting_candidates cc
                        JOIN users u ON cc.id = u.id
                        JOIN wards w ON cc.ward_id = w.ward_id
                        JOIN elections e ON cc.election_id = e.election_id
                        LEFT JOIN parties p ON cc.party_id = p.party_id
                        LEFT JOIN results r ON r.contesting_id = cc.contesting_id
                        WHERE cc.election_id = ?
                        ORDER BY w.ward_name, COALESCE(r.votes_received, 0) DESC";
                        
                    $stmt = $conn->prepare($results_query);
                    if (!$stmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
                    
                    $stmt->bind_param("i", $election_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Execute failed: " . $stmt->error);
                    }
                    
                    $results = $stmt->get_result();
                    
                    $results_data = [];
                    while ($row = $results->fetch_assoc()) {
                        $results_data[] = $row;
                    }
                    
                    if (empty($results_data)) {
                        echo json_encode(['error' => true, 'message' => 'No candidates found for this election']);
                    } else {
                        echo json_encode($results_data);
                    }
                } catch (Exception $e) {
                    echo json_encode(['error' => true, 'message' => 'Error retrieving results: ' . $e->getMessage()]);
                }
                exit();
        }
    }
?>

<!-- Completed Elections Section -->
<div class="completed-elections">
    <h2>Completed Elections</h2>
    
    <?php
    // Query to get completed elections
    $completed_query = "SELECT e.*, 
                        (SELECT COUNT(*) FROM votes WHERE election_id = e.election_id) as total_votes,
                        (SELECT COUNT(*) FROM results WHERE election_id = e.election_id) as results_published,
                        e.result_status
                        FROM elections e 
                        WHERE CURDATE() > e.end_date
                        ORDER BY e.end_date DESC";
    
    $completed_result = $conn->query($completed_query);
    
    if ($completed_result && $completed_result->num_rows > 0) {
        echo '<div class="elections-grid">';
        
        while ($election = $completed_result->fetch_assoc()) {
            echo '<div class="election-card completed">';
            echo '<div class="election-title">' . htmlspecialchars($election['Election_title']) . '</div>';
            echo '<div class="election-dates">';
            echo '<span class="date-label">Started:</span> ' . date('M d, Y', strtotime($election['start_date'])) . '<br>';
            echo '<span class="date-label">Ended:</span> ' . date('M d, Y', strtotime($election['end_date'])) . '';
            echo '</div>';
            echo '<div class="election-stats">';
            echo '<div class="stat"><i class="fas fa-vote-yea"></i> ' . $election['total_votes'] . ' votes</div>';
            echo '<div class="stat"><i class="fas fa-flag-checkered"></i> Completed</div>';
            echo '</div>';
            
            // Check if results have been published
            if ($election['result_status'] === 'published') {
                echo '<div class="results-status published"><i class="fas fa-check-circle"></i> Results Published</div>';
                echo '<button data-election-id="' . $election['election_id'] . '" class="btn btn-primary view-results">View Results</button>';
            } else {
                echo '<div class="results-status unpublished"><i class="fas fa-info-circle"></i> Results Not Published</div>';
                echo '<button data-election-id="' . $election['election_id'] . '" class="btn btn-secondary view-results">View Results</button>';
                echo '<button data-election-id="' . $election['election_id'] . '" class="btn btn-success publish-results">Publish Results</button>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
    } else {
        echo '<div class="no-elections-message">';
        echo '<i class="fas fa-info-circle"></i>';
        echo '<p>There are no completed elections at this time.</p>';
        echo '</div>';
    }
    ?>
</div>

<!-- Results Modal -->
<div id="resultsModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle"></h2>
        <div id="resultsContent"></div>
    </div>
</div>

<script>
function publishResults(electionId) {
    $.ajax({
        url: 'view_results.php',
        type: 'POST',
        data: {
            action: 'publish_results',
            election_id: electionId
        },
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    alert('Results published successfully!');
                    // Reload the page to show updated status
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (e) {
                alert('Error publishing results');
            }
        },
        error: function() {
            alert('Error publishing results');
        }
    });
}

function viewResults(electionId) {
    $.ajax({
        url: 'view_results.php',
        type: 'POST',
        data: {
            action: 'get_results',
            election_id: electionId
        },
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if (data.error) {
                    alert(data.message || 'Error loading results');
                    return;
                }
                if (Array.isArray(data) && data.length > 0) {
                    displayResults(data);
                } else {
                    alert('No results available for this election.');
                }
            } catch (e) {
                console.error('Error parsing results:', e);
                alert('Error loading results: ' + e.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            alert('Error loading results. Please try again.');
        }
    });
}

function displayResults(results) {
    const modal = document.getElementById('resultsModal');
    const modalTitle = document.getElementById('modalTitle');
    const resultsContent = document.getElementById('resultsContent');
    
    if (!results || !results.length) {
        alert('No results data available');
        return;
    }
    
    // Calculate total votes for the entire election
    const totalElectionVotes = results.reduce((sum, result) => sum + parseInt(result.votes_received), 0);
    
    // Group results by ward
    const wardResults = {};
    results.forEach(result => {
        if (!wardResults[result.ward_name]) {
            wardResults[result.ward_name] = [];
        }
        wardResults[result.ward_name].push(result);
    });
    
    modalTitle.textContent = results[0].Election_title + ' - Results';
    
    let html = '';
    for (const ward in wardResults) {
        // Find the highest votes in this ward
        const maxVotes = Math.max(...wardResults[ward].map(r => parseInt(r.votes_received)));
        
        html += `<div class="ward-results">
            <h3>${ward}</h3>
            <div class="candidates-grid">`;
        
        wardResults[ward].forEach(result => {
            const isWinner = parseInt(result.votes_received) === maxVotes && parseInt(result.votes_received) > 0;
            const winnerClass = isWinner ? 'winner' : '';
            const votePercentage = totalElectionVotes > 0 
                ? ((result.votes_received / totalElectionVotes) * 100).toFixed(2) 
                : 0;
            
            html += `
                <div class="candidate-card ${winnerClass}">
                    ${isWinner ? '<div class="winner-badge">Winner</div>' : ''}
                    <div class="candidate-details">
                        <h4>${result.name}</h4>
                        <p class="party-name">${result.is_independent === 1 ? 'Independent Candidate' : result.party_name}</p>
                        <div class="votes-info ${result.votes_received === '0' ? 'zero-votes' : ''}">
                            <div class="votes-count">
                                <span class="number">${result.votes_received}</span>
                                <span class="label">Votes Received</span>
                            </div>
                            <div class="vote-percentage">
                                <span class="percentage">${votePercentage}%</span>
                                <span class="label">of Total Votes</span>
                            </div>
                            <div class="vote-bar">
                                <div class="vote-bar-fill" style="width: ${votePercentage}%"></div>
                            </div>
                        </div>
                    </div>
                </div>`;
        });
        
        html += '</div></div>';
    }
    
    resultsContent.innerHTML = html;
    modal.style.display = "block";
}

// Change the document ready handler to use event delegation
$(document).ready(function() {
    // Use event delegation for dynamically loaded content
    $(document).on('click', '.publish-results', function() {
        const electionId = $(this).data('election-id');
        publishResults(electionId);
    });

    $(document).on('click', '.view-results', function() {
        const electionId = $(this).data('election-id');
        viewResults(electionId);
    });

    // Modal close handlers
    $(document).on('click', '.close', function() {
        $('#resultsModal').hide();
    });

    $(document).on('click', '#resultsModal', function(event) {
        if ($(event.target).is('#resultsModal')) {
            $('#resultsModal').hide();
        }
    });
});
</script>

<style>
/* Additional styling for completed elections */
.completed-elections {
    margin-top: 30px;
}

.completed-elections h2 {
    margin-bottom: 20px;
    color: #333;
    font-size: 24px;
}

.election-card.completed {
    border-left: 4px solid rgb(28, 161, 7);
    border-right: 4px solid rgb(28, 161, 7);
  

}

.results-status {
    padding: 8px;
    border-radius: 4px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.results-status.published {
    background-color: #E8F5E9;
    color: #2E7D32;
}

.results-status.unpublished {
    background-color: #FFF8E1;
    color: #F57F17;
}

.btn {
    display: inline-block;
    padding: 8px 15px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
    border: none;
    margin-right: 10px;
    margin-bottom: 5px;
}

.btn-primary {
    background-color: #4CAF50;
    color: white;
}

.btn-primary:hover {
    background-color: #45a049;
}

.btn-secondary {
    background-color: #607D8B;
    color: white;
}

.btn-secondary:hover {
    background-color: #546E7A;
}

.btn-success {
    background-color: #3949AB;
    color: white;
}

.btn-success:hover {
    background-color: #303F9F;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 1000px;
    border-radius: 8px;
    max-height: 80vh;
    overflow-y: auto;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: black;
}

/* Results display styles */
.ward-results {
    margin-bottom: 30px;
}

.ward-results h3 {
    color:rgb(31, 160, 74); 
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 1.5em;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.candidates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.candidate-card {
    position: relative;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.candidate-details {
    text-align: center;
}

.candidate-details h4 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 1.2em;
}

.party-name {
    color: #666;
    font-size: 0.9em;
    margin: 0 0 15px 0;
    font-style: italic;
}

.votes-info {
    margin-top: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.votes-count, .vote-percentage {
    text-align: center;
    margin: 5px 0;
}

.number, .percentage {
    font-size: 1.5em;
    font-weight: bold;
    color : #2E7D32;
    display: block;
}

.label {
    color: #666;
    font-size: 0.9em;
}

.vote-bar {
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    margin-top: 10px;
    overflow: hidden;
}

.vote-bar-fill {
    height: 100%;
    background : #2E7D32;
    border-radius: 3px;
    transition: width 0.3s ease;
}

.winner .vote-bar-fill {
    background: #4CAF50;
}

.zero-votes .vote-bar-fill {
    background: #ccc;
}

.zero-votes .number,
.zero-votes .percentage {
    color: #999;
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
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: bold;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.candidates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.ward-results {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>