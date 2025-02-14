<?php
include 'db.php';
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bharat V - Voting Made Simple</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #2E7D32;
            --light-green: #4CAF50;
            --dark-green: #1B5E20;
            --primary-orange: #F57C00;
            --light-orange: #FF9800;
            --dark-orange: #E65100;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--primary-green);
            color: white;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
        
        }

        .brand {
            padding: 25px 30px;
            
        }

        .brand h1 {
            font-size: 32px;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--light-orange);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .brand p {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 300;
            color: white;
        }

        .nav-menu {
            margin-top: 30px;
            flex-grow: 1;
            padding: 0 15px;
        }

        .nav-menu button {
            width: 100%;
            padding: 14px 20px;
            margin: 8px 0;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            text-align: left;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 10px;
            display: flex;
            align-items: center;
        }

        .nav-menu button:hover {
            background: var(--primary-orange);
            transform: translateX(5px);
        }

        .sidebar-logout {
            padding: 20px;
        }

        .sidebar-logout button {
            width: 100%;
            padding: 14px 20px;
            background: var(--dark-orange);
            border: none;
            color: white;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 10px;
            font-weight: 500;
            font-size: 15px;
        }

        .sidebar-logout button:hover {
            background: var(--primary-orange);
            transform: translateY(-2px);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: white;
            padding: 20px 30px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            border-bottom: 3px solid var(--light-green);
        }

        .header p {
            margin-left: auto;
            font-size: 18px;
            font-weight: 500;
            color: var(--dark-green);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header p span {
            color: white;
            padding: 8px 15px;
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--light-orange) 100%);
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .content {
            padding: 30px;
            flex-grow: 1;
            background-color: #f8f9fa;
        }

        .content-box {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
            transition: transform 0.3s ease;
            border-top: 4px solid var(--primary-green);
        }

        .content-box:hover {
            transform: translateY(-5px);
        }

        .content-box h2 {
            margin-bottom: 20px;
            color: var(--primary-green);
            font-size: 24px;
            font-weight: 600;
        }

        .content-box p {
            color: #4b5563;
            line-height: 1.6;
            font-size: 15px;
        }

        /* Add smooth transitions */
        button, .content-box {
            transition: all 0.3s ease;
        }

        /* Add hover effects */
        button:active {
            transform: scale(0.98);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--light-green);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-green);
        }
    </style>    
</head>
<body>
    

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="brand">
                <h1>Bharat V</h1>
                <p>Voting Made Simple</p>
            </div>
            <div class="nav-menu">
                <button onclick="loadContent('voter-profile')">Profile</button>
                <button onclick="loadContent('election')">Election Update</button>
                <button onclick="loadContent('vote')">Vote Now</button>
                <button onclick="loadContent('results')">View results</button>
                <button onclick="loadContent('candidates')">View Candidates</button>
            </div>
            <div class="sidebar-logout">
                <button onclick="logout()">Logout</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <p>Hello,<span><?php echo htmlspecialchars($_SESSION['name']); ?></span></p>
            </div>
            <div class="content">
                <div class="content-box" id="dynamicContent">
                    <h2>Welcome to Bharat V</h2>
                    <p>Select an option from the menu to get started with your voting experience.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function loadContent(page) {
            fetch(`pages/${page}.php`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('dynamicContent').innerHTML = data;
                })
                .catch(error => {
                    console.error('Error loading content:', error);
                });
        }

        function logout() {
            fetch('logout.php')
                .then(() => {
                    window.location.href = 'login.php';
                });
        }
    </script>
</body>
</html>