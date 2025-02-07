<?php
include 'db.php';

if(isset($_GET['party_id'])) {
    $party_id = mysqli_real_escape_string($conn, $_GET['party_id']);
    
    $sql = "SELECT party_name FROM parties WHERE party_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $party_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        echo json_encode(['party_name' => $row['party_name']]);
    } else {
        echo json_encode(['error' => 'Party not found']);
    }
} else {
    echo json_encode(['error' => 'No party ID provided']);
}
?>
