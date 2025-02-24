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
    // Update application status
    $update_stmt = $conn->prepare("
        UPDATE candidate_applications 
        SET application_status = ?
        WHERE application_id = ? AND party_id = ?
    ");

    if (!$update_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $update_stmt->bind_param("sii", $status, $application_id, $_SESSION['party_id']);
    $success = $update_stmt->execute();

    if (!$success) {
        throw new Exception("Execute failed: " . $update_stmt->error);
    }

    // If status is approved, insert into contesting_candidates
    if ($status === 'approved' && $update_stmt->affected_rows > 0) {
        // Get application details
        $select_stmt = $conn->prepare("
            SELECT id, party_id, ward_id, election_id
            FROM candidate_applications
            WHERE application_id = ?
        ");

        if (!$select_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $select_stmt->bind_param("i", $application_id);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        $application = $result->fetch_assoc();

        if (!$application) {
            throw new Exception("Application not found");
        }

        // Check if candidate is already contesting
        $check_stmt = $conn->prepare("
            SELECT contesting_id 
            FROM contesting_candidates 
            WHERE id = ? AND election_id = ?
        ");
        
        $check_stmt->bind_param("ii", $application['id'], $application['election_id']);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            throw new Exception("Candidate is already contesting in this election");
        }

        // Insert into contesting_candidates
        $insert_stmt = $conn->prepare("
            INSERT INTO contesting_candidates 
            (id, party_id, ward_id, election_id)
            VALUES (?, ?, ?, ?)
        ");

        if (!$insert_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $insert_stmt->bind_param("iiii", 
            $application['id'],
            $application['party_id'],
            $application['ward_id'],
            $application['election_id']
        );

        if (!$insert_stmt->execute()) {
            throw new Exception("Failed to insert contesting candidate: " . $insert_stmt->error);
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