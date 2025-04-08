<?php
session_start();
include 'db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'role_update_errors.log');

// Check if it's an AJAX request (more reliable detection)
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Add a direct parameter check as a more reliable method
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    $isAjax = true;
}

// Check authentication for AJAX requests
if ($isAjax) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access'
        ]);
        exit();
    }
}

// Function to update candidate roles to voter
function updateCandidateRoles($conn) {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Get all candidates who contested in elections with published results from 2 days ago
        $query = "SELECT DISTINCT u.id, u.name, e.election_id, e.Election_title, e.end_date, e.result_status, e.status, e.results_published_date
                  FROM users u 
                  JOIN contesting_candidates cc ON cc.id = u.id 
                  JOIN elections e ON cc.election_id = e.election_id 
                  WHERE u.role = 'candidate' 
                  AND e.result_status = 'published' 
                  AND e.status = 'completed'
                  AND e.results_published_date IS NOT NULL
                  AND DATEDIFF(CURDATE(), e.results_published_date) >= 2";
        
        // Log the query for debugging
        error_log("Executing query: " . $query);
        
        $result = $conn->query($query);
        
        if (!$result) {
            throw new Exception("Query error: " . $conn->error);
        }
        
        $updated_count = 0;
        $candidates_info = [];
        
        if ($result->num_rows > 0) {
            $candidate_ids = [];
            while ($row = $result->fetch_assoc()) {
                $candidate_ids[] = $row['id'];
                $candidates_info[] = [
                    'name' => $row['name'],
                    'election' => $row['Election_title'],
                    'published_date' => $row['results_published_date']
                ];
                $days_diff = floor((strtotime(date('Y-m-d')) - strtotime($row['results_published_date'])) / (60 * 60 * 24));
                error_log("Found candidate: ID=" . $row['id'] . 
                         ", Name=" . $row['name'] . 
                         ", Election=" . $row['Election_title'] . 
                         ", Results Published Date=" . $row['results_published_date'] . 
                         ", Days since results published=" . $days_diff);
            }
            
            // Update role to voter for all matching candidates
            if (!empty($candidate_ids)) {
                $ids_string = implode(',', $candidate_ids);
                $update_query = "UPDATE users SET role = 'voter' WHERE id IN ($ids_string)";
                
                error_log("Executing update query: " . $update_query);
                
                if (!$conn->query($update_query)) {
                    throw new Exception("Error updating candidate roles: " . $conn->error);
                }
                
                $updated_count = count($candidate_ids);
                error_log("Successfully updated " . $updated_count . " candidates to voters");
            }
        } else {
            error_log("No candidates found matching the criteria");
        }
        
        // If everything is successful, commit the transaction
        $conn->commit();
        
        return [
            'success' => true,
            'message' => $updated_count > 0 
                ? "Successfully updated $updated_count candidates to voters" 
                : "No candidates found eligible for role update",
            'updated_count' => $updated_count,
            'candidates' => $candidates_info
        ];
        
    } catch (Exception $e) {
        // If there's an error, rollback the transaction
        $conn->rollback();
        error_log("Error in updateCandidateRoles: " . $e->getMessage());
        return [
            'success' => false,
            'message' => "Error updating roles: " . $e->getMessage()
        ];
    }
}

// Execute the update
$result = updateCandidateRoles($conn);

// Handle the response based on request type
if ($isAjax) {
    // If it's an AJAX request, return JSON
    header('Content-Type: application/json');
    echo json_encode($result);
} else {
    // If it's a direct access or cron job, output text
    echo "Update Candidate Roles Process\n";
    echo "-------------------------------\n";
    echo "Result: " . ($result['success'] ? "Success" : "Failed") . "\n";
    echo "Message: " . $result['message'] . "\n";
    if (!empty($result['candidates'])) {
        echo "\nUpdated Candidates:\n";
        foreach ($result['candidates'] as $candidate) {
            echo "- {$candidate['name']} (Election: {$candidate['election']}, Published: {$candidate['published_date']})\n";
        }
    }
}

// Close the database connection
$conn->close();
?> 