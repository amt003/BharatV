<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

try {
    include 'db.php';
    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_GET['election_id'])) {
        die(json_encode(['success' => false, 'message' => 'Invalid request: Missing user_id or election_id']));
    }

    $userId = $_SESSION['user_id'];
    $electionId = $_GET['election_id'];

    // Log request parameters
    error_log("Report request - User ID: $userId, Election ID: $electionId");

    // Get basic election information
    $query = "SELECT 
        e.election_id,
        e.Election_title as election_title,
        e.start_date,
        e.end_date,
        w.ward_id,
        w.ward_name,
        cc.contesting_id,
        cc.application_type,
        CASE 
            WHEN cc.application_type = 'party' THEN p.party_name
            ELSE cc.independent_party_name
        END as party_name,
        COALESCE(r.votes_received, 0) as votes_received,
        COALESCE(r.is_winner, 0) as is_winner,
        (SELECT COALESCE(SUM(votes_received), 0)  FROM results WHERE election_id = e.election_id) as total_votes
    FROM users u
    JOIN contesting_candidates cc ON cc.id = u.id
    JOIN elections e ON cc.election_id = e.election_id
    JOIN wards w ON cc.ward_id = w.ward_id
    LEFT JOIN parties p ON cc.party_id = p.party_id
    LEFT JOIN results r ON r.contesting_id = cc.contesting_id AND r.election_id = e.election_id
    WHERE u.id = ? AND e.election_id = ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ii", $userId, $electionId);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Get result failed: " . $stmt->error);
    }
    
    if ($row = $result->fetch_assoc()) {
        
        
       // Get total registered voters in the ward
       $votersQuery = "SELECT COUNT(*) as total FROM users 
       WHERE ward_id = ? 
       AND status = '1' 
       AND approved_by_admin = '1'";
$votersStmt = $conn->prepare($votersQuery);
$votersStmt->bind_param("i", $row['ward_id']);
$votersStmt->execute();
$votersResult = $votersStmt->get_result();
$totalRegisteredVoters = $votersResult->fetch_assoc()['total'];

// Log the total registered voters
error_log("Total registered voters in ward {$row['ward_id']}: $totalRegisteredVoters");
        
        // Calculate voter turnout
        $voterTurnout = $totalRegisteredVoters > 0 ? 
            round(($row['total_votes'] / $totalRegisteredVoters) * 100, 2) : 0;
        
        // Calculate vote percentage
        $votePercentage = $row['total_votes'] > 0 ? 
            round(($row['votes_received'] / $row['total_votes']) * 100, 2) : 0;
        
        // Get candidate position (rank) - fixed query
        $rankQuery = "SELECT position FROM (
          SELECT 
              contesting_id,
              votes_received,
              @rank := @rank + 1 as position
          FROM 
              results, 
              (SELECT @rank := 0) r
          WHERE 
              election_id = ? AND ward_id = ?
          ORDER BY 
              votes_received DESC
      ) ranked WHERE contesting_id = ?";
      
      $rankStmt = $conn->prepare($rankQuery);
      $rankStmt->bind_param("iii", $electionId, $row['ward_id'], $row['contesting_id']);
      $rankStmt->execute();
      $rankResult = $rankStmt->get_result();
      $position = 0;
      if ($rankRow = $rankResult->fetch_assoc()) {
          $position = $rankRow['position'];
      } else {
          // Alternative method if the above doesn't work
          $positionQuery = "SELECT COUNT(*) + 1 as position FROM results 
                           WHERE election_id = ? AND ward_id = ? AND votes_received > ?";
          $positionStmt = $conn->prepare($positionQuery);
          $positionStmt->bind_param("iii", $electionId, $row['ward_id'], $row['votes_received']);
          $positionStmt->execute();
          $positionResult = $positionStmt->get_result();
          $position = $positionResult->fetch_assoc()['position'];
      }
        
       // Get total candidates in the election
$candidatesQuery = "SELECT COUNT(*) as total FROM contesting_candidates 
WHERE election_id = ?";
$candidatesStmt = $conn->prepare($candidatesQuery);
$candidatesStmt->bind_param("i", $electionId);
$candidatesStmt->execute();
$candidatesResult = $candidatesStmt->get_result();
$totalCandidates = $candidatesResult->fetch_assoc()['total'];
        
        $report = [
            'success' => true,
            'report' => [
                'election_id' => $electionId,
                'election_title' => $row['election_title'],
                'start_date' => date('d M Y', strtotime($row['start_date'])),
                'end_date' => isset($row['end_date']) ? date('d M Y', strtotime($row['end_date'])) : '',
                'ward_name' => $row['ward_name'],
                'party_type' => $row['application_type'],
                'party_name' => $row['party_name'],
                'votes_received' => (int)$row['votes_received'],
                'vote_percentage' => $votePercentage,
                'position' => $position,
                'total_candidates' => $totalCandidates,
                'voter_turnout' => $voterTurnout,
                'total_votes' => (int)$row['total_votes'],
                'total_registered_voters' => $totalRegisteredVoters,
                'is_winner' => (bool)$row['is_winner']
            ]
        ];
        
        echo json_encode($report);
    } else {
        echo json_encode(['success' => false, 'message' => 'No report data found for this election']);
    }
} catch (Exception $e) {
    error_log("Error in generate_election_report.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>