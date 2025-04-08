<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$successMessage = '';
$errorMessage = '';

// Handle AJAX password verification
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
        $errorMessage = "All fields are required";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = "New passwords do not match";
    } elseif (strlen($newPassword) < 6) {
        $errorMessage = "New password must be at least 6 characters long";
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
                $successMessage = "Password updated successfully";
            } else {
                $errorMessage = "Error updating password";
            }
        } else {
            $errorMessage = "Current password is incorrect";
        }
    }
}
?>

<div class="settings-container">
    <h2>Account Settings</h2>
    
    <?php if ($successMessage): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($successMessage); ?>
    </div>
        <?php endif; ?>
        
    <?php if ($errorMessage): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>

    <div class="settings-section">
        <h3>Change Password</h3>
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

<style>
.settings-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.settings-section {
    background: white;
    border-radius: 10px;
    padding: 25px;
    margin-top: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.settings-section h3 {
    color: var(--primary-green);
    margin-bottom: 20px;
    font-size: 1.5em;
}

.password-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 500;
    color: #333;
}

.form-group input {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.form-group input:focus {
    border-color: var(--primary-green);
    outline: none;
    box-shadow: 0 0 0 2px rgba(46, 125, 50, 0.1);
}

.form-group input.error {
    border-color: #dc3545;
}

.form-group input.success {
    border-color: #28a745;
}

.validation-message {
    font-size: 14px;
    min-height: 20px;
    margin-top: 4px;
}

.validation-message.error {
    color: #dc3545;
}

.validation-message.success {
    color: #28a745;
}

.password-strength {
    height: 4px;
    margin-top: 8px;
    border-radius: 2px;
    background: #e9ecef;
    overflow: hidden;
}

.password-strength-bar {
    height: 100%;
    width: 0;
    transition: all 0.3s ease;
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

.btn-update {
    background: var(--primary-green);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    align-self: flex-start;
}

.btn-update:hover {
    background: var(--dark-green);
}

.btn-update:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>

    <script>
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

        // Skip validation if all fields are empty (initial state or cleared form)
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
        
        const formData = new FormData(this);
        formData.append('change_password', '1'); // Make sure change_password is set
        
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
                // If current password is correct, submit the form
                fetch('settings.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    // Reset form fields before reinitializing
                    currentPassword.value = '';
                    newPassword.value = '';
                    confirmPassword.value = '';
                    
                    // Clear all validation messages
                    const messageElements = document.querySelectorAll('.validation-message');
                    messageElements.forEach(el => {
                        el.textContent = '';
                        el.className = 'validation-message';
                    });
                    
                    // Reset password strength
                    if (passwordStrength) {
                        passwordStrength.className = 'password-strength';
                        const strengthBar = passwordStrength.querySelector('.password-strength-bar');
                        if (strengthBar) {
                            strengthBar.style.width = '0';
                        }
                    }
                    
                    // Remove all success/error classes from inputs
                    document.querySelectorAll('input').forEach(input => {
                        input.className = input.className.replace(' error', '').replace(' success', '');
                    });
                    
                    // Reload the settings page to show success/error message
                    document.getElementById('dynamicContent').innerHTML = html;
                    
                    // Reinitialize password validation with a clean state
                    setTimeout(() => {
                        initializePasswordValidation();
                        // Extra check to ensure validation messages are hidden
                        const newMessageElements = document.querySelectorAll('.validation-message');
                        newMessageElements.forEach(el => {
                            el.textContent = '';
                            el.className = 'validation-message';
                        });
                    }, 100);
                });
            } else {
                // Show error message for incorrect current password
                const messageElement = document.getElementById('current_password_message');
                if (messageElement) {
                    messageElement.textContent = data.message;
                    messageElement.className = 'validation-message error';
                }
                document.getElementById('current_password').classList.add('error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error verifying password: ' + error.message);
        });
    });

    // Add form reset handler (if there's a reset button or form clear)
    passwordForm.addEventListener('reset', function() {
        clearValidationMessages();
    });
    
    // Clear validation messages for a clean initial state
    clearValidationMessages();
}

// Initialize password validation when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializePasswordValidation();
});
    </script>