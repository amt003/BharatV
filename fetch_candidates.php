<?php
require_once 'db.php';

if (isset($_GET['election_id'])) {
    $election_id = $_GET['election_id'];
    
    // Query to fetch approved candidates
    $query = $conn->prepare("
        SELECT 
            cc.id,
            cc.application_type,
            cc.independent_party_name,
            cc.independent_party_symbol,
            u.name,
            u.phone,
            u.email,
            u.profile_photo,  
            w.ward_name,
            p.party_name,
            p.party_symbol
        FROM contesting_candidates cc
        JOIN users u ON cc.id = u.id
        JOIN wards w ON cc.ward_id = w.ward_id
        LEFT JOIN parties p ON cc.party_id = p.party_id
        WHERE cc.Election_id = ?
        ORDER BY w.ward_name, u.name
    ");
    
    $query->bind_param("i", $election_id);
    $query->execute();
    $result = $query->get_result();
    
    if ($result->num_rows > 0) {
        ?>
        <style>
            .candidates-list {
                list-style-type: none;
                padding: 0;
                margin: 0;
            }

            .candidate-card {
                background: white;
                margin: 10px 0;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .candidate-name {
                font-size: 1.1em;
                font-weight: 600;
                color: #333;
                margin-bottom: 5px;
            }

            .candidate-details {
                color: #666;
                font-size: 0.9em;
            }

            .ward-section {
                margin: 20px 0;
            }

            .ward-title {
                background: #f5f5f5;
                padding: 10px;
                margin: 15px 0;
                border-radius: 5px;
                font-weight: 600;
            }

            .party-name {
                color: #2196F3;
                font-weight: 500;
            }

            .contact-info {
                margin-top: 5px;
                font-size: 0.85em;
            }
            .candidate-image {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                object-fit: cover;
                border: 2px solid #ddd;
            }
        </style>

        <?php
        // Group candidates by ward
        $current_ward = '';
        echo '<div class="candidates-list">';

        while ($candidate = $result->fetch_assoc()) {
            if ($current_ward !== $candidate['ward_name']) {
                if ($current_ward !== '') {
                    echo '</div>'; // Close previous ward section
                }
                $current_ward = $candidate['ward_name'];
                echo '<div class="ward-section">';
                echo '<div class="ward-title">Ward: ' . htmlspecialchars($candidate['ward_name']) . '</div>';
            }

            // Determine party name based on application type
            $party_name = $candidate['application_type'] === 'independent' 
                ? $candidate['independent_party_name'] . ' (Independent)'
                : $candidate['party_name'];
            ?>
            <div class="candidate-card">
                <img src="assets/candidate.jpg" alt="Candidate Image" class="candidate-image">
                <div class="candidate-name">
                    Name: <?php echo htmlspecialchars($candidate['name']); ?>
                </div>
                <div class="candidate-details">
                    <span class="party-name">
                        Party Name: <?php echo htmlspecialchars($party_name); ?>
                    </span>

                    <div class="contact-info">
                        Phone Number: <i class="fas fa-phone"></i> <?php echo htmlspecialchars($candidate['phone']); ?>
                        <br>
                        Email: <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($candidate['email']); ?>
                    </div>
                </div>
            </div>
            <?php
        }
        echo '</div>'; // Close last ward section
        echo '</div>'; // Close candidates list
        
    } else {
        echo '<div class="no-candidates">No candidates found for this election.</div>';
    }
} else {
    echo '<div class="error">No election ID provided.</div>';
}
?>