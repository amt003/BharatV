<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $errors = [];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $errors['login'] = "All fields are required";
    } else {
        // Check for admin login from users table
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? AND role = 'admin'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Verify the password (assuming it's stored using password_hash)
            if (password_verify($password, $admin['password'])) {
                // Login successful
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['name'] = $admin['name'];
                $_SESSION['role'] = $admin['role'];
                
                // Redirect to admin dashboard
                header("Location: admin.php");
                exit();
            } else {
                $errors['login'] = "Invalid password";
            }
        } else {
            $errors['login'] = "Invalid admin credentials";
        }
        
        $stmt->close();
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
            background: linear-gradient(135deg white 100%);
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
            <h1>Login As Admin</h1>
            
            <?php if (isset($errors['login'])): ?>
                <div class="error-message"><?php echo $errors['login']; ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars(string: $_SERVER["PHP_SELF"]); ?>" id="loginForm">
              
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


           
        </div>
    </div>
<script>
   document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        // Live validation for email
        emailInput.addEventListener('input', function() {
            validateEmail(this);
        });

        // Live validation for password
        passwordInput.addEventListener('input', function() {
            validatePassword(this);
        });

        // Form submission validation
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Clear previous error messages
            clearErrors();

            // Validate all fields
            if (!validateEmail(emailInput)) isValid = false;
            if (!validatePassword(passwordInput)) isValid = false;

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
            } else {
                showSuccess(input);
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
