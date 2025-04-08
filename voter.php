<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'voter') {
    header("Location: login.php");
    exit();
}

// Get user details (including profile photo)
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userProfilePhoto = $user['profile_photo'] ?? null; // Get the profile photo or set to null if not available


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
            position: relative;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--primary-green);
            color: white;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
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
            flex: 1;
            padding: 0 15px;
            overflow-y: auto;
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
            margin-top: auto;
            position: sticky;
            bottom: 0;
            background: var(--primary-green);
            width: 100%;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
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
            margin-left: 280px;
            flex: 1;
            min-height: 100vh;
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

.profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--light-green);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-left: 10px;
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

        /* Add these styles to your existing CSS */
        .hamburger-menu {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--dark-green);
            cursor: pointer;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .hamburger-menu:hover {
            color: var(--primary-orange);
            transform: scale(1.1);
        }

        .sidebar {
            transition: all 0.3s ease;
            width: 280px;
            min-width: 280px;
        }

        .sidebar.collapsed {
            margin-left: -280px;
        }

        .main-content {
            transition: all 0.3s ease;
            margin-left: 280px;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Show hamburger menu on all screen sizes */
        .hamburger-menu {
            display: block;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }
            
            .sidebar.collapsed {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.expanded {
                margin-left: 280px;
            }
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
                <button onclick="loadContent('voter_profile')">Profile</button>
                <button onclick="loadContent('election_updates')">Election Update</button>
                <button onclick="loadContent('results')">View Results</button>
                <button onclick="loadContent('settings')">Settings</button>
                
            </div>
            <div class="sidebar-logout">
                <button onclick="logout()">Logout</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <button id="sidebarToggle" class="hamburger-menu">☰</button>
                <p>Hello, 
                    <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <img class="profile-image" src="<?php echo $userProfilePhoto ? 'uploads/' . htmlspecialchars($userProfilePhoto) : 'uploads/default-avatar.png'; ?>" alt="Profile Image">
                </p>
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
    document.addEventListener('click', function(event) {
    // Your existing event delegation code
    if (event.target && event.target.id === 'toggleEdit') {
        document.getElementById('viewMode').style.display = 'none';
        document.getElementById('editMode').style.display = 'block';
    }
    
    if (event.target && event.target.id === 'cancelEdit') {
        document.getElementById('viewMode').style.display = 'block';
        document.getElementById('editMode').style.display = 'none';
    }
    
    // Add handler for the save button
    if (event.target && event.target.id === 'saveChanges') {
        // Get the form data
        const form = document.getElementById('profileUpdateForm');
        const formData = new FormData(form);
        formData.append('update_profile', '1'); // Add the form submission indicator
        
        // Send AJAX request
        fetch('update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const messageDiv = document.createElement('div');
                messageDiv.className = 'alert alert-success';
                messageDiv.textContent = data.message;
                
                // Insert message at the top of the form
                const sectionBody = document.querySelector('.section-body');
                sectionBody.insertBefore(messageDiv, sectionBody.firstChild);
                
                // Reload the profile data
                fetch('voter_profile.php')
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newProfileData = doc.querySelector('#viewMode').innerHTML;
                        document.querySelector('#viewMode').innerHTML = newProfileData;
                    })
                    .catch(error => console.error('Error reloading profile:', error));
                
                // Switch back to view mode
                document.getElementById('viewMode').style.display = 'block';
                document.getElementById('editMode').style.display = 'none';
                
                // Remove message after 3 seconds
                setTimeout(() => {
                    messageDiv.remove();
                }, 3000);
            } else {
                // Show error message
                alert(data.message || 'Error updating profile');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating profile. Please try again.');
        });
    }
});
</script>

<script>
    //password change 
// Password validation functionality
function initializePasswordValidation() {
    const passwordForm = document.getElementById('passwordForm');
    if (!passwordForm) return;

    const currentPassword = document.getElementById('current_password');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const submitButton = document.getElementById('submit_password');
    const passwordStrength = document.getElementById('password_strength');

    // Create password strength bar
    if (passwordStrength) {
        passwordStrength.innerHTML = '<div class="password-strength-bar"></div>';
    }

    function updatePasswordStrength(password) {
        if (!passwordStrength) return;
        
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;

        passwordStrength.className = 'password-strength';
        const strengthBar = passwordStrength.querySelector('.password-strength-bar');
        
        if (strength <= 1) {
            passwordStrength.classList.add('weak');
            strengthBar.style.width = '33.33%';
        } else if (strength <= 2) {
            passwordStrength.classList.add('medium');
            strengthBar.style.width = '66.66%';
        } else {
            passwordStrength.classList.add('strong');
            strengthBar.style.width = '100%';
        }
    }

    function showValidationMessage(element, message, isError = false) {
        const messageElement = document.getElementById(element.id + '_message');
        if (messageElement) {
            messageElement.textContent = message;
            messageElement.className = 'validation-message ' + (isError ? 'error' : 'success');
        }
        element.className = element.className.replace(' error', '').replace(' success', '') + 
                          (isError ? ' error' : ' success');
    }

    function clearValidationMessages() {
        // Clear all validation messages
        const inputs = [currentPassword, newPassword, confirmPassword];
        inputs.forEach(input => {
            if (input) {
                input.value = ''; // Clear the input values
                const messageElement = document.getElementById(input.id + '_message');
                if (messageElement) {
                    messageElement.textContent = '';
                    messageElement.className = 'validation-message';
                }
                input.className = input.className.replace(' error', '').replace(' success', '');
            }
        });

        // Reset password strength bar
        if (passwordStrength) {
            passwordStrength.className = 'password-strength';
            const strengthBar = passwordStrength.querySelector('.password-strength-bar');
            if (strengthBar) {
                strengthBar.style.width = '0';
            }
        }

        // Disable submit button
        if (submitButton) {
            submitButton.disabled = true;
        }
    }

    function validateForm(field = null) {
        let isValid = true;
        const currentValue = currentPassword.value;
        const newValue = newPassword.value;
        const confirmValue = confirmPassword.value;

        // Skip validation if all fields are empty (initial state or reset form)
        if (!currentValue && !newValue && !confirmValue) {
            // Just disable the button but don't show validation messages
            if (submitButton) {
                submitButton.disabled = true;
            }
            return false;
        }

        // If a specific field is provided, only validate that field
        if (field) {
            if (field === currentPassword) {
                // Validate current password
                if (currentValue.length < 6) {
                    showValidationMessage(currentPassword, 'Current password must be at least 6 characters', true);
                    isValid = false;
                } else {
                    showValidationMessage(currentPassword, 'Current password looks good');
                }
            } else if (field === newPassword) {
                // Validate new password
                if (newValue.length < 6) {
                    showValidationMessage(newPassword, 'New password must be at least 6 characters', true);
                    isValid = false;
                } else {
                    showValidationMessage(newPassword, 'New password looks good');
                    updatePasswordStrength(newValue);
                }
                
                // Also check confirm password match if it has a value
                if (confirmValue) {
                    if (newValue !== confirmValue) {
                        showValidationMessage(confirmPassword, 'Passwords do not match', true);
                        isValid = false;
                    } else {
                        showValidationMessage(confirmPassword, 'Passwords match');
                    }
                }
            } else if (field === confirmPassword) {
                // Validate confirm password
                if (newValue !== confirmValue) {
                    showValidationMessage(confirmPassword, 'Passwords do not match', true);
                    isValid = false;
                } else if (confirmValue.length >= 6) {
                    showValidationMessage(confirmPassword, 'Passwords match');
                }
            }
        } else {
            // Validate all fields (for form submission)
            
            // Validate current password
            if (currentValue.length < 6) {
                showValidationMessage(currentPassword, 'Current password must be at least 6 characters', true);
                isValid = false;
            } else {
                showValidationMessage(currentPassword, 'Current password looks good');
            }

            // Validate new password
            if (newValue.length < 6) {
                showValidationMessage(newPassword, 'New password must be at least 6 characters', true);
                isValid = false;
            } else {
                showValidationMessage(newPassword, 'New password looks good');
                updatePasswordStrength(newValue);
            }

            // Validate confirm password
            if (newValue !== confirmValue) {
                showValidationMessage(confirmPassword, 'Passwords do not match', true);
                isValid = false;
            } else if (confirmValue.length >= 6) {
                showValidationMessage(confirmPassword, 'Passwords match');
            }
        }

        // Disable submit button if form is invalid
        if (submitButton) {
            submitButton.disabled = !isValid;
        }

        return isValid;
    }

    // Add event listeners for real-time validation - only validate the changed field
    if (currentPassword) {
        currentPassword.addEventListener('input', () => validateForm(currentPassword));
        // Clear validation on focus
        currentPassword.addEventListener('focus', () => {
            const messageElement = document.getElementById('current_password_message');
            if (messageElement) {
                messageElement.textContent = '';
                messageElement.className = 'validation-message';
            }
            currentPassword.className = currentPassword.className.replace(' error', '').replace(' success', '');
        });
        // Validate on blur
        currentPassword.addEventListener('blur', () => {
            if (currentPassword.value) validateForm(currentPassword);
        });
    }
    
    if (newPassword) {
        newPassword.addEventListener('input', () => validateForm(newPassword));
        // Clear validation on focus
        newPassword.addEventListener('focus', () => {
            const messageElement = document.getElementById('new_password_message');
            if (messageElement) {
                messageElement.textContent = '';
                messageElement.className = 'validation-message';
            }
            newPassword.className = newPassword.className.replace(' error', '').replace(' success', '');
        });
        // Validate on blur
        newPassword.addEventListener('blur', () => {
            if (newPassword.value) validateForm(newPassword);
        });
    }
    
    if (confirmPassword) {
        confirmPassword.addEventListener('input', () => validateForm(confirmPassword));
        // Clear validation on focus
        confirmPassword.addEventListener('focus', () => {
            const messageElement = document.getElementById('confirm_password_message');
            if (messageElement) {
                messageElement.textContent = '';
                messageElement.className = 'validation-message';
            }
            confirmPassword.className = confirmPassword.className.replace(' error', '').replace(' success', '');
        });
        // Validate on blur
        confirmPassword.addEventListener('blur', () => {
            if (confirmPassword.value) validateForm(confirmPassword);
        });
    }

    // Form submission handler
    passwordForm.addEventListener('submit', function(e) {
        // Validate all fields for submission
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        
        e.preventDefault();
        console.log("Form is valid, submitting via AJAX");
        
        const formData = new FormData(this);
        formData.append('change_password', '1'); // Make sure this is added
        
        // First verify the current password
        const verifyData = new FormData();
        verifyData.append('verify_password', '1');
        verifyData.append('current_password', formData.get('current_password'));
        
        fetch('settings.php', {
            method: 'POST',
            body: verifyData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Current password verified, submitting new password");
                // If current password is correct, submit the form
                fetch('settings.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    console.log("Password update response received");
                    
                    // Reset form fields manually
                    currentPassword.value = '';
                    newPassword.value = '';
                    confirmPassword.value = '';
                    
                    // Clear all validation messages
                    const messageElements = document.querySelectorAll('.validation-message');
                    messageElements.forEach(el => {
                        el.textContent = '';
                        el.className = 'validation-message';
                    });
                    
                    // Reset password strength indicator
                    if (passwordStrength) {
                        passwordStrength.className = 'password-strength';
                        const strengthBar = passwordStrength.querySelector('.password-strength-bar');
                        if (strengthBar) {
                            strengthBar.style.width = '0';
                        }
                    }
                    
                    // Remove success/error classes from inputs
                    document.querySelectorAll('input').forEach(input => {
                        input.className = input.className.replace(' error', '').replace(' success', '');
                    });
                    
                    // Reload the settings page to show success/error message
                    document.getElementById('dynamicContent').innerHTML = html;
                    
                    // Reinitialize password validation with clean state
                    setTimeout(() => {
                        initializePasswordValidation();
                        // Extra check to ensure validation messages are hidden
                        const newMessageElements = document.querySelectorAll('.validation-message');
                        newMessageElements.forEach(el => {
                            el.textContent = '';
                            el.className = 'validation-message';
                        });
                    }, 100);
                })
                .catch(error => {
                    console.error('Error updating password:', error);
                    alert('Error updating password: ' + error.message);
                });
            } else {
                console.log("Current password verification failed");
                // Show error message for incorrect current password
                const messageElement = document.getElementById('current_password_message');
                if (messageElement) {
                    messageElement.textContent = data.message || 'Current password is incorrect';
                    messageElement.className = 'validation-message error';
                }
                document.getElementById('current_password').classList.add('error');
            }
        })
        .catch(error => {
            console.error('Error verifying password:', error);
            alert('Error verifying password: ' + error.message);
        });
    });
    
    // Add form reset handler
    passwordForm.addEventListener('reset', function() {
        clearValidationMessages();
    });
    
    // Clear validation messages on initial load
    clearValidationMessages();
    
    console.log("Password validation initialized");
}

function loadContent(page, electionId = null) {
    let url = `${page}.php`;
    
    if (page === 'fetch_candidates' && !electionId) {
        document.getElementById('dynamicContent').innerHTML = `
            <h2>View Candidates</h2>
            <p>Please select an election from the Election Updates page to view its candidates.</p>`;
        return;
    }

    if (electionId) {
        url += `?election_id=${electionId}`;
    }

    fetch(url)
        .then(response => response.text())
        .then(data => {
            document.getElementById('dynamicContent').innerHTML = data;
       
            if (page === 'vote') {
                initializeVoteForm();
            } else if (page === 'settings') {
                setTimeout(initializePasswordValidation, 100);
            }
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
            // Add this function to handle vote form submission
function initializeVoteForm() {
    const voteForm = document.querySelector('form[action="submit_vote.php"]');
    if (voteForm) {
        voteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            fetch('submit_vote.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Vote submitted successfully!');
                    loadContent('election_updates'); // Return to election updates
                } else {
                    alert(data.message || 'Error submitting vote');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting vote');
            });
        });
    }
}
        //modal    
        document.addEventListener("DOMContentLoaded", function () {
            document.body.addEventListener("click", function (event) {
                if (event.target.classList.contains("view-candidates-button")) {
                    let electionId = event.target.getAttribute("data-election-id");
                    openCandidateModal(electionId);
                }
            });
        });

        function openCandidateModal(electionId) {
            let modal = document.getElementById("candidateModal");
            let modalContent = document.getElementById("modalContent");

            fetch(`fetch_candidates.php?election_id=${electionId}`)
                .then(response => response.text())
                .then(data => {
                    modalContent.innerHTML = data;
                    modal.style.display = "flex"; // Show modal
                })
                .catch(error => console.error('Error fetching candidates:', error));
        }

        function closeModal() {
            document.getElementById("candidateModal").style.display = "none";
        }
    </script>
<!--candidate modal-->
<div id="candidateModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="modalContent">
            <!-- Candidate details will be loaded here -->
        </div>
    </div>
</div>

 <!-- election updates page results modal-->
 <div id="resultsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeResultsModal()">&times;</span>
            <div id="resultsContent"></div>
        </div>
    </div>
<!--election updates page results modal-->
<script>
function loadResults(electionId) {
    const formData = new FormData();
    formData.append('action', 'get_results');
    formData.append('election_id', electionId);

    fetch('election_results.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.message);
        } else {
            displayResults(data);
            openResultsModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading results');
    });
}

function displayResults(results) {
    const modalContent = document.getElementById('resultsContent');
    
    if (!results || !results.length) {
        alert('No results data available');
        return;
    }
    
    // Calculate total votes for the entire election
    const totalVotes = results.reduce((sum, result) => sum + parseInt(result.votes_received), 0);
    
    let html = `<h2>${results[0].Election_title} - Results</h2>`;
    
    // Group results by ward
    const wardResults = {};
    results.forEach(result => {
        if (!wardResults[result.ward_name]) {
            wardResults[result.ward_name] = [];
        }
        wardResults[result.ward_name].push(result);
    });
    
    for (const ward in wardResults) {
        const wardTotalVotes = wardResults[ward].reduce((sum, r) => sum + parseInt(r.votes_received), 0);
        const maxVotes = Math.max(...wardResults[ward].map(r => parseInt(r.votes_received)));
        
        html += `<div class="ward-results">
            <h3>${ward}</h3>
            <div class="ward-total-votes">Total Votes Cast: ${wardTotalVotes}</div>
            <div class="candidates-grid">`;
        
        wardResults[ward].forEach(result => {
            const isWinner = parseInt(result.votes_received) === maxVotes && parseInt(result.votes_received) > 0;
            const winnerClass = isWinner ? 'winner' : '';
            const votePercentage = wardTotalVotes > 0 
                ? ((result.votes_received / wardTotalVotes) * 100).toFixed(2)
                : 0;
            
            html += `
                <div class="candidate-card ${winnerClass}">
                    ${isWinner ? '<div class="winner-badge">Winner</div>' : ''}
                    <div class="candidate-details">
                        <h4>${result.name}</h4>
                        <p class="party-name">${result.is_independent === 1 ? 'Independent Candidate' : result.party_name}</p>
                        <div class="votes-info ${result.votes_received === '0' ? 'zero-votes' : ''}">
                            <div class="votes-count">
                                <span class="number">${result.votes_received}</span>
                                <span class="label">Votes Received</span>
                            </div>
                            <div class="vote-percentage">
                                <span class="percentage">${votePercentage}%</span>
                                <span class="label">of Ward Votes</span>
                            </div>
                            <div class="vote-bar">
                                <div class="vote-bar-fill" style="width: ${votePercentage}%"></div>
                            </div>
                        </div>
                    </div>
                </div>`;
        });
        
        html += '</div></div>';
    }
    
    modalContent.innerHTML = html;
}

function openResultsModal() {
    document.getElementById('resultsModal').style.display = 'block';
    document.body.classList.add('modal-open');
}

function closeResultsModal() {
    document.getElementById('resultsModal').style.display = 'none';
    document.body.classList.remove('modal-open');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('resultsModal');
    if (event.target == modal) {
        closeResultsModal();
    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to handle profile updates
    function handleProfileUpdate() {
        const saveChangesButton = document.getElementById('saveChanges');
        if (saveChangesButton) {
            saveChangesButton.addEventListener('click', function() {
                const form = document.getElementById('profileUpdateForm');
                if (!form) return;

                const formData = new FormData(form);
                formData.append('update_profile', '1');

                // Check if a file was selected
                const fileInput = document.getElementById('profile_photo');
                if (fileInput && fileInput.files.length > 0) {
                    formData.append('profile_photo', fileInput.files[0]);
                }

                fetch('update_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'alert alert-success';
                        messageDiv.textContent = data.message;
                        
                        // Insert message at the top of the form
                        const sectionBody = document.querySelector('.section-body');
                        if (sectionBody) {
                            sectionBody.insertBefore(messageDiv, sectionBody.firstChild);
                        }
                        
                        // Reload the profile data
                        loadProfileContent();
                        
                        // Update the header profile image if a profile photo was returned
                        if (data.profile_photo) {
                            const headerProfileImage = document.querySelector('.profile-image');
                            if (headerProfileImage) {
                                headerProfileImage.src = 'uploads/' + data.profile_photo;
                            }
                        }
                        
                        // Switch back to view mode
                        const viewMode = document.getElementById('viewMode');
                        const editMode = document.getElementById('editMode');
                        if (viewMode && editMode) {
                            viewMode.style.display = 'block';
                            editMode.style.display = 'none';
                        }
                        
                        // Remove message after 3 seconds
                        setTimeout(() => {
                            messageDiv.remove();
                        }, 3000);
                    } else {
                        // Show error message
                        alert(data.message || 'Error updating profile');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating profile. Please try again.');
                });
            });
        }
    }

    // Function to reload profile content
    function loadProfileContent() {
        fetch('voter_profile.php')
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Update the profile content
                const dynamicContent = document.getElementById('dynamicContent');
                if (dynamicContent) {
                    dynamicContent.innerHTML = doc.body.innerHTML;
                    handleProfileUpdate(); // Re-initialize event listeners
                }
            })
            .catch(error => console.error('Error reloading profile:', error));
    }

    // Initialize the profile update functionality
    handleProfileUpdate();
});
</script>
</body>
</html>