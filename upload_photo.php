<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if file is uploaded
if (isset($_FILES['profile_photo'])) {
    $userId = $_SESSION['user_id'];
    $file = $_FILES['profile_photo'];

    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo "Error uploading file!";
        exit();
    }

    // Validate file type (allow only images)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        exit();
    }

    // Generate a unique name for the uploaded file
    $fileName = uniqid() . '-' . basename($file['name']);
    $uploadDir = 'uploads/';

    // Make sure the directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . $fileName;

    // Move the uploaded file to the server
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Update database with the new profile photo path
        $stmt = $conn->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
        $stmt->bind_param("si", $fileName, $userId);
        $stmt->execute();

        if($_SESSION['role']=='voter'){
            header('Location: voter.php');
            exit();
        }else{
            header('Location: candidate.php');
            exit();
        }
    } else {
        echo "Failed to move uploaded file!";
    }
} else {
    echo "No file uploaded!";
}

$conn->close();
?>
