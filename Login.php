<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $errors = [];

    // Validate input
    if (empty($email) || empty($password) || empty($role)) {
        $errors['login'] = "All fields are required";
    } else {
        // Check for admin login
        if ($role === 'returning-officer') {
            // Admin credentials - hardcoded
            $officerName = 'Returning Officer';
            $officerEmail = 'returningofficer@gmail.com';
            $officerPassword = 'returningofficer12345';

            if ($email === $officerEmail && $password===$officerPassword) {
      
                    $_SESSION['ro_id'] = 1;
                    $_SESSION['name'] = $officerName;
                    $_SESSION['role'] = 'returning_officer';
                    header("Location: officer.php");
                    exit();
                
            } else {
                $errors['login'] = "Invalid returning officer email";
            }
        }
        // Rest of your existing login logic for other roles
        else if ($role === 'party_admin') {
            // Party login
            $stmt = $conn->prepare("SELECT * FROM parties WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($party = $result->fetch_assoc()) {
                if (password_verify($password, $party['password'])) {
                    $_SESSION['user_id'] = $party['party_id'];
                    $_SESSION['name'] = $party['party_name'];
                    $_SESSION['role'] = 'party_admin';
                    $_SESSION['party_id'] = $party['party_id'];
                    header("Location: party_dashboard.php");
                    exit();
                } else {
                    $errors['login'] = "Invalid credentials";
                }
            } else {
                $errors['login'] = "Party not found";
            }
        } else {
            // Voter and Candidate login
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
            $stmt->bind_param("ss", $email, $role);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($user = $result->fetch_assoc()) {
                if (!$user['email_verified']) {
                    $errors['email'] = "Please verify your email first";
                } else if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    // Check approval status for both voters and candidates
                    $stmt = $conn->prepare("SELECT approved_by_admin FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user['id']);
                    $stmt->execute();
                    $approval_result = $stmt->get_result();
                    $approval_status = $approval_result->fetch_assoc();

                    if ($approval_status && $approval_status['approved_by_admin'] === '1') {
                        // Redirect to respective dashboard
                        $dashboard = ($role === 'candidate') ? 'candidate.php' : 'voter.php';
                        header("Location: " . $dashboard);
                    } else {
                        header("Location: check_status.php");
                    }
                    exit();
                } else {
                    $errors['login'] = "Invalid credentials";
                }
            } else {
                $errors['login'] = "User not found";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BharatV</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: white;
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

        .page-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 500px;
            background: white;
            box-shadow: 0px 4px 6px rgba(0,0,0,0.6);
            border-radius: 8px;
            overflow: hidden;
            padding: 30px;
            animation: fadeIn 0.6s ease-out;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }
        select{
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        input.error {
            border-color: #dc3545;
        }

        button {
            background: green;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 20px;
        }

        button:hover {
            background: rgb(42, 189, 12);
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
            text-align: center;
        }

        .registration-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .registration-link a {
            color: green;
            text-decoration: none;
        }

        .registration-link a:hover {
            text-decoration: underline;
        }

        .forgot-password-link {
            text-align: center;
            padding-top:10px;
            color:green;
          
        }
        @keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}


    </style>
</head>
<body>
    <div class="header">
       <a href="home.php">  <img src="assets/logo.jpg" alt="BharatV Logo"></a>
    </div>

    <div class="page-container">
        <div class="login-wrapper">
            <h1>Login To Your Account</h1>
            
            <?php if (isset($errors['login'])): ?>
                <div class="error-message"><?php echo $errors['login']; ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="loginForm">
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" class="<?php echo isset($errors['role']) ? 'error' : ''; ?>">
                        <option value="">Select role</option>
                        <option value="voter">Voter</option>
                        <option value="candidate">Candidate</option>
                        <option value="party_admin">Political Party</option>
                        <option value="returning-officer">Returning Officer</option>
                    </select>
                    <?php if (isset($errors['role'])): ?>
                        <div class="error-message"><?php echo $errors['role']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           class="<?php echo isset($errors['email']) ? 'error' : ''; ?>">
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           class="<?php echo isset($errors['password']) ? 'error' : ''; ?>">
                    <?php if (isset($errors['password'])): ?>
                        <div class="error-message"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit">Login</button>
            </form>

            <div class="forgot-password-link">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>

            <div class="registration-link">
                Not registered yet? <a href="register_dashboard.php">Click here</a> to register.
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const roleSelect = document.getElementById('role');

            // Live validation for email
            emailInput.addEventListener('input', function() {
                validateEmail(this);
            });

            // Live validation for password
            passwordInput.addEventListener('input', function() {
                validatePassword(this);
            });

            // Live validation for role
            roleSelect.addEventListener('change', function() {
                validateRole(this);
            });

            // Form submission validation
            form.addEventListener('submit', function(e) {
                let isValid = true;

                // Clear previous error messages
                clearErrors();

                // Validate all fields
                if (!validateEmail(emailInput)) isValid = false;
                if (!validatePassword(passwordInput)) isValid = false;
                if (!validateRole(roleSelect)) isValid = false;

                if (!isValid) {
                    e.preventDefault();
                }
            });

            // Validation functions
            function validateEmail(input) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (input.value.trim() === '') {
                    showError(input, 'Email is required');
                    return false;
                } else if (!emailRegex.test(input.value)) {
                    showError(input, 'Please enter a valid email address');
                    return false;
                } else {
                    showSuccess(input);
                    return true;
                }
            }

            function validatePassword(input) {
                if (input.value.trim() === '') {
                    showError(input, 'Password is required');
                    return false;
                } else if (input.value.length < 6) {
                    showError(input, 'Password must be at least 6 characters');
                    return false;
                } else {
                    showSuccess(input);
                    return true;
                }
            }

            function validateRole(select) {
                if (select.value === '') {
                    showError(select, 'Please select a role');
                    return false;
                } else {
                    showSuccess(select);
                    return true;
                }
            }

            // Helper functions
            function showError(input, message) {
                const formGroup = input.parentElement;
                const errorDiv = formGroup.querySelector('.error-message') || document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.innerText = message;
                input.classList.add('error');
                input.classList.remove('success');
                if (!formGroup.querySelector('.error-message')) {
                    formGroup.appendChild(errorDiv);
                }
            }

            function showSuccess(input) {
                const formGroup = input.parentElement;
                const errorDiv = formGroup.querySelector('.error-message');
                if (errorDiv) {
                    formGroup.removeChild(errorDiv);
                }
                input.classList.remove('error');
                input.classList.add('success');
            }

            function clearErrors() {
                document.querySelectorAll('.error-message').forEach(error => error.remove());
                document.querySelectorAll('.error').forEach(field => field.classList.remove('error'));
            }
        });
    </script>
</body>
</html>