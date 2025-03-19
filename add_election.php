<?php
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: loginadmin.php");
    exit();
}

// Fetch all wards for checkboxes
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
    
    // Validate
    if ($end_date < $start_date) {
        $_SESSION['error'] = "End date cannot be before start date!";
    } elseif (empty($_POST['ward_ids'])) {
        $_SESSION['error'] = "Please select at least one ward!";
    } else {
        // Add new election
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Election</title>
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .add-election-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .checkbox-group {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            background: #fff;
        }
        .checkbox-item {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        .checkbox-item input[type="checkbox"] {
            margin-right: 10px;
            width: 16px;
            height: 16px;
        }
        .checkbox-item label {
            cursor: pointer;
            user-select: none;
        }
        .checkbox-item:hover {
            background-color: #f5f5f5;
        }
        .btn {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #45a049;
        }
        .back-btn {
            background: #666;
            margin-right: 10px;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add New Election</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="add-election-section">
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

                <a href="manage_elections.php" class="btn back-btn">Back</a>
                <button type="submit" name="save_election" class="btn">Save Election</button>
            </form>
        </div>
    </div>
</body>
</html> 