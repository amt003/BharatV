<?php
include 'db.php';



// $t="TRUNCATE TABLE users";
// $t="CREATE TABLE if not exists candidate_applications (
//     application_id INT PRIMARY KEY AUTO_INCREMENT,
//     id INT NOT NULL,                              -- Changed from 'id'
//     party_id INT NOT NULL,
//     election_id INT NOT NULL,  
//        ward_id INT NOT NULL,                          -- Added missing column
//     application_form TEXT NOT NULL,
//     application_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,     -- Changed from 'submitted_at'
//     FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE,
//     FOREIGN KEY (party_id) REFERENCES parties(party_id) ON DELETE CASCADE,
//     FOREIGN KEY (election_id) REFERENCES elections(election_id) ON DELETE CASCADE,
//         FOREIGN KEY (ward_id) REFERENCES wards(ward_id) ON DELETE CASCADE 
// );";



// $t="CREATE TABLE IF NOT EXISTS contesting_candidates (
//     contesting_id INT PRIMARY KEY AUTO_INCREMENT,
//     id INT NOT NULL,
//     party_id INT NOT NULL,
//     ward_id INT NOT NULL,
//     election_id INT NOT NULL,
//     added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     FOREIGN KEY (id) REFERENCES users(id),
//     FOREIGN KEY (party_id) REFERENCES parties(party_id),
//     FOREIGN KEY (ward_id) REFERENCES wards(ward_id),
//     FOREIGN KEY (election_id) REFERENCES elections(election_id)
// );";
 
// // $t="CREATE TABLE IF NOT EXISTS admin(
// admin_id INT PRIMARY KEY AUTO_INCREMENT ,
// name VARCHAR(255) not null,
// email  VARCHAR(255) not null unique,
// password varchar(255) not null)";

// Add this query at the beginning of your file, after the database connection
// First add the column without constraints
$add_column = "ALTER TABLE votes 
    ADD COLUMN ward_id INT NULL";

try {
    if ($conn->query($add_column)) {
        echo "Successfully added ward_id column to votes table<br>";
        
        // Update the ward_id values from contesting_candidates
        $update_ward_id = "UPDATE votes v 
            JOIN contesting_candidates cc ON v.contesting_id = cc.contesting_id 
            SET v.ward_id = cc.ward_id";

        if ($conn->query($update_ward_id)) {
            echo "Successfully updated ward_id values<br>";
            
            // Now add the NOT NULL constraint and foreign key
            $add_constraints = "ALTER TABLE votes 
                MODIFY ward_id INT NOT NULL,
                ADD FOREIGN KEY (ward_id) REFERENCES wards(ward_id) ON DELETE CASCADE";
                
            if ($conn->query($add_constraints)) {
                echo "Successfully added constraints to ward_id<br>";
            }
        }
    }
} catch (Exception $e) {
    // If column already exists, ignore the error
    if (!strpos($e->getMessage(), 'Duplicate column name')) {
        echo "Error: " . $e->getMessage() . "<br>";
    }
}
// $t="CREATE TABLE IF NOT EXISTS results (
//     result_id INT PRIMARY KEY AUTO_INCREMENT,
//     election_id INT NOT NULL,
//     ward_id INT NOT NULL,
//     contesting_id INT NOT NULL,
//     votes_received INT NOT NULL,
//     is_winner BOOLEAN DEFAULT FALSE,
//     FOREIGN KEY (election_id) REFERENCES elections(election_id) ON DELETE CASCADE,
//     FOREIGN KEY (ward_id) REFERENCES wards(ward_id) ON DELETE CASCADE,
//     FOREIGN KEY (contesting_id) REFERENCES contesting_candidates(contesting_id) ON DELETE CASCADE
// );";
// $t="ALTER TABLE contesting_candidates
// DROP COLUMN independent_party_name,
// DROP COLUMN independent_party_symbol;";
// $t="TRUNCATE table contesting_candidates";
// -- Add independent party symbol column
// $t="ALTER TABLE contesting_candidates 
// ADD COLUMN independent_party_symbol VARCHAR(100) NULL;";
// $t="CREATE TABLE votes (
//     vote_id INT PRIMARY KEY AUTO_INCREMENT,
//     id INT NOT NULL,
//     contesting_id INT NOT NULL,
//     casted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//    FOREIGN KEY (id) REFERENCES users(id),
//     FOREIGN KEY (contesting_id) REFERENCES contesting_candidates(contesting_id),
   
//     UNIQUE KEY unique_vote (id, contesting_id)
   
// );";
// -- Add index for faster token lookups
// CREATE INDEX idx_verification_token ON users(verification_token);
// $t="CREATE TABLE IF NOT EXISTS parties(
//     party_id INT PRIMARY KEY AUTO_INCREMENT,
//     party_name VARCHAR(100) UNIQUE NOT NULL,
//     party_symbol VARCHAR(255) NOT NULL,,
//     password VARCHAR(255) NOT NULL    );";
//     $t="CREATE TABLE IF NOT EXISTS users (
// user_id	INT	PRIMARY KEY AUTO_INCREMENT,
// name VARCHAR(100) NOT NULL,
// email VARCHAR(100) UNIQUE NOT NULL,
// dob DATE NOT NULL,
// password VARCHAR(255)	NOT NULL,
// role	ENUM('voter', 'candidate')	NOT NULL,
// phone VARCHAR(15)	NOT NULL,
// address	VARCHAR(255) NOT NULL,
// ward_id	INT,	
// FOREIGN KEY(ward_id) REFERENCES wards(ward_id),
// aadhaar_number VARCHAR(12) UNIQUE NOT NULL,
// aadhaar_file VARCHAR(255) NOT NULL,
// approved_by_admin	BOOLEAN	DEFAULT FALSE,
// created_at	TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// // );";
// $t = "INSERT INTO parties (party_name, party_symbol, password, email) VALUES
//     ('Independent Candidate', '1.png', '" . password_hash('independent123', PASSWORD_DEFAULT) . "', 'independent@gmail.com'),
//     ('Indian National Congress', '2.png', '" . password_hash('congress123', PASSWORD_DEFAULT) . "', 'congress@gmail.com'),
//     ('Bharatiya Janata Party', '3.png', '" . password_hash('bjp123', PASSWORD_DEFAULT) . "', 'bjp@gmail.com'),
//     ('Communist Party of India', '4.png', '" . password_hash('cpi123', PASSWORD_DEFAULT) . "', 'cpi@gmail.com')";
// $t="INSERT INTO admin (name, email, password) VALUES 
// ('Admin User', 'admin123@gmail.com','admin12345')";
// $t="ALTER TABLE parties ADD COLUMN email VARCHAR(255) NOT NULL unique";

    // if(mysqli_query($conn,$t)){
    //     echo"<br>Table Created";
    // }else{
    //     echo"<br>Table Not Created". mysqli_error($conn);
    // }
    

   
mysqli_close($conn);
?>
