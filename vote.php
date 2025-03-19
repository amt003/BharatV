<?php
include 'db.php';
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['voter', 'candidate'])) {
    echo "<p class='error'>Please log in to access this feature.</p>";
    exit();
}

if (!isset($_GET['election_id'])) {
    echo "<p class='error'>No election selected.</p>";
    exit();
}

$election_id = intval($_GET['election_id']);
$user_id = $_SESSION['user_id'];

// Fetch election details with prepared statement
$query = "SELECT *, 
          CASE 
              WHEN CURDATE() BETWEEN start_date AND end_date THEN 'ongoing'
              WHEN CURDATE() > end_date THEN 'completed'
              ELSE status 
          END AS dynamic_status 
          FROM elections WHERE election_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$result = $stmt->get_result();
$election = $result->fetch_assoc();

// Various checks (election validity, voting status, etc.)
if (!$election) {
    echo "<p class='error'>Invalid election.</p>";
    exit();
}

if ($election['dynamic_status'] !== 'ongoing') {
    echo "<p class='error'>This election is not currently active.</p>";
    exit();
}

// Check if user has already voted
$query = "SELECT * FROM votes WHERE id = ? AND 
          contesting_id IN (SELECT contesting_id FROM contesting_candidates WHERE Election_id = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $election_id);
$stmt->execute();
$existing_vote = $stmt->get_result()->fetch_assoc();

if ($existing_vote) {
    echo "<p class='error'>You have already voted in this election.</p>";
    exit();
}

// Fetch candidates
$query = "SELECT cc.contesting_id, 
                 cc.application_type,
                 cc.independent_party_name, 
                 cc.independent_party_symbol,
                 cc.party_id, 
                 u.name, 
                 u.phone, 
                 u.email,
                 p.party_name, 
                 p.party_symbol 
          FROM contesting_candidates cc 
          JOIN users u ON cc.id = u.id 
          LEFT JOIN parties p ON cc.party_id = p.party_id 
          WHERE cc.Election_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$candidates = $stmt->get_result();

if ($candidates->num_rows === 0) {
    echo "<p class='error'>No approved candidates found for this election.</p>";
    exit();
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    :root {
        --primary-green: #2E7D32;
        --light-green: #4CAF50;
        --dark-green: #1B5E20;
        --primary-orange: #F57C00;
        --light-orange: #FF9800;
        --dark-orange: #E65100;
    }

    .voting-section {
        padding: 30px;
        max-width: 900px;
        margin: 20px auto;
        background: linear-gradient(to bottom right, #ffffff, #f8f9fa);
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        position: relative;
        margin-top: 40px;
    }

    .voting-section h2 {
        color: var(--primary-green);
        margin-bottom: 25px;
        text-align: center;
        font-size: 2.2em;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        padding-bottom: 15px;
    }

    .voting-section h2:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 3px;
        background: linear-gradient(to right, var(--primary-green), var(--primary-orange));
        border-radius: 2px;
    }

    .election-description {
        color: #555;
        margin: 30px auto;
        text-align: center;
        padding: 20px;
        max-width: 700px;
        font-size: 1.1em;
        line-height: 1.6;
        background: rgba(46, 125, 50, 0.05);
        border-radius: 10px;
        border-left: 4px solid var(--primary-green);
    }

    .ballot-paper {
        background: #fff;
        border: 2px solid #ddd;
        border-radius: 15px;
        padding: 20px;
        margin: 20px 0;
        position: relative;
        overflow: hidden;
    }

    .ballot-paper:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 10px;
        height: 100%;
        background: repeating-linear-gradient(
            45deg,
            var(--primary-green),
            var(--primary-green) 10px,
            var(--light-green) 10px,
            var(--light-green) 20px
        );
    }

    .candidate-option {
        display: flex;
        align-items: center;
        padding: 25px;
        margin: 15px 0;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        transition: all 0.3s ease;
        background: linear-gradient(to right, #fff, #f8f9fa);
        position: relative;
        overflow: hidden;
    }

    .candidate-option:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: var(--light-green);
        background: linear-gradient(to right, #fff, #f0f7f0);
    }

    .candidate-info {
        flex: 1;
        padding: 0 25px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .candidate-name {
        font-size: 1.3em;
        font-weight: 600;
        color: var(--dark-green);
        display: flex;
        align-items: center;
        gap: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        padding-bottom: 8px;
    }

    .candidate-name:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 40px;
        height: 2px;
        background: linear-gradient(to right, var(--primary-green), transparent);
    }

    .party-info {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 5px;
    }

    .party-name {
        color: var(--primary-orange);
        font-size: 1.1em;
        font-weight: 500;
        padding: 5px 15px;
        background: rgba(245, 124, 0, 0.1);
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .party-name:before {
        content: '•';
        color: var(--dark-orange);
    }

    .party-symbol {
        width: 60px;
        height: 60px;
        object-fit: contain;
        padding: 8px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border: 2px solid var(--light-orange);
        transition: all 0.3s ease;
    }

    .candidate-option:hover .party-symbol {
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }

    /* Custom radio button styling */
    .candidate-option input[type="radio"] {
        appearance: none;
        -webkit-appearance: none;
        width: 28px;
        height: 28px;
        border: 3px solid var(--light-green);
        border-radius: 50%;
        outline: none;
        position: relative;
        margin-right: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .candidate-option input[type="radio"]:checked {
        background-color: var(--primary-green);
        border-color: var(--dark-green);
        box-shadow: 0 0 10px rgba(46, 125, 50, 0.3);
    }

    .candidate-option input[type="radio"]:checked::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 12px;
        height: 12px;
        background-color: white;
        border-radius: 50%;
    }

    .candidate-option input[type="radio"]:hover {
        border-color: var(--primary-green);
        transform: scale(1.1);
    }

    /* Additional candidate info */
    .candidate-details {
        display: flex;
        flex-direction: column;
        gap: 5px;
        margin-top: 8px;
        font-size: 0.9em;
        color: #666;
    }

    .candidate-details i {
        color: var(--primary-green);
        width: 20px;
    }

    /* Selected candidate highlight */
    .candidate-option input[type="radio"]:checked + .candidate-info .candidate-name {
        color: var(--dark-green);
        text-shadow: 0 0 1px rgba(46, 125, 50, 0.3);
    }

    .candidate-option input[type="radio"]:checked + .candidate-info .party-name {
        background: rgba(46, 125, 50, 0.1);
        color: var(--dark-green);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .candidate-option {
            padding: 15px;
        }

        .candidate-name {
            font-size: 1.1em;
        }

        .party-name {
            font-size: 0.9em;
        }

        .party-symbol {
            width: 50px;
            height: 50px;
        }
    }

    .ballot-header {
        text-align: center;
        padding: 15px;
        margin-bottom: 20px;
        border-bottom: 2px dashed #ddd;
    }

    .ballot-instructions {
        background: #f8f9fa;
        padding: 15px;
        margin: 15px 0;
        border-radius: 8px;
        font-size: 0.9em;
        color: #666;
    }

    .ballot-instructions ul {
        list-style-type: none;
        padding-left: 0;
    }

    .ballot-instructions li {
        margin: 8px 0;
        padding-left: 20px;
        position: relative;
    }

    .ballot-instructions li:before {
        content: '•';
        color: var(--primary-green);
        position: absolute;
        left: 0;
    }

    .submit-vote {
        background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
        color: white;
        padding: 15px 30px;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 600;
        font-size: 1.1em;
        margin: 30px auto 10px;
        display: block;
        width: 80%;
        max-width: 400px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(46, 125, 50, 0.2);
    }

    .ballot-footer {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px dashed #ddd;
        font-size: 0.9em;
        color: #666;
    }

    .back-button {
        position: absolute;
        top: 20px;
        left: 20px;
        background: linear-gradient(135deg, var(--primary-orange), var(--dark-orange));
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 500;
        font-size: 0.9em;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(245, 124, 0, 0.2);
        z-index: 10;
        text-decoration: none;
    }

    .back-button:hover {
        background: linear-gradient(135deg, var(--dark-orange), var(--primary-orange));
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245, 124, 0, 0.3);
        color: white;
    }

    .back-button:active {
        transform: translateY(1px);
    }

    .back-button i {
        font-size: 1.2em;
    }
</style>

<div class="voting-section">
    <a  href="<?php echo $_SESSION['role'].'.php'; ?>" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="ballot-paper">
        <div class="ballot-header">
            <h2>Official Ballot</h2>
            <p class="election-description"><?= htmlspecialchars($election['Description']); ?></p>
        </div>

        <div class="ballot-instructions">
            <h4><i class="fas fa-info-circle"></i> Voting Instructions:</h4>
            <ul>
                <li>Select only ONE candidate by clicking their box</li>
                <li>Review your selection carefully before submitting</li>
                <li>Your vote is confidential and secure</li>
                <li>This action cannot be undone once submitted</li>
            </ul>
        </div>

        <form id="voteForm" method="POST">
            <input type="hidden" name="election_id" value="<?= $election_id; ?>">
            
            <?php while ($candidate = $candidates->fetch_assoc()): ?>
                <div class="candidate-option">
                    <input type="radio" 
                           name="contesting_id" 
                           value="<?= $candidate['contesting_id']; ?>" 
                           id="candidate_<?= $candidate['contesting_id']; ?>" 
                           required>
                    <div class="candidate-info">
                        <div class="candidate-name">
                            <i class="fas fa-user-circle"></i>
                            <?= htmlspecialchars($candidate['name']); ?>
                        </div>
                        <div class="party-info">
                            <span class="party-name">
                                <i class="fas fa-flag"></i>
                                <?php 
                                echo htmlspecialchars($candidate['independent_party_name'] ?? $candidate['party_name']); 
                                ?>
                            </span>
                            <!-- <?php 
                            // Handle both independent and regular party symbols
                            if ($candidate['application_type'] === 'independent') {
                                if ($candidate['independent_party_symbol']) {
                                    echo '<img src="data:image/jpeg;base64,' . base64_encode($candidate['independent_party_symbol']) . '" 
                                          alt="Party Symbol" class="party-symbol">';
                                }
                            } else {
                                if ($candidate['party_symbol']) {
                                    echo '<img src="data:image/jpeg;base64,' . base64_encode($candidate['party_symbol']) . '" 
                                          alt="Party Symbol" class="party-symbol">';
                                }
                            }
                            ?> -->
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <button type="submit" class="submit-vote">
                <i class="fas fa-check-circle"></i> Submit Vote
            </button>
        </form>

        <div class="ballot-footer">
            <p><i class="fas fa-shield-alt"></i> Your vote is secure and confidential</p>
            <p><i class="fas fa-clock"></i> Election closes: <?= date('F j, Y, g:i a', strtotime($election['end_date'])); ?></p>
        </div>
    </div>
</div>

<script>
// First, check if loadContent exists in parent window
if (typeof loadContent !== 'function') {
    function loadContent(page) {
        let url = `${page}.php`;
        fetch(url)
            .then(response => response.text())
            .then(data => {
                document.getElementById('dynamicContent').innerHTML = data;
            })
            .catch(error => {
                console.error('Error loading content:', error);
            });
    }
}

document.getElementById('voteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!confirm('Are you sure you want to submit your vote? This action cannot be undone.')) {
        return;
    }
    
    const formData = new FormData(this);
    fetch('submit_vote.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            setTimeout(() => {
                loadContent('<?php echo $_SESSION['role']; ?>_dashboard');
            }, 1000);
        } else {
            alert(data.message || 'Error submitting vote');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting vote. Please try again.');
    });
});
</script>