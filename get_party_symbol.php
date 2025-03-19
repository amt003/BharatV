<?php
require_once 'db.php';

if (isset($_GET['party_id'])) {
    $party_id = $_GET['party_id'];
    
    $stmt = $conn->prepare("SELECT party_symbol FROM parties WHERE party_id = ?");
    $stmt->bind_param("i", $party_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $party = $result->fetch_assoc();
    
    if ($party && $party['party_symbol']) {
        header("Content-Type: image/jpeg");
        echo $party['party_symbol'];
    }
}
?> 