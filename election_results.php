<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) && $_SESSION['role'] != 'candidate' && $_SESSION['role'] != 'voter') {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_results') {
    $election_id = isset($_POST['election_id']) ? intval($_POST['election_id']) : 0;
    $user_id = $_SESSION['user_id'];
    
    // Get user's ward
    $ward_query = "SELECT ward_id FROM users WHERE id = ?";
    $stmt = $conn->prepare($ward_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_ward = $result->fetch_assoc()['ward_id'];

    if (!$election_id) {
        echo json_encode(['error' => true, 'message' => 'Invalid election ID']);
        exit();
    }

    try {
        // First check if this election is for user's ward
        $check_query = "SELECT 1 FROM elections WHERE election_id = ? AND FIND_IN_SET(?, ward_ids)";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $election_id, $user_ward);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            echo json_encode(['error' => true, 'message' => 'No results available for your ward in this election.']);
            exit();
        }

        // Get results only for user's ward
        $query = "SELECT 
                    COALESCE(r.votes_received, 0) as votes_received,
                    u.name,
                    w.ward_name,
                    CASE 
                        WHEN cc.party_id IS NULL THEN 1 
                        ELSE 0 
                    END as is_independent,
                    p.party_name,
                    e.Election_title
                FROM contesting_candidates cc
                JOIN users u ON cc.id = u.id
                JOIN wards w ON cc.ward_id = w.ward_id
                JOIN elections e ON cc.election_id = e.election_id
                LEFT JOIN parties p ON cc.party_id = p.party_id
                LEFT JOIN results r ON r.contesting_id = cc.contesting_id
                WHERE cc.election_id = ? 
                AND cc.ward_id = ?  -- Only show results for user's ward
                ORDER BY COALESCE(r.votes_received, 0) DESC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $election_id, $user_ward);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['error' => true, 'message' => 'No results available for your ward in this election.']);
            exit();
        }

        $results = [];
        while ($row = $result->fetch_assoc()) {
            $row['votes_received'] = $row['votes_received'] ?? 0;
            $results[] = $row;
        }

        header('Content-Type: application/json');
        echo json_encode($results);
        
    } catch (Exception $e) {
        echo json_encode(['error' => true, 'message' => 'Error fetching results']);
    }
    exit();
}

echo json_encode(['error' => true, 'message' => 'Invalid request']);
exit();
?>