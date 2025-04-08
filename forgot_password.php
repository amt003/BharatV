<?php
session_start();
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $conn = new mysqli('localhost', 'root', '', 'bharatv_db');
        
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        $error_message = "An error occurred during login. Please try again later.";
    } else {
        
        $email = $conn->real_escape_string(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)); 
        $sql = "SELECT * FROM users WHERE email='$email'";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            echo'<form id="form" method="POST" action="verify_otp.php">';
            echo '<input type="hidden" name="email" value="' . $email . '">';
            echo'</form>';
            echo'<script>document.getElementById("form").submit();</script>';

        } else {
            $error_message = "Invalid email";
        }
        
               $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bharatv - Forgot Password</title>
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

        .back-to-login {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .back-to-login a {
            color: green;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-to-login a:hover {
            color: #1B5E20;
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
            <h1>Forgot Password</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <button type="submit">Send An OTP</button>
            </form>

            <div class="back-to-login">
                Remember your password? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
</body>
</html>