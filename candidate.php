<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BharatV Candidate Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">


<?php
include 'db.php';
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}
?>

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
        .brand {
        padding: 25px 30px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 20px;
    }

    .brand h1 {
        font-size: 32px;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--primary-orange);
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }

    .brand h2 {
        font-size: 16px;
        color: white;
        font-weight: 300;
    }

    /* Update header styles now that brand is removed */
    header {
        background: white;
        padding: 20px 30px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        border-bottom: 3px solid var(--light-green);
    }

    .user-section {
        display: flex;
        align-items: center;
    }
    .user-section p {
        font-size: 18px;
        font-weight: 500;
        color: var(--dark-green);
    }

    .user-section p span {
        color: white;
        padding: 8px 15px;
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--light-orange) 100%);
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-left: 8px;
    }

    .user-section a {
        color: white;
        padding: 8px 20px;
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--light-orange) 100%);
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .user-section a:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* Navigation container adjustment */
    .nav-container {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar nav styles */
    nav {
        width: 280px;
        background:  var(--primary-green) ;
        padding: 20px 0;
        min-height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        display: flex;
        flex-direction: column;
    }

    .nav-links {
        flex: 1;
        padding: 20px 0;
    }

    .nav-footer {
        padding: 20px;
        margin-right:24px;
    }

    .logout-btn {
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

    .logout-btn:hover {
        background: var(--primary-orange);
        transform: translateX(5px);
    }

    nav a {
        width: calc(100% - 30px);
        padding: 14px 20px;
        margin: 8px 15px;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        text-align: left;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 10px;
        display: block;
        text-decoration: none;
    }

    nav a:hover {
        background: var(--light-green);
      
    }

        .card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
            transition: transform 0.3s ease;
            border-top: 4px solid var(--primary-green);
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h2 {
            margin-bottom: 20px;
            color: var(--primary-green);
            font-size: 24px;
            font-weight: 600;
        }

        .card p {
            color: #4b5563;
            line-height: 1.6;
            font-size: 15px;
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

        /* Main content area */
        .main-content {
            flex: 1;
            margin-left: 280px; /* Same as nav width */
        }

        /* Header styles */
        header {
            background: white;
            padding: 20px 30px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            border-bottom: 3px solid var(--light-green);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .container {
            padding: 30px;
            background-color: #f8f9fa;
        }
    
  </style>
  </head>
  <body>
  <div class="nav-container">
    <!-- Navigation Links Sidebar with brand and logout -->
    <nav>
        <div class="brand">
            <div>
                <h1>BharatV</h1>
                <h2>Voting Made Simple</h2>
            </div>
        </div>
        <div class="nav-links">
            <a href="#" id="linkProfile">Profile</a>
            <a href="#" id="linkElectionUpdates">Election Updates</a>
            <a href="#" id="linkVote">Vote Now</a>
            <a href="#" id="linkResults">View Results</a>
            <a href="#" id="Candidateform">Candidate Application</a>
        </div>
        <div class="nav-footer">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <!-- Main Content Area including header -->
    <div class="main-content">
        <header>
            <div class="user-section">
                <p>Hello, <span><?php echo htmlspecialchars($_SESSION['name']); ?></span></p>
            </div>
        </header>
        <div class="container">
            <div id="dynamicContent" class="card">
                <h2>Welcome to BharatV Candidate Dashboard</h2>
                <p>Select an option from the left to view details.</p>
            </div>
        </div>
    </div>
</div>

   

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const dynamicContent = document.getElementById("dynamicContent");

        // Profile link
        document.getElementById("linkProfile").addEventListener("click", function(e) {
            e.preventDefault();
            fetch("profile.php")
                .then(response => response.text())
                .then(data => {
                    dynamicContent.innerHTML = data;
                })
                .catch(error => console.error("Error:", error));
        });

        // Election Updates link
        document.getElementById("linkElectionUpdates").addEventListener("click", function(e) {
            e.preventDefault();
            fetch("election-updates.php")
                .then(response => response.text())
                .then(data => {
                    dynamicContent.innerHTML = data;
                })
                .catch(error => console.error("Error:", error));
        });

        // Vote Now link
        document.getElementById("linkVote").addEventListener("click", function(e) {
            e.preventDefault();
            fetch("vote.php")
                .then(response => response.text())
                .then(data => {
                    dynamicContent.innerHTML = data;
                })
                .catch(error => console.error("Error:", error));
        });

        // Results link
        document.getElementById("linkResults").addEventListener("click", function(e) {
            e.preventDefault();
            fetch("results.php")
                .then(response => response.text())
                .then(data => {
                    dynamicContent.innerHTML = data;
                })
                .catch(error => console.error("Error:", error));
        });

        // Candidate Application form link
        document.getElementById("Candidateform").addEventListener("click", function(e) {
            e.preventDefault();
            fetch("candidate_application.php")
                .then(response => response.text())
                .then(data => {
                    dynamicContent.innerHTML = data;
                    // Important: Reinitialize validation after loading the form
                    initializeFormValidation();
                })
                .catch(error => {
                    console.error("Error:", error);
                    dynamicContent.innerHTML = "Error loading the application form. Please try again.";
                });
        });

        // Add this function to initialize form validation
        function initializeFormValidation() {
            console.log('Initializing form validation'); // Debug log
            
            const form = document.getElementById('candidateForm');
            if (!form) {
                console.error('Form not found after loading');
                return;
            }

            const inputs = form.querySelectorAll('input, textarea, select');
            console.log('Found inputs:', inputs.length); // Debug log

            // Validation rules
            const rules = {
                full_name: {
                    regex: /^[a-zA-Z\s]{3,50}$/,
                    message: 'Name should be 3-50 characters long and contain only letters'
                },
                age: {
                    regex: /^(2[5-9]|[3-9][0-9])$/,
                    message: 'Age must be 25 or above'
                },
                phone: {
                    regex: /^[0-9]{10}$/,
                    message: 'Please enter a valid 10-digit phone number'
                },
                education: {
                    regex: /.{3,}/,
                    message: 'Please enter education details (minimum 3 characters)'
                },
                address: {
                    regex: /.{10,}/,
                    message: 'Please enter complete address (minimum 10 characters)'
                }
            };

            // Add validation to each input
            inputs.forEach(input => {
                console.log('Setting up validation for:', input.id); // Debug log
                
                // Create error message element
                let errorDiv = input.parentElement.querySelector('.error-message');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    input.parentElement.appendChild(errorDiv);
                }

                // Add validation events
                ['input', 'blur'].forEach(eventType => {
                    input.addEventListener(eventType, function() {
                        validateInput(this);
                    });
                });
            });

            function validateInput(input) {
                console.log('Validating:', input.id); // Debug log
                
                const rule = rules[input.id];
                const errorDiv = input.parentElement.querySelector('.error-message');
                
                // Remove previous validation classes
                input.classList.remove('valid', 'invalid');
                
                if (!rule) return;

                // Validate
                const isValid = rule.regex.test(input.value);
                console.log('Validation result:', isValid); // Debug log

                if (!isValid) {
                    input.classList.add('invalid');
                    errorDiv.textContent = rule.message;
                    errorDiv.style.display = 'block';
                } else {
                    input.classList.add('valid');
                    errorDiv.style.display = 'none';
                }
            }
        }
    });
    </script>

    <!-- Add these styles -->
    <style>
    .form-group {
        position: relative;
        margin-bottom: 20px;
    }

    .error-message {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
        display: none;
        position: absolute;
        bottom: -18px;
    }

    input.invalid,
    textarea.invalid,
    select.invalid {
        border: 2px solid #dc3545 !important;
        background-color: #fff8f8 !important;
    }

    input.valid,
    textarea.valid,
    select.valid {
        border: 2px solid #28a745 !important;
        background-color: #f8fff8 !important;
    }

    .invalid + .error-message {
        display: block !important;
    }
    </style>
  </body>
</html>
