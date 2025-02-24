<?php
include 'db.php';
session_start();

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['voter', 'candidate'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['election_id'])) {
    die("<p class='error'>No election selected.</p>");
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

if (!$election) {
    die("<p class='error'>Invalid election.</p>");
}

// Check if election is active
if ($election['dynamic_status'] !== 'ongoing') {
    die("<p class='error'>This election is not currently active.</p>");
}

// Check if user has already voted
$query = "SELECT * FROM votes WHERE id = ? AND 
          contesting_id IN (SELECT contesting_id FROM contesting_candidates WHERE Election_id = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $election_id);
$stmt->execute();
$existing_vote = $stmt->get_result()->fetch_assoc();

if ($existing_vote) {
    die("<p class='error'>You have already voted in this election.</p>");
}

// Fetch candidates for this election with their party information
$query = "SELECT cc.contesting_id, u.name, p.party_name, p.party_symbol 
          FROM contesting_candidates cc 
          JOIN users u ON cc.id = u.id 
          JOIN parties p ON cc.party_id = p.party_id 
          WHERE cc.Election_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$candidates = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote - <?= htmlspecialchars($election['Election_title']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .error { 
            color: red;
            text-align: center;
            padding: 20px;
            background: #fee;
            border-radius: 8px;
            margin: 20px 0;
        }

        h2 {
            text-align: center;
            color: #2E7D32;
            font-size: 2rem;
            margin-top: 20px;
        }

        .election-description {
            text-align: center;
            margin-bottom: 30px;
            color: #666;
        }

        .candidate-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 20px 0;
            border-top: 5px solid #2E7D32;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
        }

        .candidate-card:hover {
            transform: translateY(-5px);
        }

        .party-symbol {
            width: 50px;
            height: 50px;
            margin-right: 20px;
        }

        .candidate-info {
            flex-grow: 1;
        }

        .candidate-name {
            font-size: 1.4rem;
            color: #2E7D32;
            margin-bottom: 10px;
        }

        .party-name {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .vote-button {
            background: linear-gradient(45deg, #4CAF50, #1B5E20);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            display: block;
            margin: 30px auto;
            transition: transform 0.2s ease;
            width: 200px;
        }

        .vote-button:hover {
            transform: scale(1.05);
        }

        .stats-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 30px;
            border-top: 5px solid #2E7D32;
        }

        .stats-title {
            color: #2E7D32;
            text-align: center;
            margin-bottom: 15px;
        }

        .stats-info {
            color: #666;
            text-align: center;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <h2>Voting for: <?= htmlspecialchars($election['Election_title']); ?></h2>
    <p class="election-description"><?= htmlspecialchars($election['Description']); ?></p>
    
    <form action="submit_vote.php" method="POST">
        <input type="hidden" name="election_id" value="<?= $election_id; ?>">
     
        <?php while ($candidate = $candidates->fetch_assoc()): ?>
            <div class="candidate-card">
                <img src="<?= htmlspecialchars($candidate['party_symbol']); ?>" 
                     alt="Party Symbol" 
                     class="party-symbol">
                <div class="candidate-info">
                    <input type="radio" name="contesting_id" 
                           value="<?= $candidate['contesting_id']; ?>" 
                           id="candidate_<?= $candidate['contesting_id']; ?>" required>
                    <label for="candidate_<?= $candidate['contesting_id']; ?>">
                        <div class="candidate-name"><?= htmlspecialchars($candidate['name']); ?></div>
                        <div class="party-name"><?= htmlspecialchars($candidate['party_name']); ?></div>
                    </label>
                </div>
            </div>
        <?php endwhile; ?>
        
        <button type="submit" class="vote-button">Submit Vote</button>
    </form>

    <!-- Show statistics section for candidates -->
    <?php if ($_SESSION['role'] === 'candidate'): ?>
        <div class="stats-section">
            <h3 class="stats-title">Election Statistics</h3>
            <?php
            $query = "SELECT COUNT(*) as vote_count 
                     FROM votes v 
                     JOIN contesting_candidates cc ON v.contesting_id = cc.contesting_id 
                     WHERE cc.Election_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $election_id);
            $stmt->execute();
            $vote_count = $stmt->get_result()->fetch_assoc()['vote_count'];
            ?>
            <div class="stats-info">
                <p>Total votes cast: <?= $vote_count; ?></p>
                <p>Election ends: <?= htmlspecialchars($election['end_date']); ?></p>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>