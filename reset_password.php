<?php
session_start();
include 'db.php';

// Check if user has verified their reset code
if (!isset($_SESSION['email'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = '';
$success = '';
$email = $_SESSION['email'];

// Password validation function
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return $errors;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validate password
    $passwordErrors = validatePassword($password);
    
    if (!empty($passwordErrors)) {
        $error = implode("<br>", $passwordErrors);
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Hash password and update database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear reset tokens
        $stmt = $conn->prepare("UPDATE users SET 
            password = ?, 
            reset_code = NULL, 
            reset_code_timestamp = NULL 
            WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        
        if ($stmt->execute()) {
            $success = "Password reset successful!";
            // Clear session variables
            unset($_SESSION['reset_verified']);
            unset($_SESSION['reset_email']);
            // Redirect to login after 3 seconds
            header("refresh:3;url=login.php");
        } else {
            $error = "Failed to reset password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - BharatV</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: linear-gradient(135deg, #f5f7fa 100%);
            padding: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .header img {
            max-height: 100px;
            max-width: 250px;
        }

        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .description {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background: #45a049;
        }

        .error {
            color: #dc3545;
            font-size: 14px;
            margin: 10px 0;
            text-align: center;
            padding: 10px;
            background-color: #fff8f8;
            border-radius: 4px;
        }

        .success {
            color: #28a745;
            font-size: 14px;
            margin: 10px 0;
            text-align: center;
            padding: 10px;
            background-color: #f8fff8;
            border-radius: 4px;
        }

        .password-requirements {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .requirement-item {
            color: #666;
            font-size: 12px;
            margin: 5px 0;
            list-style-type: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 2px;
            transition: all 0.3s ease;
            background-color: #ddd;
        }

        .strength-weak { 
            background-color: #dc3545; 
            width: 33%; 
        }

        .strength-medium { 
            background-color: #ffc107; 
            width: 66%; 
        }

        .strength-strong { 
            background-color: #28a745; 
            width: 100%; 
        }

        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-login a {
            color: #4CAF50;
            text-decoration: none;
            font-size: 14px;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="assets/logo.jpg" alt="BharatV Logo">
    </div>

    <div class="container">
        <h2>Reset Password</h2>
        <p class="description">Please enter your new password below.</p>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required>
                <div class="password-strength" id="passwordStrength"></div>
                <ul id="password-requirements" class="password-requirements">
                    <li class="requirement-item">At least 8 characters</li>
                    <li class="requirement-item">One uppercase letter</li>
                    <li class="requirement-item">One lowercase letter</li>
                    <li class="requirement-item">One number</li>
                    <li class="requirement-item">One special character</li>
                </ul>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit">Reset Password</button>
        </form>

        <div class="back-to-login">
            <a href="login.php">Back to Login</a>
        </div>
    </div>

    <script>
        function validatePasswordStrength(password) {
            const errors = [];
            
            if (password.length < 6) {
                errors.push("At least 6 characters");
            }
            if (!/[A-Z]/.test(password)) {
                errors.push("One uppercase letter");
            }
            if (!/[a-z]/.test(password)) {
                errors.push("One lowercase letter");
            }
            if (!/[0-9]/.test(password)) {
                errors.push("One number");
            }
            if (!/[!@#$%^&*()\-_=+{};:,<.>]/.test(password)) {
                errors.push("One special character");
            }
            
            return errors;
        }

        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const errors = validatePasswordStrength(password);
            const requirementsList = document.getElementById('password-requirements');
            const requirements = [
                { text: "At least 6 characters", met: password.length >= 6 },
                { text: "One uppercase letter", met: /[A-Z]/.test(password) },
                { text: "One lowercase letter", met: /[a-z]/.test(password) },
                { text: "One number", met: /[0-9]/.test(password) },
                { text: "One special character", met: /[!@#$%^&*()\-_=+{};:,<.>]/.test(password) }
            ];
            
            requirementsList.innerHTML = requirements.map(req => 
                `<li class="requirement-item" style="color: ${req.met ? '#28a745' : '#666'}">
                    ${req.met ? '✓' : '○'} ${req.text}
                </li>`
            ).join('');
            
            updateStrengthIndicator(5 - errors.length);
        });

        function updateStrengthIndicator(strength) {
            const strengthBar = document.getElementById('passwordStrength');
            const percentage = (strength / 5) * 100;
            
            strengthBar.style.width = `${percentage}%`;
            if (strength <= 2) {
                strengthBar.className = 'password-strength strength-weak';
            } else if (strength <= 4) {
                strengthBar.className = 'password-strength strength-medium';
            } else {
                strengthBar.className = 'password-strength strength-strong';
            }
        }
    </script>
</body>
</html>