<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get all elections
$elections_query = "SELECT election_id, election_title, status, start_date, end_date FROM elections ORDER BY start_date DESC";
$elections_result = $conn->query($elections_query);
?>

<div class="content-section">
    <h2>Manage Candidates</h2>
    
    <button id="backToCandidatesBtn" class="action-button" style="display: none; margin-bottom: 20px;">
        <i class="fas fa-arrow-left"></i> Back to Elections List
    </button>

    <div id="electionsListContainer">
        <?php while ($election = $elections_result->fetch_assoc()): ?>
            <?php
            $current_date = date('Y-m-d');
            $start_date = $election['start_date'];
            $end_date = $election['end_date'];
            
            if ($current_date < $start_date) {
                $calculated_status = 'upcoming';
            } elseif ($current_date > $end_date) {
                $calculated_status = 'completed';
            } else {
                $calculated_status = 'ongoing';
            }
            ?>
            <div class="election-card">
                <div class="election-name"><?php echo htmlspecialchars($election['election_title']); ?></div>
                <div class="election-dates">
                    <i class="fas fa-calendar"></i> 
                    <?php 
                    echo date('M d, Y', strtotime($election['start_date'])) . ' - ' . 
                         date('M d, Y', strtotime($election['end_date'])); 
                    ?>
                </div>
                <div class="election-status status-<?php echo $calculated_status; ?>">
                    <?php echo ucfirst($calculated_status); ?>
                </div>
                <button class="view-candidates-btn" data-election-id="<?php echo $election['election_id']; ?>">
                    <i class="fas fa-users"></i> View Candidates
                </button>
            </div>
        <?php endwhile; ?>
    </div>

    <div id="candidatesContainer" style="display: none;">
        <!-- Candidates will be loaded here -->
    </div>
</div>

<script>
// This function needs to be called when the page is loaded dynamically
function initManageCandidates() {
    console.log("Initializing manage candidates...");
    const electionsContainer = document.getElementById('electionsListContainer');
    const candidatesContainer = document.getElementById('candidatesContainer');
    const backButton = document.getElementById('backToCandidatesBtn');

    if (!electionsContainer || !candidatesContainer || !backButton) {
        console.error("Required elements not found");
        return;
    }

    // Handle view candidates button clicks
    document.querySelectorAll('.view-candidates-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const electionId = this.dataset.electionId;
            console.log("Viewing candidates for election ID:", electionId);
            
            // Show loading state
            candidatesContainer.style.display = 'block';
            candidatesContainer.innerHTML = '<div class="loading-spinner">Loading candidates...</div>';
            
            // Hide elections list and show back button
            electionsContainer.style.display = 'none';
            backButton.style.display = 'block';

            // Fetch candidates with full URL path
            fetch(`fetch_candidates.php?election_id=${electionId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    console.log("Received candidates data");
                    candidatesContainer.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error fetching candidates:', error);
                    candidatesContainer.innerHTML = '<p class="error-message">Error loading candidates. Please try again.</p>';
                });
        });
    });

    // Handle back button click
    backButton.addEventListener('click', () => {
        candidatesContainer.style.display = 'none';
        electionsContainer.style.display = 'block';
        backButton.style.display = 'none';
        candidatesContainer.innerHTML = ''; // Clear the candidates data
    });
}

// Initialize when loaded
initManageCandidates();
</script>

<style>
.content-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.election-card {
    background: #f8f9fa;
    padding: 20px;
    margin: 10px 0;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.election-name {
    font-size: 1.2em;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
}

.election-dates {
    color: #666;
    font-size: 0.9em;
    margin-bottom: 10px;
}

.election-status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.8em;
    margin-bottom: 10px;
}

.status-ongoing { background-color: #4CAF50; color: white; }
.status-completed { background-color: #666; color: white; }
.status-upcoming { background-color: #2196F3; color: white; }

.view-candidates-btn {
    display: inline-block;
    padding: 8px 16px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.view-candidates-btn:hover {
    background-color: #45a049;
}

.action-button {
    padding: 8px 16px;
    background-color: #666;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.action-button:hover {
    background-color: #555;
}

.loading-spinner {
    text-align: center;
    padding: 20px;
    color: #666;
}

.error-message {
    color: #dc3545;
    text-align: center;
    padding: 20px;
}
</style>