<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'voter', 'candidate'])) {
    header("HTTP/1.1 403 Forbidden");
    echo "Access denied";
    exit();
}

// Validate election_id
if (!isset($_GET['election_id']) || !is_numeric($_GET['election_id'])) {
    header("HTTP/1.1 400 Bad Request");
    echo "Invalid election ID";
    exit();
}

$election_id = intval($_GET['election_id']);

// Get election details
$election_query = $conn->prepare("SELECT election_title FROM elections WHERE election_id = ?");
$election_query->bind_param("i", $election_id);
$election_query->execute();
$election_result = $election_query->get_result();

if ($election_result->num_rows === 0) {
    echo "<p>Election not found.</p>";
    exit();
}

$election = $election_result->fetch_assoc();

// Get candidates for this election
$candidates_query = $conn->prepare("
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

$candidates_query->bind_param("i", $election_id);
$candidates_query->execute();
$candidates_result = $candidates_query->get_result();
?>

<div class="candidates-section">
    <h3>Candidates for <?php echo htmlspecialchars($election['election_title']); ?></h3>
    
    <?php if ($candidates_result->num_rows === 0): ?>
        <p class="no-candidates">No candidates found for this election.</p>
    <?php else: ?>
        <div class="candidates-grid">
            <?php while ($candidate = $candidates_result->fetch_assoc()): ?>
                <div class="candidate-card">
                    <div class="candidate-header">
                        <h4><?php echo htmlspecialchars($candidate['name']); ?></h4>
                        <span class="candidate-type <?php echo $candidate['application_type']; ?>">
                            <?php echo ucfirst($candidate['application_type']); ?>
                        </span>
                    </div>
                    
                    <div class="candidate-photo">
                        
                            <img src="<?php echo $candidate['profile_photo'] ? 'uploads/' . htmlspecialchars($candidate['profile_photo']) : 'uploads/default-avatar.png'; ?>" 
                                 alt="<?php echo htmlspecialchars($candidate['name']); ?>'s photo" 
                                 class="candidate-image">
                        
                    </div>
                    
                    <div class="candidate-details">
                        <p><strong>Ward:</strong> <?php echo htmlspecialchars($candidate['ward_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($candidate['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($candidate['phone']); ?></p>
                        
                        <?php if ($candidate['application_type'] === 'party'): ?>
                            <p><strong>Party:</strong> <?php echo htmlspecialchars($candidate['party_name']); ?></p>
                        <?php else: ?>
                            <p><strong>Independent Party:</strong> <?php echo htmlspecialchars($candidate['independent_party_name']); ?></p>
                            
                        <?php endif; ?>
                    </div>
                    
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.candidates-section {
    margin-top: 20px;
}

.candidates-section h3 {
    margin-bottom: 20px;
    color: #333;
    font-size: 1.5em;
}

.no-candidates {
    text-align: center;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 8px;
    color: #666;
}

.candidates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.candidate-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.candidate-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.candidate-header h4 {
    margin: 0;
    color: #333;
}

.candidate-type {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: 500;
}

.candidate-type.party {
    background-color: #e3f2fd;
    color: #0d47a1;
}

.candidate-type.independent {
    background-color: #fff3e0;
    color: #e65100;
}

.candidate-details p {
    margin: 8px 0;
    color: #555;
}

.symbol-container {
    margin-top: 10px;
}

.party-symbol {
    max-width: 100px;
    max-height: 100px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.approval-status {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
}

.status-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.status-label {
    font-weight: 500;
    color: #555;
}

.status-value {
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.85em;
}

.status-value.approved {
    background-color: #d4edda;
    color: #155724;
}

.status-value.pending {
    background-color: #fff3cd;
    color: #856404;
}

.status-value.rejected {
    background-color: #f8d7da;
    color: #721c24;
}

.loading-spinner {
    text-align: center;
    padding: 40px;
    color: #666;
}

.error-message {
    color: #dc3545;
    text-align: center;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 8px;
}

.candidate-photo {
    text-align: center;
    margin: 15px auto;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.candidate-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    transition: transform 0.3s ease;
}

.candidate-image:hover {
    transform: scale(1.05);
}

@media (max-width: 768px) {
    .candidate-photo {
        width: 150px;
        height: 150px;
    }
}
</style>