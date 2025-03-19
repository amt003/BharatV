<?php
session_start();
require_once 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['party_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check if application_id is provided
if (!isset($_GET['application_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Application ID is required']);
    exit();
}

$application_id = intval($_GET['application_id']);

// Prepare the query to get application details
$stmt = $conn->prepare("
    SELECT 
        ca.application_form,
        ca.application_party_approval,
        ca.created_at,
        u.name,
        u.email,
        u.phone,
        ca.application_type
    FROM candidate_applications ca
    JOIN users u ON ca.id = u.id
    WHERE ca.application_id = ? 
    AND ca.party_id = ?
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit();
}

$stmt->bind_param("ii", $application_id, $_SESSION['party_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Application not found']);
    exit();
}

$row = $result->fetch_assoc();

// Decode the JSON application form data
$application_form = json_decode($row['application_form'], true);

// Get the file paths
$profile_photo_path = 'uploads/profile_photos/' . $application_form['profile_photo'];
$aadhar_proof_path = 'uploads/aadhar_proofs/' . $application_form['aadhar_proof'];

// Check if files exist
$profile_photo_exists = file_exists($profile_photo_path);
$aadhar_proof_exists = file_exists($aadhar_proof_path);

// Prepare the response data
$response = [
    'success' => true,
    'name' => $row['name'],
    'email' => $row['email'],
    'phone' => $row['phone'],
    'age' => $application_form['age'] ?? 'N/A',
    'education' => $application_form['education'] ?? 'N/A',
    'address' => $application_form['address'] ?? 'N/A',
    'experience' => $application_form['political_experience'] ?? 'N/A',
    'profile_photo' => $profile_photo_exists ? $profile_photo_path : '',
    'aadhar_proof' => $aadhar_proof_exists ? $aadhar_proof_path : '',
    'application_type' => $row['application_type'],
    'created_at' => $row['created_at'],
    'party_approval' => $row['application_party_approval']
];

// Send the response
header('Content-Type: application/json');
echo json_encode($response);
?>