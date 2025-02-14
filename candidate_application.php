<?php
session_start();
require_once 'db.php';
require_once('tcpdf/tcpdf.php');

if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle photo upload
    $photo = file_get_contents($_FILES['profile_photo']['tmp_name']);
    $aadhar = file_get_contents($_FILES['aadhar_proof']['tmp_name']);
    
    // Generate PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Election System');
    $pdf->SetTitle('Candidate Application');
    $pdf->AddPage();
    
    // Add content to PDF
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Candidate Application Form', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 12);
    
    // Add photo
    $pdf->Image('@'.$photo, 160, 15, 30, 40, '', '', '', false, 300, '', false, false, 1);
    
    // Personal Information
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Personal Information', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    
    $pdf->Cell(50, 7, 'Name:', 0, 0);
    $pdf->Cell(0, 7, $_POST['full_name'], 0, 1);
    
    $pdf->Cell(50, 7, 'Age:', 0, 0);
    $pdf->Cell(0, 7, $_POST['age'], 0, 1);
    
    $pdf->Cell(50, 7, 'Address:', 0, 0);
    $pdf->MultiCell(0, 7, $_POST['address'], 0, 'L');
    
    $pdf->Cell(50, 7, 'Phone:', 0, 0);
    $pdf->Cell(0, 7, $_POST['phone'], 0, 1);
    
    // Education & Experience
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Education and Experience', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    
    $pdf->Cell(50, 7, 'Education:', 0, 0);
    $pdf->MultiCell(0, 7, $_POST['education'], 0, 'L');
    
    $pdf->Cell(50, 7, 'Occupation:', 0, 0);
    $pdf->Cell(0, 7, $_POST['occupation'], 0, 1);
    
    $pdf->Cell(50, 7, 'Political Experience:', 0, 0);
    $pdf->MultiCell(0, 7, $_POST['political_experience'], 0, 'L');
    
    // Add Aadhar proof
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Identity Proof (Aadhar)', 0, 1, 'L');
    $pdf->Image('@'.$aadhar, 15, 50, 180, 0, '', '', '', false, 300);
    
    // Save PDF to string
    $pdf_content = $pdf->Output('', 'S');
    
    // Store in database
    $stmt = $conn->prepare("INSERT INTO candidate_applications (user_id, party_id, ward_id, application_form,application_status,submitted_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("iiis", $_SESSION['user_id'], $_POST['party_id'], $_POST['ward_id'], $pdf_content);
    
    if ($stmt->execute()) {
        $success = "Application submitted successfully";
    } else {
        $error = "Error submitting application";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Candidate Application Form</title>
    <style>
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; }
        .required { color: red; }
        .photo-preview { max-width: 200px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Candidate Application Form</h2>
        
        <form id="candidateForm" action="candidate_application.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Profile Photo <span class="required">*</span></label>
                <input type="file" name="profile_photo" accept="image/*" required>
                <img id="photoPreview" class="photo-preview">
            </div>

            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" required>
                <div class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="age">Age *</label>
                <input type="number" id="age" name="age" required>
                <div class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" required>
                <div class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="education">Education *</label>
                <input type="text" id="education" name="education" required>
                <div class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="address">Address *</label>
                <textarea id="address" name="address" required></textarea>
                <div class="error-message"></div>
            </div>

            <div class="form-group">
                <label>Occupation <span class="required">*</span></label>
                <input type="text" name="occupation" required>
            </div>

            <div class="form-group">
                <label>Political Experience</label>
                <textarea name="political_experience" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Aadhar Proof <span class="required">*</span></label>
                <input type="file" name="aadhar_proof" accept="image/*" required>
            </div>

            <div class="form-group">
                <label>Select Party <span class="required">*</span></label>
                <select name="party_id" required>
                    <?php
                    $parties = $conn->query("SELECT party_id, party_name FROM parties");
                    while($party = $parties->fetch_assoc()) {
                        echo "<option value='{$party['party_id']}'>{$party['party_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Select Ward <span class="required">*</span></label>
                <select name="ward_id" required>
                    <?php
                    $wards = $conn->query("SELECT ward_id, ward_name FROM wards");
                    while($ward = $wards->fetch_assoc()) {
                        echo "<option value='{$ward['ward_id']}'>Ward {$ward['ward_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn-primary">Submit Application</button>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Debug log to ensure script is running
        console.log('Script loaded');

        // Get all input fields
        const inputs = document.querySelectorAll('input, textarea, select');
        console.log('Found inputs:', inputs.length);

        // Validation rules
        const rules = {
            full_name: {
                regex: /^[a-zA-Z\s]{3,50}$/,
                message: 'Name should be 3-50 characters long and contain only letters'
            },
            age: {
                regex: /^(2[5-9]|[3-9][0-9])$/,
                message: 'Age must be 25 or above'
            },
            phone: {
                regex: /^[6,7,8,9][0-9]{9}$/,
                message: 'Please enter a valid 10-digit phone number'
            },
            education: {
                regex: /.{3,}/,
                message: 'Please enter education details (minimum 3 characters)'
            },
            address: {
                regex: /.{10,}/,
                message: 'Please enter complete address (minimum 10 characters)'
            }
        };

        // Add validation to each input
        inputs.forEach(input => {
            // Create error message element if it doesn't exist
            let errorDiv = input.parentElement.querySelector('.error-message');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                input.parentElement.appendChild(errorDiv);
            }

            // Add validation on input
            input.addEventListener('input', function() {
                validateInput(input);
            });

            // Add validation on blur
            input.addEventListener('blur', function() {
                validateInput(input);
            });

            console.log('Added validation to:', input.id);
        });

        function validateInput(input) {
            console.log('Validating:', input.id); // Debug log

            const rule = rules[input.id];
            const errorDiv = input.parentElement.querySelector('.error-message');
            
            // Remove previous validation classes
            input.classList.remove('valid', 'invalid');
            
            // Skip validation if no rule exists for this input
            if (!rule) return;

            // Validate
            const isValid = rule.regex.test(input.value);
            console.log('Validation result:', isValid); // Debug log

            if (!isValid) {
                input.classList.add('invalid');
                errorDiv.textContent = rule.message;
                errorDiv.style.display = 'block';
            } else {
                input.classList.add('valid');
                errorDiv.style.display = 'none';
            }
        }
    });
    </script>

    <style>
    /* Add these styles at the bottom of your file */
    .form-group {
        position: relative;
        margin-bottom: 20px;
    }

    .error-message {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
        display: none;
        position: absolute;
        bottom: -18px;
    }

    input.invalid,
    textarea.invalid,
    select.invalid {
        border: 2px solid #dc3545 !important;
        background-color: #fff8f8 !important;
    }

    input.valid,
    textarea.valid,
    select.valid {
        border: 2px solid #28a745 !important;
        background-color: #f8fff8 !important;
    }

    /* Make sure error messages are visible */
    .invalid + .error-message {
        display: block !important;
    }
.btn-primary{
    background-color: orange;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
    </style>
</body>
</html>