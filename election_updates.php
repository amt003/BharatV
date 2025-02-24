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

//update the status of elections based on dates
    $updateStatusQuery = "UPDATE elections 
                     SET status = 
                         CASE 
                             WHEN CURDATE() > end_date THEN 'completed'
                             WHEN CURDATE() BETWEEN start_date AND end_date THEN 'ongoing'
                             ELSE status 
                         END 
                     WHERE status != 'completed'";  // Only update non-completed elections

$conn->query($updateStatusQuery);

    // Fetch elections for the user's ward
    $query = "SELECT *, 
            CASE 
                WHEN CURDATE() BETWEEN start_date AND end_date THEN 'ongoing' 
                WHEN CURDATE() > end_date THEN 'completed' 
                ELSE status 
            END AS dynamic_status 
            FROM elections 
            WHERE FIND_IN_SET(?, ward_ids) AND status IN ('scheduled', 'ongoing')";
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
    background-color: rgba(0, 0, 0, 0.4);
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    width: 40%;
    text-align: center;
    position: relative;
}

.close {
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 24px;
    cursor: pointer;
}
</style> 
</head>
<body>
    <h2 style="text-align: center;">Elections</h2>
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

            <!-- View Candidates Button -->
            <div style="margin-top: 10px;">
            <button class="view-candidates-button" data-election-id="<?= $election['election_id']; ?>">
    View Candidates
</button>
            </div>
        </div>
    <?php endwhile; ?>
    </div>

   

</body>
</html>