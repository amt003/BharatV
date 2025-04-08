<?php
session_start(); 
$error_message = '';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function generateVerificationCode($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

function sendVerificationEmail($recipientEmail, $verificationCode) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bharatv2k25@gmail.com';
        $mail->Password   = 'tzvp bxrs pewb vdcc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('bharatv2k25@gmail.com', 'bharatv');
        $mail->addAddress($recipientEmail);
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "Your verification code is: $verificationCode\n\nThis code will expire in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $verificationCode = generateVerificationCode();
        $_SESSION['verification_code'] = $verificationCode;
        $_SESSION['email'] = $email;

        if (sendVerificationEmail($email, $verificationCode)) {
            echo"";
        } else {
            $error_message= "Failed to send verification code.";
        }
    } elseif (isset($_POST['verify'])) {
        $enteredOTP = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];
        if ($enteredOTP == $_SESSION['verification_code']) {
            header('Location: reset_password.php');
            unset($_SESSION['verification_code']);  
        } else {
            $error_message="Incorrect OTP.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bharatv - OTP Verification</title>
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

        p {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
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
            border-radius: 6px;
        }

        .otp-inputs input:focus {
            outline: none;
            border-color: green;
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

        .resend-text {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .resend-text a {
            color: green;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .resend-text a:hover {
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
            <h1>OTP Verification</h1>
            <p>Enter the 6-digit code sent to your email</p>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="otp-inputs">
                    <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp2')" id="otp1" name="otp1">
                    <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp3')" id="otp2" name="otp2">
                    <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp4')" id="otp3" name="otp3">
                    <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp5')" id="otp4" name="otp4">
                    <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp6')" id="otp5" name="otp5">
                    <input type="text" maxlength="1" required id="otp6" name="otp6">
                </div>
                <button type="submit" name="verify">Verify OTP</button>
            </form>

            <div class="resend-text">
                Didn't receive the code? <a href="forgot_password.php">Resend OTP</a>
            </div>
        </div>
    </div>

    <script>
        function moveToNext(current, nextFieldID) {
            if (current.value.length === 1) {
                document.getElementById(nextFieldID)?.focus();
            }
        }
    </script>
</body>
</html>