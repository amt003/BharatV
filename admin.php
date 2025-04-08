<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: loginadmin.php");
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
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
            transition: all 0.3s ease;
            z-index: 100;
        }

        .sidebar-collapsed {
            width: 70px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container img {
            max-width: 100%;
            height: auto;
            transition: all 0.3s ease;
        }

        .nav-links {
            list-style: none;
            padding: 0;
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

        .nav-links li {
            margin-bottom: 10px;
        }

        .nav-btn {
            width: 100%;
            padding: 12px;
            text-align: left;
            background: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 16px;
            color: #333;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .nav-btn i {
            margin-right: 10px;
            font-size: 18px;
            min-width: 24px;
            text-align: center;
        }

        .nav-btn:hover {
            background-color: #4CAF50;
            color: white;
            transform: scale(1.05);
        }

        .nav-btn.active {
            background-color: #4CAF50;
            color: white;
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

        .burger-menu {
            display: none;
            font-size: 24px;
            cursor: pointer;
            margin-right: 15px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            transition: all 0.3s ease;
        }

        .main-content-expanded {
            margin-left: 70px;
            width: calc(100% - 70px);
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
            background-color: orange;
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
            color: rgb(69, 140, 71);
        }

        .stat-card i {
            font-size: 24px;
            color: #4CAF50;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: rgb(69, 140, 71);
            margin: 10px 0;
        }

        .stat-label {
            color: rgb(69, 140, 71);
            font-size: 14px;
        }

        .active {
            background-color: #4CAF50;
            color: white !important;
        }

        /* Styling for Ongoing Elections */
        .ongoing-elections {
            margin-top: 30px;
        }

        .ongoing-elections h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 24px;
        }

        .elections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .election-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.2s;
        }

        .election-card:hover {
            transform: translateY(-5px);
        }

        .election-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }

        .election-dates {
            margin-bottom: 15px;
            color: #555;
            line-height: 1.5;
        }

        .date-label {
            font-weight: 600;
            color: #444;
        }

        .election-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #555;
        }

        .stat i {
            color: #4CAF50;
        }

        .view-results {
            display: block;
            text-align: center;
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .view-results:hover {
            background-color: #45a049;
        }

        .no-elections-message {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            color: #666;
        }

        .no-elections-message i {
            font-size: 30px;
            color: #999;
            margin-bottom: 10px;
        }

        /* Custom scrollbar styles */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #4CAF50;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #45a049;
        }

        /* Hide the scrollbar arrows */
        ::-webkit-scrollbar-button {
            display: none;
        }

        /* Responsive Design */
        @media screen and (max-width: 1024px) {
            .sidebar {
                width: 200px;
            }
            
            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
        }

        @media screen and (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 250px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .burger-menu {
                display: block;
            }
            
            .header {
                display: flex;
                flex-wrap: wrap;
            }
            
            .welcome-text {
                font-size: 20px;
                margin-bottom: 10px;
            }
            
            .dashboard-stats {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .elections-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media screen and (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .welcome-text {
                margin-bottom: 15px;
            }
            
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .elections-grid {
                grid-template-columns: 1fr;
            }
            
            .election-stats {
                flex-direction: column;
                gap: 10px;
            }
            
            .ongoing-elections h2 {
                font-size: 20px;
            }
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 99;
        }
        
        .sidebar-overlay.active {
            display: block;
        }

        .role-update-btn {
            width: 100%;
            padding: 12px;
            text-align: left;
            background: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 16px;
            color: #333;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .role-update-btn i {
            margin-right: 10px;
        }

        .role-update-btn:hover {
            background-color: #45a049;
        }

        .role-update-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
<div class="sidebar-overlay"></div>
<div class="sidebar">
    <div class="logo-container">
        <img src="assets/logo.jpg" alt="BharatV Logo">
    </div>
    <ul class="nav-links">
        <li><button class="nav-btn" data-page="approvals.php"><i class="fas fa-users"></i><span class="nav-text">Manage Approvals</span></button></li>
        <li><button class="nav-btn" data-page="manage_candidates.php"><i class="fas fa-user-tie"></i><span class="nav-text">View Candidates</span></button></li>
        <li><a class="nav-btn" href="manage_elections.php"><i class="fas fa-vote-yea"></i><span class="nav-text">Manage Elections</span></a></li>
        <li><button class="nav-btn" data-page="view_results.php"><i class="fas fa-chart-bar"></i><span class="nav-text">View Results</span></button></li>
        <li><button class="nav-btn" data-page="admin_settings.php"><i class="fas fa-cog"></i><span class="nav-text">Settings</span></button></li>
        <li><button id="updateCandidateRoles" class="role-update-btn"><i class="fas fa-sync"></i> Update Candidate Roles</button></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <div class="header-left">
            <i class="fas fa-bars burger-menu"></i>
            <h2 class="welcome-text">Welcome, Admin!</h2>
        </div>
        <a href="logoutadmin.php" class="logout-btn">Logout</a>
    </div>
    <div id="dynamic-content">

    <!-- Dashboard Statistics (Always Visible) -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <div class="stat-number">
                <?php
                $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'voter'");
                echo $result->fetch_assoc()['count'];
                ?>
            </div>
            <div class="stat-label">Total Voters Registered</div>
        </div>

        <div class="stat-card">
            <i class="fas fa-user-tie"></i>
            <div class="stat-number">
                <?php
                $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'candidate'");
                echo $result->fetch_assoc()['count'];
                ?>
            </div>
            <div class="stat-label">Total Contestants Registered</div>
        </div>

        <div class="stat-card">
            <i class="fas fa-vote-yea"></i>
            <div class="stat-number">
                <?php
                $result = $conn->query("SELECT COUNT(*) as count FROM elections 
                WHERE CURDATE() >= start_date 
                AND CURDATE() <= end_date");
                echo $result->fetch_assoc()['count'];
                ?>
            </div>
            <div class="stat-label">Active Elections</div>
        </div>
    </div>

    
        <!-- Ongoing Elections Section -->
<div class="ongoing-elections">
    <h2>Ongoing Elections Details</h2>
    
    <?php
    // Query to get ongoing elections
    $ongoing_query = "SELECT e.*, 
                      (SELECT COUNT(*) FROM votes WHERE election_id = e.election_id) as total_votes
                      FROM elections e 
                      WHERE CURDATE() >= e.start_date 
                      AND CURDATE() <= e.end_date
                      ORDER BY e.end_date ASC";
    
    $ongoing_result = $conn->query($ongoing_query);
    
    if ($ongoing_result && $ongoing_result->num_rows > 0) {
        echo '<div class="elections-grid">';
        
        while ($election = $ongoing_result->fetch_assoc()) {
            // Calculate days remaining
            $end_date = new DateTime($election['end_date']);
            $today = new DateTime(date('Y-m-d'));
            $days_remaining = $today->diff($end_date)->days;
            
            echo '<div class="election-card">';
            echo '<div class="election-title">' . htmlspecialchars($election['Election_title']) . '</div>';
            echo '<div class="election-dates">';
            echo '<span class="date-label">Started:</span> ' . date('M d, Y', strtotime($election['start_date'])) . '<br>';
            echo '<span class="date-label">Ends:</span> ' . date('M d, Y', strtotime($election['end_date'])) . '';
            echo '</div>';
            echo '<div class="election-stats">';
            echo '<div class="stat"><i class="fas fa-vote-yea"></i> ' . $election['total_votes'] . ' votes</div>';
            echo '<div class="stat"><i class="fas fa-clock"></i> ' . $days_remaining . ' days remaining</div>';
            echo '</div>';
            echo '<a href="admin.php?view_results=' . $election['election_id'] . '" class="btn btn-primary view-results">View Live Results</a>';
            echo '</div>';
        }
        
        echo '</div>';
    } else {
        echo '<div class="no-elections-message">';
        echo '<i class="fas fa-info-circle"></i>';
        echo '<p>There are no ongoing elections at this time.</p>';
        echo '</div>';
    }
    ?>
    
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

            // Mobile sidebar toggle
            const burgerMenu = document.querySelector('.burger-menu');
            const sidebar = document.querySelector('.sidebar');
            const sidebarOverlay = document.querySelector('.sidebar-overlay');
            const navTexts = document.querySelectorAll('.nav-text');

            function toggleSidebar() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
                document.body.classList.toggle('sidebar-open');
            }

            if (burgerMenu) {
                burgerMenu.addEventListener('click', toggleSidebar);
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', toggleSidebar);
            }

            // Close sidebar when a nav item is clicked (mobile)
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                        toggleSidebar();
                    }
                });
            });

            // Handle window resize
            function handleResize() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                }
            }

            window.addEventListener('resize', handleResize);
        });
        
        // dynamically load pages
        document.addEventListener("DOMContentLoaded", function () {
            const buttons = document.querySelectorAll(".nav-btn");
            const contentContainer = document.getElementById("dynamic-content");

            // Function to load page dynamically
            function loadPage(page) {
                fetch(page)
                    .then(response => response.text())
                    .then(data => {
                        contentContainer.innerHTML = data;
                        // Execute any scripts in the loaded content
                        const scripts = contentContainer.getElementsByTagName('script');
                        Array.from(scripts).forEach(script => {
                            const newScript = document.createElement('script');
                            Array.from(script.attributes).forEach(attr => {
                                newScript.setAttribute(attr.name, attr.value);
                            });
                            newScript.appendChild(document.createTextNode(script.innerHTML));
                            script.parentNode.replaceChild(newScript, script);
                        });
                    })
                    .catch(error => console.error("Error loading page:", error));
            }

            // Handle button clicks
            buttons.forEach(button => {
                button.addEventListener("click", function () {
                    // Remove active class from all buttons
                    buttons.forEach(btn => btn.classList.remove("active"));
                    this.classList.add("active");

                    // Get the page to load
                    const page = this.getAttribute("data-page");
                    loadPage(page);
                });
            });
        });

        //add election live and normal validations
        document.addEventListener('DOMContentLoaded', function() {
            // Get form elements with null checks
            const titleInput = document.getElementById('title');
            const descriptionInput = document.getElementById('description');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const form = document.querySelector('form');
            const checkboxes = document.querySelectorAll('input[name="ward_ids[]"]');
            
            // Only proceed with date validation if the elements exist
            if (startDateInput && endDateInput) {
                // Get today's date in YYYY-MM-DD format
                const today = new Date().toISOString().split('T')[0];
                
                // Set min attribute for date inputs
                startDateInput.min = today;
                endDateInput.min = today;
                
                // Set default dates
                startDateInput.value = today;
                endDateInput.value = today;
            }
            
            // Create error message element function
            function createErrorElement(message) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'validation-error';
                errorDiv.textContent = message;
                errorDiv.style.color = '#dc3545';
                errorDiv.style.fontSize = '0.85rem';
                errorDiv.style.marginTop = '5px';
                return errorDiv;
            }
            
            // Clear error message
            function clearError(element) {
                const parent = element.parentElement;
                const existingError = parent.querySelector('.validation-error');
                if (existingError) {
                    parent.removeChild(existingError);
                }
            }
            
            // Show error message
            function showError(element, message) {
                clearError(element);
                const errorElement = createErrorElement(message);
                element.parentElement.appendChild(errorElement);
            }
            
            // Validate title (required and min length)
            function validateTitle() {
                clearError(titleInput);
                
                if (titleInput.value.trim() === '') {
                    showError(titleInput, 'Election title is required');
                    return false;
                } else if (titleInput.value.trim().length < 5) {
                    showError(titleInput, 'Title should be at least 5 characters long');
                    return false;
                }
                
                return true;
            }

            // Validate description 
            function validateDescription() {
                clearError(descriptionInput);
                
                if (descriptionInput.value.trim() !== '' && descriptionInput.value.trim().length < 10) {
                    showError(descriptionInput, 'Description should be at least 10 characters long or left empty');
                    return false;
                }
                
                return true;
            }
            
            // Validate at least one ward is selected
            function validateWards() {
                // Get container for potential error message (parent of checkboxes)
                const container = document.querySelector('.checkbox-group').parentElement;
                clearError(container);
                
                let isChecked = false;
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        isChecked = true;
                    }
                });
                
                if (!isChecked) {
                    const errorElement = createErrorElement('Please select at least one ward');
                    container.appendChild(errorElement);
                    return false;
                }
                
                return true;
            }
            
            // Validate dates
            function validateDates() {
                clearError(startDateInput);
                clearError(endDateInput);
                
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;
                        
                // Check if dates are selected
                if (!startDate) {
                    showError(startDateInput, 'Start date is required');
                    return false;
                }
                
                if (!endDate) {
                    showError(endDateInput, 'End date is required');
                    return false;
                }
                
                // Check if start date is not before today
                if (startDate < today) {
                    showError(startDateInput, 'Start date cannot be in the past');
                    return false;
                }
                
                // Check if end date is not before start date
                if (endDate < startDate) {
                    showError(endDateInput, 'End date cannot be before start date');
                    return false;
                }
                
                return true;
            }
            
            // Add event listeners for live validation
            titleInput.addEventListener('blur', validateTitle);
            titleInput.addEventListener('input', function() {
                if (titleInput.value.trim().length >= 5) {
                    clearError(titleInput);
                }
            });
            
            descriptionInput.addEventListener('blur', validateDescription);
            descriptionInput.addEventListener('input', function() {
                if (descriptionInput.value.trim().length >= 10 || descriptionInput.value.trim() === '') {
                    clearError(descriptionInput);
                }
            });
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', validateWards);
            });
            
            startDateInput.addEventListener('change', validateDates);
            endDateInput.addEventListener('change', validateDates);
            
            // Form submission validation
            form.addEventListener('submit', function(e) {
                // Run all validations
                const isTitleValid = validateTitle();
                const isDescriptionValid = validateDescription();
                const areWardsValid = validateWards();
                const areDatesValid = validateDates();
                
                // Prevent form submission if any validation fails
                if (!isTitleValid || !isDescriptionValid || !areWardsValid || !areDatesValid) {
                    e.preventDefault();
                    
                    // Scroll to the first error
                    const firstError = document.querySelector('.validation-error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });

        //live validation for edit modal
        document.addEventListener('DOMContentLoaded', function() {
            // Get form elements with null checks
            const editForm = document.querySelector('#editModal form');
            const editTitleInput = document.getElementById('edit_title');
            const editDescriptionInput = document.getElementById('edit_description');
            const editStartDateInput = document.getElementById('edit_start_date');
            const editEndDateInput = document.getElementById('edit_end_date');
            const editCheckboxes = document.querySelectorAll('#editModal input[name="ward_ids[]"]');

            // Only proceed with date validation if the elements exist
            if (editStartDateInput && editEndDateInput) {
                // Get today's date in YYYY-MM-DD format
                const today = new Date().toISOString().split('T')[0];

                // Set min attributes for date inputs
                editStartDateInput.min = today;
                editEndDateInput.min = today;
            }

            // Create error message element function
            function createErrorElement(message) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'validation-error';
                errorDiv.textContent = message;
                errorDiv.style.color = '#dc3545';
                errorDiv.style.fontSize = '0.85rem';
                errorDiv.style.marginTop = '5px';
                return errorDiv;
            }

            // Clear error message
            function clearError(element) {
                const parent = element.parentElement;
                const existingError = parent.querySelector('.validation-error');
                if (existingError) {
                    parent.removeChild(existingError);
                }
            }

            // Show error message
            function showError(element, message) {
                clearError(element);
                const errorElement = createErrorElement(message);
                element.parentElement.appendChild(errorElement);
            }

            // Validate title (required and min length)
            function validateTitle() {
                clearError(editTitleInput);
                if (editTitleInput.value.trim().length < 5) {
                    showError(editTitleInput, 'Title must be at least 5 characters long');
                    return false;
                }
                return true;
            }

            // Validate description 
            function validateDescription() {
                clearError(editDescriptionInput);
                if (editDescriptionInput.value.trim() !== '' && editDescriptionInput.value.trim().length < 10) {
                    showError(editDescriptionInput, 'Description should be at least 10 characters long or left empty');
                    return false;
                }
                return true;
            }

            // Validate at least one ward is selected
            function validateWards() {
                const container = document.querySelector('#editModal .checkbox-group').parentElement;
                clearError(container);
                let isChecked = Array.from(editCheckboxes).some(checkbox => checkbox.checked);

                if (!isChecked) {
                    showError(container, 'Please select at least one ward');
                    return false;
                }
                return true;
            }

            // Validate dates
            function validateDates() {
                clearError(editStartDateInput);
                clearError(editEndDateInput);

                const startDate = editStartDateInput.value;
                const endDate = editEndDateInput.value;

                if (!startDate) {
                    showError(editStartDateInput, 'Start date is required');
                    return false;
                }

                if (!endDate) {
                    showError(editEndDateInput, 'End date is required');
                    return false;
                }

                if (startDate < today) {
                    showError(editStartDateInput, 'Start date cannot be in the past');
                    return false;
                }

                if (endDate <= startDate) {
                    showError(editEndDateInput, 'End date must be after start date');
                    return false;
                }

                return true;
            }

            // Live validation event listeners
            editTitleInput.addEventListener('blur', validateTitle);
            editTitleInput.addEventListener('input', function() {
                if (editTitleInput.value.trim().length >= 5) clearError(editTitleInput);
            });

            editDescriptionInput.addEventListener('blur', validateDescription);
            editDescriptionInput.addEventListener('input', function() {
                if (editDescriptionInput.value.trim().length >= 10 || editDescriptionInput.value.trim() === '') {
                    clearError(editDescriptionInput);
                }
            });

            editCheckboxes.forEach(checkbox => checkbox.addEventListener('change', validateWards));
            editStartDateInput.addEventListener('change', validateDates);
            editEndDateInput.addEventListener('change', validateDates);

            // Form submission validation
            editForm.addEventListener('submit', function(e) {
                const isTitleValid = validateTitle();
                const isDescriptionValid = validateDescription();
                const areWardsValid = validateWards();
                const areDatesValid = validateDates();

                if (!isTitleValid || !isDescriptionValid || !areWardsValid || !areDatesValid) {
                    e.preventDefault();
                    const firstError = document.querySelector('#editModal .validation-error');
                    if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });

        // Add this to your existing JavaScript section in admin.php
        document.addEventListener('DOMContentLoaded', function() {
            // Define showNotification function globally
            window.showNotification = function(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `alert alert-${type}`;
                notification.style.position = 'fixed';
                notification.style.top = '20px';
                notification.style.right = '20px';
                notification.style.padding = '15px';
                notification.style.borderRadius = '4px';
                notification.style.zIndex = '9999';
                notification.style.backgroundColor = type === 'success' ? '#4CAF50' : '#f44336';
                notification.style.color = 'white';
                notification.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
                
                notification.textContent = message;
                document.body.appendChild(notification);
                
                setTimeout(() => notification.remove(), 3000);
            };
            
            // Create global variables for rejection data
            window.currentRejectForm = null;
            window.currentRejectButton = null;
            
            // Function to handle approval and rejection actions
            window.handleApproval = function(form, action, button) {
                // Get the user ID from the form
                const userId = form.querySelector('[name="user_id"]').value;
                
                // Set the action in the hidden field
                form.querySelector('[name="action"]').value = action;
                
                // For rejection, show the modal to get the reason
                if (action === 'reject') {
                    window.currentRejectForm = form;
                    window.currentRejectButton = button;
                    const modal = document.getElementById('rejectionModal');
                    const reasonField = document.getElementById('rejectionReason');
                    if (modal && reasonField) {
                        reasonField.value = '';
                        modal.style.display = 'block';
                    }
                    return; // Stop here and wait for modal interaction
                }
                
                // For approval, continue with the process
                processApproval(form, action, button);
            };
            
            // Function to process the approval/rejection after getting any needed info
            window.processApproval = function(form, action, button, rejectionReason = '') {
                // Disable the buttons while processing
                const buttons = form.querySelectorAll('button');
                buttons.forEach(btn => btn.disabled = true);
                button.textContent = action === 'approve' ? 'Approving...' : 'Rejecting...';
                
                // Create FormData object
                const formData = new FormData();
                formData.append('user_id', form.querySelector('[name="user_id"]').value);
                formData.append('action', action);
                
                // Add rejection reason if rejecting
                if (action === 'reject' && rejectionReason) {
                    formData.append('rejection_reason', rejectionReason);
                }
                
                // Send AJAX request to process_approval.php
                fetch('process_approval.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success notification
                        let message = data.message;
                        showNotification(message, 'success');
                        
                        // Remove the row from the table or update its status
                        const row = form.closest('tr');
                        row.style.backgroundColor = '#e8f5e9';
                        setTimeout(() => {
                            row.remove();
                        }, 1000);
                        
                        // Check if this was the last row in the table
                        const tbody = row.parentElement;
                        if (tbody.children.length <= 1) {
                            const table = tbody.parentElement;
                            const section = table.closest('.approval-section');
                            const message = document.createElement('p');
                            message.textContent = action === 'approve' ? 
                                'No pending approvals.' : 
                                'No pending rejections.';
                            section.querySelector('.table-responsive').replaceWith(message);
                        }
                    } else {
                        // Show error notification
                        showNotification(data.message, 'error');
                        
                        // Re-enable the buttons
                        buttons.forEach(btn => btn.disabled = false);
                        button.textContent = action === 'approve' ? 'Approve' : 'Reject';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred. Please try again.', 'error');
                    
                    // Re-enable the buttons
                    buttons.forEach(btn => btn.disabled = false);
                    button.textContent = action === 'approve' ? 'Approve' : 'Reject';
                });
            };

            // Define initializeApprovalForms function globally
            window.initializeApprovalForms = function() {
                const forms = document.querySelectorAll('.approval-form');
                if (forms) {
                    forms.forEach(form => {
                        const approveBtn = form.querySelector('.btn-approve');
                        const rejectBtn = form.querySelector('.btn-reject');

                        if (approveBtn) {
                            approveBtn.addEventListener('click', function() {
                                handleApproval(form, 'approve', this);
                            });
                        }

                        if (rejectBtn) {
                            rejectBtn.addEventListener('click', function() {
                                handleApproval(form, 'reject', this);
                            });
                        }
                    });
                }
                
                // Initialize modal event listeners
                const modal = document.getElementById('rejectionModal');
                if (modal) {
                    const closeButton = modal.querySelector('.close');
                    const cancelButton = document.getElementById('cancelRejection');
                    const confirmButton = document.getElementById('confirmRejection');
                    
                    // Close modal when clicking the X
                    if (closeButton) {
                        closeButton.addEventListener('click', function() {
                            modal.style.display = 'none';
                            currentRejectForm = null;
                            currentRejectButton = null;
                        });
                    }
                    
                    // Close modal when clicking cancel
                    if (cancelButton) {
                        cancelButton.addEventListener('click', function() {
                            modal.style.display = 'none';
                            currentRejectForm = null;
                            currentRejectButton = null;
                        });
                    }
                    
                    // Process rejection when confirming
                    if (confirmButton) {
                        confirmButton.addEventListener('click', function() {
                            const reason = document.getElementById('rejectionReason').value.trim();
                            
                            if (!reason) {
                                alert('Please provide a reason for rejection.');
                                return;
                            }
                            
                            if (currentRejectForm && currentRejectButton) {
                                modal.style.display = 'none';
                                processApproval(currentRejectForm, 'reject', currentRejectButton, reason);
                                currentRejectForm = null;
                                currentRejectButton = null;
                            }
                        });
                    }
                    
                    // Close modal when clicking outside of it
                    window.addEventListener('click', function(event) {
                        if (event.target === modal) {
                            modal.style.display = 'none';
                            currentRejectForm = null;
                            currentRejectButton = null;
                        }
                    });
                }
            };

            // Define loadPage function globally
            window.loadPage = function(page) {
                fetch(page)
                    .then(response => response.text())
                    .then(data => {
                        const contentContainer = document.getElementById('dynamic-content');
                        if (contentContainer) {
                            contentContainer.innerHTML = data;
                            
                            // Initialize specific functionality based on the loaded page
                            if (page === 'approvals.php') {
                                initializeApprovalForms();
                            } else if (page === 'manage_elections.php' || page === 'admin_settings.php') {
                                initializeDateInputs();
                                // Initialize ward form validation if add_wards.php is loaded
                                if (page === 'admin_settings.php') {
                                    initializeWardFormValidation();
                                }
                            } else if (page === 'manage_candidates.php') {
                                // Initialize manage candidates functionality if the function exists
                                if (typeof initManageCandidates === 'function') {
                                    initManageCandidates();
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error loading page:', error);
                        showNotification('Error loading page', 'error');
                    });
            };

            // Initialize navigation buttons with null checks
            const navButtons = document.querySelectorAll('.nav-btn');
            if (navButtons) {
                navButtons.forEach(button => {
                    if (button.dataset.page) {
                        button.addEventListener('click', function() {
                            loadPage(this.dataset.page);
                            
                            // Remove active class from all buttons
                            navButtons.forEach(btn => {
                                btn.classList.remove('active');
                            });
                            
                            // Add active class to clicked button
                            this.classList.add('active');
                        });
                    }
                });
            }

            // Initialize forms if they exist
            if (document.querySelector('.approval-form')) {
                initializeApprovalForms();
            }
        });

        // Define initializeDateInputs function globally
        window.initializeDateInputs = function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            if (startDateInput && endDateInput) {
                const today = new Date().toISOString().split('T')[0];
                startDateInput.min = today;
                endDateInput.min = today;
                startDateInput.value = today;
                endDateInput.value = today;
            }
        };

        // Define initializeWardFormValidation function globally
        window.initializeWardFormValidation = function() {
            // Ward form validation
            const wardForm = document.getElementById('wardForm');
            const wardNameInput = document.getElementById('ward_name');
            const wardNameError = document.getElementById('wardNameError');
            
            function validateWardName() {
                const wardName = wardNameInput.value.trim();
                const isValid = wardName.length >= 3 && wardName.length <= 100;
                
                if (!isValid) {
                    wardNameInput.classList.add('invalid');
                    wardNameError.style.display = 'block';
                    return false;
                } else {
                    wardNameInput.classList.remove('invalid');
                    wardNameError.style.display = 'none';
                    return true;
                }
            }
            
            if (wardNameInput) {
                wardNameInput.addEventListener('input', validateWardName);
            }
            
            if (wardForm) {
                wardForm.addEventListener('submit', function(e) {
                    if (!validateWardName()) {
                        e.preventDefault();
                    }
                });
            }

            // Initialize tab functionality
            const tabButtons = document.querySelectorAll('.tab-btn');
            if (tabButtons) {
                tabButtons.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        const tabName = this.getAttribute('onclick').match(/openTab\(event,\s*['"](.+?)['"]\)/)[1];
                        openTab(e, tabName);
                    });
                });
            }

            // Password validation initialization if needed
            if (typeof initializePasswordValidation === 'function' && document.getElementById('passwordForm')) {
                initializePasswordValidation();
            }

            // Check if URL has tab fragment and open that tab
            checkUrlForTab();
        };

        // Add the openTab function to the global scope
        window.openTab = function(evt, tabName) {
            var i, tabContent, tabBtns;
            
            // Hide all tab content
            tabContent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabContent.length; i++) {
                tabContent[i].className = tabContent[i].className.replace(" active", "");
            }
            
            // Remove active class from tab buttons
            tabBtns = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tabBtns.length; i++) {
                tabBtns[i].className = tabBtns[i].className.replace(" active", "");
            }
            
            // Show the selected tab and add active class to the button
            document.getElementById(tabName).className += " active";
            evt.currentTarget.className += " active";
        };

        // Add checkUrlForTab function to global scope
        window.checkUrlForTab = function() {
            let hash = window.location.hash;
            if (hash) {
                hash = hash.substring(1); // Remove the # character
                const tabElement = document.getElementById(hash);
                if (tabElement) {
                    const tabBtns = document.getElementsByClassName("tab-btn");
                    for (let i = 0; i < tabBtns.length; i++) {
                        if (tabBtns[i].getAttribute("onclick").includes(hash)) {
                            tabBtns[i].click();
                            break;
                        }
                    }
                }
            }
        };

        // Add this to your existing JavaScript
        document.getElementById('updateCandidateRoles').addEventListener('click', function() {
            const button = this;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

            fetch('update_candidate_roles.php?format=json', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                    } else {
                        showNotification(data.message || 'Error updating roles', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while updating roles', 'error');
                })
                .finally(() => {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-sync"></i> Update Candidate Roles';
                });
        });
    </script>

 
</body>
</html>