<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: loginadmin.php");
    exit();
}

// Fetch all wards
$wards = [];
$ward_sql = "SELECT ward_id, ward_name FROM wards ORDER BY ward_id";
$ward_result = $conn->query($ward_sql);
if ($ward_result->num_rows > 0) {
    while($row = $ward_result->fetch_assoc()) {
        $wards[] = $row;
    }
}

// Handle Add Operation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_election'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $ward_ids = isset($_POST['ward_ids']) ? implode(',', $_POST['ward_ids']) : '';
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    if ($end_date < $start_date) {
        $_SESSION['error'] = "End date cannot be before start date!";
    } elseif (empty($_POST['ward_ids'])) {
        $_SESSION['error'] = "Please select at least one ward!";
    } else {
        $sql = "INSERT INTO elections (Election_title, Description, ward_ids, start_date, end_date) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $title, $description, $ward_ids, $start_date, $end_date);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Election added successfully!";
            header("Location: manage_elections.php");
            exit();
        } else {
            $_SESSION['error'] = "Error saving election.";
        }
    }
}

// Handle Update Operation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_election'])) {
    $election_id = $_POST['election_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $ward_ids = isset($_POST['ward_ids']) ? implode(',', $_POST['ward_ids']) : '';
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    if ($end_date < $start_date) {
        $_SESSION['error'] = "End date cannot be before start date!";
    } elseif (empty($_POST['ward_ids'])) {
        $_SESSION['error'] = "Please select at least one ward!";
    } else {
        // Calculate status based on dates
        $current_date = date('Y-m-d');
        $status = '';
        if ($current_date < $start_date) {
            $status = 'Scheduled';
        } elseif ($current_date > $end_date) {
            $status = 'Completed';
        } else {
            $status = 'Ongoing';
        }

        $sql = "UPDATE elections SET 
                Election_title = ?, 
                Description = ?, 
                ward_ids = ?,
                start_date = ?, 
                end_date = ?,
                status = ? 
                WHERE election_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $title, $description, $ward_ids, $start_date, $end_date, $status, $election_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Election updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating election.";
        }
    }
}

// Handle Delete Operation
if (isset($_POST['delete_election'])) {
    $election_id = $_POST['election_id'];

    // First, delete any related records in contesting_candidates
    $delete_candidates_stmt = $conn->prepare("DELETE FROM contesting_candidates WHERE election_id = ?");
    $delete_candidates_stmt->bind_param("i", $election_id);
    $delete_candidates_stmt->execute();

    // Now delete the election
    $delete_sql = "DELETE FROM elections WHERE election_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $election_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Election deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting election.";
    }
}

// Function to get ward names from IDs
function getWardNames($ward_ids, $wards) {
    $selected_wards = explode(',', $ward_ids);
    $ward_names = [];
    foreach ($wards as $ward) {
        if (in_array($ward['ward_id'], $selected_wards)) {
            $ward_names[] = $ward['ward_id'] . ' - ' . $ward['ward_name'];
        }
    }
    return implode(', ', $ward_names);
}

// Function to determine election status
function getElectionStatus($start_date, $end_date) {
    $current_date = date('Y-m-d');
    
    if ($current_date < $start_date) {
        return 'Scheduled';
    } elseif ($current_date > $end_date) {
        return 'Completed';
    } else {
        return 'Ongoing';
    }
}

// Fetch all elections
$elections = [];
$sql = "SELECT * FROM elections ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['ward_names'] = getWardNames($row['ward_ids'], $wards);
        // Calculate and set the status
        $row['status'] = getElectionStatus($row['start_date'], $row['end_date']);
        $elections[] = $row;
    }
}

// Display messages
if (isset($_SESSION['message'])) {
    echo '<div class="message success">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="message error">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Elections</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* General Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:white;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 25px;
        }

        h2 {
            color: #333;
            font-size: 2em;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            padding-bottom: 10px;
        }

        h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: #4CAF50;
            border-radius: 2px;
        }

        h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        /* Add these animation keyframes at the top of your CSS */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Styles */
        .add-election-section {
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            animation: fadeIn 0.6s ease-out;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="date"]:focus,
        .form-group textarea:focus {
            border-color: #4CAF50;
            outline: none;
        }

        /* Checkbox Styles */
        .checkbox-group {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            background: #fff;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            padding: 5px;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }

        .checkbox-item:hover {
            background-color: #f5f5f5;
        }

        .checkbox-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin: 0 10px 0 0;
            cursor: pointer;
        }

        .checkbox-item label {
            margin: 0;
            cursor: pointer;
            display: inline-block;
            line-height: 1.2;
            color: #333;
        }

        /* Optional: Add these styles for better checkbox alignment */
        .checkbox-item input[type="checkbox"] {
            position: relative;
            top: 1px;
        }

        /* Table Styles */
        .elections-list {
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            animation: fadeIn 0.6s ease-out 0.2s; /* 0.2s delay for staggered effect */
            opacity: 0;
            animation-fill-mode: forwards;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f5f5f5;
            color: #333;
            font-weight: 600;
            font-size: 0.9em;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        /* Action Button Styles */
        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn i {
            font-size: 14px;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-primary:hover {
            background: #45a049;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        /* Message Styles */
        .message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
            font-weight: 500;
            text-align: center;
            animation: fadeIn 0.5s ease-out;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow-y: auto;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .modal::-webkit-scrollbar {
            display: none;
        }

        .modal-content {
            background: #fff;
            margin: 30px auto;
            padding: 30px;
            width: 70%;
            max-width: 800px;
            border-radius: 8px;
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            max-height: 90vh;
            overflow-y: auto;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .modal-content::-webkit-scrollbar {
            display: none;
        }

        /* Ensure bottom padding for scrolling content */
        .modal-content form {
            padding-bottom: 20px;
        }

        .close {
            position: absolute;
            right: 25px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            color: #666;
            cursor: pointer;
        }

        .close:hover {
            color: #333;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .modal-content {
                width: 90%;
                margin: 20px auto;
                padding: 20px;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .btn {
                padding: 8px 16px;
                font-size: 13px;
            }
        }
        .btn:disabled {
    background-color: #cccccc !important;
    cursor: not-allowed !important;
    opacity: 0.7;
}

.back-button-container {
    margin-bottom: 20px;
    text-align: left;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
     
    background-color:rgb(76, 175, 79);
    color: orange;
    text-decoration: none;
    border-radius: 4px;
    border: 1px solid #ddd;
    font-weight: 500;
    transition: all 0.3s ease;
}

.back-button:hover {
    background-color:lightgreen;
    color :rgb(76, 175, 79);    
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.back-button i {
    font-size: 14px;
}

/* Media query for smaller screens */
@media (max-width: 768px) {
    .back-button {
        padding: 8px 12px;
        font-size: 14px;
    }
}
    </style>
</head>
<body>

    <div class="container">
        <div class="back-button-container">
    <a href="admin.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Admin
    </a>
</div>
        <h2>Manage Elections</h2>
        

        <!-- Add Election Form -->
        <div class="add-election-section">
            <h3>Add New Election</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="title">Election Title</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label>Select Wards</label>
                    <div class="checkbox-group">
                        <?php foreach ($wards as $ward): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" 
                                       name="ward_ids[]" 
                                       id="ward_<?php echo $ward['ward_id']; ?>" 
                                       value="<?php echo $ward['ward_id']; ?>">
                                <label for="ward_<?php echo $ward['ward_id']; ?>">
                                    <?php echo htmlspecialchars($ward['ward_id'] . ' - ' . $ward['ward_name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>

                <button type="submit" name="save_election" class="btn btn-primary">Save Election</button>
            </form>
        </div>

        <!-- Elections List -->
        <div class="elections-list">
            <h3> Elections List</h3>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Wards</th>
                        <th>Description</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($elections as $election): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($election['Election_title']); ?></td>
                            <td><?php echo htmlspecialchars($election['ward_names']); ?></td>
                            <td><?php echo htmlspecialchars($election['Description']); ?></td>
                            <td><?php echo $election['start_date']; ?></td>
                            <td><?php echo $election['end_date']; ?></td>
                            <td><?php echo $election['status']; ?></td>
                            <td>
    <div class="action-buttons">
        <button class="btn btn-primary" 
                onclick='showEditModal(<?php echo json_encode($election); ?>)'
                <?php echo ($election['status'] === 'Completed') ? 'disabled style="background-color: #cccccc; cursor: not-allowed;"' : ''; ?>>
            <i class="fas fa-edit"></i> Edit    
        </button>
        <form method="POST" style="margin: 0;">
            <input type="hidden" name="election_id" value="<?php echo $election['election_id']; ?>">
            <button type="submit" name="delete_election" class="btn btn-danger" 
                    onclick="return confirm('Are you sure you want to delete this election?')">
                <i class="fas fa-trash"></i> Delete
            </button>
        </form>
    </div>
</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3>Edit Election</h3>
            <form method="POST">
                <input type="hidden" id="edit_election_id" name="election_id">
                
                <div class="form-group">
                    <label for="edit_title">Election Title</label>
                    <input type="text" id="edit_title" name="title" required>
                </div>

                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label>Select Wards</label>
                    <div class="checkbox-group">
                        <?php foreach ($wards as $ward): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" 
                                       name="ward_ids[]" 
                                       id="edit_ward_<?php echo $ward['ward_id']; ?>" 
                                       value="<?php echo $ward['ward_id']; ?>">
                                <label for="edit_ward_<?php echo $ward['ward_id']; ?>">
                                    <?php echo htmlspecialchars($ward['ward_id'] . ' - ' . $ward['ward_name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_start_date">Start Date</label>
                    <input type="date" id="edit_start_date" name="start_date" required>
                </div>

                <div class="form-group">
                    <label for="edit_end_date">End Date</label>
                    <input type="date" id="edit_end_date" name="end_date" required>
                </div>

                <button type="submit" name="update_election" class="btn btn-primary">Update Election</button>
            </form>
        </div>
    </div>

    <script>
        function showEditModal(election) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('edit_election_id').value = election.election_id;
            document.getElementById('edit_title').value = election.Election_title;
            document.getElementById('edit_description').value = election.Description;
            document.getElementById('edit_start_date').value = election.start_date;
            document.getElementById('edit_end_date').value = election.end_date;
            
            // Clear all checkboxes first
            document.querySelectorAll('#editModal input[name="ward_ids[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Set selected wards
            const wardIds = election.ward_ids.split(',');
            wardIds.forEach(wardId => {
                const checkbox = document.getElementById('edit_ward_' + wardId);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
        }

        
//add election live and normal validations
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const form = document.querySelector('form');
    const checkboxes = document.querySelectorAll('input[name="ward_ids[]"]');
    
    // Get today's date in YYYY-MM-DD format
    const today = new Date().toISOString().split('T')[0];
    
    // Set min attribute for date inputs
    startDateInput.min = today;
    endDateInput.min = today;
    
    // Set default dates
    startDateInput.value = today;
    endDateInput.value = today;
    
    // Create error message element function
    function createErrorElement(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'validation-error';
        errorDiv.textContent = message;
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '0.85rem';
        errorDiv.style.marginTop = '5px';
        return errorDiv;
    }
    
    // Clear error message
    function clearError(element) {
        const parent = element.parentElement;
        const existingError = parent.querySelector('.validation-error');
        if (existingError) {
            parent.removeChild(existingError);
        }
    }
    
    // Show error message
    function showError(element, message) {
        clearError(element);
        const errorElement = createErrorElement(message);
        element.parentElement.appendChild(errorElement);
    }
    
    // Validate title (required and min length)
    function validateTitle() {
        clearError(titleInput);
        
        if (titleInput.value.trim() === '') {
            showError(titleInput, 'Election title is required');
            return false;
        } else if (titleInput.value.trim().length < 5) {
            showError(titleInput, 'Title should be at least 5 characters long');
            return false;
        }
        
        return true;
        }

    // Validate description (not required but min length if provided)
    function validateDescription() {
        clearError(descriptionInput);
        
        if (descriptionInput.value.trim() !== '' && descriptionInput.value.trim().length < 10) {
            showError(descriptionInput, 'Description should be at least 10 characters long or left empty');
            return false;
        }
        
        return true;
    }
    
    // Validate at least one ward is selected
    function validateWards() {
        // Get container for potential error message (parent of checkboxes)
        const container = document.querySelector('.checkbox-group').parentElement;
        clearError(container);
        
        let isChecked = false;
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                isChecked = true;
            }
        });
        
        if (!isChecked) {
            const errorElement = createErrorElement('Please select at least one ward');
            container.appendChild(errorElement);
            return false;
        }
        
        return true;
    }
    
    // Validate dates
    function validateDates() {
        clearError(startDateInput);
        clearError(endDateInput);
        
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
                
        // Check if dates are selected
        if (!startDate) {
            showError(startDateInput, 'Start date is required');
            return false;
        }
        
        if (!endDate) {
            showError(endDateInput, 'End date is required');
            return false;
        }
        
        // Check if start date is not before today
        if (startDate < today) {
            showError(startDateInput, 'Start date cannot be in the past');
            return false;
        }
        
        // Check if end date is not before start date
        if (endDate < startDate) {
            showError(endDateInput, 'End date cannot be before start date');
            return false;
        }
        
        return true;
    }
    
    // Add event listeners for live validation
    titleInput.addEventListener('blur', validateTitle);
    titleInput.addEventListener('input', function() {
        if (titleInput.value.trim().length >= 5) {
            clearError(titleInput);
        }
    });
    
    descriptionInput.addEventListener('blur', validateDescription);
    descriptionInput.addEventListener('input', function() {
        if (descriptionInput.value.trim().length >= 10 || descriptionInput.value.trim() === '') {
            clearError(descriptionInput);
        }
    });
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', validateWards);
    });
    
    startDateInput.addEventListener('change', validateDates);
    endDateInput.addEventListener('change', validateDates);
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        // Run all validations
        const isTitleValid = validateTitle();
        const isDescriptionValid = validateDescription();
        const areWardsValid = validateWards();
        const areDatesValid = validateDates();
        
        // Prevent form submission if any validation fails
        if (!isTitleValid || !isDescriptionValid || !areWardsValid || !areDatesValid) {
            e.preventDefault();
            
            // Scroll to the first error
            const firstError = document.querySelector('.validation-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});

//live validation for edit modal
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const editForm = document.querySelector('#editModal form');
    const titleInput = document.getElementById('edit_title');
    const descriptionInput = document.getElementById('edit_description');
    const startDateInput = document.getElementById('edit_start_date');
    const endDateInput = document.getElementById('edit_end_date');
    const checkboxes = document.querySelectorAll('#editModal input[name="ward_ids[]"]');

    // Get today's date in YYYY-MM-DD format
    const today = new Date().toISOString().split('T')[0];

    // Set min attributes for date inputs
    startDateInput.min = today;
    endDateInput.min = today;

    // Create error message element function
    function createErrorElement(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'validation-error';
        errorDiv.textContent = message;
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '0.85rem';
        errorDiv.style.marginTop = '5px';
        return errorDiv;
        }

    // Clear error message
    function clearError(element) {
        const parent = element.parentElement;
        const existingError = parent.querySelector('.validation-error');
        if (existingError) {
            parent.removeChild(existingError);
        }
        }

    // Show error message
    function showError(element, message) {
        clearError(element);
        const errorElement = createErrorElement(message);
        element.parentElement.appendChild(errorElement);
    }

    // Validate title (required and min length)
    function validateTitle() {
        clearError(titleInput);
        if (titleInput.value.trim().length < 5) {
            showError(titleInput, 'Title must be at least 5 characters long');
            return false;
        }
        return true;
    }

    // Validate description (optional but min length if provided)
    function validateDescription() {
        clearError(descriptionInput);
        if (descriptionInput.value.trim() !== '' && descriptionInput.value.trim().length < 10) {
            showError(descriptionInput, 'Description should be at least 10 characters long or left empty');
            return false;
        }
        return true;
        }

    // Validate at least one ward is selected
    function validateWards() {
        const container = document.querySelector('#editModal .checkbox-group').parentElement;
        clearError(container);
        let isChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);

        if (!isChecked) {
            showError(container, 'Please select at least one ward');
            return false;
        }
        return true;
        }

    // Validate dates
    function validateDates() {
        clearError(startDateInput);
        clearError(endDateInput);

        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        if (!startDate) {
            showError(startDateInput, 'Start date is required');
            return false;
        }

        if (!endDate) {
            showError(endDateInput, 'End date is required');
            return false;
        }

        if (startDate < today) {
            showError(startDateInput, 'Start date cannot be in the past');
            return false;
        }

        if (endDate <= startDate) {
            showError(endDateInput, 'End date must be after start date');
            return false;
        }

        return true;
    }

    // Live validation event listeners
    titleInput.addEventListener('blur', validateTitle);
    titleInput.addEventListener('input', function() {
        if (titleInput.value.trim().length >= 5) clearError(titleInput);
    });

    descriptionInput.addEventListener('blur', validateDescription);
    descriptionInput.addEventListener('input', function() {
        if (descriptionInput.value.trim().length >= 10 || descriptionInput.value.trim() === '') {
            clearError(descriptionInput);
            }
    });

    checkboxes.forEach(checkbox => checkbox.addEventListener('change', validateWards));
    startDateInput.addEventListener('change', validateDates);
    endDateInput.addEventListener('change', validateDates);

    // Form submission validation
    editForm.addEventListener('submit', function(e) {
        const isTitleValid = validateTitle();
        const isDescriptionValid = validateDescription();
        const areWardsValid = validateWards();
        const areDatesValid = validateDates();

        if (!isTitleValid || !isDescriptionValid || !areWardsValid || !areDatesValid) {
            e.preventDefault();
            const firstError = document.querySelector('#editModal .validation-error');
            if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
    </script>
</body>
</html> 