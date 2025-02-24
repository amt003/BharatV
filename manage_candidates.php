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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Candidates - BharatV</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .elections-list {
            margin: 20px 0;
        }

        .election-card {
            background: white;
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

        .status-ongoing {
            background-color: #4CAF50;
            color: white;
        }

        .status-completed {
            background-color: #666;
            color: white;
        }

        .status-upcoming {
            background-color: #2196F3;
            color: white;
        }

        .view-candidates-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .view-candidates-btn:hover {
            background-color: #45a049;
        }

        #candidates-container {
            margin-top: 20px;
        }
        
        .company-logo {
  top: 2px;
  left: 10px;
  display: flex;
  align-items: center;
}

.company-logo img {
  width: 140px;
  height: auto;
}
    </style>
</head>
<body>
<div class="company-logo">
           <a href="admin.php"><img src="assets/logo.jpg" alt="Company logo"></a>
        </div>
    <div class="elections-list">
        <h2>Select an Election to View Candidates</h2>
        
        <?php while ($election = $elections_result->fetch_assoc()): ?>
            <div class="election-card">
                <div class="election-name"><?php echo htmlspecialchars($election['election_title']); ?></div>
                <div class="election-dates">
                    <i class="fas fa-calendar"></i> 
                    <?php 
                    echo date('M d, Y', strtotime($election['start_date'])) . ' - ' . 
                         date('M d, Y', strtotime($election['end_date'])); 
                    ?>
                </div>
                <div class="election-status status-<?php echo strtolower($election['status']); ?>">
                    <?php echo ucfirst($election['status']); ?>
                </div>
                <a href="fetch_candidates.php?election_id=<?php echo $election['election_id']; ?>" 
                   class="view-candidates-btn">
                    <i class="fas fa-users"></i> View Candidates
                </a>
            </div>
        <?php endwhile; ?>
    </div>

    <div id="candidates-container">
        <!-- Candidates will be loaded here when an election is selected -->
    </div>

    <script>
        // If you want to load candidates dynamically using AJAX
        document.querySelectorAll('.view-candidates-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('candidates-container').innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        });
    </script>
</body>
</html>