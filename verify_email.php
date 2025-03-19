<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['temp_registration'])) {
    header("Location: register_dashboard.php");
    exit();
}

$error_message = '';
$email = $_SESSION['temp_registration']['email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];
    $stored_data = $_SESSION['temp_registration'];

    if (time() > $stored_data['otp_expiry']) {
        $error_message = "OTP has expired. Please register again.";
    } elseif ($entered_otp === $stored_data['otp']) {
        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role,aadhaar_number,aadhaar_file,address,phone,ward_id,dob,email_verified,approved_by_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1,0)");
        $stmt->bind_param("ssssssssss",     
            $stored_data['name'],
            $stored_data['email'],
            $stored_data['password'],
            $stored_data['role'],
            $stored_data['aadhaar_number'],
            $stored_data['aadhaar_file'],
            $stored_data['address'],
            $stored_data['phone'],
            $stored_data['ward_id'],
            $stored_data['dob']
        );

        if ($stmt->execute()) {
            unset($_SESSION['temp_registration']);
            $_SESSION['success_message'] = "Registration successful! Please login.";
            header("Location: login.php");
            exit();
        } else {
            $error_message = "Registration failed. Please try again.";
        }
    } else {
        $error_message = "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - BharatV</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f4;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header {
            width: 100%;
            background: white;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .header img {
            max-height: 100px;
        }

        .container {
            max-width: 400px;
            width: 90%;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .description {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .otp-inputs input {
            width: 40px;
            height: 40px;
            text-align: center;
            font-size: 18px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
        }

        button:hover {
            background: #45a049;
        }

        .error {
            color: #dc3545;
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            background: #fff8f8;
            border-radius: 4px;
        }

        .resend-link {
            text-align: center;
            margin-top: 20px;
        }

        .resend-link a {
            color: #4CAF50;
            text-decoration: none;
        }

        .resend-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="assets/logo.jpg" alt="BharatV Logo">
    </div>

    <div class="container">
        <h2>Email Verification</h2>
        <p class="description">Enter the 6-digit code sent to:<br><strong><?php echo htmlspecialchars($email); ?></strong></p>

        <?php if (!empty($error_message)): ?>
            <div class="error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="otp-inputs">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <input type="text" name="otp<?php echo $i; ?>" maxlength="1" required>
                <?php endfor; ?>
            </div>
            <button type="submit">Verify Email</button>
        </form>

        <div class="resend-link">
            <p>Didn't receive the code? <a href="resend_otp.php">Resend</a></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.otp-inputs input');
            
            inputs.forEach((input, index) => {
                // Move to next input after entering a digit
                input.addEventListener('input', function() {
                    if (this.value.length === 1) {
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        }
                    }
                });

                // Handle backspace
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });

                // Allow only numbers
                input.addEventListener('keypress', function(e) {
                    if (!/[0-9]/.test(e.key)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html> 