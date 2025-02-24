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
            }

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
                    <h1>BharatV</h1>
                    <p>Voting Made Simple</p>
                </div>
                <div class="nav-menu">
                    <button onclick="loadContent('candidate_profile')">Profile</button>
                    <button onclick="loadContent('election_updates')">Election Updates</button>
                    <button onclick="loadContent('vote')">Vote Now</button>
                    <button onclick="loadContent('results')">View Results</button>
                    <button onclick="loadContent('candidate_application')">Candidate Application</button>
                    <button onclick="loadContent('Application_status')">Application Status</button>

                </div>
                <div class="sidebar-logout">
                    <button onclick="logout()">Logout</button>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <div class="header">
                    <p>Hello, <span><?php echo htmlspecialchars($_SESSION['name']); ?></span></p>
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
function loadContent(page, electionId = null) {
    let url = `${page}.php`;

    // Append election ID if provided
    if (electionId) {
        url += `?election_id=${electionId}`;
    }

    fetch(url)
        .then(response => response.text())
        .then(data => {
            document.getElementById('dynamicContent').innerHTML = data;
            // Initialize form validation if the loaded page is candidate_application
            if (page === 'candidate_application') {
                initializeIndependentFields(); 
                initializeFormValidation();
            }
        })
        .catch(error => {
            console.error('Error loading content:', error);
        });
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

                 // Add preview functionality for education proof
          if (fieldName === 'education_proof') {
                    field.addEventListener('change', function(e) {
                        const previewContainer = document.getElementById('education_proof_preview');
                        if (!previewContainer) {
                            // Create preview container if it doesn't exist
                            const container = document.createElement('div');
                            container.id = 'education_proof_preview';
                            container.style.marginTop = '10px';
                            this.parentNode.appendChild(container);
                        }
                        
                        if (this.files && this.files[0]) {
                            const file = this.files[0];
                            if (file.type === 'application/pdf') {
                                // Create an iframe for PDF preview
                                const preview = document.getElementById('education_proof_preview');
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
                
                // Validate on blur
                field.addEventListener('blur', () => {
                    validateField(field, fields[fieldName].rules, errorElement);
                });

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


        
        </script>
        <div id="candidateModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <div id="modalContent">
                    <!-- Candidate details will be loaded here -->
                </div>
            </div>
        </div>

</body>
</html>