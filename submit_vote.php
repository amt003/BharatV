<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['voter', 'candidate'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to vote.']);
    exit();
}

if (!isset($_POST['election_id']) || !isset($_POST['contesting_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$election_id = intval($_POST['election_id']);
$contesting_id = intval($_POST['contesting_id']);

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if user has already voted
    $check_vote = $conn->prepare("SELECT * FROM votes WHERE id = ? AND election_id = ?");
    $check_vote->bind_param("ii", $user_id, $election_id);
    $check_vote->execute();
    
    if ($check_vote->get_result()->num_rows > 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'You have already voted in this election.']);
        exit();
    }

    // Insert vote
    $insert_vote = $conn->prepare("INSERT INTO votes (id, election_id, contesting_id, casted_at) VALUES (?, ?, ?, NOW())");
    $insert_vote->bind_param("iii", $user_id, $election_id, $contesting_id);
    
    if ($insert_vote->execute()) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Vote submitted successfully!']);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error recording vote.']);
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error processing vote: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>
