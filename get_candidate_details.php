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
        ca.application_status,
        ca.created_at,
        u.name,
        u.email,
        u.phone
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

// Prepare the response data
$response = [
    'name' => $row['name'],
    'email' => $row['email'],
    'phone' => $row['phone'],
    'age' => $application_form['age'],
    'education' => $application_form['education'],
    'address' => $application_form['address'],
    'occupation' => $application_form['occupation'],
    'experience' => $application_form['political_experience'],
    'status' => $row['application_status'],
    'applied_date' => $row['created_at'],
    'profile_photo' => $application_form['profile_photo'],
    'aadhar_proof' => $application_form['aadhar_proof']
];

// Send the response
header('Content-Type: application/json');
echo json_encode($response);
?>