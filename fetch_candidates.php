<?php
include 'db.php';
session_start();

if (!isset($_GET['election_id'])) {
    echo "<p>Error: No election specified.</p>";
    exit();
}

$election_id = intval($_GET['election_id']);

// Join query to get candidate details from users table
$query = "
    SELECT 
        cc.contesting_id,
        u.name,
        u.email,
        u.phone,
        p.party_name,
        w.ward_name
    FROM contesting_candidates cc
    JOIN users u ON cc.id = u.id
    JOIN parties p ON cc.party_id = p.party_id
    JOIN wards w ON cc.ward_id = w.ward_id
    WHERE cc.election_id = ?
    ORDER BY w.ward_name, p.party_name";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<p>No candidates found for this election.</p>";
    exit();
}
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
    ?>
    <div class="candidate-card">
    <img src="assets/candidate.jpg" alt="Candidate Image" class="candidate-image">
        <div class="candidate-name">
            Name:<?php echo htmlspecialchars($candidate['name']); ?>
        </div>
        <div class="candidate-details">
            <span class="party-name">
               Party Name: <?php echo htmlspecialchars($candidate['party_name']); ?>
            </span>
            <div class="contact-info">
               Phone Number:<i class="fas fa-phone"></i> <?php echo htmlspecialchars($candidate['phone']); ?>
                <br>
                Email:<i class="fas fa-envelope"></i> <?php echo htmlspecialchars($candidate['email']); ?>
            </div>
        </div>
    </div>
    <?php
}
echo '</div>'; // Close last ward section
echo '</div>'; // Close candidates list
?>