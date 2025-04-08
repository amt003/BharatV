        <?php
        session_start();
       include 'db.php';
        
       if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: loginadmin.php");
        exit();
    }

        // Initialize variables
        $wardName = "";
$errorMessage = "";
$successMessage = "";
$passwordSuccessMessage = '';
$passwordErrorMessage = '';
$userId = $_SESSION['user_id'];

// Handle password verification AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_password'])) {
    $currentPassword = $_POST['current_password'];
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (password_verify($currentPassword, $user['password'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
    }
    exit();
}

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate passwords
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $passwordErrorMessage = "All fields are required";
    } elseif ($newPassword !== $confirmPassword) {
        $passwordErrorMessage = "New passwords do not match";
    } elseif (strlen($newPassword) < 6) {
        $passwordErrorMessage = "New password must be at least 6 characters long";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (password_verify($currentPassword, $user['password'])) {
            // Hash new password and update
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->bind_param("si", $hashedPassword, $userId);
            
            if ($updateStmt->execute()) {
                $passwordSuccessMessage = "Password updated successfully";
            } else {
                $passwordErrorMessage = "Error updating password";
            }
        } else {
            $passwordErrorMessage = "Current password is incorrect";
        }
    }
}

// Handle add ward form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_ward"])) {
    // Get ward name from form
    $wardName = trim($_POST["ward_name"]);
    
    // Validate input
    if (empty($wardName)) {
        $errorMessage = "Ward name cannot be empty";
    } elseif (strlen($wardName) < 3) {
        $errorMessage = "Ward name must be at least 3 characters";
    } elseif (strlen($wardName) > 100) {
        $errorMessage = "Ward name cannot exceed 100 characters";
        } else {
            // Prepare and execute the SQL statement
            $stmt = $conn->prepare("INSERT INTO wards (ward_name) VALUES (?)");
            $stmt->bind_param("s", $wardName);
            
            if ($stmt->execute()) {
                $successMessage = "Ward added successfully!";
            $wardName = ""; // Clear the form
            } else {
                $errorMessage = "Error: " . $stmt->error;
            }
            
        // Close statement
            $stmt->close();
    }
}

// Get existing wards
$wardsQuery = "SELECT ward_id, ward_name FROM wards ORDER BY ward_name";
$wardsResult = $conn->query($wardsQuery);
?>

<div class="main-container">

    
    <div class="tabs">
        <button class="tab-btn active" onclick="openTab(event, 'wards-tab')">Ward Management</button>
        <button class="tab-btn" onclick="openTab(event, 'password-tab')">Change Password</button>
    </div>
    
    <div id="wards-tab" class="tab-content active">
        <div class="container">
            <h2>Ward Management</h2>
            
            <!-- Display error or success message -->
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($successMessage)): ?>
                <div class="success-message"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>Add New Ward</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="wardForm">
                                <div class="form-group">
                                    <label for="ward_name">Ward Name:</label>
                                    <input type="text" id="ward_name" name="ward_name" value="<?php echo htmlspecialchars($wardName); ?>" 
                                        placeholder="Enter ward name" required>
                                    <div id="wardNameError" class="error" style="display: none;">
                                        Ward name must be between 3 and 100 characters
                                    </div>
                                </div>
                                <button type="submit" name="add_ward" id="submitButton">Add Ward</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>Existing Wards</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($wardsResult && $wardsResult->num_rows > 0): ?>
                                <div class="ward-list">
                                    <table class="ward-table">
                                        <thead>
                                            <tr>
                                               
                                                <th>Ward Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($ward = $wardsResult->fetch_assoc()): ?>
                                                <tr>
                                                   
                                                    <td><?php echo htmlspecialchars($ward['ward_name']); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p>No wards found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="password-tab" class="tab-content">
        <div class="settings-container">
            <h2>Change Admin Password</h2>
            
            <?php if ($passwordSuccessMessage): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($passwordSuccessMessage); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($passwordErrorMessage): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($passwordErrorMessage); ?>
                </div>
            <?php endif; ?>

            <div class="settings-section">
                <form method="POST" class="password-form" id="passwordForm">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                        <div class="validation-message" id="current_password_message"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <div class="validation-message" id="new_password_message"></div>
                        <div class="password-strength" id="password_strength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <div class="validation-message" id="confirm_password_message"></div>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn-update" id="submit_password">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <style>
    .main-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: Arial, sans-serif;
    }
       
        .container {
        background-color: #ffffff;
        border-radius: 8px;
        padding: 25px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    

    
    h2 {
        color: #2E7D32;
        margin-bottom: 20px;
        font-size: 24px;
        font-weight: bold;
        border-bottom: 2px solid #e0e0e0;
        padding-bottom: 10px;
        text-align: center;
    }
    
    h3 {
        color: #2E7D32;
        font-size: 20px;
        margin: 0;
        font-weight: bold;
    }
    
    .tabs {
        display: flex;
        margin-bottom: 20px;
        border-bottom: 1px solid #ddd;
    }
    
    .tab-btn {
        padding: 12px 25px;
        background-color: #f5f5f5;
        border: 1px solid #ddd;
        border-bottom: none;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        border-radius: 8px 8px 0 0;
        margin-right: 5px;
        transition: all 0.3s ease;
        color: #555;
    }
    
    .tab-btn:hover {
        background-color: #e0f2f1;
        color: #2E7D32;
    }
    
    .tab-btn.active {
        background-color: #2E7D32;
        color: white;
        border-color: #2E7D32;
    }
    
    .tab-content {
        display: none;
        padding: 20px 0;
    }
    
    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -15px;
    }
    
    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0 15px;
        box-sizing: border-box;
    }
    
    .card {
        background-color: #fff;
        border-radius: 8px;
            margin-bottom: 20px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
        overflow: hidden;
    }
    
    .card-header {
        background-color: #f5f5f5;
        padding: 15px 20px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .card-body {
        padding: 20px;
    }
    
        .form-group {
        margin-bottom: 20px;
        }
    
        label {
            display: block;
        margin-bottom: 8px;
            font-weight: bold;
        color: #333;
        font-size: 15px;
        }
    
    input[type="text"],
    input[type="password"] {
            width: 100%;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 6px;
            font-size: 16px;
        box-sizing: border-box;
        transition: border-color 0.3s ease;
        background-color: #f9f9f9;
    }
    
    input[type="text"]:focus,
    input[type="password"]:focus {
        border-color: #2E7D32;
        outline: none;
        box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
        background-color: #fff;
    }
    
    button, 
    .btn-update {
        background-color: #2E7D32;
            color: white;
            border: none;
        padding: 12px 20px;
        border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    button:hover,
    .btn-update:hover {
        background-color: #1B5E20;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    button:active,
    .btn-update:active {
        transform: translateY(0);
    }
    
    .error,
    .error-message,
    .alert-error {
        color: #721c24;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 6px;
        font-weight: 500;
    }
    
    .success-message,
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 6px;
        font-weight: 500;
    }
    
    input.invalid {
        border-color: #dc3545;
        background-color: #fff8f8;
    }
    
    .validation-message {
        font-size: 14px;
        min-height: 20px;
        margin-top: 6px;
        font-weight: 500;
    }
    
    .validation-message.error {
        color: #dc3545;
    }
    
    .validation-message.success {
        color: #28a745;
    }
    
    .password-strength {
        height: 6px;
        margin-top: 10px;
        border-radius: 3px;
        background: #e9ecef;
        overflow: hidden;
    }
    
    .password-strength-bar {
        height: 100%;
        width: 0;
        transition: all 0.3s ease;
        border-radius: 3px;
    }
    
    .password-strength.weak .password-strength-bar {
        width: 33.33%;
        background: #dc3545;
    }
    
    .password-strength.medium .password-strength-bar {
        width: 66.66%;
        background: #ffc107;
    }
    
    .password-strength.strong .password-strength-bar {
        width: 100%;
        background: #28a745;
    }
    
    .ward-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    
    .ward-table th,
    .ward-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
        color:black;
    }
    
    .ward-table th {
        background-color: #f5f5f5;
        font-weight: bold;
        color: #333;
    }
    
    .ward-table tr:hover {
        background-color: #f9f9f9;
    }
    
    .settings-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .settings-section {
        background: white;
        border-radius: 8px;
        padding: 25px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #e0e0e0;
    }
    
    .password-form {
        max-width: 500px;
        margin: 0 auto;
    }
    
    .btn-update {
        margin-top: 15px;
        min-width: 150px;
    }
    
    .btn-update:disabled {
        background-color: #cccccc;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    /* Responsive styles */
    @media (max-width: 768px) {
        .row {
            flex-direction: column;
        }
        
        .col-md-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        .tabs {
            flex-direction: column;
        }
        
        .tab-btn {
            width: 100%;
            margin-bottom: 5px;
            border-radius: 5px;
        }
        
        input[type="text"],
        input[type="password"] {
            font-size: 14px;
            padding: 10px 12px;
        }
        
        button, 
        .btn-update {
            width: 100%;
            padding: 12px 0;
        }
        }
    </style>

<script>
    function openTab(evt, tabName) {
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
    }
    
    // Make functions available to the global scope (window)
    window.openTab = openTab;
    
    document.addEventListener('DOMContentLoaded', function() {
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
        
        // Password validation functionality
        if (typeof initializePasswordValidation === 'function') {
            initializePasswordValidation();
        }
        
        // Check if URL has tab fragment and open that tab
        checkUrlForTab();
    });
    
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

        function validateForm() {
            let isValid = true;
            const currentValue = currentPassword.value;
            const newValue = newPassword.value;
            const confirmValue = confirmPassword.value;

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

            // Disable submit button if form is invalid
            if (submitButton) {
                submitButton.disabled = !isValid;
            }

            return isValid;
        }

        // Add event listeners for real-time validation
        if (currentPassword) currentPassword.addEventListener('input', validateForm);
        if (newPassword) newPassword.addEventListener('input', validateForm);
        if (confirmPassword) confirmPassword.addEventListener('input', validateForm);

        // Form submission handler
        passwordForm.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            
            e.preventDefault();
            console.log("Form is valid, submitting via AJAX");
            
            const formData = new FormData(this);
            formData.append('change_password', '1');
            
            // First verify the current password
            const verifyData = new FormData();
            verifyData.append('verify_password', '1');
            verifyData.append('current_password', formData.get('current_password'));
            
            fetch('add_wards.php', {
                method: 'POST',
                body: verifyData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log("Current password verified, submitting new password");
                    // If current password is correct, submit the form
                    fetch('add_wards.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        console.log("Password update response received");
                        // Remove all validation elements and event listeners
                        if (currentPassword) currentPassword.removeEventListener('input', validateForm);
                        if (newPassword) newPassword.removeEventListener('input', validateForm);
                        if (confirmPassword) confirmPassword.removeEventListener('input', validateForm);
                        
                        // Clear validation messages and styles
                        [currentPassword, newPassword, confirmPassword].forEach(input => {
                            if (input) {
                                input.className = input.className.replace(' error', '').replace(' success', '');
                                const messageElement = document.getElementById(input.id + '_message');
                                if (messageElement) messageElement.textContent = '';
                            }
                        });
                        
                        // Remove password strength indicator
                        if (passwordStrength) {
                            passwordStrength.innerHTML = '';
                        }
                        
                        // Show success message
                        const successMessage = document.createElement('div');
                        successMessage.className = 'alert alert-success';
                        successMessage.innerHTML = '<i class="fas fa-check-circle"></i> Password updated successfully!';
                        passwordForm.insertAdjacentElement('beforebegin', successMessage);
                        
                        // Clear form
                        passwordForm.reset();
                        
                        // Remove success message after 3 seconds
                        setTimeout(() => {
                            successMessage.remove();
                        }, 3000);
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
        
        // Run initial validation
        validateForm();
    }
    
    // Make functions available to the global scope (window)
    window.initializePasswordValidation = initializePasswordValidation;
    
    // Check if URL has tab fragment and open that tab
    function checkUrlForTab() {
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
    }
    
    // Make function available to the global scope (window)
    window.checkUrlForTab = checkUrlForTab;
    
    // Run when page loads
    window.onload = checkUrlForTab;
</script>