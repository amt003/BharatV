<?php
session_start();
require_once 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['ro_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (isset($_GET['application_id'])) {
    $application_id = $_GET['application_id'];
    
    // Get candidate details including application form data
    $query = $conn->prepare("
        SELECT 
            ca.application_id,
            ca.application_form,
            ca.application_type,
            ca.independent_party_name,
            ca.independent_party_symbol,
            u.name,
            u.phone,
            u.email,
            p.party_name,
            w.ward_name,
            e.Election_title
        FROM candidate_applications ca
        JOIN users u ON ca.id = u.id
        JOIN wards w ON ca.ward_id = w.ward_id
        JOIN elections e ON ca.election_id = e.election_id
        LEFT JOIN parties p ON ca.party_id = p.party_id
        WHERE ca.application_id = ?
    ");
    
    $query->bind_param("i", $application_id);
    $query->execute();
    $result = $query->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Decode the JSON application form data
        $application_form = json_decode($row['application_form'], true);
        
        // Combine all data
        $response = [
            'success' => true,
            'data' => [
                'name' => $row['name'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'ward_name' => $row['ward_name'],
                'election_title' => $row['Election_title'],
                'party_name' => $row['party_name'] ?? 'Independent',
                'application_type' => $row['application_type'],
                'independent_party_name' => $row['independent_party_name'],
                'independent_party_symbol' => $row['independent_party_symbol'],
                'application_form' => $application_form
            ]
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Candidate not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No application ID provided']);
}
?>