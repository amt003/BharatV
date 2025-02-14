<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BharatV</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f4f4f4;
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background: #fff;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            height: 100vh;
            position: fixed;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container img {
            max-width: 150px;
            height: auto;
        }

        .nav-links {
            list-style: none;
            padding: 0;
        }

        .nav-links li {
            margin-bottom: 15px;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            text-decoration: none;
            color: #333;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            background-color: #4CAF50;
            color: white;
        }

        .nav-links i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .welcome-text {
            font-size: 24px;
            color: #333;
        }

        .logout-btn {
            padding: 8px 20px;
            background-color: orange;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color:orange;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card i {
            font-size: 24px;
            color: #4CAF50;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .active {
            background-color: #4CAF50;
            color: white !important;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-container">
            <img src="assets/logo.jpg" alt="BharatV Logo">
        </div>
        <ul class="nav-links">
            <li><a href="#" class="active"><i class="fas fa-home"></i>Dashboard</a></li>
            <li><a href="approvals.php"><i class="fas fa-users"></i>Manage Users</a></li>
            <li><a href="manage_candidates.php"><i class="fas fa-user-tie"></i>Manage Candidates</a></li>
            <li><a href="manage_elections.php"><i class="fas fa-vote-yea"></i>Manage Elections</a></li>
            <li><a href="view_results.php"><i class="fas fa-chart-bar"></i>View Results</a></li>
            <li><a href="system_settings.php"><i class="fas fa-cog"></i>Settings</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h2 class="welcome-text">Welcome, Admin!</h2>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-number">
                    <?php
                    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'voter'");
                    echo $result->fetch_assoc()['count'];
                    ?>
                </div>
                <div class="stat-label">Total Voters</div>
            </div>

            <div class="stat-card">
                <i class="fas fa-user-tie"></i>
                <div class="stat-number">
                    <?php
                    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'candidate'");
                    echo $result->fetch_assoc()['count'];
                    ?>
                </div>
                <div class="stat-label">Total Candidates</div>
            </div>

            <div class="stat-card">
                <i class="fas fa-vote-yea"></i>
                <div class="stat-number">
                    <?php
                    // $result = $conn->query("SELECT COUNT(*) as count FROM elections");
                    // echo $result->fetch_assoc()['count'];
                    ?>
                </div>
                <div class="stat-label">Active Elections</div>
            </div>

            <div class="stat-card">
                <i class="fas fa-chart-bar"></i>
                <div class="stat-number">
                    <?php
                    // $result = $conn->query("SELECT COUNT(*) as count FROM votes");
                    // echo $result->fetch_assoc()['count'];
                    ?>
                </div>
                <div class="stat-label">Total Votes Cast</div>
            </div>
        </div>
    </div>

    <script>
        // Add active class to current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-links a');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentLocation) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>