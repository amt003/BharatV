<?php
session_start();
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$wards = [];
$sql = "SELECT ward_id, ward_name FROM wards ORDER BY ward_name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $wards[] = $row;
    }
}
// Function to validate and sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate date of birth
function validateDateOfBirth($dob) {
    // Check if date is in correct format (YYYY-MM-DD)
    if (!preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $dob, $matches)) {
        return false;
    }

    // Validate date
    $year = $matches[1];
    $month = $matches[2];
    $day = $matches[3];

    // Check if date is valid
    if (!checkdate($month, $day, $year)) {
        return false;
    }

    // Calculate age (must be at least 18)
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;

    return $age >= 18;
}

function uploadAadhaarFile($file) {
  // Check if uploads directory exists and create if not
  $target_dir = "uploads/";
  if (!file_exists($target_dir)) {
      mkdir($target_dir, 0755, true);
  }
  
  // Validate file presence
  if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
      return ["error" => "No file uploaded or upload error occurred"];
  }
  
  // Generate safe filename
  $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
  $unique_filename = uniqid() . '_' . md5(basename($file["name"])) . '.' . $file_extension;
  $target_file = $target_dir . $unique_filename;
  
  // Check file size (5MB limit)
  if ($file["size"] > 5000000) {
      return ["error" => "File size must be less than 5MB"];
  }
  
  // Validate file type using both extension and MIME type
  $allowed_types = ["pdf"];
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime_type = finfo_file($finfo, $file["tmp_name"]);
  finfo_close($finfo);
  
  if (!in_array($file_extension, $allowed_types) || $mime_type !== 'application/pdf') {
      return ["error" => "Only PDF files are allowed"];
  }
  
  // Move uploaded file with additional security checks
  if (move_uploaded_file($file["tmp_name"], $target_file)) {
      // Set proper permissions
      chmod($target_file, 0644);
      return ["path" => $target_file];
  }
  
  return ["error" => "File upload failed"];
}

// Function to generate OTP
function generateOTP() {
    return sprintf("%06d", mt_rand(100000, 999999));
}

// Function to send verification email
function sendVerificationEmail($email, $name, $otp) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'bharatv2k25@gmail.com';
        $mail->Password = 'ntty itsx jfbg wvzo';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('bharatv2k25@gmail.com', 'bharatv');
        $mail->addAddress($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification - BharatV';
        $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #333;'>Email Verification</h2>
                    <p>Hello $name,</p>
                    <p>Your verification code is:</p>
                    <div style='background: #f9f9f9; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; margin: 20px 0;'>
                        <strong>$otp</strong>
                    </div>
                    <p>This code will expire in 10 minutes.</p>
                </div>
            </body>
            </html>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];
    
    // Sanitize inputs
    $name = sanitizeInput($_POST['name']);
    $dob = sanitizeInput($_POST['dob']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $aadhaar_number = sanitizeInput($_POST['aadhaar_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $ward_id = sanitizeInput($_POST['ward_id']);
    
    // Validate inputs
    if (empty($name)) $errors['name'] = "Name is required";
    if (empty($dob)) {
        $errors['dob'] = "Date of Birth is required";
    } elseif (!validateDateOfBirth($dob)) {
        $errors['dob'] = "Invalid date of birth. Must be 18 or older.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email format";
    if (empty($phone) ||!preg_match("/^[6789][0-9]{9}$/", $phone)) $errors['phone'] = "Invalid phone number";
    if (empty($address)) $errors['address'] = "Address is required";
    if (empty($aadhaar_number) || !preg_match("/^[0-9]{12}$/", $aadhaar_number)) {
        $errors['aadhaar_number'] = "Invalid Aadhaar number";
    }
    if (empty($password)) $errors['password'] = "Password is required";
    if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match";
    if (empty($ward_id)) $errors['ward_id'] = "Ward selection is required";
    
    // Handle Aadhaar file upload
    $aadhaar_file_result = uploadAadhaarFile($_FILES['aadhaar_file']);
    if (isset($aadhaar_file_result['error'])) {
        $errors['aadhaar_file'] = $aadhaar_file_result['error'];
    }
    
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $aadhaar_file_path = $aadhaar_file_result['path'];
        
        // Set role based on form type
        $role = ($_POST['form_type'] === 'candidate') ? 'candidate' : 'voter';
        
        // Generate OTP and store registration data in session
        $otp = generateOTP();
        $_SESSION['temp_registration'] = [
            'name' => $name,
            'email' => $email,
            'password' => $hashed_password,
            'role' => $role,
            'aadhaar_number' => $aadhaar_number,
            'aadhaar_file' => $aadhaar_file_path,
            'address' => $address,
            'phone' => $phone,
            'ward_id' => $ward_id,
            'dob' => $dob,
            'otp' => $otp,
            'otp_expiry' => time() + (10 * 60) // 10 minutes expiry
        ];

        // Send verification email
        if (sendVerificationEmail($email, $name, $otp)) {
            header("Location: verify_email.php");
            exit();
        } else {
            $errors['email'] = "Failed to send verification email. Please try again.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BharatV - Registration</title>
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

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 32px;
            font-size: 24px;    
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

        .form-wrapper {
            width: 100%;
            max-width: 500px;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 20px;
            background: #e0e0e0;
            cursor: pointer;
            border: none;
            flex: 1;
            font-size: 16px;
        }

        .tab.active {
            background: rgb(42, 189, 12);
            color: white;
        }

        .form-container {
            background: white;
            padding: 30px;
        }

        .form-group {
            margin-bottom: 16px;
            display: flex;
            gap: 16px;
        }

        .form-group label {
            flex: 1;
            display: block;
            font-size: 18px;
            font-weight: 500;
            color: black;
        }

        input,
        textarea,
        select {
            flex: 2;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: orange;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        button[type="submit"] {
            background: green;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        button[type="submit"]:hover {
            background: rgb(42, 189, 12);
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }

        .login-link {
            text-align: center;
            margin: 25px;
            font-size: 0.85rem;
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
  <script>

// Validation rules for all form fields
const validationRules = {
    name: {
        validate: (value) => {
            const regex = /^[a-zA-Z\s]{3,50}$/;
            return {
                isValid: regex.test(value),
                message: 'Name must be 3-50 characters long and contain only letters and spaces'
            };
        }
    },
    dob: {
        validate: (value) => {
            const inputDate = new Date(value);
            const today = new Date();
            const minAgeDate = new Date(
                today.getFullYear() - 18,
                today.getMonth(),
                today.getDate()
            );
            return {
                isValid: inputDate <= minAgeDate,
                message: 'You must be at least 18 years old to register'
            };
        }
    },
    email: {
        validate: (value) => {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return {
                isValid: regex.test(value),
                message: 'Please enter a valid email address'
            };
        }
    },
    phone: {
    validate: (value) => {
        const regex = /^[6789]\d{9}$/;  // Must start with 6 or 7 or 8 or 9 and have 10 digits total
        return {
            isValid: regex.test(value),
            message: 'Phone number must be 10 digits and start with 6 or 7 or 8 or 9'
        };
    }
},
    address: {
        validate: (value) => {
            return {
                isValid: value.trim().length >= 5,
                message: 'Address must be at least 5 characters long'
            };
        }
    },
    ward_id: {
        validate: (value) => {
            return {
                isValid: value !== '',
                message: 'Please select a ward'
            };
        }
    },
    aadhaar_number: {
        validate: (value) => {
            const regex = /^\d{12}$/;
            return {
                isValid: regex.test(value),
                message: 'Aadhaar number must be exactly 12 digits'
            };
        }
    },
    aadhaar_file: {
    validate: (fileInput) => {
        // Clear any existing error first
        clearError(fileInput);
        
        if (!fileInput.files || !fileInput.files[0]) {
            return {
                isValid: false,
                message: 'Please select a file'
            };
        }
        
        const file = fileInput.files[0];
        const isValidSize = file.size <= 5 * 1024 * 1024; // 5MB
        const isValidType = file.type === 'application/pdf';
        
        if (!isValidSize) {
            return {
                isValid: false,
                message: 'File size must be less than 5MB'
            };
        }
        
        if (!isValidType) {
            return {
                isValid: false,
                message: 'Only PDF files are allowed'
            };
        }
        
        return {
            isValid: true,
            message: ''
        };
    }
},
    password: {
        validate: (value) => {
            const hasMinLength = value.length >= 6;
            const hasUpperCase = /[A-Z]/.test(value);
            const hasLowerCase = /[a-z]/.test(value);
            const hasNumber = /\d/.test(value);
            
            const isValid = hasMinLength && hasUpperCase && hasLowerCase && hasNumber;
            
            return {
                isValid,
                message: isValid ? '' : 'Password must be at least 6 characters and contain uppercase, lowercase, and numbers'
            };
        }
    },
    confirm_password: {
        validate: (value, form) => {
            const password = form.querySelector('[name="password"]').value;
            return {
                isValid: value === password,
                message: 'Passwords do not match'
            };
        }
    }
};

// Function to show error message
function showError(input, message) {
    const formGroup = input.closest('.form-group');
    let errorDiv = formGroup.querySelector('.error');
    
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'error';
        formGroup.appendChild(errorDiv);
    }
    
    errorDiv.textContent = message;
    input.classList.add('invalid');
}

// Function to clear error message
function clearError(input) {
    const formGroup = input.closest('.form-group');
    const errorDiv = formGroup.querySelector('.error');
    
    if (errorDiv) {
        errorDiv.remove();
    }
    
    input.classList.remove('invalid');
}

// Function to validate a single input
function validateInput(input) {
    const name = input.name;
    const rule = validationRules[name];
    
    if (!rule) return true; // Skip if no validation rule exists
    
    const result = rule.validate(input.value, input.form);
    
    if (!result.isValid) {
        showError(input, result.message);
        return false;
    } else {
        clearError(input);
        return true;
    }
}

// Initialize form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('#candidate-form, #voter-form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        
        // Add validation on input/change
        inputs.forEach(input => {
            ['input', 'change', 'blur'].forEach(eventType => {
                input.addEventListener(eventType, () => {
                    validateInput(input);
                });
            });
        });
        
        // Validate all fields on form submit
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            inputs.forEach(input => {
                if (!validateInput(input)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    });
});

function sendVerificationEmail(email) {
    fetch('send_reset_otp.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'send_reset_otp.php';
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending verification email');
    });
}

// Add this to your form submit handler
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('[name="email"]').value;
    sendVerificationEmail(email);
});
  </script>
    <div class="header">
       <a href="home.php"> <img src="assets/logo.jpg" alt="Company Logo"></a>
    </div>

    <div class="page-container">
        <div class="form-wrapper">
            <div class="tabs">
                <button class="tab active" onclick="showForm('candidate')">Candidate Registration</button>
                <button class="tab" onclick="showForm('voter')">Voter Registration</button>
            </div>

            <!-- Candidate Form -->
            <form id="candidate-form" class="form-container" method="post" enctype="multipart/form-data" style="display: block">
                <h2>Candidate Registration</h2>
                <input type="hidden" name="form_type" value="candidate" />

                <div class="form-group">
                    <label for="name">Full Name*</label>
                    <input type="text" id="name" name="name" />
                    <?php if (isset($errors['name'])) : ?>
                        <div class="error"><?php echo $errors['name']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth*</label>
                    <input type="date" id="dob" name="dob" />
                    <?php if (isset($errors['dob'])) : ?>
                        <div class="error"><?php echo $errors['dob']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">Email Address*</label>
                    <input type="email" id="email" name="email" />
                    <?php if (isset($errors['email'])) : ?>
                        <div class="error"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number*</label>
                    <input type="tel" id="phone" name="phone" />
                    <?php if (isset($errors['phone'])) : ?>
                        <div class="error"><?php echo $errors['phone']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="address">Address*</label>
                    <textarea id="address" name="address" rows="3"></textarea>
                    <?php if (isset($errors['address'])) : ?>
                        <div class="error"><?php echo $errors['address']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
    <label for="ward_id">Ward*</label>
    <select id="ward_id" name="ward_id">
        <option value="">Select</option>
        <?php foreach($wards as $ward): ?>
            <option value="<?php echo htmlspecialchars($ward['ward_id']); ?>">
                <?php echo htmlspecialchars($ward['ward_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errors['ward_id'])) : ?>
        <div class="error"><?php echo $errors['ward_id']; ?></div>
    <?php endif; ?>
</div>


                <div class="form-group">
                    <label for="aadhaar_number">Aadhaar Number*</label>
                    <input type="text" id="aadhaar_number" name="aadhaar_number" maxlength="12" />
                    <?php if (isset($errors['aadhaar_number'])) : ?>
                        <div class="error"><?php echo $errors['aadhaar_number']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="aadhaar_file">Aadhaar Proof *</label>
                    <input type="file" id="aadhaar_file" name="aadhaar_file" />
                    <?php if (isset($errors['aadhaar_file'])) : ?>
                        <div class="error"><?php echo $errors['aadhaar_file']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password*</label>
                    <input type="password" id="password" name="password" />
                    <?php if (isset($errors['password'])) : ?>
                        <div class="error"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password*</label>
                    <input type="password" id="confirm_password" name="confirm_password" />
                    <?php if (isset($errors['confirm_password'])) : ?>
                        <div class="error"><?php echo $errors['confirm_password']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="login-link">
                    Already Registered? <a href="login.php">Click here</a> to Login.
                </div>

                <button type="submit">Submit Registration</button>
            </form>

          <form id="voter-form" class="form-container" method="post" enctype="multipart/form-data" style="display: none">
        <h2>Voter Registration</h2>
        <input type="hidden" name="form_type" value="voter" />

        <div class="form-group">
          <label for="name">Full Name*</label>
          <input type="text" id="name" name="name" />
          <?php if (isset($errors['name'])) : ?>
          <div class="error"><?php echo $errors['name']; ?></div>
          <?php endif; ?>
        </div>

        <div class="form-group">
        <label for="dob">Date of Birth*</label>
        <input type="date" id="dob" name="dob" />
        <?php if (isset($errors['dob'])) : ?>
        <div class="error"><?php echo $errors['dob']; ?></div>
        <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="email">Email Address*</label>
          <input type="email" id="email" name="email" />
          <?php if (isset($errors['email'])) : ?>
          <div class="error"><?php echo $errors['email']; ?></div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="phone">Phone Number*</label>
          <input type="tel" id="phone" name="phone" />
          <?php if (isset($errors['phone'])) : ?>
          <div class="error"><?php echo $errors['phone']; ?></div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="address">Address*</label>
          <textarea id="address" name="address" rows="3"></textarea>
          <?php if (isset($errors['address'])) : ?>
          <div class="error"><?php echo $errors['address']; ?></div>
          <?php endif; ?>
        </div>
        <div class="form-group">
    <label for="ward_id">Ward*</label>
    <select id="ward_id" name="ward_id">
        <option value="">Select</option>
        <?php foreach($wards as $ward): ?>
            <option value="<?php echo htmlspecialchars($ward['ward_id']); ?>">
                <?php echo htmlspecialchars($ward['ward_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errors['ward_id'])) : ?>
        <div class="error"><?php echo $errors['ward_id']; ?></div>
    <?php endif; ?>
</div>

        <div class="form-group">
          <label for="aadhaar_number">Aadhaar Number*</label>
          <input type="text" id="aadhaar_number" name="aadhaar_number" maxlength="12" />
          <?php if (isset($errors['aadhaar_number'])) : ?>
          <div class="error"><?php echo $errors['aadhaar_number']; ?></div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="aadhaar_file">Aadhaar Proof*</label>
          <input type="file" id="aadhaar_file" name="aadhaar_file" class="file-input" />
          <?php if (isset($errors['aadhaar_file'])) : ?>
          <div class="error"><?php echo $errors['aadhaar_file']; ?></div>
          <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Add Password*</label>
            <input type="password" id="password" name="password" />
            <?php if (isset($errors['password'])) : ?>
            <div class="error"><?php echo $errors['password'] ?? ''; ?></div>
          <?php endif; ?>

          </div>
          <div class="form-group">
            <label for="confirm_password">Confirm Password*</label>
            <input type="password" id="confirm_password" name="confirm_password" />
            <?php if (isset($errors['confirm_password'])) : ?>
            <div class="error"><?php echo $errors['confirm_password'] ?? ''; ?></div>
          <?php endif; ?>

          </div>
          <div class="login-link">
                Already Registered? <a href="login.php">Click here</a> to Login.
            </div>
            <button type="submit" id="submit" name="submit">Submit Registration</button>
    
      </form>
    </div>
</body>

<script>
   function showForm(type) {
        const candidateForm = document.getElementById("candidate-form");
        const voterForm = document.getElementById("voter-form");
        const tabs = document.querySelectorAll(".tab");

        if (type === "candidate") {
          candidateForm.style.display = "block";
          voterForm.style.display = "none";
          tabs[0].classList.add("active");
          tabs[1].classList.remove("active");
        } else {
          candidateForm.style.display = "none";
          voterForm.style.display = "block";
          tabs[0].classList.remove("active");
          tabs[1].classList.add("active");
        }
      }
</script>