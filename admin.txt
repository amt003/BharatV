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
    
    // Validate user_id
    if ($user_id <= 0) {
        throw new Exception("Invalid user ID");
    }
    
    // Validate action
    if (!in_array($action, ['approve', 'reject'])) {
        throw new Exception("Invalid action");
    }
    
    // Set the approval status
    $status = ($action === 'approve') ? 1 : 0;
    
    // Update the user's approval status
    $sql = "UPDATE users SET approved_by_admin = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $status, $user_id);
    
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