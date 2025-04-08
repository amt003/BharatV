<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is a voter
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'voter') {
    header('Location: login.php');
    exit();
}

//Process form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    // Update user profile
    $updateStmt = $conn->prepare("
        UPDATE users 
        SET name = ?, email = ?, phone = ?, address = ? 
        WHERE id = ?
    ");
    $updateStmt->bind_param("ssssi", $name, $email, $phone, $address, $_SESSION['user_id']);
    
    if ($updateStmt->execute()) {
        $_SESSION['message'] = "Profile updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating profile: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
    
    $updateStmt->close();
    
    // Redirect to refresh the page and show updated data
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get user details
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT u.*, w.ward_name 
    FROM users u 
    LEFT JOIN wards w ON u.ward_id = w.ward_id 
    WHERE u.id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


//Get voting history (optional)
$stmt = $conn->prepare("
    SELECT 
        e.election_title, 
        v.casted_at
    FROM 
        votes v
    JOIN 
        elections e ON v.election_id = e.election_id
    WHERE 
        v.id = ? 
    ORDER BY 
        v.casted_at DESC;
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$votingHistory = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Profile</title>
    <style>
        /* Voter Profile Styles - Matching Main Theme */
        :root {
            --primary-green: #2E7D32;
            --light-green: #4CAF50;
            --dark-green: #1B5E20;
            --primary-orange: #F57C00;
            --light-orange: #FF9800;
            --dark-orange: #E65100;
        }

        /* Profile Layout */
        .profile-container {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .profile-section {
            flex: 1;
            min-width: 300px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.3s ease;
            border-top: 4px solid var(--primary-green);
        }

        .profile-section:hover {
            transform: translateY(-5px);
        }

        .section-header {
            background: var(--primary-green);
            padding: 1.25rem;
            color: white;
        }

        .section-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--light-orange);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .section-body {
            padding: 1.5rem;
        }

        /* Table Styles */
        .profile-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .profile-table th,
        .profile-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }

        .profile-table th {
            color: var(--primary-green);
            font-weight: 600;
            width: 35%;
        }

        .profile-table td {
            color: #4b5563;
        }

        .profile-table tr:hover {
            background-color: rgba(76, 175, 80, 0.05);
        }

        /* Badge Styles */
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .status-approved {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--light-green) 100%);
            color: white;
        }

        .status-pending {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--light-orange) 100%);
            color: white;
        }

        /* Voting History Specific Styles */
        .voting-history thead th {
            background-color: rgba(76, 175, 80, 0.1);
            border-bottom: 2px solid var(--primary-green);
        }

        .text-muted {
            color: #6c757d;
            font-style: italic;
            text-align: center;
            padding: 2rem;
        }

        /* New styles for edit mode */
        .edit-button {
            background-color: var(--primary-green);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 1rem;
            transition: background-color 0.3s;
        }

        .edit-button:hover {
            background-color: var(--dark-green);
        }

        .input-field {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }

        .save-button {
            background-color: var(--primary-orange);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .save-button:hover {
            background-color: var(--dark-orange);
        }

        .cancel-button {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 0.5rem;
            transition: background-color 0.3s;
        }

        .cancel-button:hover {
            background-color: #5a6268;
        }

        .btn-container {
            display: flex;
            justify-content: flex-start;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            border: 1px solid var(--light-green);
            color: var(--dark-green);
        }

        .alert-error {
            background-color: rgba(244, 67, 54, 0.1);
            border: 1px solid #f44336;
            color: #d32f2f;
        }
        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .profile-container {
                flex-direction: column;
            }
            
            .profile-section {
                width: 100%;
            }
            
            .profile-table th,
            .profile-table td {
                padding: 0.75rem;
            }
            
            .section-header h3 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>

<div class="profile-container">
    <!-- Candidate Information -->
    <div class="profile-section">
        <div class="section-header">
            <h3>Candidate Information</h3>
        </div>
        <div class="section-body">
            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <?php 
                        echo $_SESSION['message']; 
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                    ?>
                </div>
            <?php endif; ?>

            <button class="edit-button" id="toggleEdit">Edit Profile</button>

            
            <!-- View Mode -->
            <div id="viewMode">
                <table class="profile-table">
                    <tr>
                        <th>Name:</th>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                    </tr>
                    <tr>
                        <th>Date of Birth:</th>
                        <td><?php echo date('d-m-Y', strtotime($user['dob'])); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td><?php echo htmlspecialchars($user['address']); ?></td>
                    </tr>
                    <tr>
                        <th>Ward:</th>
                        <td><?php echo htmlspecialchars($user['ward_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Aadhaar Number:</th>
                        <td><?php echo htmlspecialchars($user['aadhaar_number']); ?></td>
                    </tr>
                    <tr>
                        <th>Profile Photo:</th>
                        <td>
                            <?php if($user['profile_photo']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile Photo" style="width: 100px; height: 100px; border-radius: 50%;">
                            <?php else: ?>
                                <img src="uploads/default-avatar.png" alt="Profile Photo" style="width: 100px; height: 100px; border-radius: 50%;">
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Edit Mode -->
            <div id="editMode" style="display: none;">
                <form id="profileUpdateForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
                    <table class="profile-table">
                        <tr>
                            <th>Profile Photo:</th>
                            <td>
                                <input type="file" name="profile_photo" id="profile_photo" accept="image/*">
                                <small>Choose a new profile photo (optional)</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><input type="text" name="name" class="input-field" value="<?php echo htmlspecialchars($user['name']); ?>" required></td>
                        </tr>
                        <tr>
                            <th>Date of Birth:</th>
                            <td><?php echo date('d-m-Y', strtotime($user['dob'])); ?> <small>(Cannot be changed)</small></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><input type="email" name="email" class="input-field" value="<?php echo htmlspecialchars($user['email']); ?>" required></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><input type="tel" name="phone" class="input-field" value="<?php echo htmlspecialchars($user['phone']); ?>" required></td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td><textarea name="address" class="input-field" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th>Ward:</th>
                            <td><?php echo htmlspecialchars($user['ward_name']); ?> <small>(Cannot be changed)</small></td>
                        </tr>
                        <tr>
                            <th>Aadhaar Number:</th>
                            <td><?php echo htmlspecialchars($user['aadhaar_number']); ?> <small>(Cannot be changed)</small></td>
                        </tr>
                    </table>
                    <div class="btn-container">
                        <button type="button" id="saveChanges" class="save-button">Save Changes</button>
                        <button type="button" id="cancelEdit" class="cancel-button">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<!-- Voting History Tab -->
  <div class="profile-section">
        <div class="section-header">
            <h3>Election Voting History</h3>
  </div>
 <div id="voting-history" class="history-tab" >
    <?php if($votingHistory->num_rows > 0): ?>
        <table class="profile-table">
            <thead>
                <tr>
                    <th>Election</th>
                    <th>Date Voted</th>
                </tr>
            </thead>
            <tbody>
                <?php while($vote = $votingHistory->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vote['election_title']); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($vote['casted_at'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">No voting history available.</p>
    <?php endif; ?>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>