<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['voter', 'candidate'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    // Initialize query parts
    $updateFields = "name = ?, email = ?, phone = ?, address = ?";
    $params = [$name, $email, $phone, $address];
    $types = "ssss";
    
    // Handle profile photo upload if present
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
            exit();
        }
        
        // Generate unique filename
        $fileName = uniqid() . '-' . basename($file['name']);
        $uploadDir = 'uploads/';
        
        // Ensure directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $filePath = $uploadDir . $fileName;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Add profile_photo to update query
            $updateFields .= ", profile_photo = ?";
            $params[] = $fileName;
            $types .= "s";
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload profile photo.']);
            exit();
        }
    }
    
    // Add user_id to params
    $params[] = $_SESSION['user_id'];
    $types .= "i";
    
    // Update user profile
    $updateStmt = $conn->prepare("UPDATE users SET $updateFields WHERE id = ?");
    $updateStmt->bind_param($types, ...$params);
    
    if ($updateStmt->execute()) {
        // Get the updated profile photo path
        $profilePhoto = $fileName ?? null; // Use the new filename if uploaded, otherwise null
        
        if (!$profilePhoto) {
            // If no new photo was uploaded, get the existing one from the database
            $photoStmt = $conn->prepare("SELECT profile_photo FROM users WHERE id = ?");
            $photoStmt->bind_param("i", $_SESSION['user_id']);
            $photoStmt->execute();
            $photoResult = $photoStmt->get_result();
            $photoData = $photoResult->fetch_assoc();
            $profilePhoto = $photoData['profile_photo'];
            $photoStmt->close();
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile updated successfully!',
            'profile_photo' => $profilePhoto
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $conn->error]);
    }
    
    $updateStmt->close();
    $conn->close();
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>