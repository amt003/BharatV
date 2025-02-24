<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'candidate') {
    header('Location: login.php');
    exit();
}
$candidate_id = $_SESSION['user_id'];

// Query for party-affiliated applications
$party_applications_query = "
SELECT 
    'party' as application_type,
    ca.application_id,
    ca.application_status,
    ca.created_at,
    e.Election_title,
    w.ward_name,
    p.party_name,
    NULL as independent_party_name,
    NULL as independent_party_symbol
FROM candidate_applications ca
JOIN elections e ON ca.election_id = e.election_id
JOIN wards w ON ca.ward_id = w.ward_id
JOIN parties p ON ca.party_id = p.party_id
WHERE ca.id = ?";

// Query for independent applications
$independent_applications_query = "
SELECT 
    'independent' as application_type,
    c.contesting_id as application_id,
    'approved' as application_status,
    c.added_at as created_at,
    e.Election_title,
    w.ward_name,
    NULL as party_name,
    c.independent_party_name,
    c.independent_party_symbol
FROM contesting_candidates c
JOIN elections e ON c.election_id = e.election_id
JOIN wards w ON c.ward_id = w.ward_id
WHERE c.id = ? AND c.independent_party_name IS NOT NULL";

// Combine results using UNION
$combined_query = "($party_applications_query) UNION ($independent_applications_query) ORDER BY created_at DESC";

$stmt = $conn->prepare($combined_query);
$stmt->bind_param("ii", $candidate_id, $candidate_id);
$stmt->execute();
$applications_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Application Status</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #e3ffe7, #d9e7ff);
            margin: 0;
            padding: 0;
        }
        
        .applications-section {
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            max-width: 900px;
        }
        
        .applications-section h2 {
            margin-bottom: 1.5rem;
            color: #2c3e50;
            font-size: 1.8rem;
            text-align: center;
            font-weight: 600;
        }
        
        .applications-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .application-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .application-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        .application-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .application-header h3 {
            margin: 0;
            font-size: 1.2rem;
            color: #34495e;
            font-weight: 600;
        }
        
        .application-details p {
            margin: 0.5rem 0;
            color: #555;
            font-size: 1rem;
        }
        
        .status-badge {
            padding: 0.35rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status-pending {
            background: #ffeb99;
            color: #856404;
        }
        
        .status-approved {
            background: #b0f5c5;
            color: #1e7e34;
        }
        
        .status-rejected {
            background: #f5b7b1;
            color: #922b21;
        }
        
        .independent-candidate {
            color: #2980b9;
            font-weight: 600;
        }
        
        .party-symbol {
            max-width: 50px;
            max-height: 50px;
            margin-top: 0.5rem;
        }
        
        .no-applications {
            text-align: center;
            padding: 2rem;
            color: #666;
            background: #f8f9fa;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .applications-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="applications-section">
        <h2>My Applications</h2>
        
        <?php if($applications_result->num_rows > 0): ?>
            <div class="applications-grid">
                <?php while($application = $applications_result->fetch_assoc()): ?>
                    <div class="application-card">
                        <div class="application-header">
                            <h3><?php echo htmlspecialchars($application['Election_title']); ?></h3>
                            <span class="status-badge status-<?php echo $application['application_status']; ?>">
                                <?php echo ucfirst($application['application_status']); ?>
                            </span>
                        </div>
                        <div class="application-details">
                            <p><strong>Ward:</strong> <?php echo htmlspecialchars($application['ward_name']); ?></p>
                            <p><strong>Party:</strong> 
                                <?php if($application['application_type'] === 'independent'): ?>
                                    <span class="independent-candidate">
                                        <?php echo htmlspecialchars($application['independent_party_name']); ?> (Independent)
                                    </span>
                                    <?php if($application['independent_party_symbol']): ?>
                                        <br>
                                        <img src="<?php echo htmlspecialchars($application['independent_party_symbol']); ?>" 
                                             alt="Party Symbol" 
                                             class="party-symbol">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($application['party_name']); ?>
                                <?php endif; ?>
                            </p>
                            <p><strong>Applied on:</strong> <?php echo date('M d, Y', strtotime($application['created_at'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-applications">
                <p>You haven't submitted any applications yet.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>