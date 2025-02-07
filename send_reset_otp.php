<?php
session_start(); 
$error_message = '';
require_once 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function generateVerificationCode($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

function sendVerificationEmail($recipientEmail, $verificationCode) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->SMTPDebug = 0; 
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'bharatv2k25@gmail.com'; // Your Gmail address
        $mail->Password = 'ryow pkko prps uzzo'; // Your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('bharatv2k25@gmail.com', 'BharatV');
        $mail->addAddress($recipientEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP - BharatV';
        $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #333;'>Password Reset OTP</h2>
                    <p>Your verification code is:</p>
                    <div style='background: #f9f9f9; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; margin: 20px 0;'>
                        <strong>$verificationCode</strong>
                    </div>
                    <p>This code will expire in 10 minutes.</p>
                    <p>If you didn't request this code, please ignore this email.</p>
                </div>
            </body>
            </html>";
        $mail->AltBody = "Your verification code is: $verificationCode\n\nThis code will expire in 10 minutes.";

        $mail->send();
        error_log("Email sent successfully to: " . $recipientEmail);
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        // Debug logging
        error_log("Attempting to send OTP to email: " . $email);
        
        $verificationCode = generateVerificationCode();
        $_SESSION['verification_code'] = $verificationCode;
        $_SESSION['email'] = $email;
        $_SESSION['code_expiry'] = time() + (10 * 60); // 10 minutes expiry

        if (sendVerificationEmail($email, $verificationCode)) {
            error_log("OTP sent successfully");
            // You might want to show a success message
            $success_message = "OTP has been sent to your email.";
        } else {
            error_log("Failed to send OTP");
            $error_message = "Failed to send verification code. Please try again.";
        }
    } elseif (isset($_POST['verify'])) {
        $enteredOTP = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];
        
        // Check if OTP is expired
        if (!isset($_SESSION['code_expiry']) || time() > $_SESSION['code_expiry']) {
            $error_message = "OTP has expired. Please request a new one.";
        }
        // Verify OTP
        elseif ($enteredOTP === $_SESSION['verification_code']) {
            header('Location: reset_password.php');
            exit();
        } else {
            $error_message = "Incorrect OTP. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - BharatV</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
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

        .otp-inputs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 20px 0;
        }

        .otp-inputs input {
            width: 45px;
            height: 45px;
            text-align: center;
            font-size: 1.2rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            transition: all 0.3s ease;
        }

        .otp-inputs input:focus {
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

        .resend-text {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .resend-text a {
            color: #4CAF50;
            text-decoration: none;
            margin-left: 5px;
        }

        .resend-text a:hover {
            text-decoration: underline;
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

        .success {
            color: #28a745;
            font-size: 14px;
            margin: 10px 0;
            text-align: center;
            padding: 10px;
            background-color: #f0fff0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="assets/logo.jpg" alt="BharatV Logo">
    </div>

    <div class="container">
        <h2>OTP Verification</h2>
        <p class="description">Enter the 6-digit code sent to your email.</p>

        <?php if (isset($success_message)): ?>
            <div class="success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group otp-inputs">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp2')" id="otp1" name="otp1">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp3')" id="otp2" name="otp2">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp4')" id="otp3" name="otp3">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp5')" id="otp4" name="otp4">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp6')" id="otp5" name="otp5">
                <input type="text" maxlength="1" required id="otp6" name="otp6">
            </div>
            <button type="submit" name="verify">Verify OTP</button>
        </form>

        <p class="resend-text">
            Didn't receive the code?<a href="#">Resend OTP</a>
        </p>

        <div class="back-to-login">
            <a href="login.php">Back to Login</a>
        </div>
    </div>

    <script>
        function moveToNext(current, nextFieldID) {
            if (current.value.length === 1) {
                document.getElementById(nextFieldID)?.focus();
            }
        }

        // Add backspace handler
        document.querySelectorAll('.otp-inputs input').forEach((input, index) => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    const prevInput = document.querySelector(`input[name=otp${index}]`);
                    if (prevInput) {
                        prevInput.focus();
                    }
                }
            });
        });
    </script>
</body>
</html>