<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'voter') {
    header("Location: login.php");
    exit();
}

// Get user details (including profile photo)
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userProfilePhoto = $user['profile_photo'] ?? null; // Get the profile photo or set to null if not available


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bharat V - Voting Made Simple</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #2E7D32;
            --light-green: #4CAF50;
            --dark-green: #1B5E20;
            --primary-orange: #F57C00;
            --light-orange: #FF9800;
            --dark-orange: #E65100;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
            position: relative;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--primary-green);
            color: white;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
        }

        .brand {
            padding: 25px 30px;
            
        }

        .brand h1 {
            font-size: 32px;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--light-orange);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .brand p {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 300;
            color: white;
        }

        .nav-menu {
            flex: 1;
            padding: 0 15px;
            overflow-y: auto;
        }

        .nav-menu button {
            width: 100%;
            padding: 14px 20px;
            margin: 8px 0;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            text-align: left;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 10px;
            display: flex;
            align-items: center;
        }

        .nav-menu button:hover {
            background: var(--primary-orange);
            transform: translateX(5px);
        }

        .sidebar-logout {
            padding: 20px;
            margin-top: auto;
            position: sticky;
            bottom: 0;
            background: var(--primary-green);
            width: 100%;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-logout button {
            width: 100%;
            padding: 14px 20px;
            background: var(--dark-orange);
            border: none;
            color: white;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 10px;
            font-weight: 500;
            font-size: 15px;
        }

        .sidebar-logout button:hover {
            background: var(--primary-orange);
            transform: translateY(-2px);
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 280px;
            flex: 1;
            min-height: 100vh;
        }

        .header {
            background: white;
            padding: 20px 30px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            border-bottom: 3px solid var(--light-green);
        }

        .header p {
    margin-left: auto;
    font-size: 18px;
    font-weight: 500;
    color: var(--dark-green);
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--light-green);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-left: 10px;
        }

        .header p span {
            color: white;
            padding: 8px 15px;
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--light-orange) 100%);
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .content {
            padding: 30px;
            flex-grow: 1;
            background-color: #f8f9fa;
        }

        .content-box {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
            transition: transform 0.3s ease;
            border-top: 4px solid var(--primary-green);
        }

        .content-box:hover {
            transform: translateY(-5px);
        }

        .content-box h2 {
            margin-bottom: 20px;
            color: var(--primary-green);
            font-size: 24px;
            font-weight: 600;
        }

        .content-box p {
            color: #4b5563;
            line-height: 1.6;
            font-size: 15px;
        }

        /* Add smooth transitions */
        button, .content-box {
            transition: all 0.3s ease;
        }

        /* Add hover effects */
        button:active {
            transform: scale(0.98);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--light-green);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-green);
        }

        /* Add these styles to your existing CSS */
        .hamburger-menu {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--dark-green);
            cursor: pointer;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .hamburger-menu:hover {
            color: var(--primary-orange);
            transform: scale(1.1);
        }

        .sidebar {
            transition: all 0.3s ease;
            width: 280px;
            min-width: 280px;
        }

        .sidebar.collapsed {
            margin-left: -280px;
        }

        .main-content {
            transition: all 0.3s ease;
            margin-left: 280px;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Show hamburger menu on all screen sizes */
        .hamburger-menu {
            display: block;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }
            
            .sidebar.collapsed {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.expanded {
                margin-left: 280px;
            }
        }
    </style>    
</head>
<body>
    

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="brand">
                <h1>Bharat V</h1>
                <p>Voting Made Simple</p>
            </div>
            <div class="nav-menu">
                <button onclick="loadContent('voter_profile')">Profile</button>
                <button onclick="loadContent('election_updates')">Election Update</button>

                <button onclick="loadContent('results')">View results</button>
                
            </div>
            <div class="sidebar-logout">
                <button onclick="logout()">Logout</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <button id="sidebarToggle" class="hamburger-menu">☰</button>
                <p>Hello, 
                    <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <img class="profile-image" src="<?php echo $userProfilePhoto ? 'uploads/' . htmlspecialchars($userProfilePhoto) : 'uploads/default-avatar.png'; ?>" alt="Profile Image">
                </p>
            </div>
            <div class="content">
                <div class="content-box" id="dynamicContent">
                    <h2>Welcome to Bharat V</h2>
                    <p>Select an option from the menu to get started with your voting experience.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
         function loadContent(page, electionId = null) {
    let url = `${page}.php`;
    
    // If it's the fetch_candidates page and no electionId is provided, 
    // show a message asking to select an election first
    if (page === 'fetch_candidates' && !electionId) {
        document.getElementById('dynamicContent').innerHTML = `
            <h2>View Candidates</h2>
            <p>Please select an election from the Election Updates page to view its candidates.</p>`;
        return;
    }

    // Append election ID if provided
    if (electionId) {
        url += `?election_id=${electionId}`;
    }

    fetch(url)
        .then(response => response.text())
        .then(data => {
            document.getElementById('dynamicContent').innerHTML = data;
       
            if (page === 'vote') {
                initializeVoteForm();
            }  
        })
        .catch(error => {
            console.error('Error loading content:', error);
        });
}

            function logout() {
                fetch('logout.php')
                    .then(() => {
                        window.location.href = 'login.php';
                    });
            }
            // Add this function to handle vote form submission
function initializeVoteForm() {
    const voteForm = document.querySelector('form[action="submit_vote.php"]');
    if (voteForm) {
        voteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            fetch('submit_vote.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Vote submitted successfully!');
                    loadContent('election_updates'); // Return to election updates
                } else {
                    alert(data.message || 'Error submitting vote');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting vote');
            });
        });
    }
}
        //modal    
        document.addEventListener("DOMContentLoaded", function () {
            document.body.addEventListener("click", function (event) {
                if (event.target.classList.contains("view-candidates-button")) {
                    let electionId = event.target.getAttribute("data-election-id");
                    openCandidateModal(electionId);
                }
            });
        });

        function openCandidateModal(electionId) {
            let modal = document.getElementById("candidateModal");
            let modalContent = document.getElementById("modalContent");

            fetch(`fetch_candidates.php?election_id=${electionId}`)
                .then(response => response.text())
                .then(data => {
                    modalContent.innerHTML = data;
                    modal.style.display = "flex"; // Show modal
                })
                .catch(error => console.error('Error fetching candidates:', error));
        }

        function closeModal() {
            document.getElementById("candidateModal").style.display = "none";
        }
    </script>
<!--candidate modal-->
<div id="candidateModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="modalContent">
            <!-- Candidate details will be loaded here -->
        </div>
    </div>
</div>

 <!-- election updates page results modal-->
 <div id="resultsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeResultsModal()">&times;</span>
            <div id="resultsContent"></div>
        </div>
    </div>
<!--election updates page results modal-->
<script>
function loadResults(electionId) {
    const formData = new FormData();
    formData.append('action', 'get_results');
    formData.append('election_id', electionId);

    fetch('election_results.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.message);
        } else {
            displayResults(data);
            openResultsModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading results');
    });
}

function displayResults(results) {
    const modalContent = document.getElementById('resultsContent');
    
    if (!results || !results.length) {
        alert('No results data available');
        return;
    }
    
    // Calculate total votes for the entire election
    const totalVotes = results.reduce((sum, result) => sum + parseInt(result.votes_received), 0);
    
    let html = `<h2>${results[0].Election_title} - Results</h2>`;
    
    // Group results by ward
    const wardResults = {};
    results.forEach(result => {
        if (!wardResults[result.ward_name]) {
            wardResults[result.ward_name] = [];
        }
        wardResults[result.ward_name].push(result);
    });
    
    for (const ward in wardResults) {
        const wardTotalVotes = wardResults[ward].reduce((sum, r) => sum + parseInt(r.votes_received), 0);
        const maxVotes = Math.max(...wardResults[ward].map(r => parseInt(r.votes_received)));
        
        html += `<div class="ward-results">
            <h3>${ward}</h3>
            <div class="ward-total-votes">Total Votes Cast: ${wardTotalVotes}</div>
            <div class="candidates-grid">`;
        
        wardResults[ward].forEach(result => {
            const isWinner = parseInt(result.votes_received) === maxVotes && parseInt(result.votes_received) > 0;
            const winnerClass = isWinner ? 'winner' : '';
            const votePercentage = wardTotalVotes > 0 
                ? ((result.votes_received / wardTotalVotes) * 100).toFixed(2)
                : 0;
            
            html += `
                <div class="candidate-card ${winnerClass}">
                    ${isWinner ? '<div class="winner-badge">Winner</div>' : ''}
                    <div class="candidate-details">
                        <h4>${result.name}</h4>
                        <p class="party-name">${result.is_independent === 1 ? 'Independent Candidate' : result.party_name}</p>
                        <div class="votes-info ${result.votes_received === '0' ? 'zero-votes' : ''}">
                            <div class="votes-count">
                                <span class="number">${result.votes_received}</span>
                                <span class="label">Votes Received</span>
                            </div>
                            <div class="vote-percentage">
                                <span class="percentage">${votePercentage}%</span>
                                <span class="label">of Ward Votes</span>
                            </div>
                            <div class="vote-bar">
                                <div class="vote-bar-fill" style="width: ${votePercentage}%"></div>
                            </div>
                        </div>
                    </div>
                </div>`;
        });
        
        html += '</div></div>';
    }
    
    modalContent.innerHTML = html;
}

function openResultsModal() {
    document.getElementById('resultsModal').style.display = 'block';
    document.body.classList.add('modal-open');
}

function closeResultsModal() {
    document.getElementById('resultsModal').style.display = 'none';
    document.body.classList.remove('modal-open');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('resultsModal');
    if (event.target == modal) {
        closeResultsModal();
    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    });
});
</script>
</body>
</html>