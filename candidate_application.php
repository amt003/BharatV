<?php
session_start();
require_once 'db.php';

$message = '';
$messageType = '';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'candidate') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the candidate's ward
$wardQuery = $conn->prepare("SELECT ward_id FROM users WHERE id = ?");
$wardQuery->bind_param("i", $user_id);
$wardQuery->execute();
$wardResult = $wardQuery->get_result();
$ward = $ward_id = $wardResult->fetch_assoc()['ward_id'];

// Check if candidate is contesting in any ongoing election (both regular and independent)
$checkContestingQuery = $conn->prepare("
    SELECT 
        e.election_title,
        e.status,
        ca.application_type,  
        ca.application_party_approval,
        ca.application_ro_approval
    FROM candidate_applications ca
    JOIN elections e ON ca.election_id = e.election_id
    WHERE ca.id = ? 
    AND e.status != 'completed'
    AND (
        (ca.application_ro_approval = 'approved' AND ca.application_party_approval = 'approved')
    )
");

$checkContestingQuery->bind_param("i", $user_id);
$checkContestingQuery->execute();
$contestingResult = $checkContestingQuery->get_result();

$isContesting = false;
$contestingDetails = null;

if ($contestingResult->num_rows > 0) {
    $isContesting = true;
    $contestingDetails = $contestingResult->fetch_assoc();
}

// Check for rejected applications
$checkRejectedQuery = $conn->prepare("
    SELECT election_title 
    FROM candidate_applications ca
    JOIN elections e ON ca.election_id = e.election_id
    WHERE ca.id = ? 
    AND e.status != 'completed'
    AND (ca.application_ro_approval = 'rejected' OR ca.application_party_approval = 'rejected')
");
$checkRejectedQuery->bind_param("i", $user_id);
$checkRejectedQuery->execute();
$rejectedResult = $checkRejectedQuery->get_result();
$hasRejectedApplication = $rejectedResult->num_rows > 0;

// Check for pending applications - MODIFIED QUERY
$checkPendingQuery = $conn->prepare("
    SELECT election_title 
    FROM candidate_applications ca
    JOIN elections e ON ca.election_id = e.election_id
    WHERE ca.id = ? 
    AND (ca.application_ro_approval = 'pending' OR ca.application_party_approval = 'pending')
    AND ca.application_ro_approval != 'rejected' 
    AND ca.application_party_approval != 'rejected'
");
$checkPendingQuery->bind_param("i", $user_id);
$checkPendingQuery->execute();
$pendingResult = $checkPendingQuery->get_result();
$hasPendingApplication = $pendingResult->num_rows > 0;

if ($hasRejectedApplication) {
    $rejectedDetails = $rejectedResult->fetch_assoc();
    $message = "Your application for the election: " . 
               htmlspecialchars($rejectedDetails['election_title']) . 
               " has been rejected. You cannot submit new applications until this election is completed.";
    $messageType = "error";
} elseif ($hasPendingApplication) {
    $pendingDetails = $pendingResult->fetch_assoc();
    $message = "You have a pending application for the election: " . 
               htmlspecialchars($pendingDetails['election_title']) . 
               ". Please wait for it to be processed.";
    $messageType = "error";
} elseif ($isContesting) {
    $message = "You are currently contesting as a " . 
               ($contestingDetails['application_type'] === 'independent' ? 'an independent' : 'a party-affiliated') . 
               " candidate in the election: " . htmlspecialchars($contestingDetails['election_title']) . 
               ". You cannot submit new applications until this election is completed.";
    $messageType = "error";
}

// Get the current date and calculate the cutoff date
$current_date = date('Y-m-d');
$cutoff_date = date('Y-m-d', strtotime('+15 days'));

$electionsResult = null;
// Only fetch available elections if no pending applications, no rejected applications, and not contesting
if (!$hasPendingApplication && !$hasRejectedApplication && !$isContesting) {
    $electionsQuery = $conn->prepare("
        SELECT election_id, election_title 
        FROM elections 
        WHERE FIND_IN_SET(?, ward_ids) 
        AND status != 'completed'
        AND start_date > ?
        AND election_id NOT IN (
            SELECT election_id FROM candidate_applications WHERE id = ?
        )
    ");
    $electionsQuery->bind_param("isi", $ward_id, $cutoff_date, $user_id);
    if ($electionsQuery->execute()) {
        $electionsResult = $electionsQuery->get_result();
    } else {
        error_log("Error executing elections query: " . $electionsQuery->error);
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Set application type based on party_id at the beginning
        $application_type = isset($_POST['party_id']) && $_POST['party_id'] === 'independent' ? 'independent' : 'party';

        // Validate required fields
        $required_fields = ['full_name', 'age', 'phone', 'education', 'address', 
                            'occupation', 'ward_id', 'party_id', 'election_id'];

        // Add independent party fields if independent party is selected
        if ($application_type === 'independent') {
            $required_fields[] = 'independent_party_name';
            
            // Specific check for party symbol
            if (!isset($_FILES['independent_party_symbol']) || $_FILES['independent_party_symbol']['error'] !== 0) {
                throw new Exception("Party symbol is required for independent candidates.");
            }
        }

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All fields are required. Please fill in {$field}.");
            }
        }

        // Validate age
        if ($_POST['age'] < 25) {
            throw new Exception("Age must be at least 25 years.");
        }

        // Validate phone number
        if (!preg_match("/^[6789]\d{9}$/", $_POST['phone'])) {
            throw new Exception("Please enter a valid 10-digit phone number.");
        }

        // Handle file uploads
        $profile_photo_path = '';
        $aadhar_proof_path = '';
        $independent_party_symbol = '';

        // Profile Photo Upload
        if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== 0) {
            throw new Exception("Profile photo is required.");
        }

        $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['profile_photo']['type'], $allowed_image_types)) {
            throw new Exception("Invalid profile photo format. Please upload JPG, PNG, or GIF.");
        }

        $target_dir = "uploads/profile_photos/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $profile_photo_path = $target_dir . uniqid() . '_' . basename($_FILES['profile_photo']['name']);
        if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $profile_photo_path)) {
            throw new Exception("Failed to upload profile photo.");
        }

        // Aadhar Proof Upload
        if (!isset($_FILES['aadhar_proof']) || $_FILES['aadhar_proof']['error'] !== 0) {
            throw new Exception("Aadhar proof is required.");
        }

        if ($_FILES['aadhar_proof']['type'] !== 'application/pdf') {
            throw new Exception("Invalid Aadhar proof format. Please upload PDF only.");
        }

        $target_dir = "uploads/aadhar_proofs/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $aadhar_proof_path = $target_dir . uniqid() . '_' . basename($_FILES['aadhar_proof']['name']);
        if (!move_uploaded_file($_FILES['aadhar_proof']['tmp_name'], $aadhar_proof_path)) {
            throw new Exception("Failed to upload Aadhar proof.");
        }

        // Set party_id to NULL for independent candidates
        $party_id = $application_type === 'independent' ? null : $_POST['party_id'];

        // Prepare the application form data as JSON
        $application_form = json_encode([
            'full_name' => $_POST['full_name'],
            'age' => $_POST['age'],
            'phone' => $_POST['phone'],
            'education' => $_POST['education'],
            'address' => $_POST['address'],
            'occupation' => $_POST['occupation'],
            'political_experience' => $_POST['political_experience'],
            'profile_photo' => basename($profile_photo_path),
            'aadhar_proof' => basename($aadhar_proof_path),
            'application_type' => $application_type,
            'independent_party_name' => $party_id === null ? $_POST['independent_party_name'] : null,
            'independent_party_symbol' => $party_id === null ? basename($_FILES['independent_party_symbol']['name']) : null
        ]);

        // Set party approval status based on application type
        $party_approval = $application_type === 'independent' ? 'approved' : 'pending';

        // Set variables for binding
        $independent_party_name = $application_type === 'independent' ? $_POST['independent_party_name'] : null;
        $independent_party_symbol = $application_type === 'independent' ? basename($_FILES['independent_party_symbol']['name']) : null;

        // Prepare SQL statement and bind parameters
        $stmt = $conn->prepare("INSERT INTO candidate_applications 
            (id, party_id, ward_id, election_id, application_form, application_ro_approval, 
            application_party_approval, independent_party_name, independent_party_symbol, created_at) 
            VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, NOW())");

        $stmt->bind_param("iiisssss", 
            $user_id,
            $party_id,
            $_POST['ward_id'],
            $_POST['election_id'],
            $application_form,
            $party_approval,
            $independent_party_name,
            $independent_party_symbol
        );

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Failed to submit application: " . $stmt->error);
        }

        // Success message
        $message = "Application submitted successfully! Your application is now pending for review.";
        $messageType = "success";

        // Clear form data on successful submission
        $_POST = array();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Delete uploaded files if they exist
        if (!empty($profile_photo_path) && file_exists($profile_photo_path)) {
            unlink($profile_photo_path);
        }
        if (!empty($aadhar_proof_path) && file_exists($aadhar_proof_path)) {
            unlink($aadhar_proof_path);
        }

        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Application</title>
    <style>
        /* Form Container Styles */
.application-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 30px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    display:block;
    }
    
    .application-container h2 {
        color:green;
        text-align: center;
        margin-bottom: 30px;
        font-size: 28px;
        border-bottom: 3px solid green;
        padding-bottom: 10px;
        
    }
/* Form Element Styles */
.form-group {
    margin-bottom: 25px;
}

label {
    display: block;
    margin-bottom: 8px;
    color:rgb(30, 167, 75);
    font-weight: 600;
    font-size: 16px;
}

input[type="text"],
input[type="number"],
input[type="tel"],
textarea,
select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 15px;
    transition: border-color 0.3s ease;
    background: #f8f9fa;
}

input[type="text"]:focus,
input[type="number"]:focus,
input[type="tel"]:focus,
textarea:focus,
select:focus {
    border-color: lightgreen;
    outline: none;
    box-shadow: 0 0 5px rgba(52, 219, 127, 0.3);
}

textarea {
    resize: vertical;
    min-height: 100px;
}

/* File Input Styling */
.file-input-container {
    position: relative;
    margin-bottom: 20px;
}

.file-input-label {
    display: inline-block;
    padding: 12px 20px;
    background:green;
    color: white;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.file-input-label:hover {
    background: rgba(37, 185, 104, 0.3);
}

input[type="file"] {
    display: none;
}

/* Select Dropdown Styling */
select {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 15px;
}

/* Submit Button Styling */
button[type="submit"] {
    width: 100%;
    padding: 15px;
    background:orange;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
    margin-top: 20px;
}

button[type="submit"]:hover {
    background:rgb(155, 105, 58);
    transform: translateY(-2px);
}

/* Message Styling */
.success {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #28a745;
}

.error {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #dc3545;
}

/* Responsive Design */
@media (max-width: 768px) {
    .application-container {
        margin: 20px;
        padding: 20px;
    }
    
    button[type="submit"] {
        padding: 12px;
    }
}
.independent-fields {
            display: none;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #e0e0e0;
        }

 .independent-fields.visible {
            display: block;
        }
    </style>
</head>
<body>

<div class="application-container">
    <h2>Candidate Application Form</h2>
    
    <?php if ($message): ?>
    <div class="<?php echo $messageType; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if ($isContesting || $hasPendingApplication): ?>
    <!-- No need to display the message again here, it's already shown above -->
<?php elseif (!$electionsResult || $electionsResult->num_rows === 0): ?>
    <div class="error">
        No available elections found for your ward at this time.
    </div>
<?php else: ?>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label>Profile Photo</label>
            <div class="file-input-container">
                <label class="file-input-label" for="profile_photo">Choose Profile Photo</label>
                <input id="profile_photo" type="file" name="profile_photo" accept="image/*" required>
                <div id="profile_photo_preview" style="margin-top: 10px; display: none;">
            <img id="preview_image" style="max-width: 200px; max-height: 200px; border-radius: 5px;">
        </div>
            </div>
        </div>

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" required>
        </div>
<div class="form-group">
    <label>Age</label>
    <input type="number" name="age" required min="18">
</div>
<div class="form-group">
    <label>Phone Number</label>
    <input type="tel" name="phone" required pattern="^[6789]\d{9}$">
</div>
<div class="form-group">
     <label>Education</label>
    <input type="text" name="education" required>
</div>
<div class="form-group">
    <label>Address</label>
    <textarea name="address" required rows="3"></textarea>
</div>
<div class="form-group">
    <label>Occupation</label>
    <input type="text" name="occupation" required>
</div>
<div class="form-group">
    <label>Political Experience</label>
    <textarea name="political_experience" rows="3"></textarea>
</div>

<div class="form-group">
    <label>Aadhar Proof</label>
    <div class="file-input-container">
        <label class="file-input-label" for="aadhar_proof">Choose Aadhar Proof</label>
        <input id="aadhar_proof" type="file" name="aadhar_proof" accept="application/pdf" required>
        <div id="aadhar_proof_preview" style="margin-top: 10px;"></div>
    </div>
</div>
       <div class="form-group">
           <label>Select Ward</label>
           <select name="ward_id" required>
               <option value="">Select a ward</option>
               <?php
            $wards = $conn->query("SELECT ward_id, ward_name FROM wards ORDER BY ward_name");
            while ($ward = $wards->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($ward['ward_id']) . "'>" . htmlspecialchars($ward['ward_name']) . "</option>";
            }
            ?>
        </select>
    </div> 
    <div class="form-group">
            <label>Select Party</label>
            <select name="party_id" id="party_id" required onchange="toggleIndependentFields()">
                <option value="">Select a party</option>
                <option value="independent">Independent Candidate</option>
                <?php
                $parties = $conn->query("SELECT party_id, party_name FROM parties WHERE party_id != 'independent' ORDER BY party_name");
                while ($party = $parties->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($party['party_id']) . "'>" . htmlspecialchars($party['party_name']) . "</option>";
                }
                ?>
            </select>
        </div>

        <div id="independent-fields" class="independent-fields">
    <div class="form-group">
        <label>Independent Party Name</label>
        <input type="text" name="independent_party_name" required>
    </div>

    <div class="form-group">
        <label>Party Symbol</label>
        <div class="file-input-container">
            <label class="file-input-label" for="independent_party_symbol">Choose Party Symbol</label>
            <input id="independent_party_symbol" type="file" name="independent_party_symbol" accept="image/*" required>
            <div id="party_symbol_preview" style="margin-top: 10px; display: none;">
                <img id="symbol_preview_image" style="max-width: 200px; max-height: 200px; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>
    <div class="form-group">
        <label>Select Election</label>
        <select name="election_id" required>
            <option value="">Select an election</option>
            <?php
          while ($election = $electionsResult->fetch_assoc()) {
              echo "<option value='" . htmlspecialchars($election['election_id']) . "'>" . htmlspecialchars($election['election_title']) . "</option>";
            }
            
            ?>
        </select>
        
    </div>
        <button type="submit">Submit Application</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
