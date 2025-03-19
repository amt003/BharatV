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

        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
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
            color:rgb(69, 140, 71);
        }

        .stat-card i {
            font-size: 24px;
            color: #4CAF50;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: rgb(69, 140, 71) ;
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
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo-container">
        <img src="assets/logo.jpg" alt="BharatV Logo">
    </div>
    <ul class="nav-links">
        
        <li><button class="nav-btn" data-page="approvals.php"><i class="fas fa-users"></i>Manage Users</button></li>
        <li><button class="nav-btn" data-page="manage_candidates.php"><i class="fas fa-user-tie"></i>View Candidates</button></li>
        <li><a class="nav-btn" href="manage_elections.php"><i class="fas fa-vote-yea"></i>Manage Elections</a></li>
        <li><button class="nav-btn" data-page="view_results.php"><i class="fas fa-chart-bar"></i>View Results</button></li>
        <li><button class="nav-btn" data-page="add_wards.php"><i class="fas fa-cog"></i>Add wards</button></li>
    </ul>
</div>



<div class="main-content">
    <div class="header">
        <h2 class="welcome-text">Welcome, Admin!</h2>
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
            <div class="stat-label">Total Contestants</div>
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
        });
//dynamically load pages
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
    // Get form elements
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const form = document.querySelector('form');
    const checkboxes = document.querySelectorAll('input[name="ward_ids[]"]');
    
    // Get today's date in YYYY-MM-DD format
    const today = new Date().toISOString().split('T')[0];
    
    // Set min attribute for date inputs
    startDateInput.min = today;
    endDateInput.min = today;
    
    // Set default dates
    startDateInput.value = today;
    endDateInput.value = today;
    
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

    // Validate description (not required but min length if provided)
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
    // Get form elements
    const editForm = document.querySelector('#editModal form');
    const titleInput = document.getElementById('edit_title');
    const descriptionInput = document.getElementById('edit_description');
    const startDateInput = document.getElementById('edit_start_date');
    const endDateInput = document.getElementById('edit_end_date');
    const checkboxes = document.querySelectorAll('#editModal input[name="ward_ids[]"]');

    // Get today's date in YYYY-MM-DD format
    const today = new Date().toISOString().split('T')[0];

    // Set min attributes for date inputs
    startDateInput.min = today;
    endDateInput.min = today;

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
        if (titleInput.value.trim().length < 5) {
            showError(titleInput, 'Title must be at least 5 characters long');
            return false;
        }
        return true;
    }

    // Validate description (optional but min length if provided)
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
        const container = document.querySelector('#editModal .checkbox-group').parentElement;
        clearError(container);
        let isChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);

        if (!isChecked) {
            showError(container, 'Please select at least one ward');
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

        if (!startDate) {
            showError(startDateInput, 'Start date is required');
            return false;
        }

        if (!endDate) {
            showError(endDateInput, 'End date is required');
            return false;
        }

        if (startDate < today) {
            showError(startDateInput, 'Start date cannot be in the past');
            return false;
        }

        if (endDate <= startDate) {
            showError(endDateInput, 'End date must be after start date');
            return false;
        }

        return true;
    }

    // Live validation event listeners
    titleInput.addEventListener('blur', validateTitle);
    titleInput.addEventListener('input', function() {
        if (titleInput.value.trim().length >= 5) clearError(titleInput);
    });

    descriptionInput.addEventListener('blur', validateDescription);
    descriptionInput.addEventListener('input', function() {
        if (descriptionInput.value.trim().length >= 10 || descriptionInput.value.trim() === '') {
            clearError(descriptionInput);
            }
    });

    checkboxes.forEach(checkbox => checkbox.addEventListener('change', validateWards));
    startDateInput.addEventListener('change', validateDates);
    endDateInput.addEventListener('change', validateDates);

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
    function initializeApprovalForms() {
        document.querySelectorAll('.approval-form').forEach(form => {
            // Handle approve button
            form.querySelector('.btn-approve').addEventListener('click', function() {
                handleApproval(form, 'approve', this);
            });

            // Handle reject button
            form.querySelector('.btn-reject').addEventListener('click', function() {
                if (confirm('Are you sure you want to reject this user?')) {
                    handleApproval(form, 'reject', this);
                }
            });
        });
    }

    function handleApproval(form, action, button) {
        // Show loading state
        const originalText = button.textContent;
        button.textContent = 'Processing...';
        button.disabled = true;

        // Create FormData and append required data
        const formData = new FormData();
        formData.append('user_id', form.querySelector('[name="user_id"]').value);
        formData.append('action', action);

        // Log the data being sent (for debugging)
        console.log('Sending data:', {
            user_id: formData.get('user_id'),
            action: formData.get('action')
        });

        fetch('process_approval.php', {
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
            console.log('Response:', data); // For debugging
            
            if (data.success) {
                showNotification(data.message, 'success');
                // Reload the approvals page after successful action
                setTimeout(() => loadPage('approvals.php'), 1000);
            } else {
                showNotification(data.message || 'Error processing request', 'error');
                // Reset button state
                button.textContent = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error processing request. Please try again.', 'error');
            // Reset button state
            button.textContent = originalText;
            button.disabled = false;
        });
    }

    function showNotification(message, type = 'success') {
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
    }

    // Modify your existing loadPage function
    function loadPage(page) {
        fetch(page)
            .then(response => response.text())
            .then(data => {
                const contentContainer = document.querySelector('.main-content');
                contentContainer.innerHTML = data;
                
                // Initialize approval forms if this is the approvals page
                if (page === 'approvals.php') {
                    initializeApprovalForms();
                }
            })
            .catch(error => {
                console.error('Error loading page:', error);
                showNotification('Error loading page', 'error');
            });
    }

    // Initialize approval forms when page loads
    initializeApprovalForms();

    // Initialize approval forms when navigation occurs
    document.querySelectorAll('.nav-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (this.dataset.page === 'approvals.php') {
                setTimeout(initializeApprovalForms, 100);
            }
        });
    });
});

    </script>

 
</body>
</html>