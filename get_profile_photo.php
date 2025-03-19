<?php
include 'db.php';

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    $query = "SELECT profile_photo FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        header("Content-Type: image/jpeg");
        echo $row['profile_photo'];
    }
}
?> 