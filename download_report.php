<?php
require('fpdf/fpdf.php'); 
include 'db.php'; 
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['election_id'])) {
    die('Invalid request');
}

$userId = $_SESSION['user_id'];
$electionId = $_GET['election_id'];

// Fetch election data
$query = "SELECT 
    e.election_id,
    e.Election_title as election_title,
    e.start_date,
    e.end_date,
    w.ward_name,
    cc.application_type,
    u.name, 
    CASE 
        WHEN cc.application_type = 'party' THEN p.party_name
        ELSE cc.independent_party_name
    END as party_name,
    COALESCE(r.votes_received, 0) as votes_received,
    COALESCE(r.is_winner, 0) as is_winner,
    (SELECT COALESCE(SUM(votes_received), 0) FROM results WHERE election_id = e.election_id) as total_votes
FROM users u
JOIN contesting_candidates cc ON cc.id = u.id
JOIN elections e ON cc.election_id = e.election_id
JOIN wards w ON cc.ward_id = w.ward_id
LEFT JOIN parties p ON cc.party_id = p.party_id
LEFT JOIN results r ON r.contesting_id = cc.contesting_id AND r.election_id = e.election_id
WHERE u.id = ? AND e.election_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $electionId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die('No election report found.');
}

// Extend FPDF class for Header and Footer
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(0, 102, 204); // Blue Background
        $this->SetTextColor(255); // White Text
        $this->Cell(0, 12, 'Election Performance Report', 0, 1, 'C', true);
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Create PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);
$pdf->SetFillColor(230, 230, 230); // Light Gray for section background
$pdf->SetTextColor(0); // Black text

// Election Details Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Election Details', 0, 1, 'L', true);
$pdf->Ln(3);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, 'Election:', 1, 0, 'L', true);
$pdf->Cell(140, 10, $row['election_title'], 1, 1, 'L');

$pdf->Cell(50, 10, 'Ward:', 1, 0, 'L', true);
$pdf->Cell(140, 10, $row['ward_name'], 1, 1, 'L');

$pdf->Cell(50, 10, 'Start Date:', 1, 0, 'L', true);
$pdf->Cell(140, 10, date('d M Y', strtotime($row['start_date'])), 1, 1, 'L');

$pdf->Cell(50, 10, 'End Date:', 1, 0, 'L', true);
$pdf->Cell(140, 10, date('d M Y', strtotime($row['end_date'])), 1, 1, 'L');

$pdf->Ln(5); // Space between sections

// Candidate Information Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Candidate Information', 0, 1, 'L', true);
$pdf->Ln(3);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, 'Candidate Name:', 1, 0, 'L', true);
$pdf->Cell(140, 10, ucfirst($row['name']), 1, 1, 'L');
$pdf->Cell(50, 10, 'Party Type:', 1, 0, 'L', true);
$pdf->Cell(140, 10, ucfirst($row['application_type']), 1, 1, 'L');
$pdf->Cell(50, 10, 'Party Name:', 1, 0, 'L', true);
$pdf->Cell(140, 10, $row['party_name'], 1, 1, 'L');

$pdf->Ln(5); // Space between sections

// Voting Results Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Voting Results', 0, 1, 'L', true);
$pdf->Ln(3);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, 'Votes Received:', 1, 0, 'L', true);
$pdf->Cell(140, 10, number_format($row['votes_received']), 1, 1, 'L');

$pdf->Cell(50, 10, 'Total Votes:', 1, 0, 'L', true);
$pdf->Cell(140, 10, number_format($row['total_votes']), 1, 1, 'L');

$pdf->Cell(50, 10, 'Winner:', 1, 0, 'L', true);
$pdf->Cell(140, 10, $row['is_winner'] ? 'Yes' : 'No', 1, 1, 'L');

// Final Output as Download
$pdf->Output('D', 'Election_Report.pdf'); 

?>
