<?php
// Start output buffering to catch any accidental output
ob_start();

include 'db.php';
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['voter', 'candidate'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Please log in to vote.',
        'type' => 'error',
        'icon' => 'fas fa-exclamation-circle',
        'title' => 'Authentication Error'
    ]);
    // End output buffering and send response
    ob_end_flush();
    exit();
}

if (!isset($_POST['election_id']) || !isset($_POST['contesting_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required voting data. Please ensure you selected a candidate.',
        'type' => 'error',
        'icon' => 'fas fa-times-circle',
        'title' => 'Form Error'
    ]);
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
        echo json_encode([
            'success' => false, 
            'message' => 'You have already cast your vote in this election.',
            'type' => 'error',
            'icon' => 'fas fa-ban',
            'title' => 'Duplicate Vote'
        ]);
        exit();
    }

    // Get the user's ward_id from their profile
    $get_ward = $conn->prepare("SELECT ward_id FROM users WHERE id = ?");
    $get_ward->bind_param("i", $user_id);
    $get_ward->execute();
    $ward_result = $get_ward->get_result();
    
    if ($ward_result->num_rows === 0) {
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => 'Unable to determine your ward. Please contact support.',
            'type' => 'error',
            'icon' => 'fas fa-map-marker-alt',
            'title' => 'Ward Error'
        ]);
        exit();
    }

    $user_data = $ward_result->fetch_assoc();
    $ward_id = $user_data['ward_id'];
    
    // Verify ward_id is not null
    if ($ward_id === null) {
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => 'Your ward information is missing. Please update your profile or contact support.',
            'type' => 'error',
            'icon' => 'fas fa-exclamation-triangle',
            'title' => 'Profile Error'
        ]);
        exit();
    }

    // Insert vote with ward_id
    $insert_vote = $conn->prepare("INSERT INTO votes (id, election_id, contesting_id, ward_id, casted_at) VALUES (?, ?, ?, ?, NOW())");
    $insert_vote->bind_param("iiii", $user_id, $election_id, $contesting_id, $ward_id);
    
    if ($insert_vote->execute()) {
        $conn->commit();
        
        // Get election name for success message
        $election_query = $conn->prepare("SELECT election_title FROM elections WHERE election_id = ?");
        $election_query->bind_param("i", $election_id);
        $election_query->execute();
        $election_result = $election_query->get_result();
        $election_name = "this election";
        
        if ($election_result->num_rows > 0) {
            $election_data = $election_result->fetch_assoc();
            $election_name = htmlspecialchars($election_data['election_title']);
        }
        
        // Get user email
        $email_query = $conn->prepare("SELECT email, name FROM users WHERE id = ?");
        $email_query->bind_param("i", $user_id);
        $email_query->execute();
        $email_result = $email_query->get_result();
        
        if ($email_result->num_rows > 0) {
            $user_data = $email_result->fetch_assoc();
            $user_email = $user_data['email'];
            $user_name = $user_data['name'];
            
            // Send email confirmation
            $to = $user_email;
            $subject = "Vote Confirmation - " . $election_name;
            
            // Get the candidate name
            $candidate_query = $conn->prepare("
                SELECT u.name as candidate_name, 
                       COALESCE(cc.independent_party_name, p.party_name) as party_name 
                FROM contesting_candidates cc
                JOIN users u ON cc.id = u.id
                LEFT JOIN parties p ON cc.party_id = p.party_id
                WHERE cc.contesting_id = ?
            ");
            $candidate_query->bind_param("i", $contesting_id);
            $candidate_query->execute();
            $candidate_result = $candidate_query->get_result();
            $candidate_data = $candidate_result->fetch_assoc();
            $candidate_name = $candidate_data['candidate_name'] ?? 'Selected candidate';
            $party_name = $candidate_data['party_name'] ?? 'N/A';
            
            // Email headers
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: BharatV <noreply@bharatv.com>" . "\r\n";
            
            // Email body in HTML
            $message = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                    }
                    .container {
                        max-width: 600px;
                        margin: 0 auto;
                        padding: 20px;
                        border: 1px solid #ddd;
                        border-radius: 5px;
                    }
                    .header {
                        background-color: #2E7D32;
                        color: white;
                        padding: 15px;
                        text-align: center;
                        border-radius: 5px 5px 0 0;
                    }
                    .content {
                        padding: 20px;
                        background-color: #f9f9f9;
                    }
                    .footer {
                        text-align: center;
                        padding: 10px;
                        font-size: 12px;
                        color: #777;
                    }
                    .vote-details {
                        background-color: #fff;
                        padding: 15px;
                        border-radius: 5px;
                        margin-top: 20px;
                        border-left: 4px solid #2E7D32;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Your Vote Has Been Recorded</h2>
                    </div>
                    <div class='content'>
                        <p>Dear " . htmlspecialchars($user_name) . ",</p>
                        <p>Thank you for participating in the democratic process. Your vote for <strong>" . htmlspecialchars($election_name) . "</strong> has been successfully recorded.</p>
                        
                        <div class='vote-details'>
                            <p><strong>Election:</strong> " . htmlspecialchars($election_name) . "</p>
                            <p><strong>Candidate:</strong> " . htmlspecialchars($candidate_name) . "</p>
                            <p><strong>Party:</strong> " . htmlspecialchars($party_name) . "</p>
                            <p><strong>Time:</strong> " . date('F j, Y, g:i a') . "</p>
                        </div>
                        
                        <p>Please keep this email as confirmation of your vote. Your participation is essential to our democratic process.</p>
                        <p>Thank you for using BharatV!</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message. Please do not reply to this email.</p>
                        <p>&copy; " . date('Y') . " BharatV. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            // Send email
            $mail_sent = mail($to, $subject, $message, $headers);
            
            // Log email status for debugging
            error_log("Vote confirmation email " . ($mail_sent ? "sent to " : "failed for ") . $user_email . " for election " . $election_name);
        }
        
        // Make sure to end output buffering before sending response
        if (ob_get_length()) ob_end_clean();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Thank you! Your vote has been successfully recorded in ' . $election_name . '.',
            'type' => 'success',
            'icon' => 'fas fa-check-circle',
            'title' => 'Vote Confirmed'
        ]);
    } else {
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => 'There was an error recording your vote. Please try again or contact support. Error: ' . $conn->error,
            'type' => 'error',
            'icon' => 'fas fa-database',
            'title' => 'Database Error'
        ]);
    }

} catch (Exception $e) {
    // Make sure to end output buffering before sending response
    if (ob_get_length()) ob_end_clean();
    
    $conn->rollback();
    echo json_encode([
        'success' => false, 
        'message' => 'Error processing your vote: ' . $e->getMessage(),
        'type' => 'error',
        'icon' => 'fas fa-exclamation-triangle',
        'title' => 'System Error'
    ]);
} finally {
    $conn->close();
}
?>
