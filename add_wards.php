        <?php
        session_start();
       include 'db.php';
        
       if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: loginadmin.php");
        exit();
    }
        // Initialize variables
        $wardName = "";
        $error = "";
        $success = "";
        
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_ward"])) {
    // Get ward name from form
    $wardName =$_POST["ward_name"];
    
    // Validate input
    if (empty($wardName)) {
        $errorMessage = "Ward name cannot be empty";
    } elseif (strlen($wardName) > 100) {
        $errorMessage = "Ward name cannot exceed 100 characters";
    } else {
        // Connect to database
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        // Check connection
        if ($conn->connect_error) {
            $errorMessage = "Connection failed: " . $conn->connect_error;
        } else {
            // Prepare and execute the SQL statement
            $stmt = $conn->prepare("INSERT INTO wards (ward_name) VALUES (?)");
            $stmt->bind_param("s", $wardName);
            
            if ($stmt->execute()) {
                $successMessage = "Ward added successfully!";
            } else {
                $errorMessage = "Error: " . $stmt->error;
            }
            
            // Close statement and connection
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Wards</title>
    <style>
       
        .container {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        input.invalid {
            border-color: red;
        }
        .success-message {
            color: green;
            margin-top: 10px;
            padding: 10px;
            background-color: #f0fff0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add New Ward</h1>
        
        
        <!-- Display error or success message -->
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="wardName">Ward Name:</label>
                <input type="text" id="wardName" name="wardName" value="<?php echo htmlspecialchars($wardName); ?>" 
                       placeholder="Enter ward name" oninput="validateWardName()">
                <div id="wardNameError" class="error" style="display: none;">
                    Ward name is required and must be Minimum 10 characters
                </div>
            </div>
            <button type="submit" id="submitButton">Add Ward</button>
        </form>
    </div>

    <script>
        function validateWardName() {
            const wardNameInput = document.getElementById('wardName');
            const wardNameError = document.getElementById('wardNameError');
            const wardName = wardNameInput.value.trim();
            const isValid = wardName.length >= 3 && wardName.length <= 100;
            
            if (!isValid) {
                wardNameInput.classList.add('invalid');
                wardNameError.style.display = 'block';
            } else {
                wardNameInput.classList.remove('invalid');
                wardNameError.style.display = 'none';
            }
        }
    </script>
</body>
</html>