<?php
session_start();
require_once 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is a returning officer
if (!isset($_SESSION['ro_id']) || $_SESSION['role'] !== 'returning_officer') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

if (!isset($_GET['application_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Application ID not provided']);
    exit();
}

$application_id = $_GET['application_id'];

try {
    // Get candidate details
    $query = $conn->prepare("
        SELECT 
            ca.application_id,
            ca.application_form,
            ca.application_type,
            ca.election_id,
            ca.ward_id,
            ca.party_id,
            ca.independent_party_name,
            ca.independent_party_symbol,
            u.name,
            u.phone,
            u.email,
            p.party_name,
            e.Election_title,
            w.ward_name
        FROM candidate_applications ca 
        JOIN users u ON ca.id = u.id 
        LEFT JOIN parties p ON ca.party_id = p.party_id 
        JOIN elections e ON ca.election_id = e.election_id
        JOIN wards w ON ca.ward_id = w.ward_id
        WHERE ca.application_id = ?
    ");
    
    $query->bind_param("i", $application_id);
    $query->execute();
    $result = $query->get_result();
    
    if ($result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Candidate not found']);
        exit();
    }
    
    $data = $result->fetch_assoc();
    
    // Decode the JSON application form
    $data['application_form'] = json_decode($data['application_form'], true);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $data]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>