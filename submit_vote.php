<?php
include 'db.php';
session_start();

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['voter', 'candidate'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("<p class='error'>Invalid request.</p>");
}

if (!isset($_POST['election_id']) || !isset($_POST['contesting_id'])) {
    die("<p class='error'>Invalid submission.</p>");
}

$election_id = intval($_POST['election_id']);
$contesting_id = intval($_POST['contesting_id']);
$user_id = $_SESSION['user_id'];

// Check if election is ongoing
$query = "SELECT *, 
          CASE 
              WHEN CURDATE() BETWEEN start_date AND end_date THEN 'ongoing'
              WHEN CURDATE() > end_date THEN 'completed'
              ELSE status 
          END AS dynamic_status 
          FROM elections WHERE election_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();

if (!$election || $election['dynamic_status'] !== 'ongoing') {
    die("<p class='error'>This election is not active.</p>");
}

// Check if user has already voted
$query = "SELECT * FROM votes WHERE id = ? AND contesting_id IN 
          (SELECT contesting_id FROM contesting_candidates WHERE Election_id = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $election_id);
$stmt->execute();
$existing_vote = $stmt->get_result()->fetch_assoc();

if ($existing_vote) {
    die("<p class='error'>You have already voted in this election.</p>");
}

// Verify the selected candidate belongs to the election
$query = "SELECT * FROM contesting_candidates WHERE contesting_id = ? AND Election_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $contesting_id, $election_id);
$stmt->execute();
$valid_candidate = $stmt->get_result()->fetch_assoc();

if (!$valid_candidate) {
    die("<p class='error'>Invalid candidate selection.</p>");
}

// Insert the vote
$query = "INSERT INTO votes (id, contesting_id, election_id, casted_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $contesting_id, $election_id);
if ($stmt->execute()) {
    echo "<p class='success'>Your vote has been successfully submitted.</p>";
    header("refresh:3; url=candidate.php");
} else {
    echo "<p class='error'>Failed to submit vote. Please try again.</p>";
}
?>
