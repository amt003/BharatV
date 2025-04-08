<?php
include 'db.php';
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id'])) {
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
$query = "SELECT *,election_title,
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
$alreadyVoted = false;
$query = "SELECT * FROM votes WHERE id = ? AND 
          contesting_id IN (SELECT contesting_id FROM contesting_candidates WHERE Election_id = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $election_id);
$stmt->execute();
$existing_vote = $stmt->get_result()->fetch_assoc();

if ($existing_vote) {
    $alreadyVoted = true;
}

// Only fetch candidates if the user hasn't voted yet
$candidates = null;
if (!$alreadyVoted) {
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
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
        background: linear-gradient(135deg, var(--primary-orange), var(--dark-orange));
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 500;
        font-size: 0.9em;
    
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
    
    .navigation {
        margin-bottom: 20px;
        text-align: left;
    }
    
    /* Already voted message styling */
    .already-voted-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 300px;
        text-align: center;
        padding: 40px;
    }
    
    .already-voted-message {
        background: linear-gradient(to right, #fff, #f8f9fa);
        border-radius: 15px;
        padding: 30px 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
        border-left: 5px solid var(--primary-orange);
        max-width: 600px;
        margin: 0 auto;
        position: relative;
        overflow: hidden;
        animation: fadeInUp 0.6s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .already-voted-icon {
        font-size: 60px;
        color: var(--primary-orange);
        margin-bottom: 20px;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
        }
    }
    
    .already-voted-title {
        color: var(--dark-orange);
        font-size: 26px;
        font-weight: bold;
        margin-bottom: 15px;
    }
    
    .already-voted-text {
        color: #555;
        font-size: 18px;
        line-height: 1.6;
        margin-bottom: 25px;
    }
    
    .return-button {
        background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 600;
        font-size: 1em;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        margin-top: 10px;
        box-shadow: 0 4px 15px rgba(46, 125, 50, 0.2);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .return-button:hover {
        background: linear-gradient(135deg, var(--dark-green), var(--primary-green));
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
        color: white;
    }
    
    .already-voted-details {
        background: rgba(255, 255, 255, 0.8);
        border-radius: 10px;
        padding: 15px;
        margin-top: 20px;
        font-size: 14px;
        color: #777;
        border: 1px dashed #ddd;
    }
    
    .already-voted-details p {
        margin: 5px 0;
    }
</style>

<div class="voting-section">
    

    <?php if ($alreadyVoted): ?>
        <!-- Already voted message -->
        <div class="already-voted-container">
            <div class="already-voted-message">
                <div class="already-voted-icon">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <h2 class="already-voted-title">You've Already Cast Your Vote</h2>
                <p class="already-voted-text">
                    Thank you for participating in the democratic process! Your vote for this election has been recorded successfully and cannot be changed.
                </p>
                <div class="already-voted-details">
                    <p><i class="fas fa-calendar-check"></i> Election: <strong><?= htmlspecialchars($election['election_title']); ?></strong></p>
                    <p><i class="fas fa-clock"></i> Vote Cast: <strong><?= date('F j, Y, g:i a', strtotime($existing_vote['casted_at'])); ?></strong></p>
                </div>
                <a href="<?php echo $_SESSION['role'].'.php'; ?>" class="return-button">
                    <i class="fas fa-home"></i> Return to Dashboard
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Display ballot if user hasn't voted -->
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
    <?php endif; ?>
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
                showNotification('Error loading content', 'error');
            });
    }
}

// Create a notification function for displaying styled messages
function showNotification(message, type = 'info', title = '', icon = '', duration = 5000) {
    // If notification container doesn't exist, create it
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        container.style.maxWidth = '400px';
        document.body.appendChild(container);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    // Determine color based on type
    let bgColor, borderColor, textColor;
    if (type === 'success') {
        bgColor = '#d4edda';
        borderColor = '#c3e6cb';
        textColor = '#155724';
        icon = icon || 'fas fa-check-circle';
    } else if (type === 'error') {
        bgColor = '#f8d7da';
        borderColor = '#f5c6cb';
        textColor = '#721c24';
        icon = icon || 'fas fa-times-circle';
    } else {
        bgColor = '#e0f2f1';
        borderColor = '#b2dfdb';
        textColor = '#2E7D32';
        icon = icon || 'fas fa-info-circle';
    }
    
    // Set styles
    notification.style.padding = '15px 20px';
    notification.style.marginBottom = '15px';
    notification.style.backgroundColor = bgColor;
    notification.style.borderLeft = `5px solid ${borderColor}`;
    notification.style.borderRadius = '5px';
    notification.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
    notification.style.color = textColor;
    notification.style.position = 'relative';
    notification.style.animation = 'slideInRight 0.5s forwards';
    notification.style.opacity = '0';
    
    // Add title if provided
    if (title) {
        const titleElement = document.createElement('div');
        titleElement.style.fontWeight = 'bold';
        titleElement.style.marginBottom = '5px';
        titleElement.style.fontSize = '1.1em';
        if (icon) {
            titleElement.innerHTML = `<i class="${icon}" style="margin-right: 8px;"></i>${title}`;
        } else {
            titleElement.textContent = title;
        }
        notification.appendChild(titleElement);
    }
    
    // Add message
    const messageElement = document.createElement('div');
    messageElement.textContent = message;
    notification.appendChild(messageElement);
    
    // Add close button
    const closeButton = document.createElement('span');
    closeButton.innerHTML = '&times;';
    closeButton.style.position = 'absolute';
    closeButton.style.top = '10px';
    closeButton.style.right = '10px';
    closeButton.style.cursor = 'pointer';
    closeButton.style.fontSize = '1.2em';
    closeButton.onclick = function() {
        container.removeChild(notification);
    };
    notification.appendChild(closeButton);
    
    // Add animation keyframes
    if (!document.getElementById('notification-style')) {
        const style = document.createElement('style');
        style.id = 'notification-style';
        style.innerHTML = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes fadeOut {
                from {
                    opacity: 1;
                }
                to {
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Add to container and auto-remove after duration
    container.appendChild(notification);
    
    // Make it visible with animation
    setTimeout(() => {
        notification.style.opacity = '1';
    }, 10);
    
    // Set auto-remove timeout
    if (duration) {
        setTimeout(() => {
            if (notification.parentNode === container) {
                notification.style.animation = 'fadeOut 0.5s forwards';
                setTimeout(() => {
                    if (notification.parentNode === container) {
                        container.removeChild(notification);
                    }
                }, 500);
            }
        }, duration);
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
            throw new Error('Network response was not ok: ' + response.status);
        }
        // First, get the raw text to debug any potential issues
        return response.text().then(text => {
            console.log("Raw response:", text); // For debugging
            try {
                // Try to parse the response as JSON
                return JSON.parse(text);
            } catch (e) {
                console.error("JSON parsing error:", e);
                // If there was a parsing error, log it and throw a better error
                throw new Error('Invalid JSON response: ' + e.message);
            }
        });
    })
    .then(data => {
        // Use the enhanced notification with titles and icons
        if (data.success) {
            showNotification(
                data.message, 
                data.type || 'success', 
                data.title || 'Vote Confirmed', 
                data.icon || 'fas fa-check-circle'
            );
            
            // Redirect after successful vote
            setTimeout(() => {
                window.location.href = '<?php echo $_SESSION['role']; ?>.php';
            }, 3000);
        } else {
            showNotification(
                data.message || 'Error submitting vote', 
                data.type || 'error', 
                data.title || 'Error', 
                data.icon || 'fas fa-times-circle'
            );
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // This will provide more details about the error
        showNotification(
            'Error: ' + error.message, 
            'error', 
            'Connection Error', 
            'fas fa-wifi'
        );
    });
});
</script>