<?php
session_start();
$error_message = '';

if (!isset($_SESSION['email'])) {
    header('Location: forgot_password.php');
    exit(); 
}
$conn= new mysqli('localhost','root','','bharatv_db');
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    $error_message = "An error occurred during login. Please try again later.";
} else {
    if($_SERVER["REQUEST_METHOD"]=="POST"){
        $password= $_POST['new_password'];
        $confirm_password= $_POST['confirm_password'];
        if($password==$confirm_password){
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET password = '$hashed_password' WHERE email = '" . $_SESSION['email'] . "'";
            
            if ($conn->query($sql) === TRUE) {
                $_SESSION['success_message'] = "Your password has been successfully updated!";
                header('Location: login.php');
                unset($_SESSION['email']);  
                exit();
            } else {
                $error_message = "Error updating password: " . $conn->error;
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
    <title>Bharatv - Reset Password</title>
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
            background: linear-gradient(135deg, white 100%);
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

        .error-message1 {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(220, 53, 69, 0.2);
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
        <a href="home.php"><img src="assets/logo.jpg" alt="BharatV Logo"></a>
    </div>

    <div class="page-container">
        <div class="login-wrapper">
            <h1>Reset Password</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message1">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form id="resetForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                    <div id="password-error" class="error-message"></div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    <div id="cpassword-error" class="error-message"></div>
                </div>
                <button type="submit">Reset Password</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordError = document.getElementById('password-error');
            const cpasswordError = document.getElementById('cpassword-error');

            const passwordInput = document.getElementById('new_password');
            const cpasswordInput = document.getElementById('confirm_password');

            function checkPassword() {
                const password = passwordInput.value.trim();
                const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$/;

                if (password === '') {
                    passwordError.innerHTML = "Password is required";
                    passwordInput.style.border = "2px solid red";
                    return false;
                } else if (!passwordPattern.test(password)) {
                    passwordError.innerHTML = "Password must be 8+ characters with uppercase, lowercase, and a number.";
                    passwordInput.style.border = "2px solid red";
                    return false;
                } else {
                    passwordError.innerHTML = "";
                    passwordInput.style.border = "2px solid green";
                    return true;
                }
            }

            function checkConfirmPassword() {
                const password = passwordInput.value.trim();
                const confirmPassword = cpasswordInput.value.trim();
                
                if (confirmPassword === '') {
                    cpasswordError.innerHTML = "Please confirm your password";
                    cpasswordInput.style.border = "2px solid red";
                    return false;
                } else if (confirmPassword !== password) {
                    cpasswordError.innerHTML = "Passwords do not match";
                    cpasswordInput.style.border = "2px solid red";
                    return false;
                } else {
                    cpasswordError.innerHTML = "";
                    cpasswordInput.style.border = "2px solid green";
                    return true;
                }
            }

            passwordInput.addEventListener('input', function() {
                checkPassword();
                if (cpasswordInput.value !== '') {
                    checkConfirmPassword();
                }
            });
            
            cpasswordInput.addEventListener('input', checkConfirmPassword);

            document.getElementById('resetForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                let isValid = true;
                if (!checkPassword()) isValid = false;
                if (!checkConfirmPassword()) isValid = false;
                
                if (isValid) {
                    this.submit();
                }
            });
        });
    </script>
</body>
</html>