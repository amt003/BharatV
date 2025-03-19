<?php
session_start();
require_once 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['party_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['application_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$application_id = intval($input['application_id']);
$status = $input['status'];

// Validate status
$valid_statuses = ['pending', 'approved', 'rejected'];
if (!in_array($status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Prepare statement to update application status
    $update_stmt = $conn->prepare("
        UPDATE candidate_applications 
        SET application_party_approval = ? 
        WHERE application_id = ? AND party_id = ?
    ");

    if (!$update_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $update_stmt->bind_param("sii", $status, $application_id, $_SESSION['party_id']);
    
    // Execute the update
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update application status: " . $update_stmt->error);
    }

    // If approved, set RO approval to pending
    if ($status === 'approved') {
        $ro_update_stmt = $conn->prepare("
            UPDATE candidate_applications 
            SET application_ro_approval = 'pending' 
            WHERE application_id = ? AND party_id = ?
        ");

        if (!$ro_update_stmt) {
            throw new Exception("Prepare failed for RO update: " . $conn->error);
        }

        $ro_update_stmt->bind_param("ii", $application_id, $_SESSION['party_id']);
        if (!$ro_update_stmt->execute()) {
            throw new Exception("Failed to send application to RO: " . $ro_update_stmt->error);
        }
    }

    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Status updated successfully',
        'affected_rows' => $update_stmt->affected_rows
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>