<?php
session_start();
include 'db.php';

// Ensure this file only processes POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Method not allowed');
}

header('Content-Type: application/json');

try {
    if (!isset($_POST['action']) || !isset($_POST['user_id'])) {
        throw new Exception('Missing required parameters');
    }

    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    $rejection_reason = isset($_POST['rejection_reason']) ? trim($_POST['rejection_reason']) : '';
    
    // Validate user_id
    if ($user_id <= 0) {
        throw new Exception("Invalid user ID");
    }
    
    // Validate action
    if (!in_array($action, ['approve', 'reject'])) {
        throw new Exception("Invalid action");
    }
    
    // If rejecting, require a reason
    if ($action === 'reject' && empty($rejection_reason)) {
        $rejection_reason = "Your application did not meet our verification requirements.";
    }
    
    // Set the approval status
    $status = ($action === 'approve') ? 1 : -1;
    
    // Get user details for verification
    $userQuery = "SELECT name, email, role FROM users WHERE id = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("i", $user_id);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        throw new Exception("User not found");
    }
    
    $user = $userResult->fetch_assoc();
    
    // Update the user's approval status
    $sql = "UPDATE users SET approved_by_admin = ?, rejection_reason = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("isi", $status, $rejection_reason, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update user status");
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("No user found with ID: " . $user_id);
    }
    
    echo json_encode([
        'success' => true,
        'message' => "User successfully " . ($action === 'approve' ? "approved" : "rejected"),
        'user_id' => $user_id
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 