    <?php
    include 'db.php';
    session_start();
    if (!isset($_SESSION['name'])) {
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
        <title>BharatV Candidate Dashboard</title>
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

            .sidebar {
                width: 280px;
                background: var(--primary-green);
                color: white;
                padding: 20px 0;
                display: flex;
                flex-direction: column;
                height: 100vh;
                position: fixed;
            }

            .brand {
                padding: 25px 30px;
                flex-shrink: 0;
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
                flex-shrink: 0;
                margin-top: auto;
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

            .main-content {
                flex: 1;
                margin-left: 280px;
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

            /* Add these styles to your existing CSS */
            .election-report {
                padding: 20px;
                max-width: 800px;
                margin: 0 auto;
            }
            .election-report h2{
                color: var(--primary-green);
                font-size: 24px;
                font-weight: 600;
                text-align: center;
            }

            .report-header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid var(--light-green);
            }

            .performance-metrics {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .metric-card {
                background: white;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                text-align: center;
            }

            .large-number {
                font-size: 2em;
                font-weight: bold;
                color: var(--primary-green);
                margin: 10px 0;
            }

            .percentage {
                color: var(--primary-orange);
                font-weight: 500;
            }

            .vote-distribution {
                margin: 30px 0;
            }

            .vote-chart {
                background: #f0f0f0;
                height: 30px;
                border-radius: 15px;
                overflow: hidden;
                margin: 10px 0;
            }

            .vote-bar {
                height: 100%;
                background: linear-gradient(to right, var(--primary-green), var(--light-green));
                transition: width 1s ease-in-out;
            }

            .winner-badge {
                background: var(--primary-orange);
                color: white;
                padding: 10px 20px;
                border-radius: 20px;
                display: inline-block;
                margin: 20px 0;
                font-weight: bold;
            }

            .download-btn {
                background: var(--primary-green);
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                transition: background 0.3s ease;
            }

            .download-btn:hover {
                background: var(--dark-green);
            }

            /* Modal Styles */
            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0,0,0,0.4);
            }

            .modal-content {
                background-color: #fefefe;
                margin: 5% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
                max-width: 800px;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                position: relative;
            }

            .close-modal {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
                position: absolute;
                right: 20px;
                top: 10px;
            }

            .close-modal:hover,
            .close-modal:focus {
                color: black;
                text-decoration: none;
            }

            /* Report Styles */
            .election-report {
                padding: 20px;
                max-width: 800px;
                margin: 0 auto;
                font-family: 'Poppins', sans-serif;
            }

            .report-header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid var(--light-green);
            }

            .report-header h3 {
                color: var(--primary-green);
                font-size: 1.5em;
                margin-bottom: 10px;
            }

            .report-header p {
                margin: 5px 0;
                color: #555;
            }

            .performance-metrics {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .metric-card {
                background: white;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                text-align: center;
                border-top: 3px solid var(--primary-green);
            }

            .metric-card h4 {
                color: #555;
                margin-bottom: 10px;
                font-size: 1em;
            }

            .large-number {
                font-size: 2em;
                font-weight: bold;
                color: var(--primary-green);
                margin: 10px 0;
            }

            .percentage {
                color: var(--primary-orange);
                font-weight: 500;
            }

            .vote-distribution {
                margin: 30px 0;
                text-align: center;
            }

            .vote-distribution h4 {
                margin-bottom: 15px;
                color: #555;
            }

            .vote-chart {
                background: #f0f0f0;
                height: 30px;
                border-radius: 15px;
                overflow: hidden;
                margin: 10px 0;
                position: relative;
            }

            .vote-bar {
                height: 100%;
                background: linear-gradient(to right, var(--primary-green), var(--light-green));
                transition: width 1s ease-in-out;
            }

            .vote-percentage-label {
                text-align: right;
                font-weight: bold;
                color: var(--primary-green);
                margin-top: 5px;
            }

            .winner-badge-large {
                background: var(--primary-orange);
                color: white;
                padding: 10px 20px;
                border-radius: 20px;
                display: inline-block;
                margin: 20px auto;
                font-weight: bold;
                text-align: center;
                font-size: 1.2em;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            }

            .report-footer {
                text-align: center;
                margin-top: 30px;
            }

            .download-btn {
                background: var(--primary-green);
                color: white;
                border: none;
                padding: 12px 25px;
                border-radius: 5px;
                cursor: pointer;
                transition: background 0.3s ease;
                font-weight: 500;
            }

            .download-btn:hover {
                background: var(--dark-green);
                transform: translateY(-2px);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="brand">
                    <h1>BharatV</h1>
                    <p>Voting Made Simple</p>
                </div>
                <div class="nav-menu">
                    <button onclick="loadContent('candidate_profile')">Profile</button>
                    <button onclick="loadContent('election_updates')">Election Updates</button>
                    <button onclick="loadContent('results')">View Results</button>
                    <button onclick="loadContent('candidate_application')">Candidate Application</button>
                    <button onclick="loadContent('Application_status')">Application Status</button>
                    <button onclick="loadContent('election_reports')">My Election Reports</button>
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
                        <h2>Welcome to BharatV Candidate Dashboard</h2>
                        <p>Select an option from the menu to get started with your candidate experience.</p>

                             
                    </div>
                </div>
            </div>
        </div>
        
<script>
// Existing content loading function - modified to include form validation
function loadContent(section) {
    let contentDiv = document.getElementById('dynamicContent');
    
    switch(section) {
        case 'candidate_profile':
            fetch('candidate_profile.php')
                .then(response => response.text())
                .then(data => {
                    contentDiv.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = 'Error loading profile';
                });
            break;
        
        case 'election_updates':
            fetch('election_updates.php')
                .then(response => response.text())
                .then(data => {
                    contentDiv.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = 'Error loading election updates';
                });
            break;
        
        case 'results':
            fetch('results.php')
                .then(response => response.text())
                .then(data => {
                    contentDiv.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = 'Error loading results';
                });
            break;
        
        case 'candidate_application':
            fetch('candidate_application.php')
                .then(response => response.text())
                .then(data => {
                    contentDiv.innerHTML = data;
                    initializeFormValidation(); // Initialize form validation if needed
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = 'Error loading application form';
                });
            break;
        
        case 'Application_status':
            fetch('application_status.php')
                .then(response => response.text())
                .then(data => {
                    contentDiv.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = 'Error loading application status';
                });
            break;
        
        case 'election_reports':
            fetch('election_reports.php')
                .then(response => response.text())
                .then(data => {
                    contentDiv.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = 'Error loading election reports';
                });
            break;
        
        default:
            console.error('Invalid section:', section);
            contentDiv.innerHTML = 'Invalid section';
    }
}

// Existing logout function
function logout() {
    fetch('logout.php')
        .then(() => {
            window.location.href = 'login.php';
        });
}

// Existing modal related code
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
            modal.style.display = "flex";
        })
        .catch(error => console.error('Error fetching candidates:', error));
}

function closeModal() {
    document.getElementById("candidateModal").style.display = "none";
}


function initializeIndependentFields() {
    // Using event delegation since form is loaded dynamically
    document.getElementById('dynamicContent').addEventListener('change', function(e) {
        if (e.target && e.target.id === 'party_id') {
            const independentFields = document.getElementById('independent-fields');
            const independentInputs = independentFields?.querySelectorAll('input');
            
            if (e.target.value === 'independent') {
                independentFields?.classList.add('visible');
                independentInputs?.forEach(input => input.required = true);
                
                // Add validation rules for independent fields
                if (independentInputs) {
                    fields.independent_party_name = {
                        rules: {
                            required: true,
                            minLength: 2
                        },
                        message: 'Please enter a valid party name'
                    };
                    fields.independent_party_symbol = {
                        rules: {
                            required: true,
                            fileType: ['image/jpeg', 'image/png', 'image/gif'],
                            maxSize: 5 * 1024 * 1024 // 5MB
                        },
                        message: 'Please upload a valid party symbol (JPG, PNG, GIF) under 5MB'
                    };
                }
            } else {
                independentFields?.classList.remove('visible');
                independentInputs?.forEach(input => input.required = false);
                
                // Remove validation rules for independent fields
                delete fields.independent_party_name;
                delete fields.independent_party_symbol;
            }
        }
    });
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
    // Add preview for independent party symbol
    document.getElementById('dynamicContent').addEventListener('change', function(e) {
        if (e.target && e.target.id === 'independent_party_symbol') {
            const preview = document.getElementById('party_symbol_preview');
            const previewImg = document.getElementById('symbol_preview_image');
            
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        }
    });
}
// New form validation function
function initializeFormValidation() {
    const form = document.querySelector('form');
    const fields = {

        profile_photo: {
            rules: {
                required: true,
                fileType: ['image/jpeg', 'image/png', 'image/gif'],
                maxSize: 5 * 1024 * 1024 // 5MB
            },
            message: 'Please upload a valid image file (JPG, PNG, GIF) under 5MB'
        },
        full_name: {
            rules: {
                required: true,
                minLength: 2,
                pattern: /^[A-Za-z\s]+$/
            },
            message: 'Please enter a valid name (letters and spaces only)'
        },
        age: {
            rules: {
                required: true,
                min: 25,
                max: 100
            },
            message: 'Age must be between 25 and 100'
        },
        phone: {
            rules: {
                required: true,
                pattern: /^[6789]\d{9}$/
            },
            message: 'Please enter a valid 10-digit phone number and start with 6 or 7 or 8 or 9'
        },
        education: {
            rules: {
                required: true,
                minLength: 2
            },
            message: 'Please enter your educational qualification'
        },
        education_proof: {
            rules: {
                required: true,
                fileType: ['application/pdf'],
                maxSize: 5 * 1024 * 1024 // 5MB
            },
            message: 'Please upload a valid education proof pdf  under 5MB'
        },
        address: {
            rules: {
                required: true,
                minLength: 10
            },
            message: 'Please enter a complete address (minimum 10 characters)'
        },
        occupation: {
            rules: {
                required: true,
                minLength: 2
            },
            message: 'Please enter your current occupation'
        },
        political_experience: {
            rules: {
                minLength: 0
            },
            message: 'Please provide details of your political experience'
        },
        aadhar_proof: {
            rules: {
                required: true,
                fileType: ['application/pdf'],
                maxSize: 5 * 1024 * 1024 // 5MB
            },
            message: 'Please upload a valid Aadhar proof pdf  under 5MB'
        },
        ward_id: {
            rules: {
                required: true
            },
            message: 'Please select a ward'
        },
        party_id: {
            rules: {
                required: true
            },
            message: 'Please select a party'
        },
        election_id: {
            rules: {
                required: true
            },
            message: 'Please select an election'
        }
    };

    function createErrorElement(field) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '14px';
        errorDiv.style.marginTop = '5px';
        errorDiv.style.display = 'none';
        field.parentNode.appendChild(errorDiv);
        return errorDiv;
    }

    function validateField(field, rules, errorElement) {
        const value = field.value;
        let isValid = true;
        let errorMessage = '';

        if (field.type === 'file' && field.files.length > 0) {
            const file = field.files[0];
            if (rules.fileType && !rules.fileType.includes(file.type)) {
                isValid = false;
                errorMessage = 'Invalid file type';
            }
            if (rules.maxSize && file.size > rules.maxSize) {
                isValid = false;
                errorMessage = 'File size too large';
            }
        } else {
            if (rules.required && !value) {
                isValid = false;
                errorMessage = 'This field is required';
            }
            if (rules.minLength && value.length < rules.minLength) {
                isValid = false;
                errorMessage = `Minimum ${rules.minLength} characters required`;
            }
            if (rules.pattern && !rules.pattern.test(value)) {
                isValid = false;
                errorMessage = fields[field.name].message;
            }
            if (rules.min && (parseInt(value) < rules.min)) {
                isValid = false;
                errorMessage = `Minimum value is ${rules.min}`;
            }
            if (rules.max && (parseInt(value) > rules.max)) {
                isValid = false;
                errorMessage = `Maximum value is ${rules.max}`;
            }
        }

        if (!isValid) {
            field.style.borderColor = '#dc3545';
            errorElement.textContent = errorMessage;
            errorElement.style.display = 'block';
        } else {
            field.style.borderColor = '#28a745';
            errorElement.style.display = 'none';
        }

        return isValid;
    }

    if (form) {
        // Setup validation for each field
        Object.keys(fields).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                const errorElement = createErrorElement(field);

        // Add preview functionality for profile photo
        if (fieldName === 'profile_photo') {
            field.addEventListener('change', function(e) {
                const preview = document.getElementById('profile_photo_preview');
                const previewImg = document.getElementById('preview_image');
                
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                } else {
                    preview.style.display = 'none';
                }
            });
        }

          // Add preview functionality for Aadhar proof
          if (fieldName === 'aadhar_proof') {
                    field.addEventListener('change', function(e) {
                        const previewContainer = document.getElementById('aadhar_proof_preview');
                        if (!previewContainer) {
                            // Create preview container if it doesn't exist
                            const container = document.createElement('div');
                            container.id = 'aadhar_proof_preview';
                            container.style.marginTop = '10px';
                            this.parentNode.appendChild(container);
                        }
                        
                        if (this.files && this.files[0]) {
                            const file = this.files[0];
                            if (file.type === 'application/pdf') {
                                // Create an iframe for PDF preview
                                const preview = document.getElementById('aadhar_proof_preview');
                                preview.innerHTML = `
                                    <p style="margin-bottom: 5px;">Selected file: ${file.name}</p>
                                    <iframe 
                                        src="${URL.createObjectURL(file)}" 
                                        width="100%" 
                                        height="500px" 
                                        style="border: 1px solid #ddd; border-radius: 4px;">
                                    </iframe>`;
                            }
                        }
                    });
                }

                 

                // Validate on input/change
                const eventType = field.type === 'file' ? 'change' : 'input';
                field.addEventListener(eventType, () => {
                    validateField(field, fields[fieldName].rules, errorElement);
                });
            }
        });

        // Form submission handler
        form.addEventListener('submit', function(e) {
            let isValid = true;

            Object.keys(fields).forEach(fieldName => {
                const field = form.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    const errorElement = field.parentNode.querySelector('.error-message');
                    if (!validateField(field, fields[fieldName].rules, errorElement)) {
                        isValid = false;
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
                const firstError = form.querySelector('.error-message[style="display: block;"]');
                if (firstError) {
                    firstError.parentNode.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }
}
</script>


 <script>
    //script for showing tabs in candidate_profile.php
            function showTab(event, tabId) {
    // Hide all tabs
    document.querySelectorAll('.history-tab').forEach(tab => {
        tab.style.display = 'none';
    });

    // Show selected tab
    document.getElementById(tabId).style.display = 'block';

    // Update tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    event.target.classList.add('active');
}
</script>
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
            <span class="close-modal">&times;</span>
            <div id="resultsContent"></div>
        </div>
    </div>
        <!--election updates page results script-->
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
                                <span class="label">of Total Votes</span>
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
function generateElectionReport(electionId) {
    console.log("Generating report for election ID:", electionId);
    
    // Show loading indicator
    const modal = document.getElementById('resultsModal');
    const modalContent = document.getElementById('resultsContent');
    modalContent.innerHTML = '<div style="text-align:center;padding:30px;"><p>Loading report...</p></div>';
    modal.style.display = 'block';
    
    fetch(`generate_election_report.php?election_id=${electionId}`)
        .then(response => response.json())
        .then(data => {
            console.log("Report data:", data);
            if (data.success) {
                displayElectionReport(data.report);
            } else {
                modalContent.innerHTML = `<div style="text-align:center;padding:30px;"><p>Error: ${data.message || 'Could not generate report'}</p></div>`;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            modalContent.innerHTML = `<div style="text-align:center;padding:30px;"><p>Error: ${error.message}</p></div>`;
        });
}

function displayElectionReport(reportData) {
    const modalContent = document.getElementById('resultsContent');
    
    let html = `
        <div class="election-report">
            <h2>Election Performance Report</h2>
            <div class="report-header">
                <h3>${reportData.election_title}</h3>
                <p>Ward: ${reportData.ward_name}</p>
                <p>Election Date: ${reportData.start_date}${reportData.end_date ? ' - ' + reportData.end_date : ''}</p>
                <p>Contested As: ${reportData.party_type === 'party' ? 'Party Candidate' : 'Independent Candidate'}</p>
                <p>Party: ${reportData.party_name}</p>
            </div>
            
            <div class="performance-metrics">
                <div class="metric-card">
                    <h4>Votes Received</h4>
                    <p class="large-number">${reportData.votes_received}</p>
                    <p class="percentage">${reportData.vote_percentage}% of total votes</p>
                </div>
                
                <div class="metric-card">
                    <h4>Position Secured</h4>
                    <p class="large-number">${reportData.position}</p>
                    <p>out of ${reportData.total_candidates} candidates</p>
                </div>
                
                <div class="metric-card">
                    <h4>Voter Turnout</h4>
                    <p class="percentage">${reportData.voter_turnout}%</p>
                    <p>${reportData.total_votes} out of ${reportData.total_registered_voters} registered voters</p>
                </div>
            </div>
            
            <div class="vote-distribution">
                <h4>Vote Distribution</h4>
                <div class="vote-chart">
                    <div class="vote-bar" style="width: ${reportData.vote_percentage}%"></div>
                </div>
                <p class="vote-percentage-label">${reportData.vote_percentage}%</p>
            </div>
            
            ${reportData.is_winner ? '<div class="winner-badge-large">Winner</div>' : ''}
            
            <div class="report-footer">
                <button onclick="downloadReport(${reportData.election_id})" class="download-btn">
                    Download Report PDF
                </button>
            </div>
        </div>
    `;
    
    modalContent.innerHTML = html;
}

function downloadReport(electionId) {
    window.location.href = 'download_report.php?election_id=' + electionId;
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('resultsModal');   
    var span = document.getElementsByClassName('close-modal')[0];
    
    span.onclick = function() {
        modal.style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
});
</script>

</body>
</html>